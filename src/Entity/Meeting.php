<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MeetingRepository")
 */
class Meeting
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $title;

    /**
     * @ORM\Column(type="boolean")
     */
    private $published;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $plan;

    /**
     * @ORM\Column(type="datetimetz")
     */
    private $dateStart;

    /**
     * @ORM\Column(type="datetimetz")
     */
    private $dateEnd;

    /**
     * @ORM\Column(type="boolean")
     */
    private $havePnapn;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $createdBy;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\VotingGroup", mappedBy="Meeting", orphanRemoval=true)
     */
    private $votingGroups;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\PnapnVoting", mappedBy="Meeting", cascade={"persist", "remove"})
     */
    private $pnapnVoting;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PnapnVote", mappedBy="Meeting", orphanRemoval=true)
     */
    private $pnapnVotes;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $olimpId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ResearchGroup")
     */
    private $researchGroup;

    public function __construct()
    {
        $this->votingGroups = new ArrayCollection();
        $this->pnapnVotes = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
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

    public function getPublished(): ?bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): self
    {
        $this->published = $published;

        return $this;
    }

    public function getPlan(): ?string
    {
        return $this->plan;
    }

    public function setPlan(?string $plan): self
    {
        $this->plan = $plan;

        return $this;
    }

    public function getDateStart(): ?\DateTimeInterface
    {
        return $this->dateStart;
    }

    public function setDateStart(\DateTimeInterface $dateStart): self
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getHavePnapn(): ?bool
    {
        return $this->havePnapn;
    }

    public function setHavePnapn(bool $havePnapn): self
    {
        $this->havePnapn = $havePnapn;

        return $this;
    }

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->dateEnd;
    }

    public function setDateEnd(\DateTimeInterface $dateEnd): self
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @return Collection|VotingGroup[]
     */
    public function getVotingGroups(): Collection
    {
        return $this->votingGroups;
    }

    public function addVotingGroup(VotingGroup $votingGroup): self
    {
        if (!$this->votingGroups->contains($votingGroup)) {
            $this->votingGroups[] = $votingGroup;
            $votingGroup->setMeeting($this);
        }

        return $this;
    }

    public function removeVotingGroup(VotingGroup $votingGroup): self
    {
        if ($this->votingGroups->contains($votingGroup)) {
            $this->votingGroups->removeElement($votingGroup);
            // set the owning side to null (unless already changed)
            if ($votingGroup->getMeeting() === $this) {
                $votingGroup->setMeeting(null);
            }
        }

        return $this;
    }

    public function getPnapnVoting(): ?PnapnVoting
    {
        return $this->pnapnVoting;
    }

    public function setPnapnVoting(PnapnVoting $pnapnVoting): self
    {
        $this->pnapnVoting = $pnapnVoting;

        // set the owning side of the relation if necessary
        if ($this !== $pnapnVoting->getMeeting()) {
            $pnapnVoting->setMeeting($this);
        }

        return $this;
    }

    /**
     * @return Collection|PnapnVote[]
     */
    public function getPnapnVotes(): Collection
    {
        return $this->pnapnVotes;
    }

    public function addPnapnVote(PnapnVote $pnapnVote): self
    {
        if (!$this->pnapnVotes->contains($pnapnVote)) {
            $this->pnapnVotes[] = $pnapnVote;
            $pnapnVote->setMeeting($this);
        }

        return $this;
    }

    public function removePnapnVote(PnapnVote $pnapnVote): self
    {
        if ($this->pnapnVotes->contains($pnapnVote)) {
            $this->pnapnVotes->removeElement($pnapnVote);
            // set the owning side to null (unless already changed)
            if ($pnapnVote->getMeeting() === $this) {
                $pnapnVote->setMeeting(null);
            }
        }

        return $this;
    }

    public function getOlimpId(): ?int
    {
        return $this->olimpId;
    }

    public function setOlimpId(?int $olimpId): self
    {
        $this->olimpId = $olimpId;

        return $this;
    }

    public function getResearchGroup(): ?ResearchGroup
    {
        return $this->researchGroup;
    }

    public function setResearchGroup(?ResearchGroup $researchGroup): self
    {
        $this->researchGroup = $researchGroup;

        return $this;
    }
}
