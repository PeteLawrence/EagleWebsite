<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class SendSignupEmailsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
        ->setName('eagledb:signup:send')
        ->setDescription('Sends a link to todays signup form');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //Set the context so that URL's contain the correct URL
        $context = $this->getContainer()->get('router')->getContext();
        $context->setHost('www.eaglecanoeclub.co.uk');
        $context->setScheme('https');


        $em = $this->getContainer()->get('doctrine')->getManager();
        $templating = $this->getContainer()->get('templating');
        $logger = $this->getContainer()->get('logger');

        //Find Activities taking place
        $activities  = $em->getRepository('AppBundle:ManagedActivity')->findAll();

        $now = new \DateTime();
        foreach ($activities as $activity) {
            if ($activity->getActivityStart()->format('Y-m-d') == $now->format('Y-m-d')) {
                $logger->info('Found activity commencing today ' . $activity->getName());

                //Build the email
                $message = new \Swift_Message('Email');
                $message
                    ->setSubject(sprintf('Sign-in form for %s', $activity->getName()))
                    ->setFrom($this->getContainer()->getParameter('site.email'))
                    ->setTo($this->getContainer()->getParameter('site.signInEmail'))
                    ->setBody(
                        $templating->render(
                            'emails/signInForm.html.twig',
                            array('activity' => $activity)
                        ),
                        'text/html'
                    );

                //Send the email
                $this->getContainer()->get('mailer')->send($message);
            }
        }
    }
}
