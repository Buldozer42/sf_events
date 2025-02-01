<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as CustomAssert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'The event name cannot be empty')]
    #[Assert\Length(max: 255, maxMessage: 'The event name cannot be longer than {{ limit }} characters')]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: 'The event date cannot be empty')]
    #[Assert\GreaterThanOrEqual('today', message: 'The event date must be in the future')]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'The max number of guests cannot be empty')]
    #[Assert\Positive(message: 'The max number of guests must be a positive number')]
    private ?int $maxGuests = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'The event description cannot be empty')]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: 'The event type cannot be empty')]
    private ?Type $type = null;

    #[ORM\Column]
    private ?bool $visible = null;

    #[ORM\Column]
    private ?bool $private = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero(message: 'The price must be a positive number or zero')]
    private ?float $price = null;

    #[ORM\Column(length: 255)]
    #[CustomAssert\Address]
    private ?string $location = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'attendingEvents')]
    private Collection $guests;

    #[ORM\ManyToOne(inversedBy: 'ownedEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    /**
     * @var Collection<int, Demand>
     */
    #[ORM\OneToMany(targetEntity: Demand::class, mappedBy: 'event', orphanRemoval: true)]
    private Collection $demands;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'event', orphanRemoval: true)]
    private Collection $comments;

    #[ORM\Column(type: Types::ARRAY)]
    private array $invitedEmails = [];

    public function __construct()
    {
        $this->guests = new ArrayCollection();
        $this->demands = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getMaxGuests(): ?int
    {
        return $this->maxGuests;
    }

    public function setMaxGuests(int $maxGuests): static
    {
        $this->maxGuests = $maxGuests;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType(?Type $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function isVisible(): ?bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): static
    {
        $this->visible = $visible;

        return $this;
    }

    public function isPrivate(): ?bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): static
    {
        $this->private = $private;

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

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getGuests(): Collection
    {
        return $this->guests;
    }

    public function addGuest(User $guest): static
    {
        if (!$this->guests->contains($guest)) {
            $this->guests->add($guest);
        }

        return $this;
    }

    public function removeGuest(User $guest): static
    {
        $this->guests->removeElement($guest);

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection<int, Demand>
     */
    public function getDemands(): Collection
    {
        return $this->demands;
    }

    public function addDemand(Demand $demand): static
    {
        if (!$this->demands->contains($demand)) {
            $this->demands->add($demand);
            $demand->setEvent($this);
        }

        return $this;
    }

    public function removeDemand(Demand $demand): static
    {
        if ($this->demands->removeElement($demand)) {
            if ($demand->getEvent() === $this) {
                $demand->setEvent(null);
            }
        }

        return $this;
    }

    public function getPendingDemands(): Collection
    {
        return $this->demands->filter(fn (Demand $demand) => $demand->getCurrentState() === 'pending');
    }

    public function getPendingDemandForUser(User $user): Demand | false
    {
        return $this->demands->filter(fn (Demand $demand) => $demand->getUser() === $user && $demand->getCurrentState() === 'pending')->first();
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setEvent($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getEvent() === $this) {
                $comment->setEvent(null);
            }
        }

        return $this;
    }

    public function getInvitedEmails(): array
    {
        return $this->invitedEmails;
    }

    public function setInvitedEmails(array $invitedEmails): static
    {
        $this->invitedEmails = $invitedEmails;

        return $this;
    }

    public function addInvitedEmail(string $email): static
    {
        if (!in_array($email, $this->invitedEmails)) {
            $this->invitedEmails[] = $email;
        }

        return $this;
    }

    public function removeInvitedEmail(string $email): static
    {
        $key = array_search($email, $this->invitedEmails);
        if ($key !== false) {
            unset($this->invitedEmails[$key]);
        }

        return $this;
    }
}
