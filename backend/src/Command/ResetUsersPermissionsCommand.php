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

#[AsCommand(name: 'app:reset-users-permissions')]
class ResetUsersPermissionsCommand extends Command
{


    private UserRepository $userRepository;
    private UserHasPermissionRepository $userHasPermissionRepository;
    private RoleRepository $roleRepository;


    public function __construct(
        private readonly EntityManagerInterface $em,
    )
    {

        $this->userRepository = $em->getRepository(User::class);
        $this->userHasPermissionRepository = $em->getRepository(UserHasPermission::class);
        $this->roleRepository = $em->getRepository(Role::class);
        parent::__construct();
    }

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Reset permission.')


            ->setHelp('This command allows to reset all permissions');
    }

    protected function execute(InputInterface $input, OutputInterface $output):int
    {
        $allUsers = $this->userRepository->findAll();

        foreach ($allUsers as $user){
            try {
                    $this->userRepository->removeAllPermissions($user);
                    $rol = $this->roleRepository->findBy(['name' => $user->getRoles()])[0];
                    foreach ($rol->getPermissions() as $roleHasPermission){
                        $userHasPermission = (new UserHasPermission())->setUser($user)->setPermission($roleHasPermission->getPermission());
                        $user->addPermission($userHasPermission);
                        $this->userRepository->persist($user);
                    }
            }catch (\Exception $error){
                $output->writeln("ERROR: ".$error);
            }
        }
        $output->writeln("SUCCESS: Se han reseteado todos los permisos.");
        return Command::SUCCESS;
    }
}