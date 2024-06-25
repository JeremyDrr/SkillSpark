<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class PaginationService {

    private $entityClass;
    private $limit = 10;
    private $currentPage = 1;
    private $manager;
    private $twig;
    private $route;
    private $templatePath;
    private $additionalParams = [];
    private $filters = [];
    private $paramName = 'page';

    public function __construct(EntityManagerInterface $manager, Environment $twig, RequestStack $requestStack, $templatePath)
    {
        $this->route = $requestStack->getCurrentRequest()->attributes->get("_route");
        $this->manager = $manager;
        $this->twig = $twig;
        $this->templatePath = $templatePath;
    }

    public function display() {
        $this->twig->display($this->templatePath, [
            'page' => $this->currentPage,
            'pages' => $this->getPages(),
            'route' => $this->route,
            'data' => $this->getData(),
            'additionalParams' => $this->additionalParams,
            'paramName' => $this->paramName
        ]);
    }

    public function getPages() {
        if (empty($this->entityClass)) {
            throw new \Exception("You did not specify the entity on which you want to paginate! Use the setEntityClass() method of your PaginationService object");
        }

        $total = $this->manager->getRepository($this->entityClass)->count($this->filters);
        return ceil($total / $this->limit);
    }

    public function getData() {
        if (empty($this->entityClass)) {
            throw new \Exception("You did not specify the entity on which you want to paginate! Use the setEntityClass() method of your PaginationService object");
        }
        $offset = ($this->currentPage - 1) * $this->limit;
        return $this->manager->getRepository($this->entityClass)->findBy($this->filters, [], $this->limit, $offset);
    }

    public function setEntityClass($entityClass): self {
        $this->entityClass = $entityClass;
        return $this;
    }

    public function setLimit(int $limit): self {
        $this->limit = $limit;
        return $this;
    }

    public function setPage(int $currentPage): self {
        $this->currentPage = $currentPage;
        return $this;
    }

    public function setRoute($route): self {
        $this->route = $route;
        return $this;
    }

    public function setTemplatePath($templatePath): self {
        $this->templatePath = $templatePath;
        return $this;
    }

    public function setAdditionalParams(array $params): self {
        $this->additionalParams = $params;
        return $this;
    }

    public function setFilters(array $filters): self {
        $this->filters = $filters;
        return $this;
    }

    public function setParamName(string $paramName): self {
        $this->paramName = $paramName;
        return $this;
    }
}
