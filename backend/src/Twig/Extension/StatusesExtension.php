<?php


namespace App\Twig\Extension;


use App\Entity\Task\Task;
use App\Repository\ConfigRepository;
use App\Repository\StatusRepository;
use App\Shared\Utils\DocumentImage;
use App\Shared\Utils\Util;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class StatusesExtension extends AbstractExtension implements GlobalsInterface
{


    public function __construct(
        private readonly StatusRepository $statusRepository
    )
    {

    }

    public function getGlobals():array
    {

        return [
            'taskStatuses' => $this->statusRepository->findBy(['entityType' => Task::ENTITY])
        ];
    }


}