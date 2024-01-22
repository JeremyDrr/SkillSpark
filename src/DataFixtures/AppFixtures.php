<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private $encoder;

    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager): void
    {

        $faker = Factory::create("en_GB");

        // Handle admin account
        $admin = new User();
        $admin->setFirstName("Jeremy")
            ->setLastName("Durrieu")
            ->setEmail("jeremy@skillspark.com")
            ->setPicture("https://randomuser.me/api/portraits/men/52.jpg")
            ->setPassword($this->encoder->hashPassword($admin, 'password'));
        $manager->persist($admin);

        // Handle users
        $users = [];

        for($i = 1; $i <= 50; $i++){
            $user = new User();
            $gender = $faker->randomElement(['male', 'female']);
            $picture = 'https://randomuser.me/api/portraits/';
            $pictureID = $faker->biasedNumberBetween(1, 99) . '.jpg';
            $picture .= ($gender == 'male' ? 'men/' : 'women/') . $pictureID;
            $user->setFirstName($faker->firstName($gender))
                ->setLastName($faker->lastName)
                ->setEmail($faker->unique()->email)
                ->setPassword($this->encoder->hashPassword($user, 'password'))
                ->setPicture($picture);
            $manager->persist($user);
            $users[] = $user;
        }

        $manager->flush();
    }
}
