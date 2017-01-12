<?php

namespace AppBundle\Command;

use AppBundle\Entity\Gitlab\Project;
use AppBundle\Factory\GitlabApiFactory;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AppGitlabPruneCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:gitlab:prune')
            ->setDescription(
                'Removes repositories from the local database that have been deleted on the defined remotes.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $localProjects = $this->getContainer()->get('doctrine')->getRepository(Project::class)->findAll();

        $io->comment('Querying remote projects..');

        $table = [];
        $io->progressStart(count($localProjects));
        foreach($localProjects as $project) {
            $status = $this->checkAndPruneProject($project) ? 'OK' : 'Missing, Removed';
            $table[] = [$project->getName(), $status];
            $io->progressAdvance();
        }
        $io->progressFinish();

        $io->table(['Name', 'Status'], $table);
    }

    /**
     * @param Project $project
     *
     * @return bool
     */
    private function checkAndPruneProject(Project $project)
    {
        $remoteApi = GitlabApiFactory::fromProject($project);

        try {
            $remoteProject = $remoteApi->repositories->contributors($project->getRemoteId());
        } catch (\Exception $e) {
            $em = $this->getContainer()->get('doctrine')->getEntityManager();
            $em->remove($project);
            $em->flush($project);

            return false;
        }

        return true;
    }
}
