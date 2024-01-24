<?php

namespace App\DataFixtures;

use App\Entity\Course;
use App\Entity\Level;
use App\Entity\Role;
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

        // Handle roles
        $adminRole = new Role();
        $adminRole->setName('ROLE_ADMIN');
        $manager->persist($adminRole);

        // Handle levels
        $levels = [];
        $beginner = new Level();
        $beginner->setName('Beginner');
        $levels[] = $beginner;
        $manager->persist($beginner);
        $intermediate = new Level();
        $intermediate->setName('Intermediate');
        $levels[] = $intermediate;
        $manager->persist($intermediate);
        $advanced = new Level();
        $advanced->setName('Advanced');
        $levels[] = $advanced;
        $manager->persist($advanced);

        // Handle admin account
        $admin = new User();
        $admin->setFirstName("Jeremy")
            ->setLastName("Durrieu")
            ->setEmail("jeremy@skillspark.com")
            ->setPicture("https://randomuser.me/api/portraits/men/52.jpg")
            ->setPassword($this->encoder->hashPassword($admin, 'password'))
            ->addRole($adminRole);
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

        // Handle courses
        for($i = 0; $i < 20; $i++){
            $course = new Course();
            $course->setTitle($faker->realTextBetween(5, 20))
                ->setIntroduction($faker->realText)
                ->setInstructor($users[mt_rand(0, count($users) -1)])
                ->setThumbnail('https://place-hold.it/200x100')
                ->setPrice($faker->randomFloat(2, 1, 2000))
                ->setLevel($levels[mt_rand(0, count($levels) -1)]);
            $manager->persist($course);
        }

        $manager->flush();
    }
}
