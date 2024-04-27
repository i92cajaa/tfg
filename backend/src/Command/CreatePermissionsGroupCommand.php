<?php

namespace App\Command;

use App\Entity\Permission\PermissionGroup;
use App\Repository\PermissionGroupRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'app:create-permissions-group')]
class CreatePermissionsGroupCommand extends Command
{

    private PermissionGroupRepository $permissionGroupRepository;


    public function __construct(
        private readonly EntityManagerInterface $em,
    )
    {
        $this->permissionGroupRepository = $em->getRepository(PermissionGroup::class);

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Create base Permission Group.')
            ->setHelp('This command allows to create  all permissions group');
    }

    protected function execute(InputInterface $input, OutputInterface $output):int
    {
        try {
            $entities = [
                [1, 'users', 'Users'],
                [2, 'roles', 'Roles'],
                [3, 'clients', 'Clients'],
                [4, 'appointments', 'Appointments'],
                [5, 'festives', 'Absences'],
                [6, 'payment_methods', 'Payment Methods'],
                [7, 'services', 'Services'],
                [8, 'invoices', 'Invoices'],
                [9, 'configs', 'Configuration'],
                [10, 'templates', 'Templates'],
                [11, 'template_types', 'Template Types'],
                [12, 'extra_appointment_field_types', 'Additional Appointment Field Types'],
                [13, 'extra_appointment_fields', 'Additional Appointment Fields'],
                [14, 'centers', 'Centers']
            ];

            foreach ($entities as $entity){
                $existingEntity = $this->permissionGroupRepository->findOneBy(['id' =>$entity[0]]);

                if ($existingEntity != null) {
                    $output->writeln("SUCCESS: Group permission ".$existingEntity->getName()." actualizado.");
                    $existingEntity->setName($entity[1]);
                    $existingEntity->setLabel($entity[2]);

                    $this->permissionGroupRepository->persist($existingEntity);
                } else {
                    $output->writeln("NEW: Group permission ".$entity[1]." created.");

                    $newGroup = new PermissionGroup();
                    $newGroup->setName($entity[1]);
                    $newGroup->setLabel($entity[2]);

                    $this->permissionGroupRepository->persist($newGroup);
                }

            }
        }catch (\Exception $error){
            $output->writeln("ERROR: ".$error);
        }
        return Command::SUCCESS;
    }
}