<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Exception;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PasswordRecoveryRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("token")
 */
class PasswordRecovery
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="passwordRecoveries")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $user;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private ?string $token = null;

    /**
     * @ORM\PrePersist()
     *
     * @throws Exception
     */
    public function setPrePersistDefaults()
    {
        if ($this->token === null) {
            $this->token = md5(random_bytes(10));
        }
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     *
     * @return $this
     */
    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @param string $token
     *
     * @return $this
     */
    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }
}
