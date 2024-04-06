<?php

namespace App\Command;

use App\Entity\Appointment\Appointment;
use App\Entity\Document\Document;
use App\Entity\Document\SurveyRange;
use App\Entity\User\UserHasDocument;
use App\Entity\Service\Service;
use App\Repository\AppointmentRepository;
use App\Repository\ClientHasDocumentRepository;
use App\Repository\DocumentRepository;
use App\Repository\ServiceRepository;
use App\Repository\SurveyRangeRepository;
use App\Repository\UserHasDocumentRepository;
use App\Service\FilterService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;

#[AsCommand(name: 'app:add-time-mentored-to-surveys')]
class AddTimeMentoredToDocumentDatabaseCommand extends Command
{

    private SurveyRangeRepository $surveyRangeRepository;
    private DocumentRepository $documentRepository;
    private AppointmentRepository $appointmentRepository;
    private UserHasDocumentRepository $userHasDocumentRepository;


    public function __construct(
        private EntityManagerInterface $em,
        private Connection $connection,
        private readonly ServiceRepository $serviceRepository,
        private readonly ClientHasDocumentRepository $clientHasDocumentRepository

    )
    {
        $this->surveyRangeRepository = $this->em->getRepository(SurveyRange::class);
        $this->documentRepository = $this->em->getRepository(Document::class);
        $this->appointmentRepository = $this->em->getRepository(Appointment::class);
        $this->userHasDocumentRepository = $this->em->getRepository(UserHasDocument::class);

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Add time mentored to mentor surveys so it is easier to do a weighted average.')
            ->setHelp('This command adds time mentored to mentor surveys so it is easier to do a weighted average.');
    }

    protected function execute(InputInterface $input, OutputInterface $output):int
    {
        try {

            $filterService = new FilterService(new Request());

            $mentorHasDocuments = $this->userHasDocumentRepository->findAll();

            $mentorDocuments = [];
            foreach ($mentorHasDocuments as $mentorHasDocument) {
                if ($mentorHasDocument->getDocument()->isMentorSurvey()) {

                    $mentorDocuments[$mentorHasDocument->getDocument()->getId()] = $mentorHasDocument->getUser()->getId();
                }
            }

            $clientDocuments = [];
            $clientHasDocuments = $this->clientHasDocumentRepository->findAll();
            foreach ($clientHasDocuments as $clientHasDocument) {
                if ($clientHasDocument->getDocument()->isMentorSurvey()) {

                    $clientDocuments[$clientHasDocument->getDocument()->getId()] = $clientHasDocument->getClient()->getId();
                }
            }

            $filterService->addFilter('statusType', 9);
            $filterService->addFilter('services', $this->serviceRepository->findBy(['forAdmin'=>false, 'forClient' => false]));

            $appointments = $this->appointmentRepository->findAppointments($filterService, true);

            $times = [];
            foreach ($appointments['data'] as $appointment) {
                $timeDiff = $appointment->getTimeTo()->diff($appointment->getTimeFrom());

                if (isset($times[$appointment->getUser()->getId() . ':' . $appointment->getClients()[0]->getId()])) {
                    $times[$appointment->getUser()->getId() . ':' . $appointment->getClients()[0]->getId()] += $timeDiff->h + ($timeDiff->i / 60);
                } else {
                    $times[$appointment->getUser()->getId() . ':' . $appointment->getClients()[0]->getId()] = $timeDiff->h + ($timeDiff->i / 60);
                }
            }

            $documentTimes = [];
            foreach ($times as $index => $time) {
                $userClient = explode(':', $index);

                foreach ($mentorDocuments as $mentorDocumentId => $mentorDocument) {
                    if ($userClient[0] == $mentorDocument) {
                        foreach ($clientDocuments as $documentId => $clientDocument) {
                            if ($userClient[1] == $clientDocument && $documentId == $mentorDocumentId) {
                                $documentTimes[$documentId] = $time;
                            }
                        }
                    }
                }
            }

            foreach ($documentTimes as $documentId => $time) {
                $document = $this->documentRepository->find($documentId);

                $document->setMentoredTime($time);

                $this->documentRepository->saveDocument($document);
            }

            $output->writeln("SUCCESS: Documents updated.");
        }catch (\Exception $error){
            $output->writeln("ERROR: ".$error);
        }
        return Command::SUCCESS;
    }
}