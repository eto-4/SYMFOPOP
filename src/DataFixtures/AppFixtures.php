<?php
namespace App\DataFixtures;

use Faker\Factory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use App\Entity\Product;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('ca_ES');
        $users = [];

        $titles = [
            "Taula de menjador extensible",
            "Cadira ergonòmica de treball",
            "Llum ambiental LED regulable",
            "Sofà modular de tela premium",
            "Llibreria oberta de fusta natural",
            "Escriptori minimalista amb calaixos",
            "Armari d’emmagatzematge amb portes corredisses",
            "Fanal decoratiu de vidre i metall",
            "Matalàs ortopèdic de doble fermesa",
            "Silla de disseny escandinau",
            "Tauleta auxiliar rodona amb emmagatzematge",
            "Làmpada de peu amb dimmer",
            "Estanteria flotant de paret",
            "Banc de fusta multifunció",
            "Escriptori plegable compacte",
            "Coixí decoratiu amb teixit natural",
            "Mirall de paret amb llum integrada",
            "Rellotge de paret modern i minimalista",
            "Taula de cafè amb estructura metàl·lica",
            "Sofà llit amb sistema d’emmagatzematge"
        ];

        $descriptions = [
            "Aquest producte combina funcionalitat i estil per transformar qualsevol espai de la teva llar.",
            "Fabricat amb materials resistents i sostenibles, ideal per a un ús prolongat.",
            "Disseny innovador que s’adapta perfectament a interiors moderns i clàssics.",
            "Perfecte per a relaxar-se després d’un dia llarg de treball o estudis.",
            "La seva estructura lleugera però robusta garanteix durabilitat i comoditat.",
            "Màxim confort gràcies a la combinació de materials suaus i ferms.",
            "Elegant i pràctic, amb detalls que aporten un toc sofisticat a l’espai.",
            "Fàcil de muntar i mantenir net, pensat per a la vida quotidiana.",
            "Un element decoratiu que destaca pel seu disseny minimalista i refinat.",
            "Ideal per a qualsevol estança, aportant llum i calidesa alhora.",
            "Funcionalitat i estètica en un sol producte, pensat per a l’ús diari.",
            "Compacte i versàtil, perfecte per a espais petits i apartaments moderns.",
            "Combina elegància i practicitat amb un acabat de qualitat superior.",
            "Material hipoal·lergènic i agradable al tacte, perfecte per a famílies.",
            "Una peça única que aporta personalitat i estil a qualsevol habitació.",
            "Diseño cuidat fins al més mínim detall per a un resultat impecable.",
            "Ideal per a organitzar i decorar sense renunciar a l’espai.",
            "Resistent al pas del temps, combinant qualitat i bellesa natural.",
            "Perfecte per a crear un ambient càlid i acollidor a la llar.",
            "Un producte que combina practicitat, durabilitat i un toc de luxe."
        ];

        // crear 5 usuaris
        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setEmail( $faker->unique()->safeEmail());
            $user->setName( $faker->name());
            $user->setPassword( 
                $this->passwordHasher->hashPassword($user, 'password')
            );

            $manager->persist($user);
            $users[] = $user;
        }

        // Crear 20 productes
        for ($i = 0; $i < 20; $i++) {
            $product = new Product();
            $product->setName($titles[array_rand($titles)]);
            $product->setDescription(
                implode(' ', array_map(fn() => $descriptions[array_rand($descriptions)], range(1,3)))
            );
            $product->setPrice($faker->randomFloat(2, 10, 500));
            $product->setCreatedAt(new \DateTimeImmutable());
            $product->setUpdatedAt(new \DateTimeImmutable());

            // Imatge amb picsum
            $product->setImage('https://picsum.photos/seed/'.$faker->uuid().'/600/400');

            $product->setOwner($users[array_rand($users)]);

            $manager->persist($product);
        }

        $manager->flush();
    }
}
