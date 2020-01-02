<?php

namespace App\Controller;


use App\Entity\Product;
use App\Entity\ProductTranslation;
use App\Entity\User;
use App\Exception\ValidationException;
use App\Repository\ProductRepository;
use App\Services\ProductService;
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
     * @param ProductService $productService
     *
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function addProduct(
        Request $request,
        TranslatorInterface $translator,
        ValidationService $validationService,
        ProductService $productService
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $product = $productService->createProduct($user, $request);

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

    /**
     * @Route("", name="getAllProducts", methods={"GET"})
     *
     * @IsGranted(User::ROLE_USER)
     *
     * @param Request $request
     * @param ProductService $productService
     *
     * @return JsonResponse
     */
    public function getAllProducts(Request $request, ProductService $productService): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $products = $productService->getProducts($user, $request);

        return $this->json(['data' => compact('products')]);
    }
}
