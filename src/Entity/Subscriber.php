<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SubscriberRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubscriberRepository::class)]
#[ORM\Table(name: 'subscribers')]
#[ORM\Index(name: 'idx_unsubscribed_at', columns: ['unsubscribed_at'])]
#[ORM\Index(name: 'idx_sent_at', columns: ['sent_at'])]
#[ORM\HasLifecycleCallbacks]
class Subscriber
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?string $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $sentAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastClickedLink = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastClickedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $unsubscribedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function updateTimestamp(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?string
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getSentAt(): ?\DateTimeInterface
    {
        return $this->sentAt;
    }

    public function setSentAt(?\DateTimeInterface $sentAt): static
    {
        $this->sentAt = $sentAt;
        return $this;
    }

    public function getLastClickedLink(): ?string
    {
        return $this->lastClickedLink;
    }

    public function setLastClickedLink(?string $lastClickedLink): static
    {
        $this->lastClickedLink = $lastClickedLink;
        return $this;
    }

    public function getLastClickedAt(): ?\DateTimeInterface
    {
        return $this->lastClickedAt;
    }

    public function setLastClickedAt(?\DateTimeInterface $lastClickedAt): static
    {
        $this->lastClickedAt = $lastClickedAt;
        return $this;
    }

    public function getUnsubscribedAt(): ?\DateTimeInterface
    {
        return $this->unsubscribedAt;
    }

    public function setUnsubscribedAt(?\DateTimeInterface $unsubscribedAt): static
    {
        $this->unsubscribedAt = $unsubscribedAt;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function isSent(): bool
    {
        return $this->sentAt instanceof \DateTimeInterface;
    }

    public function isUnsubscribed(): bool
    {
        return $this->unsubscribedAt instanceof \DateTimeInterface;
    }

    public function isPending(): bool
    {
        return !$this->isSent() && !$this->isUnsubscribed();
    }

    public function hasClicked(): bool
    {
        return $this->lastClickedAt instanceof \DateTimeInterface;
    }

    public function markAsSent(): void
    {
        $this->sentAt = new \DateTimeImmutable();
    }

    public function markAsUnsubscribed(): void
    {
        $this->unsubscribedAt = new \DateTimeImmutable();
    }

    public function recordClick(string $linkName): void
    {
        $this->lastClickedLink = $linkName;
        $this->lastClickedAt = new \DateTimeImmutable();
    }
}
