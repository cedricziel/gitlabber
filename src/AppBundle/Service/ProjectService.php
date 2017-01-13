<?php

namespace AppBundle\Service;

use AppBundle\Entity\Gitlab\Group;
use AppBundle\Entity\Gitlab\Host;
use AppBundle\Entity\Gitlab\Project;
use AppBundle\Factory\GitlabApiFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class ProjectService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * ProjectService constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * This will either create a new project, or retrieve an existing one.
     * New projects will be created with builds turned off, so we dont't
     * have any unpleasant surprises.
     *
     * @param Request $request
     * @param Project $sourceProject
     *
     * @return Project
     */
    public function findOrCreateTargetProjectFromRequest(Request $request, Project $sourceProject)
    {
        if ($sourceProject->getMigratedTo()->first()) {
            return $sourceProject->getMigratedTo()->first();
        }

        $targetHost = $this->entityManager->getRepository(Host::class)->find($request->get('host'));
        /** @var Group $targetGroup */
        $targetGroup = $this->entityManager->getRepository(Group::class)->find($request->get('group'));
        $targetProjectName = $request->get('name');

        if ($targetHost === null || $targetGroup === null) {
            throw new \InvalidArgumentException('No valid IDs given');
        }

        $targetHostApi = GitlabApiFactory::fromHost($targetHost);
        $remoteProject = $targetHostApi->projects->create(
            $targetProjectName,
            [
                'namespace_id'   => $targetGroup->getRemoteId(),
                'description'    => $sourceProject->getDescription(),
                'builds_enabled' => false,
                'issues_enabled' => $sourceProject->getIssuesEnabled(),
            ]
        );

        $localProject = new Project();
        $localProject
            ->setHost($targetHost)
            ->setRemoteId($remoteProject['id'])
            ->setGroup($targetGroup)
            ->setPublic((bool) $remoteProject['public'])
            ->setArchived((bool) $remoteProject['archived'])
            ->setVisibilityLevel((int) $remoteProject['visibility_level'])
            ->setSshUrlToRepo($remoteProject['ssh_url_to_repo'])
            ->setHttpUrlToRepo($remoteProject['http_url_to_repo'])
            ->setWebUrl($remoteProject['web_url'])
            ->setName($remoteProject['name'])
            ->setNameWithNamespace($remoteProject['name_with_namespace'])
            ->setPath($remoteProject['path'])
            ->setPathWithNamespace($remoteProject['path_with_namespace'])
            ->setContainerRegistryEnabled((bool) $remoteProject['container_registry_enabled'])
            ->setSnippetsEnabled((bool) $remoteProject['snippets_enabled'])
            ->setIssuesEnabled((bool) $remoteProject['issues_enabled'])
            ->setMergeRequestsEnabled((bool) $remoteProject['merge_requests_enabled'])
            ->setWikiEnabled((bool) $remoteProject['wiki_enabled'])
            ->setBuildsEnabled((bool) $remoteProject['builds_enabled'])
            ->setRemoteCreatedAt(new \DateTime())
            ->setRemoteLastActivityAt(new \DateTime())
            ->setSharedRunnersEnabled((bool) $remoteProject['shared_runners_enabled'])
            ->setLfsEnabled((bool) $remoteProject['lfs_enabled'])
            ->setCreatorId((int) $remoteProject['creator_id'])
            ->setAvatarUrl((string) $remoteProject['avatar_url'])
            ->setSourceProject($sourceProject)
        ;

        $this->entityManager->persist($localProject);
        $this->entityManager->flush($localProject);
        $this->entityManager->refresh($localProject);

        return $localProject;
    }

    public function removeProjectFromSourceAndDatabase(Project $project)
    {
        $dependantProjects = $project->getMigratedTo();

        $sourceHostApi = GitlabApiFactory::fromProject($project);
        $sourceHostApi->projects->remove($project->getRemoteId());

        foreach($dependantProjects as $dependantProject) {
            /** @var Project $dependantProject */
            $dependantProject->setSourceProject(null);
            $this->entityManager->persist($dependantProject);
            $this->entityManager->flush($dependantProject);
        }

        $this->entityManager->remove($project);
        $this->entityManager->flush($project);
    }
}
