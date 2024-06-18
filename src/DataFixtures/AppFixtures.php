<?php
namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Chapter;
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
            ->setPicture("https://avatars.githubusercontent.com/u/57372428?v=4")
            ->setIntroduction('Hey, I\'m the founder of SkillSpark! Never stop learning')
            ->setPassword($this->encoder->hashPassword($admin, 'password'))
            ->addRole($adminRole)
            ->setVerified(true);
        $manager->persist($admin);

// Handle users
        $users = [];
        for ($i = 1; $i <= 20; $i++) {
            $user = new User();

            $gender = $faker->randomElement(['male', 'female']);
            $picture = 'https://randomuser.me/api/portraits/';
            $pictureID = $faker->biasedNumberBetween(1, 99) . '.jpg';
            $picture .= ($gender == 'male' ? 'men/' : 'women/') . $pictureID;
            $user->setFirstName($faker->firstName($gender))
                ->setLastName($faker->lastName)
                ->setEmail($faker->unique()->email)
                ->setPassword($this->encoder->hashPassword($user, 'password'))
                ->setPicture($picture)
                ->setIntroduction($faker->realTextBetween(100, 200))
                ->setVerified($faker->boolean);

            $manager->persist($user);
            $users[] = $user;
        }

// Flush users and levels before creating courses
        $manager->flush();

// Handle categories
        $categoryList = ['Coding', 'Cooking', 'Gardening', 'Music', 'Art & Design', 'Writing', 'Fitness & Health', 'Languages'];
        $categories = [];
        foreach ($categoryList as $categoryItem) {
            $category = new Category();
            $category->setName($categoryItem)
                ->setColour($faker->hexColor);

            $categories[] = $category;
            $manager->persist($category);
        }

// Flush categories before creating courses
        $manager->flush();

// Handle courses
        for ($i = 0; $i < 10; $i++) {
            $course = new Course();
            $course->setTitle($faker->sentence)
                ->setIntroduction($faker->realText)
                ->setInstructor($users[mt_rand(0, count($users) - 1)])
                ->setThumbnail('https://picsum.photos/210/118?random=' . mt_rand(1, 55000))
                ->setPrice($faker->randomFloat(2, 1, 500))
                ->setActive(true)
                ->setLevel($levels[mt_rand(0, count($levels) - 1)])
                ->setCategory($faker->randomElement($categories));

// Handle chapters
            for ($j = 1; $j <= $faker->numberBetween(1, 15); $j++) {
                $chapter = new Chapter();
                $chapter->setCourse($course)
                    ->setTitle($faker->sentence)
                    ->setContent($faker->realTextBetween(150, 500));
                $course->addChapter($chapter);
                $manager->persist($chapter);
            }

// Handle students
            for ($k = 0; $k <= $faker->numberBetween(0, 5); $k++) {
                $course->addStudent($faker->randomElement($users));
            }

            $manager->persist($course);
        }

        $manager->flush();
    }
}
