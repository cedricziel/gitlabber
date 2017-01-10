<?php

namespace AppBundle\Controller\Gitlab;

use AppBundle\Entity\Gitlab\Group;
use AppBundle\Form\Gitlab\GroupType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("gitlab/group")
 */
class GroupController extends Controller
{
    /**
     * @Route("/", name="gitlab_group_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $groups = $em->getRepository(Group::class)->findAll();

        return [
            'groups' => $groups,
        ];
    }

    /**
     * @Route("/new", name="gitlab_group_new")
     * @Method({"GET", "POST"})
     * @Template()
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    public function newAction(Request $request)
    {
        $group = new Group();
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($group);
            $em->flush();

            return $this->redirectToRoute('gitlab_group_show', ['id' => $group->getId()]);
        }

        return [
            'group' => $group,
            'form'  => $form->createView(),
        ];
    }

    /**
     * Finds and displays a group entity.
     * @Route("/{id}", name="gitlab_group_show")
     * @Method("GET")
     * @Template()
     *
     * @param Group $group
     *
     * @return array
     */
    public function showAction(Group $group)
    {
        $deleteForm = $this->createDeleteForm($group);

        return [
            'group'       => $group,
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * @Route("/{id}/edit", name="gitlab_group_edit")
     * @Method({"GET", "POST"})
     * @Template()
     * @param Request $request
     * @param Group   $group
     *
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, Group $group)
    {
        $deleteForm = $this->createDeleteForm($group);
        $editForm = $this->createForm(GroupType::class, $group);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('gitlab_group_edit', ['id' => $group->getId()]);
        }

        return [
            'group'       => $group,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * @Route("/{id}", name="gitlab_group_delete")
     * @Method("DELETE")
     * @param Request $request
     * @param Group   $group
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, Group $group)
    {
        $form = $this->createDeleteForm($group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($group);
            $em->flush();
        }

        return $this->redirectToRoute('gitlab_group_index');
    }

    /**
     * Creates a form to delete a group entity.
     *
     * @param Group $group The group entity
     *
     * @return Form The form
     */
    private function createDeleteForm(Group $group)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('gitlab_group_delete', ['id' => $group->getId()]))
            ->setMethod('DELETE')
            ->getForm();
    }
}
