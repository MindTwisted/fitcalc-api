<?php

namespace App\Services;


use App\Entity\Product;
use App\Entity\ProductTranslation;
use App\Entity\User;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class ProductService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * ProductService constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param User $user
     * @param Request $request
     *
     * @return Product
     */
    public function createProduct(User $user, Request $request): Product
    {
        return $user->isAdmin() ?
            $this->createProductAsAdmin($request) :
            $this->createProductAsUser($user, $request);
    }

    /**
     * @param User $user
     * @param Request $request
     *
     * @return array
     */
    public function getProducts(User $user, Request $request): array
    {
        return $user->isAdmin() ?
            $this->getProductsAsAdmin($user, $request) :
            $this->getProductsAsUser($user, $request);
    }

    /**
     * @param User $user
     * @param Request $request
     *
     * @return array
     */
    private function getProductsAsAdmin(User $user, Request $request): array
    {
        /** @var ProductRepository $productRepository */
        $productRepository = $this->entityManager->getRepository(Product::class);

        return $productRepository->findWithTranslation(
            $request->get('name', ''),
            $user->isAdmin() ? null : $user->getId(),
            $request->query->getInt('offset', 0)
        );
    }

    /**
     * @param User $user
     * @param Request $request
     *
     * @return array
     */
    private function getProductsAsUser(User $user, Request $request): array
    {
        /** @var ProductRepository $productRepository */
        $productRepository = $this->entityManager->getRepository(Product::class);

        return $productRepository->findWithTranslationLocalized(
            $request->get('name', ''),
            $user->isAdmin() ? null : $user->getId(),
            $request->getLocale(),
            $request->query->getInt('offset', 0)
        );
    }

    /**
     * @param Request $request
     *
     * @return Product
     */
    private function createProductAsAdmin(Request $request): Product
    {
        $product = new Product();
        $product = $this->fillProductFromRequest($product, $request);
        $product->setLocale('en');
        $product->addTranslation(
            new ProductTranslation('ru', 'name', $request->get('name_ru', ''))
        );

        return $product;
    }

    /**
     * @param User $user
     * @param Request $request
     *
     * @return Product
     */
    private function createProductAsUser(User $user, Request $request): Product
    {
        $product = new Product();
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
        $product->setCalories($request->request->getInt('calories'));

        return $product;
    }
}