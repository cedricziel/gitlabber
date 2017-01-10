<?php

namespace AppBundle\Entity\Gitlab;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="gitlab_group")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Gitlab\GroupRepository")
 */
class Group
{
    /**
     * @var string
     * @ORM\Column(name="id", type="guid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(name="path", type="string", length=255)
     */
    private $path;

    /**
     * @var string
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var int
     * @ORM\Column(name="visibilityLevel", type="integer")
     */
    private $visibilityLevel;

    /**
     * @var string
     * @ORM\Column(name="avatarUrl", type="string", length=255, nullable=true)
     */
    private $avatarUrl;

    /**
     * @var string
     * @ORM\Column(name="webUrl", type="string", length=255, nullable=true)
     */
    private $webUrl;

    /**
     * @var int
     * @ORM\Column(name="remote_id", type="integer", nullable=true)
     */
    private $remoteId;

    /**
     * @var Host
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Gitlab\Host", inversedBy="groups")
     * @ORM\JoinColumn(name="gitlab_id", referencedColumnName="id")
     */
    private $gitlab;


    /**
     * @var Project[]
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Gitlab\Project", mappedBy="group", cascade={"PERSIST"})
     */
    private $projects;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->projects = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Group
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     *
     * @return Group
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return Group
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return int
     */
    public function getVisibilityLevel()
    {
        return $this->visibilityLevel;
    }

    /**
     * @param integer $visibilityLevel
     *
     * @return Group
     */
    public function setVisibilityLevel($visibilityLevel)
    {
        $this->visibilityLevel = $visibilityLevel;

        return $this;
    }

    /**
     * @return string
     */
    public function getAvatarUrl()
    {
        return $this->avatarUrl;
    }

    /**
     * @param string $avatarUrl
     *
     * @return Group
     */
    public function setAvatarUrl($avatarUrl)
    {
        $this->avatarUrl = $avatarUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getWebUrl()
    {
        return $this->webUrl;
    }

    /**
     * @param string $webUrl
     *
     * @return Group
     */
    public function setWebUrl($webUrl)
    {
        $this->webUrl = $webUrl;

        return $this;
    }

    /**
     * @return int
     */
    public function getRemoteId()
    {
        return $this->remoteId;
    }

    /**
     * @param integer $remoteId
     *
     * @return Group
     */
    public function setRemoteId($remoteId)
    {
        $this->remoteId = $remoteId;

        return $this;
    }

    /**
     * @return Host
     */
    public function getGitlab()
    {
        return $this->gitlab;
    }

    /**
     * @param Host $gitlab
     *
     * @return Group
     */
    public function setGitlab(Host $gitlab = null)
    {
        $this->gitlab = $gitlab;

        return $this;
    }

    /**
     * @param Project $project
     *
     * @return Group
     */
    public function addProject(Project $project)
    {
        $project->setGroup($this);
        $this->projects[] = $project;

        return $this;
    }

    /**
     * @param Project $repository
     */
    public function removeProject(Project $repository)
    {
        $this->projects->removeElement($repository);
    }

    /**
     * @return Collection|Project[]
     */
    public function getProjects()
    {
        return $this->projects;
    }
}
