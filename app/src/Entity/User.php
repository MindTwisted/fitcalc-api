<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(indexes={@ORM\Index(name="user_name_search_index", columns={"name"})})
 * @UniqueEntity("email")
 */
class User implements UserInterface
{
    use TimestampableEntity;

    const ROLE_USER = 'ROLE_USER';
    const ROLE_APP_USER = 'ROLE_APP_USER';
    const ROLE_ADMIN = 'ROLE_ADMIN';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     *
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $emailConfirmedAt;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(min="8")
     * @Assert\Regex(
     *     pattern="/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/",
     *     message="Password should contains minimum eight characters, at least one letter, one number and one special character."
     * )
     */
    private $plainPassword;

    /**
     * @var string The hashed password
     *
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RefreshToken", mappedBy="user", orphanRemoval=true, cascade={"persist"})
     */
    private $refreshTokens;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EmailConfirmation", mappedBy="user", orphanRemoval=true, cascade={"persist"})
     */
    private $emailConfirmations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PasswordRecovery", mappedBy="user", orphanRemoval=true)
     */
    private $passwordRecoveries;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->refreshTokens = new ArrayCollection();
        $this->emailConfirmations = new ArrayCollection();
        $this->passwordRecoveries = new ArrayCollection();
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return in_array(self::ROLE_ADMIN, $this->getRoles());
    }

    /**
     * @return bool
     */
    public function isAppUser(): bool
    {
        return in_array(self::ROLE_APP_USER, $this->getRoles());
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername()
    {
        return $this->getEmail();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return (string) $this->name;
    }

    /**
     * @param string $name
     *
     * @return User
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return (string) $this->email;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return DateTimeInterface
     */
    public function getEmailConfirmedAt(): ?DateTimeInterface
    {
        return $this->emailConfirmedAt;
    }

    /**
     * @param DateTimeInterface $emailConfirmedAt
     *
     * @return User
     */
    public function setEmailConfirmedAt(DateTimeInterface $emailConfirmedAt): self
    {
        $this->emailConfirmedAt = $emailConfirmedAt;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = self::ROLE_USER;

        return array_unique($roles);
    }

    /**
     * @param array $roles
     *
     * @return $this
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     */
    public function setPlainPassword(string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    /**
     * @param string $password
     *
     * @return $this
     */
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

    /**
     * @param RefreshToken $refreshToken
     *
     * @return $this
     */
    public function addRefreshToken(RefreshToken $refreshToken): self
    {
        if (!$this->refreshTokens->contains($refreshToken)) {
            $this->refreshTokens[] = $refreshToken;
            $refreshToken->setUser($this);
        }

        return $this;
    }

    /**
     * @param RefreshToken $refreshToken
     *
     * @return $this
     */
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
     * @return Collection|EmailConfirmation[]
     */
    public function getEmailConfirmations(): Collection
    {
        return $this->emailConfirmations;
    }

    /**
     * @param EmailConfirmation $emailConfirmation
     *
     * @return $this
     */
    public function addEmailConfirmation(EmailConfirmation $emailConfirmation): self
    {
        if (!$this->emailConfirmations->contains($emailConfirmation)) {
            $this->emailConfirmations[] = $emailConfirmation;
            $emailConfirmation->setUser($this);
        }

        return $this;
    }

    /**
     * @param EmailConfirmation $email
     *
     * @return $this
     */
    public function removeEmail(EmailConfirmation $email): self
    {
        if ($this->emailConfirmations->contains($email)) {
            $this->emailConfirmations->removeElement($email);
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

    /**
     * @param PasswordRecovery $passwordRecovery
     *
     * @return $this
     */
    public function addPasswordRecovery(PasswordRecovery $passwordRecovery): self
    {
        if (!$this->passwordRecoveries->contains($passwordRecovery)) {
            $this->passwordRecoveries[] = $passwordRecovery;
            $passwordRecovery->setUser($this);
        }

        return $this;
    }

    /**
     * @param PasswordRecovery $passwordRecovery
     *
     * @return $this
     */
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
