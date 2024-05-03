<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Cocur\Slugify\Slugify;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use function Symfony\Component\Clock\now;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: "email", message: "This email address is already used by another user")]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotNull(message: "You must enter your first name")]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotNull(message: "You must enter your family name")]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotNull(message: "You must enter a valid email address")]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(min: 6, minMessage: "Your password must be at least 6 characters long")]
    private ?string $password = null;

    #[Assert\EqualTo(propertyPath: "password", message: "The passwords are not matching")]
    private ?string $confirmPassword = null;

    #[ORM\Column(length: 255)]
    private ?string $picture = null;

    #[ORM\ManyToMany(targetEntity: Role::class, mappedBy: 'users')]
    private Collection $roles;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\OneToMany(mappedBy: 'instructor', targetEntity: Course::class, orphanRemoval: true)]
    private Collection $courses;

    #[ORM\Column(length: 255)]
    private ?string $introduction = null;

    #[ORM\Column(nullable: true)]
    private ?bool $verified = null;

    #[ORM\ManyToMany(targetEntity: Course::class, inversedBy: 'students')]
    private Collection $coursesFollowed;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->courses = new ArrayCollection();
        $this->coursesFollowed = new ArrayCollection();
    }

    /**
     * @throws \DateMalformedStringException
     */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function initialise()
    {

        if(empty($this->picture)){
            $this->picture = '/assets/img/user.png';
        }

        if(empty($this->introduction)) {
            $this->introduction = "Hi, I joined SkillSpark on the ". (new DateTime())->format('d-m-Y');
        }
        if(empty($this->slug)){
            $slugify = new Slugify();
            $this->slug = $slugify->slugify($this->firstName.' '.$this->lastName);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFullName(): ?string
    {
        return "{$this->firstName} {$this->lastName}";
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getConfirmPassword(): ?string
    {
        return $this->confirmPassword;
    }

    public function setConfirmPassword(string $confirmPassword): static
    {
        $this->confirmPassword = $confirmPassword;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(string $picture): static
    {
        $this->picture = $picture;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles->map(function ($role){
            return $role->getName();
        })->toArray();

        $roles[] = 'ROLE_USER';

        return $roles;
    }

    public function eraseCredentials(): void
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function addRole(Role $role): static
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
            $role->addUser($this);
        }

        return $this;
    }

    public function removeRole(Role $role): static
    {
        if ($this->roles->removeElement($role)) {
            $role->removeUser($this);
        }

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

    /**
     * @return Collection<int, Course>
     */
    public function getCourses(): Collection
    {
        return $this->courses;
    }

    public function addCourse(Course $course): static
    {
        if (!$this->courses->contains($course)) {
            $this->courses->add($course);
            $course->setInstructor($this);
        }

        return $this;
    }

    public function removeCourse(Course $course): static
    {
        if ($this->courses->removeElement($course)) {
            // set the owning side to null (unless already changed)
            if ($course->getInstructor() === $this) {
                $course->setInstructor(null);
            }
        }

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

    public function isVerified(): ?bool
    {
        return $this->verified;
    }

    public function setVerified(?bool $verified): static
    {
        $this->verified = $verified;

        return $this;
    }

    /**
     * @return Collection<int, Course>
     */
    public function getCoursesFollowed(): Collection
    {
        return $this->coursesFollowed;
    }

    public function addCoursesFollowed(Course $coursesFollowed): static
    {
        if (!$this->coursesFollowed->contains($coursesFollowed)) {
            $this->coursesFollowed->add($coursesFollowed);
        }

        return $this;
    }

    public function removeCoursesFollowed(Course $coursesFollowed): static
    {
        $this->coursesFollowed->removeElement($coursesFollowed);

        return $this;
    }
}
