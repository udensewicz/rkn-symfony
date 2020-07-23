<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VotingGroupRepository")
 */
class VotingGroup
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
    private $subject;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Voting", mappedBy="VotingGroup", orphanRemoval=true, cascade={"persist"})
     */
    private $votings;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Meeting", inversedBy="votingGroups")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Meeting;

    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    private $started;

    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    private $ended;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maxVotesFor;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isBestOpiekun;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted;

    public function __construct()
    {
        $this->votings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return Collection|Voting[]
     */
    public function getVotings(): Collection
    {
        return $this->votings;
    }

    public function addVoting(Voting $voting): self
    {
        if (!$this->votings->contains($voting)) {
            $this->votings[] = $voting;
            $voting->setVotingGroup($this);
        }

        return $this;
    }

    public function removeVoting(Voting $voting): self
    {
        if ($this->votings->contains($voting)) {
            $this->votings->removeElement($voting);
            // set the owning side to null (unless already changed)
            if ($voting->getVotingGroup() === $this) {
                $voting->setVotingGroup(null);
            }
        }

        return $this;
    }

    public function getMeeting(): ?Meeting
    {
        return $this->Meeting;
    }

    public function setMeeting(?Meeting $Meeting): self
    {
        $this->Meeting = $Meeting;

        return $this;
    }

    public function getStarted(): ?\DateTimeInterface
    {
        return $this->started;
    }

    public function setStarted(?\DateTimeInterface $started): self
    {
        $this->started = $started;

        return $this;
    }

    public function getEnded(): ?\DateTimeInterface
    {
        return $this->ended;
    }

    public function setEnded(?\DateTimeInterface $ended): self
    {
        $this->ended = $ended;

        return $this;
    }

    public function getMaxVotesFor(): ?int
    {
        return $this->maxVotesFor;
    }

    public function setMaxVotesFor(?int $maxVotesFor): self
    {
        $this->maxVotesFor = $maxVotesFor;

        return $this;
    }

    public function getIsBestOpiekun(): ?bool
    {
        return $this->isBestOpiekun;
    }

    public function setIsBestOpiekun(?bool $isBestOpiekun): self
    {
        $this->isBestOpiekun = $isBestOpiekun;

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
}
