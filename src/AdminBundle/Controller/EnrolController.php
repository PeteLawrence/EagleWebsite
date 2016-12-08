<?php

namespace AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Activity;
use AppBundle\Entity\ManagedActivity;
use AppBundle\Entity\UnmanagedActivity;

/**
 * Activity controller.
 *
 * @Route("/enrol")
 */
class EnrolController extends Controller
{
    /**
     * Enrol Step 1
     *
     * @Route("/", name="admin_enrol")
     * @Method({"GET", "POST"})
     */
    public function enrolAction(Request $request)
    {
        if (!$request->query->get('person')) {
            throw new \Exception('No person specified');
        } else {
            //Save the person id to the session
            $session = $request->getSession();

            $membershipService = $this->get('membership_service');
            $membershipService->clearSessionEntries($session);

            $form = $membershipService->buildMembershipTypeForm();
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                $session->set('enrol_person', $request->query->get('person'));
                $request->getSession()->set('enrol_mtp', $data['membershipTypePeriod']->getId());

                return $this->redirectToRoute('admin_enrol_2');
            }
        }

        return $this->render('admin/enrol/enrol.html.twig', array(
            'form' => $form->createView()
        ));
    }


    /**
     * Enrol Step 2
     *
     * @Route("/2", name="admin_enrol_2")
     * @Method({"GET", "POST"})
     */
    public function enrol2Action(Request $request)
    {
        $membershipService = $this->get('membership_service');

        $session = $request->getSession();

        $em = $this->get('doctrine')->getManager();
        $mtp = $em->getRepository('AppBundle:MembershipTypePeriod')->findOneById($session->get('enrol_mtp'));

        $form = $membershipService->buildMembershipExtrasForm($mtp);
        $form->handleRequest($request);

        if ($request->getMethod() == 'POST') {
            $extras = [];
            $data = $request->request->get('form');
            foreach ($data as $field => $enabled) {
                if ($field == '_token') {
                    continue;
                }

                $extras[] = substr($field, 6);
            }
            $session->set('enrol_extras', $extras);

            return $this->redirectToRoute('admin_enrol_3');
        }

        return $this->render('admin/enrol/enrol2.html.twig', array(
            'form' => $form->createView()
        ));
    }


    /**
     * Enrol Step 3
     *
     * @Route("/3", name="admin_enrol_3")
     * @Method({"GET", "POST"})
     */
    public function enrol3Action(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $membershipService = $this->get('membership_service');
        $session = $request->getSession();

        $form = $this->buildConfirmationForm();

        $membershipTypePeriodId = $session->get('enrol_mtp');
        $membershipTypePeriodExtraIds = $session->get('enrol_extras');
        $memberRegistration = $membershipService->buildMembershipRegistration($membershipTypePeriodId, $membershipTypePeriodExtraIds);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $memberRegistration->setRegistrationDateTime(new \DateTime());
            $memberRegistration->setPerson($this->getUser());
            $em->persist($memberRegistration);

            $memberRegistrationCharge = $membershipService->buildMemberRegistrationCharge($memberRegistration, $memberRegistration->getTotal(), false);
            $em->persist($memberRegistrationCharge);

            $em->flush();

            return $this->redirectToRoute('admin_person_edit', [ 'id' => $memberRegistration->getPerson()->getId() ]);
        }

        return $this->render('admin/enrol/enrol3.html.twig', array(
            'form' => $form->createView(),
            'memberRegistration' => $memberRegistration
        ));
    }

    private function buildConfirmationForm()
    {
        return $this->createFormBuilder()
            ->setMethod('POST')
            ->getForm();
    }
}