<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controlador de la pàgina principal.
 *
 * La ruta arrel (/) redirigeix directament al llistat de productes,
 * que és el punt d'entrada real de l'aplicació.
 */
final class HomeController extends AbstractController
{
    /**
     * Redirigeix l'usuari al llistat públic de productes.
     * És el punt d'entrada de l'aplicació.
     */
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_product_index');
    }
}