<?php

namespace App\Command;

use App\Entity\Invoice\Invoice;
use App\Entity\Payment\PaymentMethod;
use App\Entity\Permission\Permission;
use App\Entity\Role\Role;
use App\Repository\PermissionRepository;
use App\Repository\RoleRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function GuzzleHttp\Promise\all;

#[AsCommand(name: 'app:create-roles')]
class CreateRoleCommand extends Command
{

    private RoleRepository $roleRepository;


    public function __construct(
        private EntityManagerInterface $em,
        private Connection $connection,

    )
    {
        $this->roleRepository = $em->getRepository(Role::class);

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Create base roles.')
            ->setHelp('This command allows to create all roles.');
    }

    protected function execute(InputInterface $input, OutputInterface $output):int
    {
        //$this->connection->executeQuery("DELETE FROM `role`;");
        try {

            $roles = [
                [1,"Jefe de Estudios",1],
                [2,"Director",1],
                [3,"Mentor",0],
                [4,"Proyecto",0],
                [5,"Sandetel",1],
            ];

            foreach ($roles as $role) {
                    $sql = "
                    INSERT IGNORE INTO role(id, name, admin)
                    VALUES ($role[0], '$role[1]', $role[2])
                    ON DUPLICATE KEY UPDATE 
                    name='$role[1]', admin=$role[2];
                ";
                    $this->connection->executeQuery($sql);

            }

            $output->writeln("SUCCESS: Role created.");
        }catch (\Exception $error){
            $output->writeln("ERROR: ".$error);
        }
        return Command::SUCCESS;
    }
}