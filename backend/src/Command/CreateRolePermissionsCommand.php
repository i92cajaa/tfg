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
use function GuzzleHttp\Promise\all;

#[AsCommand(name: 'app:create-role-permissions')]
class CreateRolePermissionsCommand extends Command
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
            ->setDescription('Create base role permissions.')
            ->setHelp('This command allows to create all role permissions.');
    }

    protected function execute(InputInterface $input, OutputInterface $output):int
    {
        $this->connection->executeQuery("DELETE FROM role_has_permission;");
        try {
            $rolePermissions = [
                1 => $this->permissionRepository->findAll(),
                2 => [1, 2, 3, 4, 5, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 49, 50, 51, 52, 53, 62, 63, 64, 65, 66, 67, 68, 69, 70, 71],
                3 => [14, 15, 19, 20],
                4 => [19,20],
                5 => [1,2,9,10,14,15,19,20,26,27,31,32,36,37,42,43,49,54,58,62,67,68],
            ];

            foreach ($rolePermissions as $roleId => $permissions) {
                foreach ($permissions as $permission) {
                    $sql = "
                    INSERT IGNORE INTO role_has_permission (role_id, permission_id)
                    VALUES ($roleId, " . $permission . ");
                ";
                    $this->connection->executeQuery($sql);
                }
            }

            $output->writeln("SUCCESS: Permissions created.");
        }catch (\Exception $error){
            $output->writeln("ERROR: ".$error);
        }
        return Command::SUCCESS;
    }
}