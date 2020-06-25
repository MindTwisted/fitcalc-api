<?php

namespace App\Controller;


use App\Entity\EatingScheme;
use App\Entity\EatingSchemeDetail;
use App\Entity\User;
use App\Exception\ValidationException;
use App\Security\Voter\EatingSchemeVoter;
use App\Services\EatingSchemeService;
use App\Services\EatingService;
use Doctrine\DBAL\ConnectionException;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class EatingSchemeController
 *
 * @package App\Controller
 *
 * @Route("/{_locale}/api/eating_scheme", requirements={"_locale": "en|ru"})
 */
class EatingSchemeController extends AbstractController
{
    /**
     * @Route("", name="getAllEatingScheme", methods={"GET"})
     *
     * @IsGranted(User::ROLE_APP_USER, message="Forbidden.")
     *
     * @param Request $request
     * @param EatingSchemeService $eatingSchemeService
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function getAllEatingScheme(
        Request $request,
        EatingSchemeService $eatingSchemeService
    ): JsonResponse
    {
        $eatingScheme = $eatingSchemeService->getAllEatingSchemeOfCurrentUser($request);

        return $this->json([
            'data' => compact('eatingScheme')
        ]);
    }

    /**
     * @Route("", name="addEatingScheme", methods={"POST"})
     *
     * @IsGranted(User::ROLE_APP_USER, message="Forbidden.")
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param EatingSchemeService $eatingSchemeService
     *
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function addEatingScheme(
        Request $request,
        TranslatorInterface $translator,
        EatingSchemeService $eatingSchemeService
    ): JsonResponse
    {
        $eatingScheme = $eatingSchemeService->createOrUpdateEatingScheme($request);

        return $this->json([
            'message' => $translator->trans('Eating scheme has been successfully added.'),
            'data' => compact('eatingScheme')
        ]);
    }

    /**
     * @Route(
     *     "/{id}",
     *     requirements={"id"="\d+"},
     *     name="updateEatingScheme",
     *     methods={"PUT"}
     * )
     *
     * @IsGranted(User::ROLE_APP_USER, message="Forbidden.")
     *
     * @param EatingScheme $eatingScheme
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param EatingSchemeService $eatingSchemeService
     *
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function updateEatingScheme(
        EatingScheme $eatingScheme,
        Request $request,
        TranslatorInterface $translator,
        EatingSchemeService $eatingSchemeService
    ): JsonResponse
    {
        $this->denyAccessUnlessGranted(EatingSchemeVoter::EDIT, $eatingScheme);

        $eatingScheme = $eatingSchemeService->createOrUpdateEatingScheme($request, $eatingScheme);

        return $this->json([
            'message' => $translator->trans('Eating scheme has been successfully updated.'),
            'data' => compact('eatingScheme')
        ]);
    }

    /**
     * @Route(
     *     "/{id}",
     *     requirements={"id"="\d+"},
     *     name="deleteEatingScheme",
     *     methods={"DELETE"}
     * )
     *
     * @IsGranted(User::ROLE_APP_USER, message="Forbidden.")
     *
     * @param EatingScheme $eatingScheme
     * @param TranslatorInterface $translator
     * @param EatingSchemeService $eatingSchemeService
     *
     * @return JsonResponse
     */
    public function deleteEatingScheme(
        EatingScheme $eatingScheme,
        TranslatorInterface $translator,
        EatingSchemeService $eatingSchemeService
    ): JsonResponse
    {
        $this->denyAccessUnlessGranted(EatingSchemeVoter::DELETE, $eatingScheme);

        $eatingSchemeService->deleteEatingScheme($eatingScheme);

        return $this->json([
            'message' => $translator->trans('Eating scheme has been successfully deleted.')
        ]);
    }

