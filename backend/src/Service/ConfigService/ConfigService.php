<?php
namespace App\Service\ConfigService;

use App\Entity\Config\Config;
use App\Entity\Config\ConfigType;
use App\Repository\ConfigRepository;
use App\Repository\ConfigTypeRepository;
use App\Service\DocumentService\DocumentService;
use App\Shared\Classes\AbstractService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class ConfigService extends AbstractService
{

    const UPLOAD_FILES_PATH = 'configs';

    public function __construct(
        private readonly DocumentService $documentService,
        private readonly ConfigRepository $configRepository,
        private readonly ConfigTypeRepository $configTypeRepository,

        EntityManagerInterface $em,

        RouterInterface       $router,
        Environment           $twig,
        RequestStack          $requestStack,
        TokenStorageInterface $tokenStorage,
        CsrfTokenManagerInterface      $tokenManager,
        FormFactoryInterface           $formFactory,
        SerializerInterface            $serializer,
        TranslatorInterface $translator
    )
    {
        parent::__construct(
            $requestStack,
            $router,
            $twig,
            $tokenStorage,
            $tokenManager,
            $formFactory,
            $serializer,
            $translator,
            $this->configRepository
        );
    }

    public function toggleDarkMode(): void
    {
        if($this->getSession()->get('darkMode') == null || !$this->getSession()->get('darkMode')){
            $this->getSession()->set('darkMode', true);
        }else{
            $this->getSession()->set('darkMode', false);
        };
    }

    public function toggleMenuExpanded(): void
    {
        if($this->getSession()->get('menuExpanded') == null || !$this->getSession()->get('menuExpanded')){
            $this->getSession()->set('menuExpanded', true);
        }else{
            $this->getSession()->set('menuExpanded', false);
        };
    }

    public function findAllConfigs(?bool $hydrateObject = false)
    {
        return $this->configRepository->findAllConfigs($hydrateObject);
    }

    public function findConfigsFormatted()
    {
        $configs = $this->configRepository->findAll();

        $result = [];
        foreach ($configs as $config){
            $result[$config->getTag()] = $config->getValue();
        }
        return $result;
    }

    public function findAllModules(?bool $hydrateObject = false)
    {
        return $this->configRepository->findAllModules($hydrateObject);
    }

    public function findConfigValueByTags(array $tags): array
    {
        /** @var Config[] $configs */
        $configs = $this->configRepository->findByTags($tags);

        $result = [];
        if($configs){

            foreach ($configs as $config){
                $result[$config->getTag()] = $config->getValue();
            }

        }

        return $result;
    }

    public function findConfigValueByTag(string $tag): ?string
    {
        $config = $this->configRepository->findOneBy(['tag' => $tag]);
        if($config){
            return $config->getValue();
        }
        return null;
    }

    public function editConfig(){

        if ($this->getCurrentRequest()->isMethod('POST') && $this->isCsrfTokenValid('edit-config', $this->getRequestPostParam('_token'))) {

            /** @var ConfigType $configType */
            foreach ($this->findAllConfigTypes() as $configType) {
                $value = null;

                if ($configType->getType() == ConfigType::SOURCE_TYPE) {
                    if ($this->getCurrentRequest()->files->get($configType->getTag())) {
                        $value = $this->documentService->uploadDocument($this->getCurrentRequest()->files->get($configType->getTag()), self::UPLOAD_FILES_PATH)->getId();
                    }
                } else {
                    $value = @$this->getRequestPostParam($configType->getTag());
                }

                if(
                    ($configType->isModule() && $this->getUser()->isSuperAdmin()) ||
                    (
                        !$configType->isModule() &&
                        (
                            ($value && $configType->getType() == ConfigType::SOURCE_TYPE) ||
                            ($configType->getType() != ConfigType::SOURCE_TYPE)
                        )
                    )
                ){
                    $this->createOrUpdateConfig(
                        $configType->getName(),
                        $configType->getTag(),
                        $configType->getDescription(),
                        $value
                    );
                }

            }

            $this->addFlash(
                'success',
                $this->translate('Successfully edit configuration')
             );

            return $this->redirectToRoute('config_edit');
        }

        return $this->render('config/edit.html.twig', [
            'company_fields' => $this->findAllConfigTypes(true)
        ]);
    }

    public function createOrUpdateConfig(
        string $name,
        string $tag,
        ?string $description,
        ?string $value
    )
    {
        $exist = $this->configRepository->findOneBy(['tag' => $tag]);
        if($exist){
            $this->configRepository->updateConfig(
                $exist,
                $name,
                $tag,
                $description,
                $value
            );
        }else{
            $this->configRepository->createConfig(
                $name,
                $tag,
                $description,
                $value
            );
        }
    }


    public function findAllConfigTypes(?bool $formatted = false): array
    {
        $configTypes = $this->configTypeRepository->findAllOrdered();

        if($formatted){

            $configTypesFormatted = ['info' => [], 'modules' => [], 'modules_dependant' => []];
            foreach ($configTypes as $configType)
            {
                if($configType->isModule())
                {
                    $configTypesFormatted['modules'][] = $configType;
                    $configTypesFormatted['modules_dependant'][$configType->getTag()] = ['name' => $configType->getName(), 'configTypes' => []];
                }elseif($configType->getModuleDependant())
                {
                    $configTypesFormatted['modules_dependant'][$configType->getModuleDependant()]['configTypes'][] = $configType;
                }else{
                    $configTypesFormatted['info'][] = $configType;
                }
            }

            return $configTypesFormatted;

        }

        return $configTypes;
    }


}