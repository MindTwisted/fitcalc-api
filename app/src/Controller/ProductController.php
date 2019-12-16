<?php

namespace App\Controller;


use App\Entity\Product;
use App\Exception\ValidationException;
use App\Services\ValidationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
     * @Route("", name="createProduct", methods={"POST"})
     *
     * @param Request $request
     * @param ValidationService $validationService
     *
     * @return JsonResponse
     * @throws ValidationException
     */
    public function createProduct(Request $request, ValidationService $validationService): JsonResponse
    {
        /**
         * TODO:
         * 1) должны быть поля name_en, name_ru.. en версию загружать в таблицу products, ru версию добавлять как перевод
         * 2) обязательно проверять на float БЖУ и integer калории
         */

        $product = new Product();
        $product->setName($request->get('name', ''));
        $product->setProteins(10.5);
        $product->setCarbohydrates(50);
        $product->setFats(25);
        $product->setCalories(1500);
        $product->setLocale($request->getLocale());

        $validationService->validate($product);

        dd('123');

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($product);
        $entityManager->flush();

        dd('stop');
    }
}
