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

/**
 * Controlador principal de productes.
 *
 * Gestiona totes les operacions CRUD sobre l'entitat Product:
 * - Llistat públic i llistat personal
 * - Detall d'un producte
 * - Creació, edició i esborrat (només usuaris autenticats i propietaris)
 *
 * Totes les rutes amb {id} tenen el requisit '\d+' per evitar
 * conflictes amb rutes literals com /new o /my.
 */
#[Route('/product')]
final class ProductController extends AbstractController
{
    /**
     * Llistat públic de tots els productes, ordenats per data de creació (més recents primer).
     * Accessible i visible per a qualsevol usuari (autenticat o no).
     *
     * Passa paràmetres a la vista per desactivar els botons d'acció,
     * ja que aquest llistat és públic i no pertany a cap usuari concret.
     */
    #[Route('', name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('product/index.html.twig', [
            'products'        => $products,
            'title'           => 'Tots els productes',
            'show_actions'    => false, // No mostrem botons d'edició/esborrat en el llistat públic
            'show_new_button' => false,
            'empty_message'   => 'Encara no hi ha productes publicats.',
        ]);
    }

    /**
     * Mostra el detall complet d'un producte.
     *
     * Symfony resol automàticament l'objecte Product a partir del {id}
     * gràcies al ParamConverter. Si el producte no existeix, llança un 404.
     * El requirement '\d+' garanteix que /product/new no col·lisioni amb aquesta ruta.
     */
    #[Route('/{id}', name: 'app_product_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    /**
     * Crea un nou producte.
     *
     * Només accessible per a usuaris autenticats (#[IsGranted]).
     * L'owner i les dates s'assignen automàticament al controlador,
     * mai provenen del formulari (seguretat).
     * Si l'usuari no especifica imatge, se'n genera una automàticament amb Picsum.
     */
    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si no s'ha especificat imatge, generem una amb Picsum usant un seed únic
            if (!$product->getImage()) {
                $product->setImage('https://picsum.photos/seed/' . uniqid() . '/640/480');
            }

            // Assignem l'usuari autenticat com a propietari — mai ve del formulari
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
     * Edita un producte existent.
     *
     * Requereix autenticació i que l'usuari sigui el propietari del producte.
     * Si no ho és, es llança una AccessDeniedException (HTTP 403).
     * S'actualitza updatedAt en cada edició exitosa.
     */
    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Product $product, EntityManagerInterface $em): Response
    {
        // Comprovem que l'usuari actual sigui el propietari — si no, accés denegat (403)
        if ($product->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No tens permís per editar aquest producte.');
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Actualitzem la data de modificació manualment en cada edició
            $product->setUpdatedAt(new \DateTimeImmutable());

            // Si l'usuari ha buidat el camp imatge, en generem una nova amb Picsum
            if (!$product->getImage()) {
                $product->setImage('https://picsum.photos/seed/' . uniqid() . '/640/480');
            }

            // Doctrine detecta els canvis automàticament (Unit of Work), flush és suficient
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
     * Esborra un producte.
     *
     * Requereix autenticació, que l'usuari sigui el propietari i un token CSRF vàlid.
     * El token CSRF es valida per prevenir atacs de Cross-Site Request Forgery:
     * un tercer no pot forçar l'esborrat enviant una petició des d'una altra web.
     * Només accepta mètode POST per evitar esborrats accidentals per GET.
     */
    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, Product $product, EntityManagerInterface $em): Response
    {
        // Comprovem que l'usuari actual sigui el propietari — si no, accés denegat (403)
        if ($product->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No tens permís per eliminar aquest producte.');
        }

        // Validem el token CSRF que s'ha enviat des del formulari ocult de la vista
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
     * Llistat personal de l'usuari autenticat ("Els meus productes").
     *
     * Reutilitza la mateixa vista index.html.twig que el llistat públic,
     * però amb paràmetres diferents: mostra botons d'acció i el botó de nou producte.
     * Aplica el principi DRY — una sola vista per a dos contextos diferents.
     *
     * IMPORTANT: aquesta ruta (/my) ha d'estar definida ABANS de /{id} al fitxer
     * per evitar que Symfony intenti resoldre "my" com un identificador numèric.
     * El requirement '\d+' a /{id} ja ho prevé, però és bona pràctica mantenir l'ordre.
     */
    #[Route('/my', name: 'app_my_products', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function myProducts(ProductRepository $productRepository): Response
    {
        // Filtrem únicament els productes de l'usuari autenticat, ordenats per data
        $products = $productRepository->findBy(
            ['owner' => $this->getUser()],
            ['createdAt' => 'DESC']
        );

        return $this->render('product/index.html.twig', [
            'products'        => $products,
            'title'           => 'Els meus productes',
            'show_actions'    => true,  // Activa botons Editar/Esborrar a cada targeta
            'show_new_button' => true,  // Mostra el botó "Nou producte" a la capçalera
            'empty_message'   => 'Encara no has publicat cap producte.',
        ]);
    }
}