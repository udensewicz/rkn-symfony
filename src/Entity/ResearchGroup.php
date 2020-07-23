<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ResearchGroupRepository")
 */
class ResearchGroup
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
     * @ORM\Column(type="text", nullable=true)
     */
    private $nameD;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nameShort;

    /**
     * @ORM\Column(type="bigint")
     */
    private $olimpId;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="researchGroups")
     * @ORM\JoinTable(name="research_group_members")
     */
    private $members;

    public function __construct()
    {
        $this->members = new ArrayCollection();
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

    public function getNameD(): ?string
    {
        return $this->nameD;
    }

    public function setNameD(?string $nameD): self
    {
        $this->nameD = $nameD;

        return $this;
    }

    public function getNameShort(): ?string
    {
        return $this->nameShort;
    }

    public function setNameShort(?string $nameShort): self
    {
        $this->nameShort = $nameShort;

        return $this;
    }

    public function getOlimpId(): ?int
    {
        return $this->olimpId;
    }

    public function setOlimpId(int $olimpId): self
    {
        $this->olimpId = $olimpId;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(User $member): self
    {
        if (!$this->members->contains($member)) {
            $this->members[] = $member;
            $member->addResearchGroup($this);
        }

        return $this;
    }

    public function removeMember(User $member): self
    {
        if ($this->members->contains($member)) {
            $this->members->removeElement($member);
            $member->removeResearchGroup($this);
        }

        return $this;
    }
}