    /**
     * @Route(
     *     "/{id}/details",
     *     requirements={"id"="\d+"},
     *     name="addEatingSchemeDetails",
     *     methods={"POST"}
     * )
     *
     * @IsGranted(User::ROLE_APP_USER, message="Forbidden.")
     *
     * @param EatingScheme $eatingScheme
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param EatingSchemeService $eatingSchemeService
     *
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function addEatingSchemeDetails(
        EatingScheme $eatingScheme,
        Request $request,
        TranslatorInterface $translator,
        EatingSchemeService $eatingSchemeService
    ): JsonResponse
    {
        $this->denyAccessUnlessGranted(EatingSchemeVoter::EDIT, $eatingScheme);

        $eatingSchemeService->createOrUpdateEatingSchemeDetail($request, $eatingScheme);

        return $this->json([
            'message' => $translator->trans('Eating scheme detail has been successfully added.'),
            'data' => compact('eatingScheme')
        ]);
    }

    /**
     * @Route(
     *     "/{eating_scheme_id}/details/{detail_id}",
     *     requirements={"eating_scheme_id"="\d+", "detail_id"="\d+"},
     *     name="updateEatingSchemeDetails",
     *     methods={"PUT"}
     * )
     *
     * @Entity("eatingSchemeDetail", expr="repository.findOneWithEatingSchemeByIdAndEatingSchemeId(detail_id, eating_scheme_id)")
     *
     * @IsGranted(User::ROLE_APP_USER, message="Forbidden.")
     *
     * @param EatingSchemeDetail $eatingSchemeDetail
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param EatingSchemeService $eatingSchemeService
     *
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function updateEatingSchemeDetails(
        EatingSchemeDetail $eatingSchemeDetail,
        Request $request,
        TranslatorInterface $translator,
        EatingSchemeService $eatingSchemeService
    ): JsonResponse
    {
        $eatingScheme = $eatingSchemeDetail->getEatingScheme();

        $this->denyAccessUnlessGranted(EatingSchemeVoter::EDIT, $eatingScheme);

        $eatingSchemeService->createOrUpdateEatingSchemeDetail($request, $eatingScheme, $eatingSchemeDetail);

        return $this->json([
            'message' => $translator->trans('Eating scheme detail has been successfully updated.'),
            'data' => compact('eatingScheme')
        ]);
    }

    /**
     * @Route(
     *     "/{eating_scheme_id}/details/{detail_id}",
     *     requirements={"eating_scheme_id"="\d+", "detail_id"="\d+"},
     *     name="deleteEatingSchemeDetails",
     *     methods={"DELETE"}
     * )
     *
     * @Entity("eatingSchemeDetail", expr="repository.findOneWithEatingSchemeByIdAndEatingSchemeId(detail_id, eating_scheme_id)")
     *
     * @IsGranted(User::ROLE_APP_USER, message="Forbidden.")
     *
     * @param EatingSchemeDetail $eatingSchemeDetail
     * @param TranslatorInterface $translator
     * @param EatingSchemeService $eatingSchemeService
     *
     * @return JsonResponse
     */
    public function deleteEatingSchemeDetails(
        EatingSchemeDetail $eatingSchemeDetail,
        TranslatorInterface $translator,
        EatingSchemeService $eatingSchemeService
    ): JsonResponse
    {
        $eatingScheme = $eatingSchemeDetail->getEatingScheme();

        $this->denyAccessUnlessGranted(EatingSchemeVoter::EDIT, $eatingScheme);

        $eatingSchemeService->deleteEatingSchemeDetail($eatingSchemeDetail);

        return $this->json([
            'message' => $translator->trans('Eating scheme detail has been successfully deleted.'),
            'data' => compact('eatingScheme')
        ]);
    }

    /**
     * @Route(
     *     "/{id}/default",
     *     requirements={"id"="\d+"},
     *     name="setDefaultEatingScheme",
     *     methods={"POST"}
     * )
     *
     * @IsGranted(User::ROLE_APP_USER, message="Forbidden.")
     *
     * @param EatingScheme $eatingScheme
     * @param TranslatorInterface $translator
     * @param EatingSchemeService $eatingSchemeService
     *
     * @return JsonResponse
     *
     * @throws ConnectionException
     */
    public function setDefaultEatingScheme(
        EatingScheme $eatingScheme,
        TranslatorInterface $translator,
        EatingSchemeService $eatingSchemeService
    ): JsonResponse
    {
        $this->denyAccessUnlessGranted(EatingSchemeVoter::EDIT, $eatingScheme);

        $eatingSchemeService->setDefaultEatingScheme($eatingScheme);

        return $this->json([
            'message' => $translator->trans('Default eating scheme has been successfully set.'),
            'data' => compact('eatingScheme')
        ]);
    }

    /**
     * @Route(
     *     "/{id}/apply",
     *     requirements={"id"="\d+"},
     *     name="applyEatingScheme",
     *     methods={"POST"}
     * )
     *
     * @IsGranted(User::ROLE_APP_USER, message="Forbidden.")
     *
     * @param EatingScheme $eatingScheme
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param EatingService $eatingService
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function applyEatingScheme(
        EatingScheme $eatingScheme,
        Request $request,
        TranslatorInterface $translator,
        EatingService $eatingService
    ): JsonResponse
    {
        $this->denyAccessUnlessGranted(EatingSchemeVoter::EDIT, $eatingScheme);

        $eating = $eatingService->applyEatingScheme($eatingScheme, $request);

        return $this->json([
            'message' => $translator->trans('Eating scheme has been successfully applied.'),
            'data' => compact('eating')
        ]);
    }
}
