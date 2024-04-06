<?php


namespace App\Twig\Extension;

use App\Entity\Config;
use App\Repository\ConfigRepository;
use App\Repository\ConfigTypeRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class DatabaseGlobalsExtension extends AbstractExtension implements GlobalsInterface
{

    public function __construct(
        private readonly ConfigRepository $configRepository,
        private readonly RequestStack $requestStack,
        private readonly ConfigTypeRepository $configTypeRepository
    )
    {

    }

    public function getGlobals():array
    {
        $configurations = $this->requestStack->getSession()->get('configuration') ? $this->requestStack->getSession()->get('configuration') : [];
        if(!$configurations){
            $configTypes = $this->configTypeRepository->findAll();

            foreach ($configTypes as $configType) {
                $config = $this->configRepository->findOneBy(['tag' => $configType->getTag()]);
                $configurations[$configType->getTag()] = $config?->getValue();

            }
        }

        return [
            'configuration' => $configurations,
        ];
    }
}