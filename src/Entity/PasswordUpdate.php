<?php

namespace App\Entity;

use App\Repository\PasswordUpdateRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

class PasswordUpdate
{

    private ?string $oldPassword = null;

    #[Assert\NotEqualTo(propertyPath: "oldPassword", message: "The new password is identical as the current password")]
    #[Assert\Length(min: 6, minMessage: "The new password must be at least 6 characters long")]
    private ?string $newPassword = null;

    #[Assert\EqualTo(propertyPath: 'newPassword', message: "The passwords do not match")]
    private ?string $confirmPassword = null;


    public function getOldPassword(): ?string
    {
        return $this->oldPassword;
    }

    public function setOldPassword(string $oldPassword): static
    {
        $this->oldPassword = $oldPassword;

        return $this;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword(string $newPassword): static
    {
        $this->newPassword = $newPassword;

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
}
