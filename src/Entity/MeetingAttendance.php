<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MeetingAttendanceRepository")
 */
class MeetingAttendance
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Meeting")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Meeting;

    /**
     * @ORM\Column(type="bigint")
     */
    private $roleValidityId;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $present;

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

    public function getRoleValidityId(): ?int
    {
        return $this->roleValidityId;
    }

    public function setRoleValidityId(int $roleValidityId): self
    {
        $this->roleValidityId = $roleValidityId;

        return $this;
    }

    public function getPresent(): ?bool
    {
        return $this->present;
    }

    public function setPresent(?bool $present): self
    {
        $this->present = $present;

        return $this;
    }
}
