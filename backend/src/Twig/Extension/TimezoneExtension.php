<?php


namespace App\Twig\Extension;

use App\Entity\Config;
use App\Repository\ConfigRepository;
use App\Shared\Utils\DocumentImage;
use App\Shared\Utils\Util;
use DateTimeZone;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class TimezoneExtension extends AbstractExtension implements GlobalsInterface
{

    public function getGlobals():array
    {

        return [
            '_timezones' => DateTimeZone::listIdentifiers(DateTimeZone::ALL)
        ];
    }

}