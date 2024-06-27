<?php

namespace App\Entity;

use App\Repository\CourseRepository;
use App\Service\StripeService;
use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CourseRepository::class)]
#[ORM\HasLifecycleCallbacks()]
#[UniqueEntity(fields: 'title', message: 'A course is already named like this')]
class Course
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $introduction = null;

    #[ORM\Column(length: 255)]
    private ?string $thumbnail = null;

    #[ORM\ManyToOne(inversedBy: 'courses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $instructor = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\ManyToOne(inversedBy: 'courses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Level $level = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripeProductId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripePriceId = null;

    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Chapter::class, orphanRemoval: true)]
    private Collection $chapters;

    #[ORM\Column(nullable: true)]
    private ?bool $active = null;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'coursesFollowed')]
    private Collection $students;

    #[ORM\ManyToOne(inversedBy: 'Courses')]
    private ?Category $category = null;
    private StripeService $stripeService;


    public function __construct()
    {
        $this->chapters = new ArrayCollection();
        $this->students = new ArrayCollection();

    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function PrePersist(): void
    {

        if(empty($this->slug)){
            $slugify = new Slugify();
            $this->slug = $slugify->slugify($this->title.' '.$this->instructor->getFullName());
        }

        if(count($this->getChapters()) == 0){
            $this->active = false;
        }
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function updateStripeProduct(LifecycleEventArgs $args)
    {
        $entityManager = $args->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        if ($this->stripeProductId) {
            // Update existing Stripe product
            $this->stripeClient->products->update(
                $this->stripeProductId,
                [
                    'name' => $this->title,
                    'description' => $this->description,
                ]
            );
        } else {
            // Create new Stripe product
            $product = $this->stripeClient->products->create([
                'name' => $this->title,
                'description' => $this->description,
            ]);
            $this->stripeProductId = $product->id;
        }

        if ($this->stripePriceId) {
            // Update existing Stripe price
            // Note: Stripe prices are immutable, so you need to create a new price
            $this->stripeClient->prices->update(
                $this->stripePriceId,
                ['active' => false]
            );
            $price = $this->stripeClient->prices->create([
                'unit_amount' => $this->price * 100, // Stripe expects the amount in cents
                'currency' => 'usd',
                'product' => $this->stripeProductId,
            ]);
            $this->stripePriceId = $price->id;
        } else {
            // Create new Stripe price
            $price = $this->stripeClient->prices->create([
                'unit_amount' => $this->price * 100,
                'currency' => 'usd',
                'product' => $this->stripeProductId,
            ]);
            $this->stripePriceId = $price->id;
        }

        $unitOfWork->computeChangeSet($entityManager->getClassMetadata(get_class($this)), $this);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getIntroduction(): ?string
    {
        return $this->introduction;
    }

    public function setIntroduction(string $introduction): static
    {
        $this->introduction = $introduction;

        return $this;
    }

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(string $thumbnail): static
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    public function getInstructor(): ?User
    {
        return $this->instructor;
    }

    public function setInstructor(?User $instructor): static
    {
        $this->instructor = $instructor;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }


    public function getLevel(): ?Level
    {
        return $this->level;
    }

    public function setLevel(?Level $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getStripeProductId(): ?string
    {
        return $this->stripeProductId;
    }

    public function setStripeProductId(string $stripeProductId): static
    {
        $this->stripeProductId = $stripeProductId;

        return $this;
    }

    public function getStripePriceId(): ?string
    {
        return $this->stripePriceId;
    }

    public function setStripePriceId(string $stripePriceId): static
    {
        $this->stripePriceId = $stripePriceId;

        return $this;
    }

    /**
     * @return Collection<int, Chapter>
     */
    public function getChapters(): Collection
    {
        return $this->chapters;
    }

    public function addChapter(Chapter $chapter): static
    {
        if (!$this->chapters->contains($chapter)) {
            $this->chapters->add($chapter);
            $chapter->setCourse($this);
        }

        return $this;
    }

    public function removeChapter(Chapter $chapter): static
    {
        if ($this->chapters->removeElement($chapter)) {
            // set the owning side to null (unless already changed)
            if ($chapter->getCourse() === $this) {
                $chapter->setCourse(null);
            }
        }

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getStudents(): Collection
    {
        return $this->students;
    }

    public function addStudent(User $student): static
    {
        if (!$this->students->contains($student)) {
            $this->students->add($student);
            $student->addCoursesFollowed($this);
        }

        return $this;
    }

    public function removeStudent(User $student): static
    {
        if ($this->students->removeElement($student)) {
            $student->removeCoursesFollowed($this);
        }

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }


}
