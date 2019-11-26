<?php

namespace App\Controller;

use App\Entity\Email;
use App\Entity\User;
use App\Serializer\Normalizer\ConstraintViolationListNormalizer;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
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
     * @param SerializerInterface $serializer
     * @param UserPasswordEncoderInterface $userPasswordEncoder
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
        SerializerInterface $serializer,
        UserPasswordEncoderInterface $userPasswordEncoder
    ): JsonResponse
    {
        /** @var User $user */
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');
        $errors = $validator->validate($user);

        if (count($errors) > 0) {
            return $this->json(
                $constraintViolationListNormalizer->normalize($errors),
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $user->setPassword($userPasswordEncoder->encodePassword($user, $user->getPassword()));

        /** @var Email $email */
        $email = $user->getEmails()->first();
        $email->setVerified(false);
        $email->setHash(md5(random_bytes(10)));

        /**
         * 1) как при сохранении записывать created_at, updated_at
         */
        dd($user);
        dd('valid');
    }
}
