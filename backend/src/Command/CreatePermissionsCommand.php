<?php

namespace App\Command;

use App\Entity\Invoice\Invoice;
use App\Entity\Payment\PaymentMethod;
use App\Entity\Permission\Permission;
use App\Repository\PermissionRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:create-permissions')]
class CreatePermissionsCommand extends Command
{

    private PermissionRepository $permissionRepository;


    public function __construct(
        private EntityManagerInterface $em,
        private Connection $connection,

    )
    {
        $this->permissionRepository = $em->getRepository(Permission::class);

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Create base Permissions.')
            ->setHelp('This command allows to create all permissions.');
    }

    protected function execute(InputInterface $input, OutputInterface $output):int
    {
        try {
            $permissions = [
                [1, 'List', 'list', 0, NULL],
                [1, 'Show', 'show', 0, NULL],
                [1, 'Create', 'create', 0, NULL],
                [1, 'Edit', 'edit', 0, NULL],
                [1, 'Delete', 'delete', 0, NULL],
                [1, 'Assign Tasks', 'assign_tasks', 0, NULL],
                [1, 'Manage Services', 'manage_services', 0, NULL],
                [1, 'Manage Schedules', 'manage_schedules', 0, NULL],

                [2, 'List', 'list', 1, NULL],
                [2, 'Show', 'show', 1, NULL],
                [2, 'Create', 'create', 1, NULL],
                [2, 'Edit', 'edit', 1, NULL],
                [2, 'Delete', 'delete', 1, NULL],

                [3, 'List', 'list', 0, NULL],
                [3, 'Show', 'show', 0, NULL],
                [3, 'Create', 'create', 0, NULL],
                [3, 'Edit', 'edit', 0, NULL],
                [3, 'Delete', 'delete', 0, NULL],

                [4, 'List', 'list', 0, NULL],
                [4, 'Show', 'show', 0, NULL],
                [4, 'Create', 'create', 0, NULL],
                [4, 'Edit', 'edit', 0, NULL],
                [4, 'Delete', 'delete', 0, NULL],
                [4, 'Manage Payments', 'manage_payments', 0, PaymentMethod::ENTITY],
                [4, 'Modify Hour', 'modify_hour', 1, NULL],

                [5, 'List', 'list', 0, NULL],
                [5, 'Show', 'show', 0, NULL],
                [5, 'Create', 'create', 0, NULL],
                [5, 'Edit', 'edit', 0, NULL],
                [5, 'Delete', 'delete', 0, NULL],

                [6, 'List', 'list', 1, PaymentMethod::ENTITY],
                [6, 'Show', 'show', 1, PaymentMethod::ENTITY],
                [6, 'Create', 'create', 1, PaymentMethod::ENTITY],
                [6, 'Edit', 'edit', 1, PaymentMethod::ENTITY],
                [6, 'Delete', 'delete', 1, PaymentMethod::ENTITY],

                [7, 'List', 'list', 0, NULL],
                [7, 'Show', 'show', 0, NULL],
                [7, 'Create', 'create', 0, NULL],
                [7, 'Edit', 'edit', 0, NULL],
                [7, 'Delete', 'delete', 0, NULL],
                [7, 'Manage Divisions', 'manage_divisions', 0, NULL],

                [8, 'List', 'list', 0, Invoice::ENTITY],
                [8, 'Show', 'show', 0, Invoice::ENTITY],
                [8, 'Create', 'create', 0, Invoice::ENTITY],
                [8, 'Edit', 'edit', 0, Invoice::ENTITY],
                [8, 'Delete', 'delete', 0, Invoice::ENTITY],
                [8, 'Export', 'export', 0, Invoice::ENTITY],

                [9, 'Edit', 'edit', 1, NULL],

                [10, 'List', 'list', 0, NULL],
                [10, 'Create', 'create', 0, NULL],
                [10, 'Edit', 'edit', 0, NULL],
                [10, 'Delete', 'delete', 0, NULL],
                [10, 'Export', 'export', 0, NULL],

                [11, 'List', 'list', 1, NULL],
                [11, 'Create', 'create', 1, NULL],
                [11, 'Edit', 'edit', 1, NULL],
                [11, 'Delete', 'delete', 1, NULL],

                [12, 'List', 'list', 1, NULL],
                [12, 'Create', 'create', 1, NULL],
                [12, 'Edit', 'edit', 1, NULL],
                [12, 'Delete', 'delete', 1, NULL],

                [13, 'List', 'list', 0, NULL],
                [13, 'Create', 'create', 0, NULL],
                [13, 'Edit', 'edit', 0, NULL],
                [13, 'Delete', 'delete', 0, NULL],
                [13, 'Export', 'export', 0, NULL],

                [14, 'List', 'list', 0, NULL],
                [14, 'Show', 'show', 0, NULL],
                [14, 'Create', 'create', 0, NULL],
                [14, 'Edit', 'edit', 0, NULL],
                [14, 'Delete', 'delete', 0, NULL],
            ];

            $this->connection->executeQuery("DELETE FROM permission;");

            foreach ($permissions as $index => $permissionData) {
                $index = $index+1;
                $sql = "
                    INSERT INTO `permission`
                        (`id`, `group_id`, `label`, `action`, `admin_managed`, `module_dependant`) 
                    VALUES 
                        (".$index.",".$permissionData[0]." ,'".$permissionData[1]."','".$permissionData[2]."','".$permissionData[3]."','".$permissionData[4]."')
                ";
                $this->connection->executeQuery($sql);
            }


            $output->writeln("NEWS: Permissions created.");
        }catch (\Exception $error){
            $output->writeln("ERROR: ".$error);
        }
        return Command::SUCCESS;
    }
}