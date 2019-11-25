<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RefreshToken", mappedBy="user", orphanRemoval=true)
     */
    private $refreshTokens;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Email", mappedBy="user", orphanRemoval=true)
     */
    private $emails;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PasswordRecovery", mappedBy="user", orphanRemoval=true)
     */
    private $passwordRecoveries;

    public function __construct()
    {
        $this->refreshTokens = new ArrayCollection();
        $this->emails = new ArrayCollection();
        $this->passwordRecoveries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return (string) $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|RefreshToken[]
     */
    public function getRefreshTokens(): Collection
    {
        return $this->refreshTokens;
    }

    public function addRefreshToken(RefreshToken $refreshToken): self
    {
        if (!$this->refreshTokens->contains($refreshToken)) {
            $this->refreshTokens[] = $refreshToken;
            $refreshToken->setUser($this);
        }

        return $this;
    }

    public function removeRefreshToken(RefreshToken $refreshToken): self
    {
        if ($this->refreshTokens->contains($refreshToken)) {
            $this->refreshTokens->removeElement($refreshToken);
            // set the owning side to null (unless already changed)
            if ($refreshToken->getUser() === $this) {
                $refreshToken->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Email[]
     */
    public function getEmails(): Collection
    {
        return $this->emails;
    }

    public function addEmail(Email $email): self
    {
        if (!$this->emails->contains($email)) {
            $this->emails[] = $email;
            $email->setUser($this);
        }

        return $this;
    }

    public function removeEmail(Email $email): self
    {
        if ($this->emails->contains($email)) {
            $this->emails->removeElement($email);
            // set the owning side to null (unless already changed)
            if ($email->getUser() === $this) {
                $email->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PasswordRecovery[]
     */
    public function getPasswordRecoveries(): Collection
    {
        return $this->passwordRecoveries;
    }

    public function addPasswordRecovery(PasswordRecovery $passwordRecovery): self
    {
        if (!$this->passwordRecoveries->contains($passwordRecovery)) {
            $this->passwordRecoveries[] = $passwordRecovery;
            $passwordRecovery->setUser($this);
        }

        return $this;
    }

    public function removePasswordRecovery(PasswordRecovery $passwordRecovery): self
    {
        if ($this->passwordRecoveries->contains($passwordRecovery)) {
            $this->passwordRecoveries->removeElement($passwordRecovery);
            // set the owning side to null (unless already changed)
            if ($passwordRecovery->getUser() === $this) {
                $passwordRecovery->setUser(null);
            }
        }

        return $this;
    }
}
