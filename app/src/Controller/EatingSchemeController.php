<?php

namespace App\Controller;


use App\Entity\EatingScheme;
use App\Entity\User;
use App\Exception\ValidationException;
use App\Security\Voter\EatingSchemeVoter;
use App\Services\EatingSchemeService;
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
}
