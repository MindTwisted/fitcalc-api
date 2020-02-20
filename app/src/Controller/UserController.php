<?php

namespace App\Controller;


use App\Entity\EmailConfirmation;
use App\Entity\PasswordRecovery;
use App\Entity\User;
use App\Exception\ValidationException;
use App\Form\InitiatePasswordRecoveryType;
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
     * @Route("/initiate_password_recovery", name="initiatePasswordRecovery", methods={"POST"})
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param UserService $userService
     * @param EmailService $emailService
     * @param ValidationService $validationService
     *
     * @return JsonResponse
     *
     * @throws NonUniqueResultException
     * @throws ValidationException
     * @throws Exception
     */
    public function initiatePasswordRecovery(
        Request $request,
        TranslatorInterface $translator,
        UserService $userService,
        EmailService $emailService,
        ValidationService $validationService
    ): JsonResponse
    {
        $email = $request->get('email');
        $form = $this->createForm(InitiatePasswordRecoveryType::class)->submit(compact('email'));

        $validationService->validateForm($form);

        $user = $userService->getUserByEmail($email);

        if (!$user) {
            return $this->json(
                ['message' => $translator->trans("Email %email% doesn't exist.", ['%email%' => $email])],
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
     * @Route("/confirm_password_recovery", name="confirmPasswordRecovery", methods={"POST"})
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
    public function confirmPasswordRecovery(
        Request $request,
        ValidationService $validationService,
        UserService $userService,
        TranslatorInterface $translator
    ): JsonResponse
    {
        /** @var PasswordRecoveryRepository $passwordRecoveryRepository */
        $passwordRecoveryRepository = $this->getDoctrine()->getRepository(PasswordRecovery::class);
        $passwordRecovery = $passwordRecoveryRepository->findOneByTokenJoinedToUser($request->get('token', ''));

        if (!$passwordRecovery) {
            return $this->json(
                ['message' => $translator->trans('Password recovery token is invalid.')],
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

        $user->setPlainPassword($request->get('password', ''));

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
     * @IsGranted(User::ROLE_ADMIN, message="Forbidden.")
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
     * @param ValidationService $validationService
     *
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function updateCurrentUser(
        Request $request,
        TranslatorInterface $translator,
        ValidationService $validationService
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $user->setName($request->get('name', ''));

        $validationService->validate($user, [User::GROUP_GENERAL_UPDATE]);

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
     * @Route("/password", name="updateCurrentUserPassword", methods={"PUT"})
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
    public function updateCurrentUserPassword(
        Request $request,
        TranslatorInterface $translator,
        UserService $userService,
        AuthService $authService,
        ValidationService $validationService
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$authService->isPasswordValid($user, $request->get('current_password'))) {
            return $this->json(
                ['message' => $translator->trans('Current password is invalid.')],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $user->setPlainPassword($request->get('new_password', ''));

        $validationService->validate($user, [User::GROUP_PASSWORD_UPDATE]);
        $userService->encodeUserPassword($user);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'message' => $translator->trans(
                'Password for user %name% has been successfully updated.',
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

        if (!$authService->isPasswordValid($user, $request->get('current_password'))) {
            return $this->json(
                ['message' => $translator->trans('Current password is invalid.')],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $emailConfirmation = new EmailConfirmation();
        $emailConfirmation->setEmail($request->get('email', ''));
        $emailConfirmation->setPrePersistDefaults();

        $validationService->validate($emailConfirmation);

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
                'Email change process has been started. Please follow the instructions that were sent to email - %email%.',
                ['%email%' => $emailConfirmation->getEmail()]
            )
        ]);
    }
}
