<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VotingRepository")
 */
class Voting
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
     * @ORM\ManyToOne(targetEntity="App\Entity\VotingGroup", inversedBy="votings")
     * @ORM\JoinColumn(nullable=true)
     */
    private $VotingGroup;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Vote", mappedBy="Voting", orphanRemoval=true)
     */
    private $votes;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Meeting")
     */
    private $Meeting;

    public function __construct()
    {
        $this->votes = new ArrayCollection();
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

    public function getVotingGroup(): ?VotingGroup
    {
        return $this->VotingGroup;
    }

    public function setVotingGroup(?VotingGroup $VotingGroup): self
    {
        $this->VotingGroup = $VotingGroup;

        return $this;
    }

    /**
     * @return Collection|Vote[]
     */
    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(Vote $vote): self
    {
        if (!$this->votes->contains($vote)) {
            $this->votes[] = $vote;
            $vote->setVoting($this);
        }

        return $this;
    }

    public function removeVote(Vote $vote): self
    {
        if ($this->votes->contains($vote)) {
            $this->votes->removeElement($vote);
            // set the owning side to null (unless already changed)
            if ($vote->getVoting() === $this) {
                $vote->setVoting(null);
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

    //score is for best opiekun votings
    private $score;

    public function getScore()
    {
        return $this->score;
    }

    public function setScore($score): self
    {
        $this->score = $score;
        return $this;
    }
}
