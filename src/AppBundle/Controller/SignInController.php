<?php

namespace AppBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use AppBundle\Entity\Activity;
use AppBundle\Entity\Participant;
use AppBundle\Entity\Person;

class SignInController extends Controller
{
    /**
     * Displays a sign-in page
     *
     * @Route("/signin/{id}", name="signin_show", methods={"GET", "POST"})
     */
    public function signinAction(Request $request, Activity $activity)
    {
        //Check to see if the signin key is valid
        $signInKey = $request->get('key');
        if ($signInKey === null || $signInKey != $activity->getSigninKey()) {
            throw $this->createAccessDeniedException('Sign In Key was invalid');
        }

        $form = $this->createSigninForm($activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $data = $form->getData();

            $p = $data['person'];

            if ($p != null) {
                $participantStatus = $em->getRepository('AppBundle:ParticipantStatus')->findOneByStatus('Attending');

                $participant = new Participant();
                $participant->setSignupMethod('onsite');
                $participant->setSignupDatetime(new \DateTime());
                $participant->setManagedActivity($activity);
                $participant->setParticipantStatus($participantStatus);
                $participant->setPerson($p);

                //Limit sign-in's to within 6 hours of the end of the event to prevent people signing in to the wrong event
                $limit = clone $activity->getActivityEnd();
                $limit->add(new \DateInterval('PT6H'));
                $now = new \DateTime();
                if ($now > $limit) {
                    $this->addFlash('error', 'Sign-in period has expired.  Are you signing into the right event?');

                    return $this->redirectToRoute('signin_show', [ 'id' => $activity->getId(), 'key' => $activity->getSigninKey() ]);
                }

                try {
                    $em->persist($participant);
                    $em->flush();

                    $message = sprintf('%s, you have been signed in!', $participant->getPerson()->getForename());
                    $this->addFlash('notice', $message);
                } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                    $message = sprintf('%s, you have already signed in!', $participant->getPerson()->getForename());
                    $this->addFlash('error', $message);
                }

                return $this->redirectToRoute('signin_show', [ 'id' => $activity->getId(), 'key' => $activity->getSigninKey() ]);
            }
        }

        return $this->render('signin/show.html.twig', array(
            'activity' => $activity,
            'signin_form' => $form->createView(),
        ));
    }


    private function createSigninForm(Activity $activity)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('signin_show', array('id' => $activity->getId(), 'key' => $activity->getSigninKey())))
            ->setMethod('POST')
            ->add('person', EntityType::class, [
                'class' => 'AppBundle:Person',
                'query_builder' => function (\Doctrine\ORM\EntityRepository $er) {
                    return $er->queryMembersAtDate(new \DateTime());
                },
                'choice_label' => function (Person $a) {
                    return $a->getForename() . ' ' . $a->getSurname() . ' (' . $a->getId() . ')';
                },
                'label' => 'Select your name from the list below',
                'placeholder' => '',
                'attr' => [
                    'class' => 'input-lg'
                ]
            ])
            ->getForm()
        ;
    }
}
