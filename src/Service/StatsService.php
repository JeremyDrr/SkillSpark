<?php


namespace App\Service;


use App\Entity\Article;
use App\Entity\Course;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;

class StatsService
{

    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {

        $this->manager = $manager;
    }

    public function getStats(){
        $users      = $this->getUsersCount();
        $courses   = $this->getCoursesCount();
        $activecourses = $this->getActiveCoursesCount();

        return compact('users', 'courses', 'activecourses');
    }

    public function getUsersCount() {
        return $this->manager->createQuery('SELECT COUNT(u) FROM App\Entity\User u')->getSingleScalarResult();
    }

    public function getCoursesCount() {
        return $this->manager->createQuery('SELECT COUNT(a) FROM App\Entity\Course a')->getSingleScalarResult();
    }

    public function getActiveCoursesCount(){
        $courses = $this->manager->getRepository(Course::class)->findAll();
        $amount = 0;
        foreach($courses as $course){
            if($course->isActive())
                $amount++;
        }
        return $amount;
    }


    public function getArticlesStats($direction) {
        return $this->manager->createQuery(
            'SELECT a.views as views, a.title, a.id, COUNT(c.id) AS comments, u.firstName, u.lastName, u.picture
                FROM App\Entity\Article a
                JOIN a.author u 
                JOIN a.comments c
                GROUP BY a
                ORDER BY views ' . $direction
        )
            ->setMaxResults(5)
            ->getResult();
    }

}