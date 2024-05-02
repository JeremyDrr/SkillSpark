<?php

namespace App\Service;

use App\Entity\Article;
use App\Entity\User;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class PaginationService{

    private $entityClass;
    private $limit = 10;
    private $currentPage = 1;
    private $manager;
    private $twig;
    private $route;
    private $templatePath;

    public function __construct(EntityManagerInterface $manager, Environment $twig, RequestStack $request, $templatePath)
    {
        $this->route = $request->getCurrentRequest()->attributes->get("_route");
        $this->manager = $manager;
        $this->twig = $twig;
        $this->templatePath = $templatePath;
    }

    public function display(){

        if(!empty($this->options)){
            $this->twig->display($this->templatePath, [
                'page' => $this->currentPage,
                'pages' => $this->getPages(),
                'route' => $this->route,
            ]);
        }else{
            $this->twig->display($this->templatePath, [
                'page' => $this->currentPage,
                'pages' => $this->getPages(),
                'route' => $this->route,
            ]);
        }


    }

    public function getPages(){

        if(empty($this->entityClass)){
            throw new \Exception("You did not specify the entity on which you want to paginate! Use the setEntityClass() method of your PaginationService object");
        }
        $total = count(
            $this->manager->getRepository($this->entityClass)
                ->findAll());

        $pages = ceil($total/$this->limit);

        return $pages;
    }

    public function getData(){

        if(empty($this->entityClass)){
            throw new \Exception("You did not specify the entity on which you want to paginate! Use the setEntityClass() method of your PaginationService object");
        }

        $offset = $this->currentPage * $this->limit - $this->limit;

        return $this->manager->getRepository($this->entityClass)
            ->findBy([], [], $this->limit, $offset);


    }

    /**
     * @return mixed
     */
    public function getEntityClass() : string
    {
        return $this->entityClass;
    }

    /**
     * @param mixed $entityClass
     * @return PaginationService
     */
    public function setEntityClass($entityClass) : self
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    /**
     * @return int
     */
    public function getLimit() : int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     * @return PaginationService
     */
    public function setLimit(int $limit) : self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return int
     */
    public function getPage() : int
    {
        return $this->currentPage;
    }

    /**
     * @param int $currentPage
     * @return PaginationService
     */
    public function setPage(int $currentPage) : self
    {
        $this->currentPage = $currentPage;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRoute() : string
    {
        return $this->route;
    }

    /**
     * @param mixed $route
     * @return PaginationService
     */
    public function setRoute($route) : self
    {
        $this->route = $route;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTemplatePath() : string
    {
        return $this->templatePath;
    }

    /**
     * @param mixed $templatePath
     * @return PaginationService
     */
    public function setTemplatePath($templatePath) : self
    {
        $this->templatePath = $templatePath;

        return $this;
    }

}