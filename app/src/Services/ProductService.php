<?php

namespace App\Services;


use App\Entity\Product;
use App\Entity\ProductTranslation;
use App\Entity\User;
use App\Exception\ValidationException;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProductService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ValidationService
     */
    private $validationService;

    /**
     * @var User
     */
    private $user;

    /**
     * ProductService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ValidationService $validationService
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ValidationService $validationService,
        TokenStorageInterface $tokenStorage
    )
    {
        $this->entityManager = $entityManager;
        $this->validationService = $validationService;
        $this->user = $tokenStorage->getToken()->getUser();
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
        $product = $this->user->isAdmin() ?
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
        return $this->user->isAdmin() ?
            $this->getProductsAsAdmin($request) :
            $this->getProductsAsUser($request);
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
        $product = $product ?? new Product();
        $product = $this->fillProductFromRequest($product, $request);
        $product->setLocale($request->getLocale());
        $product->setUser($this->user);

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

        return $productRepository->findWithTranslation(
            $request->get('name', ''),
            $this->user->isAdmin() ? null : $this->user->getId(),
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

        return $productRepository->findWithTranslationLocalized(
            $request->get('name', ''),
            $this->user->isAdmin() ? null : $this->user->getId(),
            $request->getLocale(),
            $request->query->getInt('offset', 0)
        );
    }
}