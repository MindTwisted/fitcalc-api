<?php

namespace App\Controller;

use App\Entity\Email;
use App\Exception\ValidationException;
use App\Repository\EmailRepository;
use App\Serializer\Normalizer\UserNormalizer;
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
     * @param UserNormalizer $userNormalizer
     *
     * @return JsonResponse
     */
    public function index(UserNormalizer $userNormalizer)
    {
        return $this->json([
            'data' => [
                'user' => $userNormalizer->normalize($this->getUser())
            ]
        ]);
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
            'message' => sprintf('User %s has been registered.', $user->getFullname())
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
            return $this->json(
                [
                    'message' => 'Forbidden.'
                ],
                JsonResponse::HTTP_FORBIDDEN
            );
        }

        $email->setVerified(true);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($email);
        $entityManager->flush();

        return $this->json([
            'message' => sprintf('Email %s has been confirmed.', $email->getEmail())
        ]);
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

        $refreshToken = $authService->generateRefreshToken();
        $accessToken = $authService->generateAccessToken($user);

        $userService->storeRefreshToken($user, $refreshToken, $request->server->get('HTTP_USER_AGENT'));

        return $this->json([
            'message' => sprintf('User %s has been successfully logged-in.', $user->getFullname()),
            'data' => [
                'refresh_token' => $refreshToken,
                'access_token' => $accessToken
            ]
        ]);
    }
}
