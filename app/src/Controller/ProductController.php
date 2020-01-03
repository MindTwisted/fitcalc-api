<?php

namespace App\Controller;


use App\Entity\Product;
use App\Entity\User;
use App\Exception\ValidationException;
use App\Repository\ProductRepository;
use App\Services\ProductService;
use Doctrine\ORM\NonUniqueResultException;
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
     * @param ProductService $productService
     *
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function addProduct(
        Request $request,
        TranslatorInterface $translator,
        ProductService $productService
    ): JsonResponse
    {
        $product = $productService->createOrUpdateProduct($request);

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
        $products = $productService->getProducts($request);

        return $this->json(['data' => compact('products')]);
    }

    /**
     * @Route(
     *     "/{id}",
     *     requirements={"id"="\d+"},
     *     name="updateProduct",
     *     methods={"PUT"}
     * )
     *
     * @IsGranted(User::ROLE_USER)
     *
     * @param int $id
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param ProductService $productService
     *
     * @return JsonResponse
     *
     * @throws NonUniqueResultException
     * @throws ValidationException
     */
    public function updateProduct(
        int $id,
        Request $request,
        TranslatorInterface $translator,
        ProductService $productService
    ): JsonResponse
    {
        /** @var ProductRepository $productRepository */
        $productRepository = $this->getDoctrine()->getRepository(Product::class);
        $product = $productRepository->findOneWithTranslationById($id);

        if (!$product) {
            return $this->json(
                ['message' => $translator->trans('Not found.')],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $this->denyAccessUnlessGranted('edit', $product);

        $product = $productService->createOrUpdateProduct($request, $product);

        return $this->json([
            'message' => $translator->trans('Product has been successfully updated.'),
            'data' => compact('product')
        ]);
    }
}
