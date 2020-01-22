<?php

namespace App\Controller;


use App\Entity\Eating;
use App\Entity\EatingDetail;
use App\Exception\ValidationException;
use App\Security\Voter\EatingVoter;
use App\Services\EatingService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Doctrine\ORM\NonUniqueResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class EatingController
 *
 * @package App\Controller
 *
 * @Route("/{_locale}/api/eating", requirements={"_locale": "en|ru"})
 */
class EatingController extends AbstractController
{
    /**
     * @Route("", name="addEating", methods={"POST"})
     *
     * @IsGranted(User::ROLE_APP_USER, message="Forbidden.")
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param EatingService $eatingService
     *
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function addEating(
        Request $request,
        TranslatorInterface $translator,
        EatingService $eatingService
    ): JsonResponse
    {
        $eating = $eatingService->createOrUpdateEating($request);

        return $this->json([
            'message' => $translator->trans('Eating has been successfully added.'),
            'data' => compact('eating')
        ]);
    }

    /**
     * @Route(
     *     "/{id}",
     *     requirements={"id"="\d+"},
     *     name="updateEating",
     *     methods={"PUT"}
     * )
     *
     * @IsGranted(User::ROLE_APP_USER, message="Forbidden.")
     *
     * @param Eating $eating
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param EatingService $eatingService
     *
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function updateEating(
        Eating $eating,
        Request $request,
        TranslatorInterface $translator,
        EatingService $eatingService
    ): JsonResponse
    {
        $this->denyAccessUnlessGranted(EatingVoter::EDIT, $eating);

        $eating = $eatingService->createOrUpdateEating($request, $eating);

        return $this->json([
            'message' => $translator->trans('Eating has been successfully updated.'),
            'data' => compact('eating')
        ]);
    }

    /**
     * @Route(
     *     "/{id}/details",
     *     requirements={"id"="\d+"},
     *     name="addEatingDetails",
     *     methods={"POST"}
     * )
     *
     * @IsGranted(User::ROLE_APP_USER, message="Forbidden.")
     *
     * @param Eating $eating
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param EatingService $eatingService
     *
     * @return JsonResponse
     *
     * @throws ValidationException
     * @throws NonUniqueResultException
     */
    public function addEatingDetails(
        Eating $eating,
        Request $request,
        TranslatorInterface $translator,
        EatingService $eatingService
    ): JsonResponse
    {
        $this->denyAccessUnlessGranted(EatingVoter::EDIT, $eating);

        $eatingService->createOrUpdateEatingDetail($request, $eating);
        $eating = $eatingService->getOneWithDetailsById($eating->getId(), $request->getLocale());

        return $this->json([
            'message' => $translator->trans('Eating detail has been successfully added.'),
            'data' => compact('eating')
        ]);
    }

    /**
     * @Route(
     *     "/{eating_id}/details/{detail_id}",
     *     requirements={"eating_id"="\d+", "detail_id"="\d+"},
     *     name="updateEatingDetails",
     *     methods={"PUT"}
     * )
     *
     * @Entity("eatingDetail", expr="repository.findOneWithEatingByIdAndEatingId(detail_id, eating_id)")
     *
     * @IsGranted(User::ROLE_APP_USER, message="Forbidden.")
     *
     * @param EatingDetail $eatingDetail
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param EatingService $eatingService
     *
     * @return JsonResponse
     *
     * @throws NonUniqueResultException
     * @throws ValidationException
     */
    public function updateEatingDetails(
        EatingDetail $eatingDetail,
        Request $request,
        TranslatorInterface $translator,
        EatingService $eatingService
    ): JsonResponse
    {
        $eating = $eatingDetail->getEating();

        $this->denyAccessUnlessGranted(EatingVoter::EDIT, $eating);

        $eatingService->createOrUpdateEatingDetail($request, $eating, $eatingDetail);
        $eating = $eatingService->getOneWithDetailsById($eating->getId(), $request->getLocale());

        return $this->json([
            'message' => $translator->trans('Eating detail has been successfully updated.'),
            'data' => compact('eating')
        ]);
    }
}
