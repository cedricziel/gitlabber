<?php

namespace AppBundle\Model;

use AppBundle\Entity\Gitlab\Group;
use AppBundle\Entity\Gitlab\Host;
use AppBundle\Entity\Gitlab\Project;

class ProjectMigration
{
    /**
     * @var Project
     */
    private $initialProject;

    /**
     * @var string
     */
    private $targetName;

    /**
     * @var Group
     */
    private $targetGroup;

    /**
     * @var Host
     */
    private $targetHost;

    public function __construct(Project $initialProject)
    {
        $this->initialProject = $initialProject;
    }

    /**
     * @return Project
     */
    public function getInitialProject()
    {
        return $this->initialProject;
    }

    /**
     * @param Project $initialProject
     *
     * @return ProjectMigration
     */
    public function setInitialProject(Project $initialProject): ProjectMigration
    {
        $this->initialProject = $initialProject;

        return $this;
    }

    /**
     * @return string
     */
    public function getTargetName()
    {
        return $this->targetName;
    }

    /**
     * @param string $targetName
     *
     * @return ProjectMigration
     */
    public function setTargetName(string $targetName): ProjectMigration
    {
        $this->targetName = $targetName;

        return $this;
    }

    /**
     * @return Group
     */
    public function getTargetGroup()
    {
        return $this->targetGroup;
    }

    /**
     * @param Group $targetGroup
     *
     * @return ProjectMigration
     */
    public function setTargetGroup(Group $targetGroup): ProjectMigration
    {
        $this->targetGroup = $targetGroup;

        return $this;
    }

    /**
     * @return Host
     */
    public function getTargetHost()
    {
        return $this->targetHost;
    }

    /**
     * @param Host $targetHost
     *
     * @return ProjectMigration
     */
    public function setTargetHost(Host $targetHost): ProjectMigration
    {
        $this->targetHost = $targetHost;

        return $this;
    }
}
