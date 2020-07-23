<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MenuItemsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class MenuItem
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    public function getId()
    {
        return $this->id;
    }

    /**
     * @ORM\Column(type="text")
     */
    private $name;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @ORM\Column(type="text")
     */
    private $link;

    public function getLink()
    {
        return $this->link;
    }

    public function setLink($link)
    {
        $this->link = $link;
    }
    /**
     * @ORM\Column(type="integer", nullable=TRUE)
     */
    private $ordering;

    public function getOrdering()
    {
        return $this->ordering;
    }

    public function setOrdering($ordering)
    {
        $this->ordering = $ordering;
    }

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted;

    public function getDeleted()
    {
        return $this->deleted;
    }

    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MenuCategory", inversedBy="menuItems")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Parent;

    public function getParent(): ?MenuCategory
    {
        return $this->Parent;
    }

    public function setParent(?MenuCategory $Parent): self
    {
        $this->Parent = $Parent;

        return $this;
    }

}
