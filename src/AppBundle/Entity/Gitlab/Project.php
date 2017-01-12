<?php

namespace AppBundle\Entity\Gitlab;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="gitlab_project")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Gitlab\ProjectRepository")
 */
class Project
{
    /**
     * @var string
     * @ORM\Column(name="id", type="guid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(name="remote_id", type="integer")
     * @Assert\NotNull()
     */
    private $remoteId;

    /**
     * @var string
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var array
     * @ORM\Column(name="tag_list", type="array")
     */
    private $tagList;

    /**
     * @var bool
     * @ORM\Column(name="public", type="boolean")
     * @Assert\NotNull()
     */
    private $public;

    /**
     * @var bool
     * @ORM\Column(name="archived", type="boolean")
     * @Assert\NotNull()
     */
    private $archived;

    /**
     * @var int
     * @ORM\Column(name="visibility_level", type="integer")
     * @Assert\NotNull()
     */
    private $visibilityLevel;

    /**
     * @var string
     * @ORM\Column(name="ssh_url_to_repo", type="string", length=255)
     * @Assert\NotNull()
     * @Assert\NotBlank()
     */
    private $sshUrlToRepo;

    /**
     * @var string
     * @ORM\Column(name="http_url_to_repo", type="string", length=255)
     * @Assert\NotNull()
     * @Assert\NotBlank()
     */
    private $httpUrlToRepo;

    /**
     * @var string
     * @ORM\Column(name="web_url", type="string", length=255)
     * @Assert\NotNull()
     * @Assert\NotBlank()
     */
    private $webUrl;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotNull()
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(name="name_with_namespace", type="string", length=255)
     * @Assert\NotNull()
     * @Assert\NotBlank()
     */
    private $nameWithNamespace;

    /**
     * @var string
     * @ORM\Column(name="path", type="string", length=255)
     * @Assert\NotNull()
     * @Assert\NotBlank()
     */
    private $path;

    /**
     * @var string
     * @ORM\Column(name="path_with_namespace", type="string", length=255)
     * @Assert\NotNull()
     * @Assert\NotBlank()
     */
    private $pathWithNamespace;

    /**
     * @var bool
     * @ORM\Column(name="container_registry_enabled", type="boolean")
     * @Assert\NotNull()
     */
    private $containerRegistryEnabled;

    /**
     * @var bool
     * @ORM\Column(name="issues_enabled", type="boolean")
     * @Assert\NotNull()
     */
    private $issuesEnabled;

    /**
     * @var bool
     * @ORM\Column(name="merge_requests_enabled", type="boolean")
     * @Assert\NotNull()
     */
    private $mergeRequestsEnabled;

    /**
     * @var bool
     * @ORM\Column(name="wiki_enabled", type="boolean")
     * @Assert\NotNull()
     */
    private $wikiEnabled;

    /**
     * @var bool
     * @ORM\Column(name="builds_enabled", type="boolean")
     * @Assert\NotNull()
     */
    private $buildsEnabled;

    /**
     * @var bool
     * @ORM\Column(name="snippets_enabled", type="boolean")
     * @Assert\NotNull()
     */
    private $snippetsEnabled;

    /**
     * @var \DateTime
     * @ORM\Column(name="remote_created_at", type="datetime")
     * @Assert\NotNull()
     */
    private $remoteCreatedAt;

    /**
     * @var \DateTime
     * @ORM\Column(name="remote_last_activity_at", type="datetime")
     * @Assert\NotNull()
     */
    private $remoteLastActivityAt;

    /**
     * @var bool
     * @ORM\Column(name="shared_runners_enabled", type="boolean")
     * @Assert\NotNull()
     */
    private $sharedRunnersEnabled;

    /**
     * @var bool
     * @ORM\Column(name="lfs_enabled", type="boolean")
     * @Assert\NotNull()
     */
    private $lfsEnabled;

    /**
     * @var int
     * @ORM\Column(name="creator_id", type="integer")
     * @Assert\NotNull()
     */
    private $creatorId;

