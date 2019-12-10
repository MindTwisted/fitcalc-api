<?php

namespace App\Controller;

use App\Entity\Email;
use App\Entity\PasswordRecovery;
use App\Repository\EmailRepository;
use App\Services\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserController
 *
 * @package App\Controller
 *
 * @Route("/api/users")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/reset_password", name="initiatePasswordReset", methods={"POST"})
     *
     * @param Request $request
     * @param EmailService $emailService
     *
     * @return JsonResponse
     *
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function initiatePasswordReset(Request $request, EmailService $emailService): JsonResponse
    {
        $emailAddress = $request->get('email');

        if (!$emailAddress) {
            return $this->json(['message' => 'Please provide an email.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        /** @var EmailRepository $emailRepository */
        $emailRepository = $this->getDoctrine()->getRepository(Email::class);
        $email = $emailRepository->findVerifiedOneByEmailJoinedToUser($emailAddress);

        if (!$email) {
            return $this->json(
                ['message' => sprintf("Email %s doesn't exist.", $emailAddress)],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $user = $email->getUser();

        if (!$user->isAppUser()) {
            return $this->json(['message' => 'Forbidden.'], JsonResponse::HTTP_FORBIDDEN);
        }

        if ($user->getPasswordRecoveries()->count() > 0) {
            return $this->json(
                ['message' => 'Password recovery procedure has already been started.'],
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
                ['message' => 'Unexpected error has been occurred, please try again later.'],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($passwordRecovery);
        $entityManager->flush();

        return $this->json(['message' => 'Password recovery token has been successfully sent.']);
    }
}
