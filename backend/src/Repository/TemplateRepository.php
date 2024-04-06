<?php

namespace App\Repository;

use App\Entity\Appointment\Appointment;
use App\Entity\Client\Client;
use App\Entity\Template\Template;
use App\Entity\Template\TemplateLine;
use App\Entity\Template\TemplateType;
use App\Entity\User\User;
use App\Service\FilterService;
use App\Shared\Traits\DoctrineStorableObject;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Template|null find($id, $lockMode = null, $lockVersion = null)
 * @method Template|null findOneBy(array $criteria, array $orderBy = null)
 * @method Template[]    findAll()
 * @method Template[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TemplateRepository extends ServiceEntityRepository
{
    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Template::class);
    }

    public function persist(Template $template)
    {
        $this->save($this->_em, $template);
    }

    public function remove(Template $template)
    {
        $this->delete($this->_em, $template);
    }

    public function findTemplates(FilterService $filterService, $showAll = false)
    {

        $query = $this->createQueryBuilder('t')
            ->join('t.client', 'client')
            ->addSelect('client')
            ->leftJoin('t.appointment', 'appointment')
            ->addSelect('appointment')
        ;

        $query->setFirstResult($filterService->page > 1 ? (($filterService->page - 1)*$filterService->limit) : $filterService->page - 1);
        $query->setMaxResults($filterService->limit);

        if (count($filterService->getFilters()) > 0) {
            if($filterService->getFilterValue('client') != null && $filterService->getFilterValue('client') != ''){
                $query->andWhere('client.id = :client')
                    ->setParameter('client', $filterService->getFilterValue('client'));
            }

            if($filterService->getFilterValue('appointment') != null && $filterService->getFilterValue('appointment') != ''){
                $query->andWhere('appointment.id = :appointment')
                    ->setParameter('appointment', $filterService->getFilterValue('appointment'));
            }


        }

        if (count($filterService->getOrders()) > 0) {
            foreach ($filterService->getOrders() as $order) {
                switch ($order['field']) {
                    case "name":
                        $query->orderBy('t.name', $order['order']);
                        break;
                    case "entity":
                        $query->orderBy('t.entity', $order['order']);
                        break;
                    case "description":
                        $query->orderBy('t.description', $order['order']);
                        break;
                }
            }
        } else {
            $query->orderBy('t.id', 'DESC');
        }

        // Pagination process
        $paginator = new Paginator($query, $fetchJoinCollection = true);

        $totalRegisters = $paginator->count();
        $result         = [];
        foreach ($paginator as $verification) {
            $result[] = $verification;
        }

        $lastPage = (integer)ceil($totalRegisters / $filterService->limit);

        return [
            'totalRegisters' => $totalRegisters,
            'data'           => $result,
            'lastPage'       => $lastPage
        ];
    }

    public function createTemplate(
        string $name,
        TemplateType $templateType,
        ?Client $client,
        ?Appointment $appointment,
        User $user,
        array $templateLines = []
    ): ?Template{
        $template = (new Template())
            ->setName($name)
            ->setTemplateType($templateType)
            ->setClient($client)
            ->setAppointment($appointment)
            ->setUser($user)
        ;

        foreach ($templateLines as $templateLineArray) {

            $templateLine = (new TemplateLine())
                ->setName($templateLineArray['name'])
                ->setType($templateLineArray['type'])
                ->setValue($templateLineArray['value'])
            ;

            $template->addTemplateLine($templateLine);
        }

        $this->save($this->_em, $template);
        return $template;
    }

    public function editTemplate(
        Template $template,
        string $name,
        TemplateType $templateType,
        ?Client $client,
        ?Appointment $appointment,
        User $user,
        array $templateLines = []
    ){
        $template
            ->setName($name)
            ->setTemplateType($templateType)
            ->setClient($client)
            ->setAppointment($appointment)
            ->setUser($user)
        ;

        $this->editTemplateLines($template, $templateLines);

        $this->save($this->_em, $template);
    }

    public function editTemplateLines(
        Template $template,
        array $templateLines = []
    ){
        $template->removeAllTemplateLines();

        $this->save($this->_em, $template);

        foreach ($templateLines as $templateLineArray) {

            $templateLine = (new TemplateLine())
                ->setName($templateLineArray['name'])
                ->setType($templateLineArray['type'])
                ->setValue($templateLineArray['value'])
            ;

            $template->addTemplateLine($templateLine);
        }

        $this->save($this->_em, $template);
    }

    public function findAllInfoByTypes(?string $appointmentId, ?string $clientId, ?string $user, ?array $types, DateTime $dateFrom, DateTime $dateTo){
        $query = $this->createQueryBuilder('t')
            ->leftJoin('t.appointment', 'appointment')
            ->addSelect('appointment')
            ->leftJoin('t.user', 'user')
            ->addSelect('user')
            ->leftJoin('t.client', 'client')
            ->addSelect('client')
            ->leftJoin('t.templateType', 'templateType')
            ->addSelect('templateType')
            ->andWhere('t.createdAt BETWEEN :date_from AND :date_to')
            ->setParameter('date_from', $dateFrom)
            ->setParameter('date_to', $dateTo)
        ;

        if($types){

            $query->andWhere('templateType.id IN (:types)')
                ->setParameter('types', $types);
        }

        if($appointmentId){
            $query->andWhere('appointment.id LIKE :appointment')
                ->setParameter('appointment', $appointmentId);
        }

        if($clientId){
            $query->andWhere('client.id LIKE :client')
                ->setParameter('client', $clientId);
        }

        if($user){
            $query->andWhere('user.id LIKE :user')
                ->setParameter('user', $user);
        }

        $query->orderBy('t.createdAt', 'ASC');

        return $query->getQuery()
            ->getResult();
    }

}
