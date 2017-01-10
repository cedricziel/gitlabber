<?php

namespace AppBundle\Controller\Gitlab;

use AppBundle\Entity\Gitlab\Project;
use AppBundle\Form\Gitlab\MigrateProjectType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Mirates a project to a different host.
 * @Route(name="gitlab_migrate_project", path="/gitlab/migrate-project")
 */
class MigrateProjectController extends Controller
{
    /**
     * @Route()
     * @Template()
     * @param Request $request
     *
     * @return array
     */
    public function previewAction(Request $request)
    {
        $projectId = $request->get('project_id', null);
        $project = $this->getDoctrine()->getManager()->find(Project::class, $projectId);

        if ($project === null) {
            throw new NotFoundHttpException(sprintf('Could not find project with ID %s', $projectId));
        }

        $form = $this->createForm(MigrateProjectType::class);
        $form->handleRequest($request);

        return [
            'form'    => $form->createView(),
            'project' => $project,
        ];
    }
}
