<?php

namespace AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Entity\Qualification;

/**
 * Qualification controller.
 *
 * @Route("/qualification")
 */
class QualificationController extends Controller
{
    /**
     * Lists all Qualification entities.
     *
     * @Route("/", name="admin_qualification_index", methods={"GET"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $qualifications = $em->getRepository('AppBundle:Qualification')->findAll();

        return $this->render('admin/qualification/index.html.twig', array(
            'qualifications' => $qualifications,
        ));
    }

    /**
     * Creates a new Qualification entity.
     *
     * @Route("/new", name="admin_qualification_new", methods={"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $qualification = new Qualification();
        $form = $this->createForm('AppBundle\Form\Type\QualificationType', $qualification);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($qualification);
            $em->flush();

            return $this->redirectToRoute('admin_qualification_show', array('id' => $qualification->getId()));
        }

        return $this->render('admin/qualification/new.html.twig', array(
            'qualification' => $qualification,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Qualification entity.
     *
     * @Route("/{id}", name="admin_qualification_show", methods={"GET"})
     */
    public function showAction(Qualification $qualification)
    {
        $deleteForm = $this->createDeleteForm($qualification);

        return $this->render('admin/qualification/show.html.twig', array(
            'qualification' => $qualification,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Qualification entity.
     *
     * @Route("/{id}/edit", name="admin_qualification_edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Qualification $qualification)
    {
        $deleteForm = $this->createDeleteForm($qualification);
        $editForm = $this->createForm('AppBundle\Form\Type\QualificationType', $qualification);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($qualification);
            $em->flush();

            return $this->redirectToRoute('admin_qualification_index');
        }

        return $this->render('admin/qualification/edit.html.twig', array(
            'qualification' => $qualification,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Qualification entity.
     *
     * @Route("/{id}", name="admin_qualification_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, Qualification $qualification)
    {
        $form = $this->createDeleteForm($qualification);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($qualification);
            $em->flush();
        }

        return $this->redirectToRoute('admin_qualification_index');
    }

    /**
     * Creates a form to delete a Qualification entity.
     *
     * @param Qualification $qualification The Qualification entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Qualification $qualification)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_qualification_delete', array('id' => $qualification->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
