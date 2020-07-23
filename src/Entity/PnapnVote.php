<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PnapnVoteRepository")
 */
class PnapnVote
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Meeting", inversedBy="pnapnVotes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Meeting;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PnapnProject", inversedBy="pnapnVotes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Project;

    /**
     * @ORM\Column(type="bigint")
     */
    private $roleValidityId;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $voteCat1;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $voteCat2;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $voteCat3;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $voteCat4;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $voteCat5;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getProject(): ?PnapnProject
    {
        return $this->Project;
    }

    public function setProject(?PnapnProject $Project): self
    {
        $this->Project = $Project;

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

    public function getVoteCat1(): ?int
    {
        return $this->voteCat1;
    }

    public function setVoteCat1(?int $voteCat1): self
    {
        $this->voteCat1 = $voteCat1;

        return $this;
    }

    public function getVoteCat2(): ?int
    {
        return $this->voteCat2;
    }

    public function setVoteCat2(?int $voteCat2): self
    {
        $this->voteCat2 = $voteCat2;

        return $this;
    }

    public function getVoteCat3(): ?int
    {
        return $this->voteCat3;
    }

    public function setVoteCat3(?int $voteCat3): self
    {
        $this->voteCat3 = $voteCat3;

        return $this;
    }

    public function getVoteCat4(): ?int
    {
        return $this->voteCat4;
    }

    public function setVoteCat4(?int $voteCat4): self
    {
        $this->voteCat4 = $voteCat4;

        return $this;
    }

    public function getVoteCat5(): ?int
    {
        return $this->voteCat5;
    }

    public function setVoteCat5(?int $voteCat5): self
    {
        $this->voteCat5 = $voteCat5;

        return $this;
    }

//    private $voteSum;

    public function getVoteSum()
    {
        return $this->voteCat1*0.35+$this->voteCat2*0.2+$this->voteCat3*0.2+$this->voteCat4*0.15+$this->voteCat5*0.1;
    }
    public function isValid(){
        $hasAllCats = $this->voteCat1 && $this->voteCat2 && $this->voteCat3 && $this->voteCat4 && $this->voteCat5;
        $hasNoZeros = $this->voteCat1 != 0 && $this->voteCat2 != 0 && $this->voteCat3 != 0 && $this->voteCat4 != 0 && $this->voteCat5 != 0;
        //and respective regular vote has status 99
        return $hasAllCats && $hasNoZeros;
    }
}
