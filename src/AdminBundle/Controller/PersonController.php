<?php

namespace AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Person;
use AppBundle\Form\Type\PersonType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use AppBundle\Services\PersonReportService;

/**
 * Person controller.
 *
 * @Route("/person")
 */
class PersonController extends Controller
{
    /**
     * Lists all Person entities.
     *
     * @Route("/", name="admin_person_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $people = $em->getRepository('AppBundle:Person')->findAll();

        return $this->render('admin/person/index.html.twig', array(
            'people' => $people,
        ));
    }

    /**
     * Creates a new Person entity.
     *
     * @Route("/new", name="admin_person_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $person = new Person();
        $form = $this->createForm(PersonType::class, $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($person);
            $em->flush();

            return $this->redirectToRoute('admin_person_show', array('id' => $person->getId()));
        }

        return $this->render('admin/person/new.html.twig', array(
            'person' => $person,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Person entity.
     *
     * @Route("/{id}", name="admin_person_show")
     * @Method("GET")
     */
    public function showAction(Person $person)
    {
        $deleteForm = $this->createDeleteForm($person);

        return $this->render('admin/person/show.html.twig', array(
            'person' => $person,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Person entity.
     *
     * @Route("/{id}/edit", name="admin_person_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Person $person)
    {
        $deleteForm = $this->createDeleteForm($person);
        $editForm = $this->createForm('AppBundle\Form\Type\PersonType', $person);
        $editForm->handleRequest($request);

        $nextOfKinForm = $this->buildNextOfKinForm($person);
        $nextOfKinForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($person);
            $em->flush();

            return $this->redirectToRoute('admin_person_index');
        }

        if ($nextOfKinForm->isSubmitted() && $nextOfKinForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($person);
            $em->flush();

            return $this->redirectToRoute('admin_person_index');
        }



        return $this->render('admin/person/edit.html.twig', array(
            'person' => $person,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'nextofkin_form' => $nextOfKinForm->createView()
        ));
    }

    /**
     * Deletes a Person entity.
     *
     * @Route("/{id}", name="admin_person_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Person $person)
    {
        $form = $this->createDeleteForm($person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($person);
            $em->flush();
        }

        return $this->redirectToRoute('admin_person_index');
    }


    /**
     * Displays a form to edit an existing Person entity.
     *
     * @Route("/{id}/stats", name="admin_person_stats")
     * @Method({"GET", "POST"})
     */
    public function statsAction(Request $request, Person $person, PersonReportService $personReportService)
    {
        $timeline = $personReportService->buildParticipationTimeline($person);



        return $this->render('admin/person/stats.html.twig', array(
            'person' => $person,
            'timeline' => $timeline
        ));
    }

    /**
     * Creates a form to delete a Person entity.
     *
     * @param Person $person The Person entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Person $person)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_person_delete', array('id' => $person->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }


    private function buildNextOfKinForm($user)
    {
        return $this->createFormBuilder($user)
            ->setAction($this->generateUrl('admin_person_edit', [ 'id' => $user->getId() ]))
            ->setMethod('POST')
            ->add('nextOfKinName', TextType::class, [ 'attr' => ['placeholder' => 'Name'], 'label' => 'Name' ])
            ->add('nextOfKinRelation', TextType::class, [ 'attr' => ['placeholder' => 'Relation'], 'label' => 'Relation' ])
            ->add('nextOfKinContactDetails', TextareaType::class, [ 'attr' => ['placeholder' => 'Contact Details', 'rows' => 3], 'label' => 'Contact Details' ])
            ->getForm()
        ;
    }
}
