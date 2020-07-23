<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PnapnProjectRepository")
 */
class PnapnProject
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
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $knShort;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $moneyMaxAmount;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $projectID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $presentationOrder;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PnapnVoting", inversedBy="pnapnProjects")
     */
    private $Voting;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PnapnVote", mappedBy="Project", orphanRemoval=true)
     */
    private $pnapnVotes;

    public function __construct()
    {
        $this->pnapnVotes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getKnShort(): ?string
    {
        return $this->knShort;
    }

    public function setKnShort(string $knShort): self
    {
        $this->knShort = $knShort;

        return $this;
    }

    public function getMoneyMaxAmount(): ?float
    {
        return $this->moneyMaxAmount;
    }

    public function setMoneyMaxAmount(?float $moneyMaxAmount): self
    {
        $this->moneyMaxAmount = $moneyMaxAmount;

        return $this;
    }

    public function getProjectID(): ?int
    {
        return $this->projectID;
    }

    public function setProjectID(int $projectID): self
    {
        $this->projectID = $projectID;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getPresentationOrder(): ?int
    {
        return $this->presentationOrder;
    }

    public function setPresentationOrder(?int $presentationOrder): self
    {
        $this->presentationOrder = $presentationOrder;

        return $this;
    }

    public function getVoting(): ?PnapnVoting
    {
        return $this->Voting;
    }

    public function setVoting(?PnapnVoting $Voting): self
    {
        $this->Voting = $Voting;

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
            $pnapnVote->setProject($this);
        }

        return $this;
    }

    public function removePnapnVote(PnapnVote $pnapnVote): self
    {
        if ($this->pnapnVotes->contains($pnapnVote)) {
            $this->pnapnVotes->removeElement($pnapnVote);
            // set the owning side to null (unless already changed)
            if ($pnapnVote->getProject() === $this) {
                $pnapnVote->setProject(null);
            }
        }

        return $this;
    }

    private $score;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $moneyMinAmount;
    public function getScore()
    {
        return $this->score;
    }

    public function setScore($score): self
    {
        $this->score = $score;
        return $this;
    }

    public function getMoneyMinAmount(): ?float
    {
        return $this->moneyMinAmount;
    }

    public function setMoneyMinAmount(?float $moneyMinAmount): self
    {
        $this->moneyMinAmount = $moneyMinAmount;

        return $this;
    }
}
