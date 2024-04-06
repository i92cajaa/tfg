<?php

namespace App\Command;

use App\Entity\Permission\Permission;
use App\Entity\Role\Role;
use App\Entity\User\User;
use App\Entity\User\UserHasPermission;
use App\Repository\PermissionRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

#[AsCommand(name: 'app:create-admin-user')]
class CreateAdminCommand extends Command
{

    private UserRepository $userRepository;

    private RoleRepository $roleRepository;

    private PermissionRepository $permissionRepository;


    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly ?string $name = null
    )
    {
        $this->userRepository = $em->getRepository(User::class);
        $this->roleRepository = $em->getRepository(Role::class);
        $this->permissionRepository = $em->getRepository(Permission::class);

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Creates a new admin user.')
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'email of user')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'user password')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to create an admin user...');
    }

    protected function execute(InputInterface $input, OutputInterface $output):int
    {
        $email         = $input->getOption('email');
        $plainPassword = $input->getOption('password');

        if(!$email or !$plainPassword) {
            $output->writeln('ERROR: No se ha proporcionado un email o contraseña válido');
            return Command::FAILURE;
        }
        $findUser      = $this->userRepository->findOneBy(['email' => $email]);

        if ($findUser) {
            $output->writeln("El usuario ya existe en la base de datos");
            return Command::FAILURE;
        }

        $user = new User();
        $user->setName("Admin");
        $user->setSurnames("Admin");
        $user->setEmail($email);
        $role = $this->roleRepository->find(Role::ROLE_SUPERADMIN);
        $user->addRole($role);
        $user->setDarkMode(false);
        $user->setStatus(true);
        $user->setVip(true);
        $user->setCalendarInterval("00:30");

        $permissions = $this->permissionRepository->findAll();
        if($permissions) {
            foreach ($permissions as $permission) {
                $newPermission = new UserHasPermission();
                $newPermission->setUser($user);
                $newPermission->setPermission($permission);
                $user->addPermission($newPermission);
            }
        }
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $plainPassword));
        $this->em->persist($user);
        $this->em->flush();

        $output->writeln("SUCCESS: El usuario administrador se ha creado en la base de datos.");
        return Command::SUCCESS;
    }
}