    /**
     * @var string
     * @ORM\Column(name="avatar_url", type="string", length=255)
     */
    private $avatarUrl;

    /**
     * @var Group
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Gitlab\Group", inversedBy="projects")
     * @Assert\NotNull()
     */
    private $group;

    /**
     * @var Host
     * @ORM\JoinColumn(name="host_id", referencedColumnName="id")
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Gitlab\Host", inversedBy="projects")
     * @Assert\NotNull()
     */
    private $host;

    /**
     * @var Project
     * @ORM\JoinColumn(name="source_project_id", referencedColumnName="id")
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Gitlab\Project", inversedBy="migratedTo")
     */
    private $sourceProject;

    /**
     * @var Project
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Gitlab\Project", mappedBy="sourceProject")
     */
    private $migratedTo;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->migratedTo = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
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
     * @return Project
     */
    public function setRemoteId($remoteId)
    {
        $this->remoteId = $remoteId;

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
     * @return Project
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return array
     */
    public function getTagList()
    {
        return $this->tagList;
    }

    /**
     * @param array $tagList
     *
     * @return Project
     */
    public function setTagList($tagList)
    {
        $this->tagList = $tagList;

        return $this;
    }

    /**
     * @return bool
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * @param boolean $public
     *
     * @return Project
     */
    public function setPublic($public)
    {
        $this->public = $public;

        return $this;
    }

    /**
     * @return bool
     */
    public function getArchived()
    {
        return $this->archived;
    }

    /**
     * @param boolean $archived
     *
     * @return Project
     */
    public function setArchived($archived)
    {
        $this->archived = $archived;

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
     * @return Project
     */
    public function setVisibilityLevel($visibilityLevel)
    {
        $this->visibilityLevel = $visibilityLevel;

        return $this;
    }

    /**
     * @return string
     */
    public function getSshUrlToRepo()
    {
        return $this->sshUrlToRepo;
    }

    /**
     * @param string $sshUrlToRepo
     *
     * @return Project
     */
    public function setSshUrlToRepo($sshUrlToRepo)
    {
        $this->sshUrlToRepo = $sshUrlToRepo;

        return $this;
    }

    /**
     * @return string
     */
    public function getHttpUrlToRepo()
    {
        return $this->httpUrlToRepo;
    }

    /**
     * @param string $httpUrlToRepo
     *
     * @return Project
     */
    public function setHttpUrlToRepo($httpUrlToRepo)
    {
        $this->httpUrlToRepo = $httpUrlToRepo;

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
     * @return Project
     */
    public function setWebUrl($webUrl)
    {
        $this->webUrl = $webUrl;

        return $this;
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
     * @return Project
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getNameWithNamespace()
    {
        return $this->nameWithNamespace;
    }

    /**
     * @param string $nameWithNamespace
     *
     * @return Project
     */
    public function setNameWithNamespace($nameWithNamespace)
    {
        $this->nameWithNamespace = $nameWithNamespace;

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
     * @return Project
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function getPathWithNamespace()
    {
        return $this->pathWithNamespace;
    }

    /**
     * @param string $pathWithNamespace
     *
     * @return Project
     */
    public function setPathWithNamespace($pathWithNamespace)
    {
        $this->pathWithNamespace = $pathWithNamespace;

        return $this;
    }

    /**
     * @return bool
     */
    public function getContainerRegistryEnabled()
    {
        return $this->containerRegistryEnabled;
    }

    /**
     * @param boolean $containerRegistryEnabled
     *
     * @return Project
     */
    public function setContainerRegistryEnabled($containerRegistryEnabled)
    {
        $this->containerRegistryEnabled = $containerRegistryEnabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIssuesEnabled()
    {
        return $this->issuesEnabled;
    }

    /**
     * @param boolean $issuesEnabled
     *
     * @return Project
     */
    public function setIssuesEnabled($issuesEnabled)
    {
        $this->issuesEnabled = $issuesEnabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function getMergeRequestsEnabled()
    {
        return $this->mergeRequestsEnabled;
    }

    /**
     * @param boolean $mergeRequestsEnabled
     *
     * @return Project
     */
    public function setMergeRequestsEnabled($mergeRequestsEnabled)
    {
        $this->mergeRequestsEnabled = $mergeRequestsEnabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function getWikiEnabled()
    {
        return $this->wikiEnabled;
    }

    /**
     * @param boolean $wikiEnabled
     *
     * @return Project
     */
    public function setWikiEnabled($wikiEnabled)
    {
        $this->wikiEnabled = $wikiEnabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function getBuildsEnabled()
    {
        return $this->buildsEnabled;
    }

    /**
     * @param boolean $buildsEnabled
     *
     * @return Project
     */
    public function setBuildsEnabled($buildsEnabled)
    {
        $this->buildsEnabled = $buildsEnabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function getSnippetsEnabled()
    {
        return $this->snippetsEnabled;
    }

    /**
     * @param boolean $snippetsEnabled
     *
     * @return Project
     */
    public function setSnippetsEnabled($snippetsEnabled)
    {
        $this->snippetsEnabled = $snippetsEnabled;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getRemoteCreatedAt()
    {
        return $this->remoteCreatedAt;
    }

    /**
     * @param \DateTime $remoteCreatedAt
     *
     * @return Project
     */
    public function setRemoteCreatedAt($remoteCreatedAt)
    {
        $this->remoteCreatedAt = $remoteCreatedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getRemoteLastActivityAt()
    {
        return $this->remoteLastActivityAt;
    }

    /**
     * @param \DateTime $remoteLastActivityAt
     *
     * @return Project
     */
    public function setRemoteLastActivityAt($remoteLastActivityAt)
    {
        $this->remoteLastActivityAt = $remoteLastActivityAt;

        return $this;
    }

    /**
     * @return bool
     */
    public function getSharedRunnersEnabled()
    {
        return $this->sharedRunnersEnabled;
    }

    /**
     * @param boolean $sharedRunnersEnabled
     *
     * @return Project
     */
    public function setSharedRunnersEnabled($sharedRunnersEnabled)
    {
        $this->sharedRunnersEnabled = $sharedRunnersEnabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function getLfsEnabled()
    {
        return $this->lfsEnabled;
    }

    /**
     * @param boolean $lfsEnabled
     *
     * @return Project
     */
    public function setLfsEnabled($lfsEnabled)
    {
        $this->lfsEnabled = $lfsEnabled;

        return $this;
    }

    /**
     * @return int
     */
    public function getCreatorId()
    {
        return $this->creatorId;
    }

    /**
     * @param integer $creatorId
     *
     * @return Project
     */
    public function setCreatorId($creatorId)
    {
        $this->creatorId = $creatorId;

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
     * @return Project
     */
    public function setAvatarUrl($avatarUrl)
    {
        $this->avatarUrl = $avatarUrl;

        return $this;
    }

    /**
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param Group $group
     *
     * @return Project
     */
    public function setGroup(Group $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return Host
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param Host $host
     *
     * @return Project
     */
    public function setHost(Host $host = null)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get sourceProject
     *
     * @return Project
     */
    public function getSourceProject()
    {
        return $this->sourceProject;
    }

    /**
     * Set sourceProject
     *
     * @param Project $sourceProject
     *
     * @return Project
     */
    public function setSourceProject(Project $sourceProject = null)
    {
        $this->sourceProject = $sourceProject;

        return $this;
    }

    /**
     * Add migratedTo
     *
     * @param Project $migratedTo
     *
     * @return Project
     */
    public function addMigratedTo(Project $migratedTo)
    {
        $this->migratedTo[] = $migratedTo;

        return $this;
    }

    /**
     * Remove migratedTo
     *
     * @param Project $migratedTo
     */
    public function removeMigratedTo(Project $migratedTo)
    {
        $this->migratedTo->removeElement($migratedTo);
    }

    /**
     * Get migratedTo
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMigratedTo()
    {
        return $this->migratedTo;
    }
}
