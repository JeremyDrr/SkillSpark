<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class ResetPassword
{

    #[Assert\Length(min: 6, minMessage: "The password must be at least 6 characters long")]
    private ?string $password = null;

    #[Assert\EqualTo(propertyPath: 'password', message: "The passwords do not match")]
    private ?string $confirmPassword = null;

    private ?string $token = null;

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

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }
}
