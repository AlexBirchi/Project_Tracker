<?php

namespace App\Entity;

use App\Repository\ItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
class Item
{
    public const ITEM_TYPE_IMPROVEMENT = 'Improvement';
    public const ITEM_TYPE_BUG = 'Bug';
    public const ITEM_TYPE_NEW_FEATURE = 'New Feature';
    public const ITEM_TYPE_TASK = 'Task';
    public const ITEM_TYPE_EPIC = 'Epic';
    public const ITEM_TYPE_STORY = 'Story';

    public const ITEM_TYPE_COLOR = [
        self::ITEM_TYPE_IMPROVEMENT => "#2196F3",
        self::ITEM_TYPE_BUG => "#F44336",
        self::ITEM_TYPE_NEW_FEATURE => "#4CAF50",
        self::ITEM_TYPE_TASK => "#FFEB3B",
        self::ITEM_TYPE_EPIC => "#607D8B",
        self::ITEM_TYPE_STORY => "#9C27B0",
    ];

    public const ITEM_PRIORITY_TRIVIAL = 'Trivial';
    public const ITEM_PRIORITY_MINOR = 'Minor';
    public const ITEM_PRIORITY_MAJOR = 'Major';
    public const ITEM_PRIORITY_CRITICAL = 'Critical';
    public const ITEM_PRIORITY_BLOCKER = 'Blocker';

    public const ITEM_PRIORITY_ICON = [
        self::ITEM_PRIORITY_TRIVIAL => "fa-solid fa-down-long text-success",
        self::ITEM_PRIORITY_MINOR => "fa-solid fa-arrow-down text-success",
        self::ITEM_PRIORITY_MAJOR => "fa-solid fa-arrow-up text-danger",
        self::ITEM_PRIORITY_CRITICAL => "fa-solid fa-up-long text-danger",
        self::ITEM_PRIORITY_BLOCKER => "fa-solid fa-ban text-danger",
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $number = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $reporter = null;

    #[ORM\ManyToOne(inversedBy: 'asignedItems')]
    private ?User $asignee = null;

    #[ORM\Column(nullable: true)]
    private ?int $storyPoints = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    private ?string $priority = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?ItemStatus $itemStatus = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updated = null;

    #[ORM\OneToMany(mappedBy: 'item', targetEntity: Comment::class)]
    private Collection $comments;

    #[ORM\ManyToMany(targetEntity: Sprint::class, mappedBy: 'items')]
    private Collection $sprints;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->sprints = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function getReporter(): ?User
    {
        return $this->reporter;
    }

    public function setReporter(?User $reporter): self
    {
        $this->reporter = $reporter;

        return $this;
    }

    public function getAsignee(): ?User
    {
        return $this->asignee;
    }

    public function setAsignee(?User $asignee): self
    {
        $this->asignee = $asignee;

        return $this;
    }

    public function getStoryPoints(): ?int
    {
        return $this->storyPoints;
    }

    public function setStoryPoints(?int $storyPoints): self
    {
        $this->storyPoints = $storyPoints;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function setPriority(string $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getItemStatus(): ?ItemStatus
    {
        return $this->itemStatus;
    }

    public function setItemStatus(?ItemStatus $itemStatus): self
    {
        $this->itemStatus = $itemStatus;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }

    public function setUpdated(\DateTimeInterface $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setItem($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getItem() === $this) {
                $comment->setItem(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Sprint>
     */
    public function getSprints(): Collection
    {
        return $this->sprints;
    }

    public function addSprint(Sprint $sprint): self
    {
        if (!$this->sprints->contains($sprint)) {
            $this->sprints->add($sprint);
        }

        return $this;
    }

    public function removeSprint(Sprint $sprint): self
    {
        $this->sprints->removeElement($sprint);

        return $this;
    }
}
