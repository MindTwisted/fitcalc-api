<?php

namespace App\Controller;


use App\Entity\Eating;
use App\Exception\ValidationException;
use App\Security\Voter\EatingVoter;
use App\Services\EatingService;
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
}
