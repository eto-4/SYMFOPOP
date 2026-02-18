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
        $faker = Factory::create();
        $users = [];

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
            $product->setName($faker->words(3, true));
            $product->setDescription($faker->paragraph());
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
