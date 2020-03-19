<?php

namespace App\Services;


use App\Entity\Product;
use App\Entity\ProductTranslation;
use App\Entity\User;
use App\Exception\ValidationException;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var ValidationService
     */
    private $validationService;

    /**
     * @var Security
     */
    private $security;


    /**
     * ProductService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TranslatorInterface $translator
     * @param ValidationService $validationService
     * @param Security $security
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
        ValidationService $validationService,
        Security $security
    )
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->validationService = $validationService;
        $this->security = $security;
    }

    /**
     * @param Request $request
     * @param Product|null $product
     *
     * @return Product
     *
     * @throws ValidationException
     */
    public function createOrUpdateProduct(Request $request, ?Product $product = null): Product
    {
        $user = $this->security->getUser();
        $product = $user->isAdmin() ?
            $this->createOrUpdateProductAsAdmin($request, $product) :
            $this->createOrUpdateProductAsUser($request, $product);

        $this->validationService->validate($product);
        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $product;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function getProducts(Request $request): array
    {
        $user = $this->security->getUser();

        return $user->isAdmin() ?
            $this->getProductsAsAdmin($request) :
            $this->getProductsAsUser($request);
    }

    /**
     * @param Product $product
     */
    public function deleteProduct(Product $product): void
    {
        $this->entityManager->remove($product);
        $this->entityManager->flush();
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function getFavouriteProducts(Request $request): array
    {
        /** @var ProductRepository $productRepository */
        $productRepository = $this->entityManager->getRepository(Product::class);
        $user = $this->security->getUser();

        return $productRepository->findFavouriteWithTranslationLocalizedByUserId(
            $user->getId(),
            $request->getLocale(),
            $request->query->getInt('offset', 0)
        );
    }

    /**
     * @param Product $product
     *
     * @throws NonUniqueResultException
     */
    public function addFavouriteProduct(Product $product): void
    {
        /** @var User $user */
        $user = $this->security->getUser();

        /** @var ProductRepository $productRepository */
        $productRepository = $this->entityManager->getRepository(Product::class);
        $favouriteProduct = $productRepository->findOneFavouriteWithTranslationByIdAndUserId(
            $product->getId(),
            $user->getId()
        );

        if ($favouriteProduct) {
            throw new HttpException(
                JsonResponse::HTTP_BAD_REQUEST,
                $this->translator->trans('This product has already been added to favourites.')
            );
        }

        $user->addFavouriteProductHard($product);

        $this->entityManager->flush();
    }

    /**
     * @param Product $product
     *
     * @throws NonUniqueResultException
     */
    public function deleteFavouriteProduct(Product $product): void
    {
        /** @var User $user */
        $user = $this->security->getUser();

        /** @var ProductRepository $productRepository */
        $productRepository = $this->entityManager->getRepository(Product::class);
        $favouriteProduct = $productRepository->findOneFavouriteWithTranslationByIdAndUserId(
            $product->getId(),
            $user->getId()
        );

        if (!$favouriteProduct) {
            throw new HttpException(
                JsonResponse::HTTP_BAD_REQUEST,
                $this->translator->trans('This product has not been added to favourites.')
            );
        }

        $user->removeFavouriteProductHard($product);

        $this->entityManager->flush();
    }

    /**
     * @param Request $request
     * @param Product|null $product
     *
     * @return Product
     */
    private function createOrUpdateProductAsAdmin(Request $request, ?Product $product = null): Product
    {
        $product = $product ?? new Product();
        $product = $this->fillProductFromRequest($product, $request);
        $product->setLocale('en');
        $product->setUser(null);

        /** @var ProductTranslation|null $productTranslation */
        $productTranslation = $product->getTranslations()
            ->filter(function (ProductTranslation $productTranslation) {
                return $productTranslation->getLocale() === 'ru';
            })
            ->first();

        if ($productTranslation) {
            $productTranslation->setContent($request->get('name_ru', ''));
        } else {
            $product->addTranslation(
                new ProductTranslation('ru', 'name', $request->get('name_ru', ''))
            );
        }

        return $product;
    }

    /**
     * @param Request $request
     * @param Product|null $product
     *
     * @return Product
     */
    private function createOrUpdateProductAsUser(Request $request, ?Product $product = null): Product
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $product = $product ?? new Product();
        $product = $this->fillProductFromRequest($product, $request);
        $product->setLocale($request->getLocale());
        $product->setUser($user);

        return $product;
    }

    /**
     * @param Product $product
     * @param Request $request
     *
     * @return Product
     */
    private function fillProductFromRequest(Product $product, Request $request): Product
    {
        $product->setName($request->get('name', ''));
        $product->setProteins(floatval($request->get('proteins')));
        $product->setCarbohydrates(floatval($request->get('carbohydrates')));
        $product->setFats(floatval($request->get('fats')));
        $product->setFiber(floatval($request->get('fiber')));
        $product->setCalories($request->request->getInt('calories'));

        return $product;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    private function getProductsAsAdmin(Request $request): array
    {
        /** @var ProductRepository $productRepository */
        $productRepository = $this->entityManager->getRepository(Product::class);
        $user = $this->security->getUser();

        return $productRepository->findWithTranslation(
            $request->get('name', ''),
            $user->isAdmin() ? null : $user->getId(),
            $request->query->getInt('offset', 0)
        );
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    private function getProductsAsUser(Request $request): array
    {
        /** @var ProductRepository $productRepository */
        $productRepository = $this->entityManager->getRepository(Product::class);
        $user = $this->security->getUser();

        return $productRepository->findWithTranslationAndWithFavouritesLocalized(
            $request->get('name', ''),
            $user->isAdmin() ? null : $user->getId(),
            $request->getLocale(),
            $request->query->getInt('offset', 0)
        );
    }
}