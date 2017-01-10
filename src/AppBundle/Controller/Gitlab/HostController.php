<?php

namespace AppBundle\Controller\Gitlab;

use AppBundle\Entity\Gitlab\Host;
use AppBundle\Form\Gitlab\HostType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("gitlab/host")
 */
class HostController extends Controller
{
    /**
     * @Route("/", name="gitlab_host_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $hosts = $em->getRepository(Host::class)->findAll();

        return [
            'hosts' => $hosts,
        ];
    }

    /**
     * @Route("/new", name="gitlab_host_new")
     * @Method({"GET", "POST"})
     * @Template()
     * @param Request $request
     *
     * @return array|Response
     */
    public function newAction(Request $request)
    {
        $host = new Host();
        $form = $this->createForm(HostType::class, $host);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($host);
            $em->flush();

            return $this->redirectToRoute('gitlab_host_show', ['id' => $host->getId()]);
        }

        return [
            'host' => $host,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/{id}", name="gitlab_host_show")
     * @Method("GET")
     * @Template()
     * @param Host $host
     *
     * @return array
     */
    public function showAction(Host $host)
    {
        $deleteForm = $this->createDeleteForm($host);

        return [
            'host'        => $host,
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * @Route("/{id}/edit", name="gitlab_host_edit")
     * @Method({"GET", "POST"})
     * @Template()
     * @param Request $request
     * @param Host    $host
     *
     * @return array|RedirectResponse
     */
    public function editAction(Request $request, Host $host)
    {
        $deleteForm = $this->createDeleteForm($host);
        $editForm = $this->createForm(HostType::class, $host);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()
                ->getManager()
                ->flush();

            return $this->redirectToRoute('gitlab_host_edit', ['id' => $host->getId()]);
        }

        return [
            'host'        => $host,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * @Route("/{id}", name="gitlab_host_delete")
     * @Method("DELETE")
     * @param Request $request
     * @param Host    $host
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, Host $host)
    {
        $form = $this->createDeleteForm($host);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($host);
            $em->flush();
        }

        return $this->redirectToRoute('gitlab_host_index');
    }

    /**
     * Creates a form to delete a host entity.
     *
     * @param Host $host The host entity
     *
     * @return Form The form
     */
    private function createDeleteForm(Host $host)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('gitlab_host_delete', ['id' => $host->getId()]))
            ->setMethod('DELETE')
            ->getForm();
    }
}
