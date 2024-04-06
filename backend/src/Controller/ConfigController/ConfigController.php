<?php

namespace App\Controller\ConfigController;

use App\Entity\Config\Config;
use App\Annotation\Permission;
use App\Annotation\Admin;
use App\Service\ConfigService\ConfigService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route(path: '/config')]
class ConfigController extends AbstractController
{

    public function __construct(
        private readonly ConfigService $configService

    )
    {
    }

    #[Route(path: '/edit', name: 'config_edit', methods: ["GET","POST"])]
    #[Permission(group: 'configs', action:"edit")]
    #[Admin]
    public function edit(): Response
    {
        return $this->configService->editConfig();
    }

}
