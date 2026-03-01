<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Fixtures de dades de prova per a SymfoPop.
 *
 * Genera dades realistes per a l'entorn de desenvolupament:
 * - 5 usuaris amb emails únics i contrasenyes hashejades
 * - 20 productes amb títols, descripcions, preus i imatges
 *
 * Totes les contrasenyes dels usuaris de prova són: "password"
 *
 * Per carregar les fixtures:
 *   php bin/console doctrine:fixtures:load
 *
 * AVÍS: aquesta comanda esborra totes les dades existents abans de carregar-ne de noves.
 */
class AppFixtures extends Fixture
{
    /**
     * Injectem el servei de hashejat de contrasenyes via constructor.
     * Necessari per no desar les contrasenyes en text pla a la base de dades.
     */
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    /**
     * Punt d'entrada de les fixtures. Crea i persisteix totes les dades de prova.
     *
     * L'ordre és important: primer es creen els usuaris, després els productes,
     * ja que cada producte necessita un owner (relació ManyToOne → User).
     *
     * @param ObjectManager $manager Gestor d'entitats de Doctrine
     */
    public function load(ObjectManager $manager): void
    {
        // Inicialitzem Faker en català per generar noms i emails realistes
        $faker = Factory::create('ca_ES');

        // Array temporal per guardar els usuaris creats i assignar-los com a owners
        $users = [];

        // ── Títols de productes predefinits ───────────────────────────────────
        // S'utilitzen títols reals per fer les dades de prova més llegibles i creïbles
        $titles = [
            "Taula de menjador extensible",
            "Cadira ergonòmica de treball",
            "Llum ambiental LED regulable",
            "Sofà modular de tela prèmium",
            "Llibreria oberta de fusta natural",
            "Escriptori minimalista amb calaixos",
            "Armari d'emmagatzematge amb portes corredisses",
            "Fanal decoratiu de vidre i metall",
            "Matalàs ortopèdic de doble fermesa",
            "Cadira de disseny escandinau",
            "Tauleta auxiliar rodona amb emmagatzematge",
            "Làmpada de peu amb dímer",
            "Estanteria flotant de paret",
            "Banc de fusta multifunció",
            "Escriptori plegable compacte",
            "Coixí decoratiu amb teixit natural",
            "Mirall de paret amb llum integrada",
            "Rellotge de paret modern i minimalista",
            "Taula de cafè amb estructura metàl·lica",
            "Sofà llit amb sistema d'emmagatzematge",
        ];

        // ── Fragments de descripció predefinits ───────────────────────────────
        // Cada producte combinarà 3 fragments aleatoris per generar descripcions variades
        $descriptions = [
            "Aquest producte combina funcionalitat i estil per transformar qualsevol espai de la teva llar.",
            "Fabricat amb materials resistents i sostenibles, ideal per a un ús prolongat.",
            "Disseny innovador que s'adapta perfectament a interiors moderns i clàssics.",
            "Perfecte per a relaxar-se després d'un dia llarg de treball o estudis.",
            "La seva estructura lleugera, però robusta garanteix durabilitat i comoditat.",
            "Màxim confort gràcies a la combinació de materials suaus i ferms.",
            "Elegant i pràctic, amb detalls que aporten un toc sofisticat a l'espai.",
            "Fàcil de muntar i mantenir net, pensat per a la vida quotidiana.",
            "Un element decoratiu que destaca pel seu disseny minimalista i refinat.",
            "Ideal per a qualsevol estança, aportant llum i calidesa alhora.",
            "Funcionalitat i estètica en un sol producte, pensat per a l'ús diari.",
            "Compacte i versàtil, perfecte per a espais petits i apartaments moderns.",
            "Combina elegància i practicitat amb un acabat de qualitat superior.",
            "Material hipoal·lèrgic i agradable al tacte, perfecte per a famílies.",
            "Una peça única que aporta personalitat i estil a qualsevol habitació.",
            "Disseny cuidat fins al més mínim detall per a un resultat impecable.",
            "Ideal per a organitzar i decorar sense renunciar a l'espai.",
            "Resistent al pas del temps, combinant qualitat i bellesa natural.",
            "Perfecte per a crear un ambient càlid i acollidor a la llar.",
            "Un producte que combina practicitat, durabilitat i un toc de luxe.",
        ];

        // ── Creació d'usuaris ─────────────────────────────────────────────────
        for ($i = 0; $i < 5; $i++) {
            $user = new User();

            // Email únic generat per Faker — unique() garanteix que no es repeteixi
            $user->setEmail($faker->unique()->safeEmail());
            $user->setName($faker->name());

            // Hashegem la contrasenya "password" — mai es desa en text pla
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, 'password')
            );

            $manager->persist($user);

            // Guardem la referència per assignar-los com a owners dels productes
            $users[] = $user;
        }

        // ── Creació de productes ──────────────────────────────────────────────
        for ($i = 0; $i < 20; $i++) {
            $product = new Product();

            // Títol aleatori de la llista predefinida
            $product->setName($titles[array_rand($titles)]);

            // Descripció composta per 3 fragments aleatoris units en un paràgraf
            $product->setDescription(
                implode(' ', array_map(
                    fn() => $descriptions[array_rand($descriptions)],
                    range(1, 3)
                ))
            );

            // Preu aleatori entre 10 i 500 euros amb 2 decimals
            $product->setPrice($faker->randomFloat(2, 10, 500));

            $product->setCreatedAt(new \DateTimeImmutable());
            $product->setUpdatedAt(new \DateTimeImmutable());

            // Imatge de Picsum amb seed UUID — garanteix imatges diverses i consistents
            // Format: https://picsum.photos/seed/{seed}/amplada/alçada
            $product->setImage('https://picsum.photos/seed/' . $faker->uuid() . '/600/400');

            // Assignem un owner aleatori dels 5 usuaris creats anteriorment
            $product->setOwner($users[array_rand($users)]);

            $manager->persist($product);
        }

        // Executem totes les insercions en una sola transacció a la base de dades
        $manager->flush();
    }
}