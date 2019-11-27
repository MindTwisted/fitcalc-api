<?php

namespace App\Controller;

use App\Entity\Email;
use App\Entity\User;
use App\Serializer\Normalizer\ConstraintViolationListNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class AuthController
 *
 * @package App\Controller
 *
 * @Route("/api")
 */
class AuthController extends AbstractController
{
    /**
     * @Route("/auth", name="auth", methods={"GET"})
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
     *
     * @return JsonResponse
     *
     * @throws ExceptionInterface
     */
    public function register(
        ValidatorInterface $validator,
        ConstraintViolationListNormalizer $constraintViolationListNormalizer,
        Request $request,
        UserPasswordEncoderInterface $userPasswordEncoder
    ): JsonResponse
    {
        $user = new User();
        $user->setFullname($request->get('fullname', ''));
        $user->setUsername($request->get('username', ''));
        $user->setPassword($request->get('password', ''));
        $email = new Email();
        $email->setEmail($request->get('email', ''));
        $user->addEmail($email);
        $errors = $validator->validate($user);

        if (count($errors) > 0) {
            return $this->json(
                $constraintViolationListNormalizer->normalize($errors),
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $user->setPassword($userPasswordEncoder->encodePassword($user, $user->getPassword()));

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'status' => 'success',
            'message' => sprintf('User %s has been registered.', $user->getFullname())
        ]);
    }
}
