<?php

namespace App\Command;

use App\Entity\Document\Document;
use App\Entity\Document\SurveyRange;
use App\Entity\Invoice\Invoice;
use App\Entity\Payment\PaymentMethod;
use App\Entity\Permission\Permission;
use App\Entity\Role\Role;
use App\Repository\DocumentRepository;
use App\Repository\PermissionRepository;
use App\Repository\RoleRepository;
use App\Repository\SurveyRangeRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function GuzzleHttp\Promise\all;

#[AsCommand(name: 'app:create-assign-first-survey-range')]
class AddFirstSurveyRangeToSurveysCommand extends Command
{

    private SurveyRangeRepository $surveyRangeRepository;
    private DocumentRepository $documentRepository;


    public function __construct(
        private EntityManagerInterface $em,
        private Connection $connection,

    )
    {
        $this->surveyRangeRepository = $em->getRepository(SurveyRange::class);
        $this->documentRepository = $em->getRepository(Document::class);

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Create first surveyRange and assign it to all surveys created until now.')
            ->setHelp('This command creates the first surveyRange and assigns it to all surveys already created.');
    }

    protected function execute(InputInterface $input, OutputInterface $output):int
    {
        try {

            $surveyRange = $this->surveyRangeRepository->createSurveyRange(date_create_from_format('d-m-Y-H-i-s', '01-01-2000-00-00-00'), date_create_from_format('d-m-Y-H-i-s', '29-02-2024-23-59-59'));

            $documents = $this->documentRepository->findBy(['isMentorSurvey' => true]);

            foreach ($documents as $document) {
                $this->documentRepository->addSurveyRangeToDocument($document, $surveyRange);
            }

            $output->writeln("SUCCESS: Documents updated.");
        }catch (\Exception $error){
            $output->writeln("ERROR: ".$error);
        }
        return Command::SUCCESS;
    }
}