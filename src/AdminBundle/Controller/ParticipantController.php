<?php

namespace AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Participant;
use AppBundle\Form\ParticipantType;

/**
 * Participant controller.
 *
 * @Route("/participant")
 */
class ParticipantController extends Controller
{
    /**
     * Lists all Participant entities.
     *
     * @Route("/", name="admin_participant_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $participants = $em->getRepository('AppBundle:Participant')->findAll();

        return $this->render('admin/participant/index.html.twig', array(
            'participants' => $participants,
        ));
    }

    /**
     * Creates a new Participant entity.
     *
     * @Route("/new", name="admin_participant_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $participant = new Participant();
        $form = $this->createForm('AppBundle\Form\ParticipantType', $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($participant);
            $em->flush();

            return $this->redirectToRoute('admin_participant_index');
        }

        return $this->render('admin/participant/new.html.twig', array(
            'participant' => $participant,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Participant entity.
     *
     * @Route("/{id}", name="admin_participant_show")
     * @Method("GET")
     */
    public function showAction(Participant $participant)
    {
        $deleteForm = $this->createDeleteForm($participant);

        return $this->render('admin/participant/show.html.twig', array(
            'participant' => $participant,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Participant entity.
     *
     * @Route("/{id}/edit", name="admin_participant_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Participant $participant)
    {
        $deleteForm = $this->createDeleteForm($participant);
        $editForm = $this->createForm('AppBundle\Form\ParticipantType', $participant);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($participant);
            $em->flush();

            return $this->redirectToRoute('activity_participant_list', [ 'id' => $participant->getActivity()->getId() ]);
        }

        return $this->render('admin/participant/edit.html.twig', array(
            'participant' => $participant,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Participant entity.
     *
     * @Route("/{id}", name="admin_participant_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Participant $participant)
    {
        $form = $this->createDeleteForm($participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($participant);
            $em->flush();
        }

        return $this->redirectToRoute('admin_participant_index');
    }

    /**
     * Creates a form to delete a Participant entity.
     *
     * @param Participant $participant The Participant entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Participant $participant)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_participant_delete', array('id' => $participant->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}