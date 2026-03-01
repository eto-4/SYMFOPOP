<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/product')]
final class ProductController extends AbstractController
{
    /**
     * Llistat públic de tots els productes, ordenats per data de creació (més recents primer).
     * Accessible i Visible per a qualsevol usuari (autenticat o no).
     */
    #[Route('', name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('product/index.html.twig', [
            'products'        => $products,
            'title'           => 'Tots els productes',
            'show_actions'    => false,
            'show_new_button' => false,
            'empty_message'   => 'Encara no hi ha productes publicats.',
        ]);
    }

    /**
     * Detall d'un producte.
     * Symfony resol automàticament el Product pel {id} (ParamConverter).
     * Si no existeix, llança un 404 automàticament.
     * Si el parametre de la url NO es un digit, no entra com {id}.
     */
    #[Route('/{id}', name: 'app_product_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    /**
     * Crear un nou producte.
     * Requereix usuari autenticat (ROLE_USER).
     * Assigna automàticament l'owner i les dates.
     */
    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si no s'ha especificat imatge, generem una amb Picsum
            if (!$product->getImage()) {
                $product->setImage('https://picsum.photos/seed/' . uniqid() . '/640/480');
            }

            // Assignem l'usuari autenticat com a propietari del producte
            $product->setOwner($this->getUser());

            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Producte creat correctament!');

            return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
        }

        return $this->render('product/new.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * Editar un producte existent.
     * Requereix usuari autenticat i que sigui el propietari.
     */
    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Product $product, EntityManagerInterface $em): Response
    {
        // Validem que l'usuari actual sigui el propietari del producte
        if ($product->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No tens permís per editar aquest producte.');
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Actualitzem la data de modificació
            $product->setUpdatedAt(new \DateTimeImmutable());

            // Si s'ha buidat la imatge, generem una nova amb Picsum
            if (!$product->getImage()) {
                $product->setImage('https://picsum.photos/seed/' . uniqid() . '/640/480');
            }

            $em->flush();

            $this->addFlash('success', 'Producte actualitzat correctament!');
            
            return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
        }

        return $this->render('product/edit.html.twig', [
            'form'    => $form,
            'product' => $product,
        ]);
    }

    /**
     * Eliminar un producte.
     * Requereix usuari autenticat, que sigui el propietari i token CSRF vàlid.
     */
    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, Product $product, EntityManagerInterface $em): Response
    {
        // Validem que l'usuari actual sigui el propietari del producte
        if ($product->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No tens permís per eliminar aquest producte.');
        }

        // Validem el token CSRF per evitar atacs de cross-site request forgery
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($product);
            $em->flush();
            $this->addFlash('success', 'Producte eliminat correctament.');
        } else {
            $this->addFlash('error', 'Token CSRF invàlid. No s\'ha pogut eliminar el producte.');
        }

        return $this->redirectToRoute('app_product_index');
    }

    /**
     * "Els meus productes": llistat personal de l'usuari autenticat.
     * Reutilitza la mateixa vista index.html.twig amb paràmetres addicionals.
     * IMPORTANT: aquesta ruta ha d'anar ABANS de /{id} per evitar conflictes.
     */
    #[Route('/my', name: 'app_my_products', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function myProducts(ProductRepository $productRepository): Response
    {
        // Filtrem els productes de l'usuari autenticat, ordenats per data
        $products = $productRepository->findBy(
            ['owner' => $this->getUser()],
            ['createdAt' => 'DESC']
        );

        return $this->render('product/index.html.twig', [
            'products'        => $products,
            'title'           => 'Els meus productes',
            'show_actions'    => true,   // Mostra botons Editar/Esborrar
            'show_new_button' => true,   // Mostra botó "Nou producte"
            'empty_message'   => 'Encara no has publicat cap producte.',
        ]);
    }
}