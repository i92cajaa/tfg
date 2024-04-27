<?php

namespace App\Command;


use App\Entity\Config\Config;
use App\Entity\Config\ConfigType;
use App\Repository\ConfigRepository;
use App\Repository\ConfigTypeRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Uuid;

#[AsCommand(name: 'app:create-config')]
class CreateConfigCommand extends Command
{
    /**
     * @var ConfigTypeRepository
     */
    private ConfigTypeRepository $configTypeRepository;
    /**
     * @var ConfigRepository
     */
    private ConfigRepository $configRepository;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ?string $name = null
    )
    {
        $this->configRepository = $this->em->getRepository(Config::class);
        $this->configTypeRepository = $this->em->getRepository(ConfigType::class);

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Crea una configuración inicial para el proyecto.');

        // the full command description shown when running the command with
        // the "--help" option


        foreach ($this->configTypeRepository->findAll() as $configType){
            $this->addOption($configType->getTag(),null, InputOption::VALUE_OPTIONAL, $configType->getName() . ': ' . $configType->getDescription());
        }

        $this->setHelp('This command allows you to create a Config field...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        foreach ($this->configTypeRepository->findAll() as $configType){
            $exist = $this->configRepository->findOneBy(['tag' => $configType->getTag()]);
            $option = $input->getOption($configType->getTag());
            if($option){
                if($exist){
                    $this->configRepository->updateConfig(
                        $exist,
                        $configType->getName(),
                        $configType->getTag(),
                        $configType->getDescription(),
                        $input->getOption($configType->getTag())
                    );
                }else{
                    $this->configRepository->createConfig(
                        $configType->getName(),
                        $configType->getTag(),
                        $configType->getDescription(),
                        $input->getOption($configType->getTag())
                    );
                }
            }

        }

        $output->writeln("SUCCESS: La configuración se ha creado en la base de datos.");
        return Command::SUCCESS;
    }
}