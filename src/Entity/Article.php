<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArticleRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Article
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
    private $title;

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @ORM\Column(type="text")
     */
    private $body;

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @ORM\Column(type="text")
     */
    private $articleType;

    public function getArticleType()
    {
        return $this->articleType;
    }

    public function setArticleType($articleType)
    {
        $this->articleType = $articleType;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {

        if (!$this->getCreatedTime()) {
            $this->setCreatedTime((new\DateTime()));
        }

        if (!$this->getModifiedTime()) {
            $this->setModifiedTime(new \DateTime());
        }
    }

    /**
     * @ORM\PreUpdate
     */
    public function preModifiedTime()
    {
        $this->setModifiedTime(new \DateTime());
    }


    /**
     * @ORM\Column(type="datetimetz")
     * @var \DateTime
     */
    private $created;

    public function getCreatedTime()
    {
        return $this->created;
    }

    public function setCreatedTime($created)
    {
        $this->created = $created;
    }

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
    private $createdBy;

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
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $modifiedTime;

    public function getModifiedTime()
    {
        return $this->modifiedTime;
    }

    public function setModifiedTime($modifiedTime)
    {
        $this->modifiedTime = $modifiedTime;
    }

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
    private $modifiedBy;

    public function getModifiedBy(): ?User
    {
        return $this->modifiedBy;
    }

    public function setModifiedBy(?User $modifiedBy): self
    {
        $this->modifiedBy = $modifiedBy;

        return $this;
    }

}