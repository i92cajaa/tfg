<?php

namespace App\Command;

use App\Entity\Permission\Permission;
use App\Entity\Role\Role;
use App\Entity\Area\Area;
use App\Entity\Schedules\Schedules;
use App\Entity\User\User;
use App\Entity\User\UserHasPermission;
use App\Repository\PermissionRepository;
use App\Repository\RoleRepository;
use App\Repository\AreaRepository;
use App\Repository\SchedulesRepository;
use App\Repository\UserHasPermissionRepository;
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

#[AsCommand(name: 'app:create-users-schedules')]
class CreateUsersSchedulesCommand extends Command
{


    private UserRepository $userRepository;
    private SchedulesRepository $schedulesRepository;


    public function __construct(
        private readonly EntityManagerInterface $em,
    )
    {

        $this->userRepository = $em->getRepository(User::class);
        $this->schedulesRepository = $em->getRepository(Schedules::class);
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Create user schedules.')
            ->setHelp('This command allows to create users schedules');
    }

    protected function execute(InputInterface $input, OutputInterface $output):int
    {
        $allUsers = $this->userRepository->findAll();

        foreach ($allUsers as $user){
            try {
                $schedules = $user->getSchedules();
                if (count($schedules)<5){
                    $this->schedulesRepository->createAllWeekSchedules($user);
                }
            }catch (\Exception $error){
                $output->writeln("ERROR: ".$error);
            }
        }
        $output->writeln("SUCCESS: Se han reseteado todos los permisos.");
        return Command::SUCCESS;
    }
}