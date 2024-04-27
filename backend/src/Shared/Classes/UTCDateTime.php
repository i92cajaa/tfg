<?php

namespace App\Shared\Classes;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;

class UTCDateTime
{
    public static function create(?string $format = null, ?string $date = null, ?DateTimeZone $timezone = null): ?DateTime
    {
        if ($date !== null) {
            return DateTime::createFromFormat($format, $date, $timezone ?: self::validTimezone());
        } else {
            return new DateTime('NOW', $timezone ?: self::validTimezone());
        }
    }

    public static function setUTC(?DateTime $dateTime): ?DateTime
    {
        $dateTime?->setTimezone(self::validTimezone());

        return $dateTime;
    }

    public static function format(?DateTime $dateTime): ?DateTime
    {
        $resultDatetime = null;

        if($dateTime){
            $resultDatetime = clone $dateTime;

            $resultDatetime?->setTimezone(self::validTimezone());
        }

        return $resultDatetime;
    }

    public static function getTimezone(){
        if(in_array(@$_COOKIE['timezone'], DateTimeZone::listIdentifiers(DateTimeZone::ALL))){
            $timezone = @$_COOKIE['timezone'];
        }else{
            $timezone = 'Europe/Madrid';
            setrawcookie('timezone', $timezone, [
                'expires' => time() + 86400,
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'None',
            ]);
        }

        return $timezone;
    }

    public static function validTimezone(): DateTimeZone
    {

        return new DateTimeZone(self::getTimezone());
    }
}