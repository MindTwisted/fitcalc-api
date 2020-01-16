<?php

namespace App\Controller;

use App\Entity\EmailConfirmation;
use App\Entity\RefreshToken;
use App\Entity\User;
use App\Exception\ValidationException;
use App\Repository\EmailConfirmationRepository;
use App\Repository\RefreshTokenRepository;
use App\Services\AuthService;
use App\Services\UserService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
     * @param TranslatorInterface $translator
     *
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function register(
        Request $request,
        UserService $userService,
        TranslatorInterface $translator
    ): JsonResponse
    {
        $user = $userService->registerUser($request);

        return $this->json([
            'message' => $translator->trans(
                'User %name% has been registered. Please confirm your email address.',
                ['%name%' => $user->getName()]
            ),
            'data' => compact('user')
        ]);
    }

    /**
     * @Route("/email_confirmation/{hash}", name="emailConfirmation", methods={"GET"})
     *
     * @param string $hash
     * @param TranslatorInterface $translator
     * @param UserService $userService
     *
     * @return JsonResponse
     *
     * @throws NonUniqueResultException
     */
    public function emailConfirmation(
        string $hash,
        TranslatorInterface $translator,
        UserService $userService
    ): JsonResponse
    {
        $user = $userService->confirmEmail($hash);

        return $this->json(
            [
                'message' => $translator->trans(
                    'Email %email% has been confirmed.',
                    ['%email%' => $user->getEmail()]
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
        $user = $userService->getUserByEmail($request->get('email', ''));

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
                'User %name% has been successfully logged-in.',
                ['%name%' => $user->getName()]
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
                'Access token for user %name% has been successfully refreshed.',
                ['%name%' => $user->getName()]
            ),
            'data' => ['access_token' => $accessToken]
        ]);
    }
}
