<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controlador del registre d'usuaris.
 *
 * Gestiona el formulari de registre, el hashejat de la contrasenya
 * i l'inici de sessió automàtic després d'un registre exitós.
 */
class RegistrationController extends AbstractController
{
    /**
     * Mostra i processa el formulari de registre.
     *
     * Flux del registre:
     * 1. Es crea un objecte User buit i es vincula al formulari.
     * 2. En enviar el formulari, es valida.
     * 3. La contrasenya en text pla es hasheja amb bcrypt/argon2 — mai es desa en clar.
     * 4. L'usuari es desa a la base de dades.
     * 5. Es fa login automàtic sense que l'usuari hagi de tornar a introduir credencials.
     *
     * @param UserPasswordHasherInterface $userPasswordHasher Servei per hashejar contrasenyes
     * @param Security                    $security           Servei per fer login programàtic
     * @param EntityManagerInterface      $entityManager      Gestor d'entitats de Doctrine
     */
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        Security $security,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Obtenim la contrasenya en text pla del camp del formulari
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // Hashegem la contrasenya — Symfony tria l'algorisme configurat a security.yaml
            // La contrasenya en text pla mai es desa a la base de dades
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            // Login automàtic: l'usuari queda autenticat immediatament després del registre
            // 'form_login' és el nom de l'autenticador | 'main' és el nom del firewall
            return $security->login($user, 'form_login', 'main');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}