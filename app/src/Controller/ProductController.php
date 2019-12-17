<?php

namespace App\Controller;


use App\Entity\Product;
use App\Entity\ProductTranslation;
use App\Entity\User;
use App\Exception\ValidationException;
use App\Services\ValidationService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ProductController
 *
 * @package App\Controller
 *
 * @Route("/{_locale}/api/products", requirements={"_locale": "en|ru"})
 */
class ProductController extends AbstractController
{
    /**
     * @Route("", name="addProduct", methods={"POST"})
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
    public function addProduct(
        Request $request,
        TranslatorInterface $translator,
        ValidationService $validationService
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $product = new Product();
        $product->setName($request->get('name', ''));
        $product->setProteins(floatval($request->get('proteins')));
        $product->setCarbohydrates(floatval($request->get('carbohydrates')));
        $product->setFats(floatval($request->get('fats')));
        $product->setCalories($request->request->getInt('calories'));
        $product->setLocale('en');
        $product->addTranslation(
            new ProductTranslation('ru', 'name', $request->get('name_ru', ''))
        );

        if (!$user->isAdmin()) {
            $product->setUser($user);
        }

        $validationService->validate($product);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($product);
        $entityManager->flush();

        return $this->json([
            'message' => $translator->trans('Product has been successfully added.'),
            'data' => compact('product')
        ]);
    }
}
