<?php

namespace App\Controller;

use App\Entity\EmailConfirmation;
use App\Entity\PasswordRecovery;
use App\Entity\User;
use App\Exception\ValidationException;
use App\Repository\PasswordRecoveryRepository;
use App\Repository\UserRepository;
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
 * Class UserController
 *
 * @package App\Controller
 *
 * @Route("/{_locale}/api/users", requirements={"_locale": "en|ru"})
 */
class UserController extends AbstractController
{
    /**
     * @Route("/initiate_password_reset", name="initiatePasswordReset", methods={"POST"})
     *
     * @param Request $request
     * @param UserService $userService
     * @param EmailService $emailService
     * @param TranslatorInterface $translator
     *
     * @return JsonResponse
     *
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function initiatePasswordReset(
        Request $request,
        UserService $userService,
        EmailService $emailService,
        TranslatorInterface $translator
    ): JsonResponse
    {
        $email = $request->get('email');

        if (!$email) {
            return $this->json(
                ['message' => $translator->trans('Please provide an email.')],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $user = $userService->getUserByEmail($email);

        if (!$user) {
            return $this->json(
                ['message' => $translator->trans(
                    "Email %email% doesn't exist.",
                    ['%email%' => $email]
                )],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        if (!$user->isAppUser()) {
            return $this->json(
                ['message' => $translator->trans('Forbidden.')],
                JsonResponse::HTTP_FORBIDDEN
            );
        }

        if ($user->getPasswordRecoveries()->count() > 0) {
            return $this->json(
                ['message' => $translator->trans('Password recovery procedure has already been started.')],
                JsonResponse::HTTP_FORBIDDEN
            );
        }

        $passwordRecovery = new PasswordRecovery();
        $passwordRecovery->setUser($user);
        $passwordRecovery->setPrePersistDefaults();

        try {
            $emailService->sendPasswordRecoveryMessage($user, $passwordRecovery->getToken());
        } catch (TransportExceptionInterface $exception) {
            return $this->json(
                ['message' => $translator->trans('Unexpected error has been occurred, please try again later.')],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($passwordRecovery);
        $entityManager->flush();

        return $this->json(['message' => $translator->trans('Password recovery token has been successfully sent.')]);
    }

    /**
     * @Route("/confirm_password_reset", name="confirmPasswordReset", methods={"POST"})
     *
     * @param Request $request
     *
     * @param ValidationService $validationService
     * @param UserService $userService
     * @param TranslatorInterface $translator
     *
     * @return JsonResponse
     * @throws NonUniqueResultException
     * @throws ValidationException
     */
    public function confirmPasswordReset(
        Request $request,
        ValidationService $validationService,
        UserService $userService,
        TranslatorInterface $translator
    ): JsonResponse
    {
        $token = $request->get('token');

        if (!$token) {
            return $this->json(
                ['message' => $translator->trans('Please provide a token.')],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        /** @var PasswordRecoveryRepository $passwordRecoveryRepository */
        $passwordRecoveryRepository = $this->getDoctrine()->getRepository(PasswordRecovery::class);
        $passwordRecovery = $passwordRecoveryRepository->findOneByTokenJoinedToUser($token);

        if (!$passwordRecovery) {
            return $this->json(
                ['message' => $translator->trans('Token is invalid.')],
                JsonResponse::HTTP_FORBIDDEN
            );
        }

        $user = $passwordRecovery->getUser();

        if (!$user->isAppUser()) {
            return $this->json(
                ['message' => $translator->trans('Forbidden.')],
                JsonResponse::HTTP_FORBIDDEN
            );
        }

        $password = $request->get('password');

        if (!$password) {
            return $this->json(
                ['message' => $translator->trans('Please provide a password.')],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $user->setPlainPassword($password);

        $validationService->validate($user);
        $userService->encodeUserPassword($user);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->remove($passwordRecovery);
        $entityManager->flush();

        return $this->json(['message' => $translator->trans('Password has been successfully changed.')]);
    }

    /**
     * @Route("", name="getAllUsers", methods={"GET"})
     *
     * @IsGranted(User::ROLE_ADMIN, message="Access denied.")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getAllUsers(Request $request): JsonResponse
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->getDoctrine()->getRepository(User::class);
        $users = $userRepository->findAppUsersWithConfirmedEmail(
            $request->query->get('name', ''),
            $request->query->get('email', ''),
            $request->query->getInt('offset', 0)
        );

        return $this->json(['data' => compact('users')]);
    }

    /**
     * @Route("", name="updateCurrentUser", methods={"PUT"})
     *
     * @IsGranted(User::ROLE_USER)
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param UserService $userService
     * @param AuthService $authService
     * @param ValidationService $validationService
     *
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function updateCurrentUser(
        Request $request,
        TranslatorInterface $translator,
        UserService $userService,
        AuthService $authService,
        ValidationService $validationService
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$authService->isPasswordValid($user, $request->get('old_password'))) {
            return $this->json(
                ['message' => $translator->trans('Old password is invalid.')],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $user->setName($request->get('name', ''));
        $user->setPlainPassword($request->get('plainPassword', ''));

        $validationService->validate($user);
        $userService->encodeUserPassword($user);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'message' => $translator->trans(
                'User %name% has been successfully updated.',
                ['%name%' => $user->getName()]
            ),
            'data' => compact('user')
        ]);
    }

    /**
     * @Route("/email", name="updateCurrentUserEmail", methods={"PUT"})
     *
     * @IsGranted(User::ROLE_USER)
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param AuthService $authService
     * @param ValidationService $validationService
     * @param EmailService $emailService
     *
     * @return JsonResponse
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function updateCurrentUserEmail(
        Request $request,
        TranslatorInterface $translator,
        AuthService $authService,
        ValidationService $validationService,
        EmailService $emailService
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$authService->isPasswordValid($user, $request->get('old_password'))) {
            return $this->json(
                ['message' => $translator->trans('Old password is invalid.')],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        /**
         * TODO: найти способ валидировать уникальность имейла в User и в EmailConfirmation
         */

        dd('stop');

        $oldEmail = $user->getEmail();
        $newEmail = $request->get('email', '');

        $user->setEmail($newEmail);

        $validationService->validate($user);

        $user->setEmail($oldEmail);
        $emailConfirmation = new EmailConfirmation();
        $emailConfirmation->setEmail($newEmail);
        $emailConfirmation->setPrePersistDefaults();
        $user->addEmailConfirmation($emailConfirmation);

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
        $entityManager->persist($emailConfirmation);
        $entityManager->flush();

        return $this->json([
            'message' => $translator->trans(
                'Email change process has been started. Please follow the instructions that has been sent to email - %email%.',
                ['%email%' => $emailConfirmation->getEmail()]
            )
        ]);
    }
}
