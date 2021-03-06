<?php

// src/AppBundle/Twig/AppExtension.php
namespace AppBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DateDiffExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return array(
            new TwigFunction('dateDiff', [ $this, 'dateDiffFunction' ]),
        );
    }

    public function dateDiffFunction($date1, $date2)
    {
        if ($date1 == "now") {
            $date1 = new \DateTime();
        }

        if ($date2 == "now") {
            $date2 = new \DateTime();
        }

        $diff = $date1->diff($date2);

        return $diff;
    }
}
