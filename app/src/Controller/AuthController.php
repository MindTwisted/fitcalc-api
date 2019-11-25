<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AuthController
 *
 * @package App\Controller
 *
 * @Route("/api")
 */
class AuthController extends AbstractController
{
    /**
     * @Route("/auth", name="auth", methods={"GET"})
     */
    public function index()
    {
        return $this->json(['message' => 'Coming soon.']);
    }
}
