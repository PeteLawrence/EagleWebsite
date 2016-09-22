<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/calendar")
 */
class CalendarController extends Controller
{
    /**
     * @Route("/ical", name="calendar_ical")
     */
    public function icalAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        //Create a calendar object
        $cal = new Calendar('Eagle Canoe Club');

        //Loop over all activities
        $activities = $em->getRepository('AppBundle:Activity')->findAll();
        foreach ($activities as $activity) {
            //Build the event
            $event = new Event();
            $event->setUseUtc(false);
            $event->setDtStart($activity->getActivityStart());
            $event->setDtEnd($activity->getActivityEnd());
            $event->setSummary($activity->getName());
            $event->setDescription($activity->getDescription());
            if ($activity->getLocation()) {
                $event->setLocation(
                    str_replace(',', "\n", $activity->getLocation()->getAddress() . ', ' . $activity->getLocation()->getPostcode()),
                    $activity->getLocation()->getName(),
                    sprintf('%s,%s', $activity->getLocation()->getLongitude(), $activity->getLocation()->getLatitude())
                );
            }

            //Add the event to the calendar
            $cal->addComponent($event);
        }

        //Output the calendar along with the correct headers
        $response = new Response();
        $response->headers->set('Content-Type', 'text/calendar; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="cal.ics"');
        $response->setContent($cal->render());

        return $response;
    }
}
