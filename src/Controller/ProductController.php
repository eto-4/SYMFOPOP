<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_product_index')]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findBy([], [
            'createdAt' => 'DESC'
        ]);

        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/product/{id}', name: 'app_product_show')]
    public function show(Product $product): Response
    {
        // El ParamConverter de Symfony converteix automàticament el id a un obj.product
        // Si no existeix symfony llança un 404 automàticament.
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }
}
