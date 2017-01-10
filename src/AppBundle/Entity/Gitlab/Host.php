<?php

namespace AppBundle\Entity\Gitlab;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="gitlab_host")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Gitlab\HostRepository")
 */
class Host
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
     * @ORM\Column(name="url", type="string", length=255)
     */
    private $url;

    /**
     * @var string
     * @ORM\Column(name="token", type="string", length=255)
     */
    private $token;

    /**
     * @var Group[]
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Gitlab\Group", mappedBy="gitlab", cascade={"PERSIST"})
     */
    private $groups;

    /**
     * @var Project[]
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Gitlab\Project", mappedBy="host", cascade={"PERSIST"})
     */
    private $projects;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
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
     * @return Host
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return Host
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     *
     * @return Host
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @param Group $group
     *
     * @return Host
     */
    public function addGroup(Group $group)
    {
        $group->setGitlab($this);

        $this->groups[] = $group;

        return $this;
    }

    /**
     * @param Group $group
     */
    public function removeGroup(Group $group)
    {
        $this->groups->removeElement($group);
    }

    /**
     * @return Collection|Group[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param Project $project
     *
     * @return Host
     */
    public function addProject(Project $project)
    {
        $project->setHost($this);
        $this->projects[] = $project;

        return $this;
    }

    /**
     * @param Project $project
     */
    public function removeProject(Project $project)
    {
        $this->projects->removeElement($project);
    }

    /**
     * @return Collection|Project[]
     */
    public function getProjects()
    {
        return $this->projects;
    }
}
