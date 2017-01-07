<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Activity;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * @Route("/activity")
 */
class ActivityController extends Controller
{
    /**
     * @Route("/", name="activity_index")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $activities = $em->getRepository('AppBundle:Activity')->findAll();
        //$activities = $em->getRepository('AppBundle:ManagedActivity')->findActivitiesAvailableToPerson($user);

        return $this->render('activity/index.html.twig', array(
            'activities' => $activities,
        ));
    }


    /**
     * @Route("/{id}", name="activity_view")
     */
    public function viewAction(Request $request, Activity $activity)
    {

        // replace this example code with whatever you need
        return $this->render(
            'activity/view.html.twig',
            [
                'activity' => $activity,
                'google_maps_key' => $this->getParameter('site.google_maps_key')
            ]
        );
    }


    /**
     * @Route("/{id}/signup", name="activity_signup")
     */
    public function signupAction(Request $request, Activity $activity)
    {
        $em = $this->get('doctrine')->getManager();

        $signupForm = $this->buildSignupForm($activity);
        $signupForm->handleRequest($request);

        if ($signupForm->isSubmitted() && $signupForm->isValid()) {
            $data = $signupForm->getData();
            $participantStatus = $em->getRepository('AppBundle:ParticipantStatus')->findOneByStatus('Attending');

            $participant = new \AppBundle\Entity\Participant;
            $participant->setManagedActivity($activity);
            $participant->setPerson($this->get('security.token_storage')->getToken()->getUser());
            $participant->setSignupDateTime(new \DateTime());
            $participant->setParticipantStatus($participantStatus);
            $participant->setNotes($data['notes']);

            $em->persist($participant);
            $em->flush();

            //Send an email to the organiser
            $message = \Swift_Message::newInstance()
                ->setSubject(sprintf('%s has signed up to %s', $participant->getPerson()->getName(), $activity->getName()))
                ->setFrom($this->getParameter('site.email'))
                ->setTo($activity->getOrganiser()->getEmail())
                ->setBody(
                    $this->renderView(
                        'emails/activitySignup.html.twig',
                        array('signup' => $participant)
                    ),
                    'text/html'
                );
            $this->get('mailer')->send($message);

            //Display confirmation flash message
            $this->addFlash('notice', 'You have signed up to the activity');

            return $this->redirectToRoute('activity_view', [ 'id' => $activity->getId() ]);
        }

        // replace this example code with whatever you need
        return $this->render(
            'activity/signup.html.twig',
            [
                'activity' => $activity,
                'form' => $signupForm->createView()
            ]
        );
    }


    /**
     * @Route("/{id}/participants", name="activity_participants")
     */
    public function participantsAction(Request $request, Activity $activity)
    {
        //Only allow the organiser access to this page
        if ($activity->getOrganiser() != $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render(
            'activity/participants.html.twig',
            [
                'activity' => $activity
            ]
        );
    }


    private function buildSignupForm($activity)
    {
        return $this->createFormBuilder()
            ->add('notes', TextareaType::class, [ 'attr' => ['rows' => '5'], 'label' => 'Notes to the organiser' ])
            ->setAction($this->generateUrl('activity_signup', array('id' => $activity->getId())))
            ->setMethod('POST')
            ->getForm()
        ;
    }
}
