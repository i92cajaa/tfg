<?php

namespace App\Twig\Extension;

use App\Shared\Classes\UTCDateTime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class UTCDatetimeExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('UTCDateTimeFormat', [$this, 'format']),
        ];
    }

    public function format(?\DateTime $dateTime, string $format = ''): string
    {
        if($dateTime){
            $dateTimeClone = clone $dateTime;
            $dateTimeClone = UTCDateTime::format($dateTimeClone);

            return $dateTimeClone->format($format);
        }

        return '';
    }


}