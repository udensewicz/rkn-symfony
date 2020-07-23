<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VoteRepository")
 */
class Vote
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Voting", inversedBy="votes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Voting;

    /**
     * @ORM\Column(type="bigint")
     */
    private $roleValidityId;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $vote;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVoting(): ?Voting
    {
        return $this->Voting;
    }

    public function setVoting(?Voting $Voting): self
    {
        $this->Voting = $Voting;

        return $this;
    }

    public function getRoleValidityId(): ?int
    {
        return $this->roleValidityId;
    }

    public function setRoleValidityId(int $roleValidityId): self
    {
        $this->roleValidityId = $roleValidityId;

        return $this;
    }

    public function getVote(): ?int
    {
        return $this->vote;
    }

    public function setVote(?int $vote): self
    {
        $this->vote = $vote;

        return $this;
    }
}
