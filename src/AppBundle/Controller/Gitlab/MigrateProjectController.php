<?php

namespace AppBundle\Controller\Gitlab;

use AppBundle\Entity\Gitlab\Project;
use AppBundle\Form\Gitlab\ProjectMigrationType;
use AppBundle\Model\ProjectMigration;
use AppBundle\Service\MigrationProcess;
use AppBundle\Service\ProjectService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Migrates a project to a different host.
 * @Route(name="gitlab_migrate_project", path="/gitlab/migrate-project", service="app.controller.migrate_project")
 */
class MigrateProjectController extends Controller
{
    /**
     * @var ProjectService
     */
    private $projectService;

    /**
     * MigrateProjectController constructor.
     *
     * @param ProjectService $projectService
     */
    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    /**
     * @Route()
     * @Template()
     * @param Request $request
     *
     * @return array|Response
     */
    public function previewAction(Request $request)
    {
        $projectId = $request->get('project_id', null);
        $project = $this->getDoctrine()->getManager()->find(Project::class, $projectId);

        if ($project === null) {
            throw new NotFoundHttpException(sprintf('Could not find project with ID %s', $projectId));
        }

        $projectMigration = new ProjectMigration($project);

        $form = $this->createForm(ProjectMigrationType::class, $projectMigration);
        $form->handleRequest($request);

        if ($this->formIsValidAndWasSubmittedWithFinalizeButton($form)) {

            $data = $form->getData();

            return $this->redirectToRoute(
                'app_gitlab_migrateproject_migrate',
                [
                    'project_id' => $projectId,
                    'host'       => $projectMigration->getTargetHost()->getId(),
                    'group'      => $projectMigration->getTargetGroup()->getId(),
                    'name'       => $projectMigration->getTargetName(),
                ]
            );
        }

        return [
            'form'    => $form->createView(),
            'project' => $project,
        ];
    }

    /**
     * @Route("/migrate")
     * @Template()
     * @param Request $request
     *
     * @return array
     */
    public function migrateAction(Request $request)
    {
        $projectRepository = $this->getDoctrine()->getRepository(Project::class);
        $sourceProject = $projectRepository->find($request->get('project_id'));
        $targetProject = $this->projectService->findOrCreateTargetProjectFromRequest($request, $sourceProject);

        $migrationProcess = new MigrationProcess($sourceProject, $targetProject);
        $migrationProcess->migrate();

        return [
            'project' => $sourceProject,
        ];
    }

    /**
     * @param $form
     *
     * @return bool
     */
    protected function formIsValidAndWasSubmittedWithFinalizeButton(FormInterface $form): bool
    {
        return $form->isSubmitted()
            && $form->isValid()
            && $form->has('yes')
            && $form->get('yes')->isClicked();
    }
}
