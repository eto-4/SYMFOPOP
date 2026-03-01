<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Controlador de seguretat.
 *
 * Gestiona el login i el logout de l'aplicació.
 * El procés d'autenticació real el fa Symfony internament
 * a través del firewall configurat a security.yaml —
 * aquest controlador només renderitza la vista i passa les dades necessàries.
 */
class SecurityController extends AbstractController
{
    /**
     * Mostra el formulari de login.
     *
     * Symfony intercepta el POST del formulari automàticament (form_login al firewall).
     * Aquest mètode només s'executa en GET (mostrar el formulari) i quan hi ha
     * un error d'autenticació (credencials incorrectes).
     *
     * AuthenticationUtils proporciona:
     * - L'últim email introduït (per no obligar l'usuari a tornar-lo a escriure)
     * - L'error d'autenticació si n'hi ha (credencials incorrectes, compte bloquejat, etc.)
     *
     * @param AuthenticationUtils $authenticationUtils Utilitat de Symfony per accedir a errors i últim usuari
     */
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Recuperem l'error d'autenticació de la sessió (null si no n'hi ha)
        $error = $authenticationUtils->getLastAuthenticationError();

        // Recuperem l'últim email introduït per pre-emplenar el camp del formulari
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }

    /**
     * Gestiona el logout de l'usuari.
     *
     * Aquest mètode mai s'executa realment — Symfony intercepta la petició
     * a /logout abans d'arribar aquí, gràcies a la configuració del firewall
     * a security.yaml (clau 'logout').
     * El LogicException és intencional: documenta aquest comportament
     * i alerta si per error el mètode fos cridat directament.
     */
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Symfony intercepta aquesta ruta automàticament.
        // Aquest codi mai s'executa — és un placeholder obligatori per Symfony.
        throw new \LogicException('Aquest mètode no hauria d\'executar-se mai. El firewall intercepta /logout automàticament.');
    }
}