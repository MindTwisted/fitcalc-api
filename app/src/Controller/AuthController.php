<?php

namespace App\Controller;

use App\Entity\Email;
use App\Entity\RefreshToken;
use App\Entity\User;
use App\Exception\ValidationException;
use App\Repository\EmailRepository;
use App\Repository\RefreshTokenRepository;
use App\Services\AuthService;
use App\Services\EmailService;
use App\Services\UserService;
use App\Services\ValidationService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AuthController
 *
 * @package App\Controller
 *
 * @Route("/api/auth")
 */
class AuthController extends AbstractController
{
    /**
     * @Route("/", name="auth", methods={"GET"})
     *
     * @IsGranted("ROLE_USER")
     *
     * @return JsonResponse
     */
    public function index()
    {
        $user = $this->getUser();

        return $this->json(['data' => compact('user')]);
    }

    /**
     * @Route("/register", name="register", methods={"POST"})
     *
     * @param Request $request
     * @param UserService $userService
     * @param ValidationService $validationService
     * @param EmailService $emailService
     *
     * @return JsonResponse
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function register(
        Request $request,
        UserService $userService,
        ValidationService $validationService,
        EmailService $emailService
    ): JsonResponse
    {
        $user = $userService->createUserFromRequest($request);
        $validationService->validate($user);
        $userService->encodeUserPassword($user);

        try {
            $emailService->sendEmailConfirmationMessage($request, $user);
        } catch (TransportExceptionInterface $exception) {
            return $this->json(
                ['message' => 'Unexpected error has been occurred, please try again later.'],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'message' => 'User has been registered. Please confirm your email address.',
            'data' => compact('user')
        ]);
    }

    /**
     * @Route("/register_email_confirmation/{hash}", name="registerEmailConfirmation", methods={"GET"})
     *
     * @param string $hash
     *
     * @return JsonResponse
     *
     * @throws NonUniqueResultException
     */
    public function registerEmailConfirmation(string $hash): JsonResponse
    {
        /** @var EmailRepository $emailRepository */
        $emailRepository = $this->getDoctrine()->getRepository(Email::class);
        $email = $emailRepository->findNotVerifiedOneByHash($hash);

        if (!$email) {
            return $this->json(['message' => 'Forbidden.'], JsonResponse::HTTP_FORBIDDEN);
        }

        $email->setVerified(true);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($email);
        $entityManager->flush();

        return $this->json(['message' => sprintf('Email %s has been confirmed.', $email->getEmail())]);
    }

    /**
     * @Route("/login", name="login", methods={"POST"})
     *
     * @param Request $request
     * @param UserService $userService
     * @param AuthService $authService
     *
     * @return JsonResponse
     *
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function login(
        Request $request,
        UserService $userService,
        AuthService $authService
    ): JsonResponse
    {
        $user = $userService->getUserByUsernameOrEmail($request->get('username', ''), $request->get('email', ''));

        if (!$user) {
            return $this->json(['message' => 'Invalid credentials.'], JsonResponse::HTTP_FORBIDDEN);
        }

        $isPasswordValid = $authService->isPasswordValid($user, $request->get('password'));

        if (!$isPasswordValid) {
            return $this->json(['message' => 'Invalid credentials.'], JsonResponse::HTTP_FORBIDDEN);
        }

        $accessToken = $authService->generateAccessToken($user);
        $refreshToken = $userService->storeRefreshToken(
            $user,
            $authService->generateRefreshToken(),
            $request->server->get('HTTP_USER_AGENT'),
            $request->server->get('REMOTE_ADDR')
        );

        return $this->json([
            'message' => sprintf('User %s has been successfully logged-in.', $user->getFullname()),
            'data' => [
                'access_token' => $accessToken,
                'refresh_token' => [
                    'id' => $refreshToken->getId(),
                    'token' => $refreshToken->getToken(),
                    'expires_at' => $refreshToken->getExpiresAt()
                ]
            ]
        ]);
    }

    /**
     * @Route(
     *     "/refresh_tokens",
     *     name="getAllRefreshTokensOfCurrentUser",
     *     methods={"GET"}
     * )
     *
     * @IsGranted("ROLE_USER")
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function getAllRefreshTokensOfCurrentUser(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        /** @var RefreshTokenRepository $refreshTokenRepository */
        $refreshTokenRepository = $this->getDoctrine()->getRepository(RefreshToken::class);
        $refreshTokens = $refreshTokenRepository->findNotExpiredAndNotDeletedByUserId($user->getId());

        return $this->json(['data' => compact('refreshTokens')]);
    }

    /**
     * @Route(
     *     "/refresh_tokens/{id}",
     *     requirements={"id"="\d+"},
     *     name="deleteRefreshTokenOfCurrentUserById",
     *     methods={"DELETE"}
     * )
     *
     * @IsGranted("ROLE_USER")
     *
     * @param int $id
     *
     * @return JsonResponse
     *
     * @throws NonUniqueResultException
     */
    public function deleteRefreshTokenOfCurrentUserById(int $id): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        /** @var RefreshTokenRepository $refreshTokenRepository */
        $refreshTokenRepository = $this->getDoctrine()->getRepository(RefreshToken::class);
        $refreshToken = $refreshTokenRepository->findOneNotExpiredAndNotDeletedByIdAndUserId($id, $user->getId());

        if (!$refreshToken) {
            return $this->json(['message' => 'Not found.'], JsonResponse::HTTP_NOT_FOUND);
        }

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($refreshToken);
        $entityManager->flush();

        return $this->json(['message' => 'Refresh token has been successfully removed.']);
    }

    /**
     * @Route(
     *     "/refresh_tokens",
     *     name="deleteAllRefreshTokensOfCurrentUser",
     *     methods={"DELETE"}
     * )
     *
     * @IsGranted("ROLE_USER")
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function deleteAllRefreshTokensOfCurrentUser(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        /** @var RefreshTokenRepository $refreshTokenRepository */
        $refreshTokenRepository = $this->getDoctrine()->getRepository(RefreshToken::class);
        $refreshTokenRepository->softDeleteNotExpiredAndNotDeletedByUserId($user->getId());

        return $this->json(['message' => 'Refresh tokens have been successfully removed.']);
    }

    /**
     * @Route("/refresh", name="refreshAccessToken", methods={"POST"})
     *
     * @param Request $request
     * @param AuthService $authService
     *
     * @return JsonResponse
     *
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function refreshAccessToken(Request $request, AuthService $authService): JsonResponse
    {
        $token = $request->get('refresh_token');

        if (!$token) {
            return $this->json(['message' => 'Please provide a refresh token.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        /** @var RefreshTokenRepository $refreshTokenRepository */
        $refreshTokenRepository = $this->getDoctrine()->getRepository(RefreshToken::class);
        $refreshToken = $refreshTokenRepository->findOneNotExpiredAndNotDeletedByTokenJoinedToUser($token);

        if (!$refreshToken) {
            return $this->json(['message' => 'Refresh token is invalid.'], JsonResponse::HTTP_FORBIDDEN);
        }

        $user = $refreshToken->getUser();
        $accessToken = $authService->generateAccessToken($user);

        return $this->json([
            'message' => sprintf('Access token for user %s has been successfully refreshed.', $user->getFullname()),
            'data' => ['access_token' => $accessToken]
        ]);
    }
}
