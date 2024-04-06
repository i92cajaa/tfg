<?php

namespace App\Command;

use App\Entity\Permission\Permission;
use App\Entity\Role\Role;
use App\Entity\Area\Area;
use App\Entity\User\User;
use App\Entity\User\UserHasPermission;
use App\Repository\PermissionRepository;
use App\Repository\RoleRepository;
use App\Repository\AreaRepository;
use App\Repository\UserRepository;


use App\Shared\Utils\Util;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

#[AsCommand(name: 'app:create-areas-entity')]
class CreateAreaCommand extends Command
{


    private AreaRepository $areaRepository;


    public function __construct(
        private readonly EntityManagerInterface $em,
    )
    {

        $this->areaRepository = $em->getRepository(Area::class);
        parent::__construct();
    }

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Creates areas.')


            ->setHelp('This command allows to create all areas');
    }

    protected function execute(InputInterface $input, OutputInterface $output):int
    {
        $areas = Util::AREA;


    foreach ($areas as $area){
        $newArea = new Area();
        $newArea->setName($area);
        $this->em->persist($newArea);
        $this->em->flush();
    }

        $output->writeln("SUCCESS: Se han creado las Áreas de mentorización en la base de datos.");
        return Command::SUCCESS;
    }
}