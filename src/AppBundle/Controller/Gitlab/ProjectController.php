<?php

namespace AppBundle\Controller\Gitlab;

use AppBundle\Entity\Gitlab\Project;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("gitlab/project")
 */
class ProjectController extends Controller
{
    /**
     * @Route()
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAction(Request $request)
    {
        $projectRepository = $this->getDoctrine()->getRepository(Project::class);
        $project = $projectRepository->find($request->get('project_id'));

        if ($project === null) {
            throw new NotFoundHttpException('Could not find project');
        }

        $projectService = $this->get('app.service.project');
        $projectService->removeProjectFromSourceAndDatabase($project);

        $this->addFlash(
            'notice',
            sprintf(
                'Removed project %s (%d) from host %s',
                $project->getName(),
                $project->getRemoteId(),
                $project->getHost()->getName()
            )
        );

        return $this->redirectToRoute('gitlab_host_index');
    }
}
