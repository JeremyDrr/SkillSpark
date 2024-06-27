<?php

namespace App\EventSubscriber;

use App\Entity\Course;
use App\Service\StripeService;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class CourseSubscriber implements EventSubscriber
{
    private $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Course) {
            $this->handleStripeProductAndPrice($entity);

            $em = $args->getEntityManager();
            $em->persist($entity);
        }
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Course) {
            $this->handleStripeProductAndPrice($entity);

            $em = $args->getEntityManager();
            $em->persist($entity, $args);
        }
    }

    public function handleStripeProductAndPrice(Course $course, ?LifecycleEventArgs $args = null)
    {
        if (empty($course->getStripeProductId())) {
            $name = $course->getTitle();
            $description = $course->getIntroduction();
            $amount = $course->getPrice() * 100; // Convert to cents
            $currency = 'eur';
            $active = $course->isActive();

            $stripeData = $this->stripeService->createProductAndPrice($name, $description, $amount, $currency, $active);
            $course->setStripeProductId($stripeData['product']->id);
            $course->setStripePriceId($stripeData['price']->id);

            // In case of preUpdate, tell Doctrine to update the changes
            if ($args) {
                $em = $args->getObjectManager();
                $classMetadata = $em->getClassMetadata(get_class($course));
                $em->getUnitOfWork()->recomputeSingleEntityChangeSet($classMetadata, $course);
            }
        }
    }
}
