<?php

namespace AppBundle\Service;

use AppBundle\Entity\Gitlab\Project;
use AppBundle\Factory\GitlabApiFactory;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class MigrationProcess
{
    const TIMEOUT = 3600;
    /**
     * @var string
     */
    protected $migrationId;

    /**
     * @var Project
     */
    private $sourceProject;

    /**
     * @var Project
     */
    private $targetProject;

    /**
     * MigrationProcess constructor.
     *
     * @param Project $sourceProject
     * @param Project $targetProject
     */
    public function __construct(Project $sourceProject, Project $targetProject = null)
    {
        $this->sourceProject = $sourceProject;
        $this->targetProject = $targetProject;

        $this->sourceApi = GitlabApiFactory::fromProject($sourceProject);
        $this->targetApi = GitlabApiFactory::fromProject($targetProject);

        $this->migrationId = uniqid('migration', true);
    }

    /**
     * Does the actual migration.
     */
    public function migrate()
    {
        $this->syncRepository();
        $this->copyVariables();
        $this->copyDeployKeys();
        $this->reEnableBuilds();
    }

    public function copyVariables()
    {
        $sourceApi = GitlabApiFactory::fromProject($this->sourceProject);
        $variables = $sourceApi->projects->variables($this->sourceProject->getRemoteId());

        $targetApi = GitlabApiFactory::fromProject($this->targetProject);

        foreach ($variables as $variable) {
            try {
                $targetApi->projects->addVariable(
                    $this->targetProject->getRemoteId(),
                    $variable['key'],
                    $variable['value']
                );
            } catch (\Exception $e) {
                // Intentionally left blank
            }
        }
    }

    public function copyDeployKeys()
    {
        $sourceApi = GitlabApiFactory::fromProject($this->sourceProject);
        $deployKeys = $sourceApi->projects->deployKeys($this->sourceProject->getRemoteId());

        $targetApi = GitlabApiFactory::fromProject($this->targetProject);

        foreach ($deployKeys as $key) {
            try {
                $targetApi->projects->addKey($this->targetProject->getRemoteId(), $key['title'], $key['key']);
            } catch (\Exception $e) {
                // Intentionally left blank
            }
        }
    }

    /**
     * Syncs the repository from source to target.
     */
    private function syncRepository()
    {
        $cwd = '/tmp/'.$this->migrationId;
        (new ProcessBuilder(['mkdir']))->add('-p')->add($cwd)->getProcess()->mustRun();

        $cloneSourceCommandBuilder = new ProcessBuilder(['git', 'clone', $this->sourceProject->getSshUrlToRepo(), '.']);
        $cloneSourceCommandBuilder
            ->setWorkingDirectory($cwd)
            ->setTimeout(self::TIMEOUT)
            ->getProcess()
            ->mustRun();

        $addNewRemoteBuilder = new ProcessBuilder(['git', 'remote', 'add', 'new']);
        $addNewRemoteBuilder
            ->setWorkingDirectory($cwd)
            ->add($this->targetProject->getSshUrlToRepo())
            ->getProcess()
            ->mustRun();

        $pushToRemoteBuilder = new ProcessBuilder(['git', 'push', 'new', '--all']);
        $pushToRemoteBuilder
            ->setWorkingDirectory($cwd)
            ->setTimeout(self::TIMEOUT)
            ->getProcess()
            ->mustRun();

        $pushTagsToRemoteBuilder = new ProcessBuilder(['git', 'push', 'new', '--tags']);
        $pushTagsToRemoteBuilder
            ->setWorkingDirectory($cwd)
            ->setTimeout(self::TIMEOUT)
            ->getProcess()
            ->mustRun();
    }

    /**
     * This will re-enable builds if source had them enabled.
     */
    private function reEnableBuilds()
    {
        if (false === $this->sourceProject->getBuildsEnabled()) {
            return;
        }

        $targetApi = GitlabApiFactory::fromProject($this->targetProject);
        $targetApi->projects->update($this->targetProject->getRemoteId(), ['builds_enabled' => true]);
    }
}
