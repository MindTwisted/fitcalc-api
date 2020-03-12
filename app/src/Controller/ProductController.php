<?php

namespace App\Controller;


use App\Entity\Product;
use App\Entity\User;
use App\Exception\ValidationException;
use App\Repository\ProductRepository;
use App\Security\Voter\FavouriteProductVoter;
use App\Security\Voter\ProductVoter;
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

        $this->denyAccessUnlessGranted(ProductVoter::EDIT, $product);

        $product = $productService->createOrUpdateProduct($request, $product);

        return $this->json([
            'message' => $translator->trans('Product has been successfully updated.'),
            'data' => compact('product')
        ]);
    }

    /**
     * @Route(
     *     "/{id}",
     *     requirements={"id"="\d+"},
     *     name="deleteProduct",
     *     methods={"DELETE"}
     * )
     *
     * @IsGranted(User::ROLE_USER)
     *
     * @param int $id
     * @param TranslatorInterface $translator
     * @param ProductService $productService
     *
     * @return JsonResponse
     *
     * @throws NonUniqueResultException
     */
    public function deleteProduct(
        int $id,
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

        $this->denyAccessUnlessGranted(ProductVoter::DELETE, $product);

        $productService->deleteProduct($product);

        return $this->json([
            'message' => $translator->trans('Product has been successfully deleted.')
        ]);
    }

    /**
     * @Route(
     *     "/favourites",
     *     name="getAllFavouriteProducts",
     *     methods={"GET"}
     * )
     *
     * @IsGranted(User::ROLE_APP_USER, message="Forbidden.")
     *
     * @param Request $request
     * @param ProductService $productService
     *
     * @return JsonResponse
     */
    public function getAllFavouriteProducts(Request $request, ProductService $productService): JsonResponse
    {
        $favouriteProducts = $productService->getFavouriteProducts($request);

        return $this->json([
            'data' => ['products' => $favouriteProducts]
        ]);
    }

    /**
     * @Route(
     *     "/{id}/favourites",
     *     requirements={"id"="\d+"},
     *     name="addFavouriteProduct",
     *     methods={"POST"}
     * )
     *
     * @IsGranted(User::ROLE_APP_USER, message="Forbidden.")
     *
     * @param int $id
     * @param TranslatorInterface $translator
     * @param ProductService $productService
     *
     * @return JsonResponse
     *
     * @throws NonUniqueResultException
     */
    public function addFavouriteProduct(
        int $id,
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

        $this->denyAccessUnlessGranted(FavouriteProductVoter::EDIT, $product);

        $productService->addFavouriteProduct($product);

        return $this->json([
            'message' => $translator->trans('Product has been successfully added to favourites.')
        ]);
    }

    /**
     * @Route(
     *     "/{id}/favourites",
     *     requirements={"id"="\d+"},
     *     name="deleteFavouriteProduct",
     *     methods={"DELETE"}
     * )
     *
     * @IsGranted(User::ROLE_APP_USER, message="Forbidden.")
     *
     * @param int $id
     * @param TranslatorInterface $translator
     * @param ProductService $productService
     *
     * @return JsonResponse
     *
     * @throws NonUniqueResultException
     */
    public function deleteFavouriteProduct(
        int $id,
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

        $this->denyAccessUnlessGranted(FavouriteProductVoter::DELETE, $product);

        $productService->deleteFavouriteProduct($product);

        return $this->json([
            'message' => $translator->trans('Product has been successfully deleted from favourites.')
        ]);
    }
}
