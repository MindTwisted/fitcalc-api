<?php

namespace App\Services;


use App\Entity\Product;
use App\Entity\ProductTranslation;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;

class ProductService
{
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