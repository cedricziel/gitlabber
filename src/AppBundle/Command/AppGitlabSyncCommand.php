<?php

namespace AppBundle\Command;

use AppBundle\Entity\Gitlab\Group;
use AppBundle\Entity\Gitlab\Host;
use AppBundle\Entity\Gitlab\Project;
use AppBundle\Repository\Gitlab\GroupRepository;
use AppBundle\Repository\Gitlab\HostRepository;
use AppBundle\Repository\Gitlab\ProjectRepository;
use Buzz\Client\Curl;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Gitlab\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * {@inheritdoc}
 */
class AppGitlabSyncCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:gitlab:sync')
            ->setDescription('Syncs the gitlab remotes to the local database');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $hosts = $this->getHostRepository()->findAll();

        foreach ($hosts as $host) {
            $output->writeln(sprintf('<info>Syncing Host %s - %s</info>', $host->getName(), $host->getUrl()));

            $this->syncGroups($host, $io);
        }
    }

    /**
     * @param Host         $host
     * @param SymfonyStyle $io
     */
    protected function syncGroups(Host $host, $io)
    {
        $apiClient = $this->getClientForHost($host);

        $groups = $apiClient->groups->all();

        foreach ($groups as $apiGroup) {
            $io->section(sprintf('Host: %s - Group: %s', $host->getName(), $apiGroup['name']));

            $localGroups = $this->getGroupRepository()->findBy(['gitlab' => $host, 'remoteId' => $apiGroup['id']]);
            $localGroup = null;
            if (count($localGroups) === 0) {
                $localGroup = new Group();
                $localGroup
                    ->setRemoteId($apiGroup['id'])
                    ->setGitlab($host);

                $io->success(
                    sprintf(
                        'Creating Group %s (%d).',
                        $localGroup->getName(),
                        $localGroup->getRemoteId()
                    )
                );
            } else {
                /** @var Group $localGroup */
                $localGroup = $localGroups[0];
                $io->note(
                    sprintf(
                        'Group %s (%d) already exists. Syncing properties.',
                        $localGroup->getName(),
                        $localGroup->getRemoteId()
                    )
                );
            }

            $localGroup
                ->setName($apiGroup['name'])
                ->setPath($apiGroup['path'])
                ->setDescription($apiGroup['description'])
                ->setVisibilityLevel($apiGroup['visibility_level'])
                ->setAvatarUrl($apiGroup['avatar_url'])
                ->setWebUrl($apiGroup['web_url']);

            $this->getEntityManager()->persist($localGroup);
            $this->getEntityManager()->flush($localGroup);

            $this->syncProjects($localGroup, $io);
        }
    }

    /**
     * @param Group        $group
     * @param SymfonyStyle $io
     */
    protected function syncProjects($group, $io)
    {
        $apiClient = $this->getClientForHost($group->getGitlab());
        $remoteProjects = $apiClient->projects->accessible(1, 1000);

        $remoteProjectsInGroup = (new ArrayCollection($remoteProjects))->filter(
            function ($project) use ($group) {
                return $project['namespace']['id'] === $group->getRemoteId();
            }
        );

        $io->listing(
            array_map(
                function ($project) {
                    return sprintf('%s (%d)', $project['name'], $project['id']);
                },
                $remoteProjectsInGroup->toArray()
            )
        );

        foreach ($remoteProjectsInGroup->toArray() as $remoteProject) {
            $localProject = $this->getProjectRepository()->findOneBy(
                [
                    'host'     => $group->getGitlab(),
                    'group'    => $group,
                    'remoteId' => $remoteProject['id'],
                ]
            );

            if ($localProject === null) {
                $localProject = new Project();
                $localProject
                    ->setHost($group->getGitlab())
                    ->setRemoteId($remoteProject['id'])
                    ->setGroup($group);
            }

            $localProject
                ->setPublic((bool)$remoteProject['public'])
                ->setArchived((bool)$remoteProject['archived'])
                ->setVisibilityLevel((int)$remoteProject['visibility_level'])
                ->setSshUrlToRepo($remoteProject['ssh_url_to_repo'])
                ->setHttpUrlToRepo($remoteProject['http_url_to_repo'])
                ->setWebUrl($remoteProject['web_url'])
                ->setName($remoteProject['name'])
                ->setNameWithNamespace($remoteProject['name_with_namespace'])
                ->setPath($remoteProject['path'])
                ->setPathWithNamespace($remoteProject['path_with_namespace'])
                ->setContainerRegistryEnabled((bool)$remoteProject['container_registry_enabled'])
                ->setSnippetsEnabled((bool)$remoteProject['snippets_enabled'])
                ->setIssuesEnabled((bool)$remoteProject['issues_enabled'])
                ->setMergeRequestsEnabled((bool)$remoteProject['merge_requests_enabled'])
                ->setWikiEnabled((bool)$remoteProject['wiki_enabled'])
                ->setBuildsEnabled((bool)$remoteProject['builds_enabled'])
                ->setRemoteCreatedAt(new \DateTime())
                ->setRemoteLastActivityAt(new \DateTime())
                ->setSharedRunnersEnabled((bool)$remoteProject['shared_runners_enabled'])
                ->setLfsEnabled((bool)$remoteProject['lfs_enabled'])
                ->setCreatorId((int)$remoteProject['creator_id'])
                ->setAvatarUrl((string)$remoteProject['avatar_url'])
            ;


            $validator = $this->getValidator();
            $errors = $validator->validate($localProject);
            if (count($errors) > 0) {
                $io->error(
                    sprintf(
                        'Cannot persist %s (%d), the following errors occured:',
                        $localProject->getName(),
                        $localProject->getRemoteId()
                    )
                );

                foreach ($errors as $error) {
                    /** @var ConstraintViolation $error */
                    $io->error($error->getPropertyPath() . ' ' . $error->getMessage());
                }

                continue;
            }

            $this->getEntityManager()->persist($localProject);
            $this->getEntityManager()->flush($localProject);
        }

    }

    /**
     * @return HostRepository
     */
    protected function getHostRepository()
    {
        return $this->getContainer()->get('doctrine')->getRepository(Host::class);
    }

    /**
     * @return GroupRepository
     */
    protected function getGroupRepository()
    {
        return $this->getContainer()->get('doctrine')->getRepository(Group::class);
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getContainer()->get('doctrine.orm.default_entity_manager');
    }

    /**
     * @return ProjectRepository
     */
    protected function getProjectRepository()
    {
        return $this->getContainer()->get('doctrine')->getRepository(Project::class);
    }

    /**
     * @return object|\Symfony\Component\Validator\Validator\ValidatorInterface
     */
    protected function getValidator()
    {
        return $this->getContainer()->get('validator');
    }

    /**
     * @param Host $host
     *
     * @return Client
     */
    private function getClientForHost(Host $host)
    {
        $httpClient = new Curl();

        $client = new Client($host->getUrl(), $httpClient);
        $client->authenticate($host->getToken(), Client::AUTH_URL_TOKEN);

        return $client;
    }
}
