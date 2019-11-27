<?php

namespace App\Controller;

use App\Entity\Email;
use App\Entity\User;
use App\Repository\EmailRepository;
use App\Serializer\Normalizer\ConstraintViolationListNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
     */
    public function index()
    {
        return $this->json(['message' => 'Coming soon.']);
    }

    /**
     * @Route("/register", name="register", methods={"POST"})
     *
     * @param ValidatorInterface $validator
     * @param ConstraintViolationListNormalizer $constraintViolationListNormalizer
     * @param Request $request
     * @param UserPasswordEncoderInterface $userPasswordEncoder
     * @param MailerInterface $mailer
     *
     * @return JsonResponse
     *
     * @throws ExceptionInterface
     * @throws Exception
     */
    public function register(
        ValidatorInterface $validator,
        ConstraintViolationListNormalizer $constraintViolationListNormalizer,
        Request $request,
        UserPasswordEncoderInterface $userPasswordEncoder,
        MailerInterface $mailer
    ): JsonResponse
    {
        $user = new User();
        $user->setFullname($request->get('fullname', ''));
        $user->setUsername($request->get('username', ''));
        $user->setPassword($request->get('password', ''));
        $email = new Email();
        $email->setEmail($request->get('email', ''));
        $email->setPrePersistDefaults();
        $user->addEmail($email);
        $errors = $validator->validate($user);

        if (count($errors) > 0) {
            return $this->json(
                $constraintViolationListNormalizer->normalize($errors),
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $user->setPassword($userPasswordEncoder->encodePassword($user, $user->getPassword()));

        $protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === 0 ? 'https://' : 'http://';
        $domain = $_ENV['APP_DOMAIN'];
        $emailConfirmationUrl = $protocol . $domain . $this->generateUrl(
                'registerEmailConfirmation',
                [
                    'hash' => $email->getHash(),
                ]
            );

        try {
            $sendEmail = (new TemplatedEmail())
                ->from('admin@' . $domain)
                ->to($email->getEmail())
                ->subject('Email confirmation')
                ->htmlTemplate('emails/email_confirmation.html.twig')
                ->context(compact('user', 'emailConfirmationUrl'));

            $mailer->send($sendEmail);
        } catch (TransportExceptionInterface $exception) {
            return $this->json(
                [
                    'status' => 'failed',
                    'message' => $exception->getMessage()
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'status' => 'success',
            'message' => sprintf('User %s has been registered.', $user->getFullname())
        ]);
    }

    /**
     * @Route("/register_email_confirmation/{hash}", name="registerEmailConfirmation", methods={"GET"})
     *
     * @param string $hash
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws NonUniqueResultException
     */
    public function registerEmailConfirmation(string $hash, Request $request): JsonResponse
    {
        /** @var EmailRepository $emailRepository */
        $emailRepository = $this->getDoctrine()->getRepository(Email::class);
        $email = $emailRepository->findNotVerifiedOneByHash($hash);

        if (!$email) {
            return $this->json(
                [
                    'status' => 'failed',
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
            'status' => 'success',
            'message' => sprintf('Email %s has been confirmed.', $email->getEmail())
        ]);
    }
}
