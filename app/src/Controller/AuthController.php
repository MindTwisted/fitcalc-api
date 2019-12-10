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
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AuthController
 *
 * @package App\Controller
 *
 * @Route("/{_locale}/api/auth", requirements={"_locale": "en|ru"})
 */
class AuthController extends AbstractController
{
    /**
     * @Route("/", name="auth", methods={"GET"})
     *
     * @IsGranted(User::ROLE_USER)
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
     * @param TranslatorInterface $translator
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
        EmailService $emailService,
        TranslatorInterface $translator
    ): JsonResponse
    {
        $user = $userService->createUserFromRequest($request);
        $user->setRoles([User::ROLE_APP_USER]);
        $validationService->validate($user);
        $userService->encodeUserPassword($user);

        try {
            $emailService->sendEmailConfirmationMessage($request, $user);
        } catch (TransportExceptionInterface $exception) {
            return $this->json(
                ['message' => $translator->trans('Unexpected error has been occurred, please try again later.')],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'message' => $translator->trans(
                'User %fullname% has been registered. Please confirm your email address.',
                ['%fullname%' => $user->getFullname()]
            ),
            'data' => compact('user')
        ]);
    }

    /**
     * @Route("/register_email_confirmation/{hash}", name="registerEmailConfirmation", methods={"GET"})
     *
     * @param string $hash
     * @param TranslatorInterface $translator
     *
     * @return JsonResponse
     *
     * @throws NonUniqueResultException
     */
    public function registerEmailConfirmation(string $hash, TranslatorInterface $translator): JsonResponse
    {
        /** @var EmailRepository $emailRepository */
        $emailRepository = $this->getDoctrine()->getRepository(Email::class);
        $email = $emailRepository->findNotVerifiedOneByHash($hash);

        if (!$email) {
            return $this->json(
                ['message' => $translator->trans('Forbidden.')],
                JsonResponse::HTTP_FORBIDDEN
            );
        }

        $email->setVerified(true);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($email);
        $entityManager->flush();

        return $this->json(
            [
                'message' => $translator->trans(
                    'Email %email% has been confirmed.',
                    ['%email%' => $email->getEmail()]
                )
            ]
        );
    }

    /**
     * @Route("/login", name="login", methods={"POST"})
     *
     * @param Request $request
     * @param UserService $userService
     * @param AuthService $authService
     * @param TranslatorInterface $translator
     *
     * @return JsonResponse
     *
     * @throws NonUniqueResultException
     * @throws ValidationException
     * @throws Exception
     */
    public function login(
        Request $request,
        UserService $userService,
        AuthService $authService,
        TranslatorInterface $translator
    ): JsonResponse
    {
        $user = $userService->getUserByUsernameOrEmail($request->get('username', ''), $request->get('email', ''));

        if (!$user) {
            return $this->json(
                ['message' => $translator->trans('Invalid credentials.')],
                JsonResponse::HTTP_FORBIDDEN
            );
        }

        $isPasswordValid = $authService->isPasswordValid($user, $request->get('password'));

        if (!$isPasswordValid) {
            return $this->json(
                ['message' => $translator->trans('Invalid credentials.')],
                JsonResponse::HTTP_FORBIDDEN
            );
        }

        $accessToken = $authService->generateAccessToken($user);
        $refreshToken = $userService->storeRefreshToken(
            $user,
            $authService->generateRefreshToken(),
            $request->server->get('HTTP_USER_AGENT'),
            $request->server->get('REMOTE_ADDR')
        );

        return $this->json([
            'message' => $translator->trans(
                'User %fullname% has been successfully logged-in.',
                ['%fullname%' => $user->getFullname()]
            ),
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
     * @Route("/refresh", name="refreshAccessToken", methods={"POST"})
     *
     * @param Request $request
     * @param AuthService $authService
     * @param TranslatorInterface $translator
     *
     * @return JsonResponse
     *
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function refreshAccessToken(
        Request $request,
        AuthService $authService,
        TranslatorInterface $translator
    ): JsonResponse
    {
        $token = $request->get('refresh_token');

        if (!$token) {
            return $this->json(
                ['message' => $translator->trans('Please provide a refresh token.')],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        /** @var RefreshTokenRepository $refreshTokenRepository */
        $refreshTokenRepository = $this->getDoctrine()->getRepository(RefreshToken::class);
        $refreshToken = $refreshTokenRepository->findOneNotExpiredAndNotDeletedByTokenJoinedToUser($token);

        if (!$refreshToken) {
            return $this->json(
                ['message' => $translator->trans('Refresh token is invalid.')],
                JsonResponse::HTTP_FORBIDDEN
            );
        }

        $user = $refreshToken->getUser();
        $accessToken = $authService->generateAccessToken($user);

        return $this->json([
            'message' => $translator->trans(
                'Access token for user %fullname% has been successfully refreshed.',
                ['%fullname%' => $user->getFullname()]
            ),
            'data' => ['access_token' => $accessToken]
        ]);
    }
}
