<?php

namespace App\Controller;


use App\Entity\RefreshToken;
use App\Entity\User;
use App\Repository\RefreshTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class RefreshTokenController
 *
 * @package App\Controller
 *
 * @Route("/{_locale}/api/refresh_tokens", requirements={"_locale": "en|ru"})
 */
class RefreshTokenController extends AbstractController
{
    /**
     * @Route("", name="getAllRefreshTokensOfCurrentUser", methods={"GET"})
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
     *     "/{id}",
     *     requirements={"id"="\d+"},
     *     name="deleteRefreshTokenOfCurrentUserById",
     *     methods={"DELETE"}
     * )
     *
     * @IsGranted("ROLE_USER")
     *
     * @param int $id
     * @param TranslatorInterface $translator
     *
     * @return JsonResponse
     *
     * @throws NonUniqueResultException
     */
    public function deleteRefreshTokenOfCurrentUserById(
        int $id,
        TranslatorInterface $translator
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        /** @var RefreshTokenRepository $refreshTokenRepository */
        $refreshTokenRepository = $this->getDoctrine()->getRepository(RefreshToken::class);
        $refreshToken = $refreshTokenRepository->findOneNotExpiredAndNotDeletedByIdAndUserId($id, $user->getId());

        if (!$refreshToken) {
            return $this->json(
                ['message' => $translator->trans('Not found.')],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($refreshToken);
        $entityManager->flush();

        return $this->json([
            'message' => $translator->trans('Refresh token has been successfully removed.')
        ]);
    }

    /**
     * @Route("", name="deleteAllRefreshTokensOfCurrentUser", methods={"DELETE"})
     *
     * @IsGranted("ROLE_USER")
     *
     * @param TranslatorInterface $translator
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function deleteAllRefreshTokensOfCurrentUser(TranslatorInterface $translator): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        /** @var RefreshTokenRepository $refreshTokenRepository */
        $refreshTokenRepository = $this->getDoctrine()->getRepository(RefreshToken::class);
        $refreshTokenRepository->softDeleteNotExpiredAndNotDeletedByUserId($user->getId());

        return $this->json([
            'message' => $translator->trans('Refresh tokens have been successfully removed.')
        ]);
    }
}
