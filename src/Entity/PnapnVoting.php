<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PnapnVotingRepository")
 */
class PnapnVoting
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    private $applyingFrom;

    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    private $applyingTo;

    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    private $dateStarted;

    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    private $dateEnded;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Meeting", inversedBy="pnapnVoting", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $Meeting;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PnapnProject", mappedBy="Voting")
     */
    private $pnapnProjects;

    public function __construct()
    {
        $this->pnapnProjects = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApplyingFrom(): ?\DateTimeInterface
    {
        return $this->applyingFrom;
    }

    public function setApplyingFrom(\DateTimeInterface $applyingFrom): self
    {
        $this->applyingFrom = $applyingFrom;

        return $this;
    }

    public function getApplyingTo(): ?\DateTimeInterface
    {
        return $this->applyingTo;
    }

    public function setApplyingTo(?\DateTimeInterface $applyingTo): self
    {
        $this->applyingTo = $applyingTo;

        return $this;
    }

    public function getDateStarted(): ?\DateTimeInterface
    {
        return $this->dateStarted;
    }

    public function setDateStarted(?\DateTimeInterface $dateStarted): self
    {
        $this->dateStarted = $dateStarted;

        return $this;
    }

    public function getDateEnded(): ?\DateTimeInterface
    {
        return $this->dateEnded;
    }

    public function setDateEnded(?\DateTimeInterface $dateEnded): self
    {
        $this->dateEnded = $dateEnded;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getMeeting(): ?Meeting
    {
        return $this->Meeting;
    }

    public function setMeeting(Meeting $Meeting): self
    {
        $this->Meeting = $Meeting;

        return $this;
    }

    /**
     * @return Collection|PnapnProject[]
     */
    public function getPnapnProjects(): Collection
    {
        return $this->pnapnProjects;
    }

    public function addPnapnProject(PnapnProject $pnapnProject): self
    {
        if (!$this->pnapnProjects->contains($pnapnProject)) {
            $this->pnapnProjects[] = $pnapnProject;
            $pnapnProject->setVoting($this);
        }

        return $this;
    }

    public function removePnapnProject(PnapnProject $pnapnProject): self
    {
        if ($this->pnapnProjects->contains($pnapnProject)) {
            $this->pnapnProjects->removeElement($pnapnProject);
            // set the owning side to null (unless already changed)
            if ($pnapnProject->getVoting() === $this) {
                $pnapnProject->setVoting(null);
            }
        }

        return $this;
    }
}
