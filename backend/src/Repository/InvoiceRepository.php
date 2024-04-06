<?php

namespace App\Repository;

use App\Entity\Appointment\Appointment;
use App\Entity\Client\Client;
use App\Entity\Config\Config;
use App\Entity\Invoice\Invoice;
use App\Entity\User\User;
use App\Service\FilterService;
use App\Shared\Classes\UTCDateTime;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Invoice|null find($id, $lockMode = null, $lockVersion = null)
 * @method Invoice|null findOneBy(array $criteria, array $orderBy = null)
 * @method Invoice[]    findAll()
 * @method Invoice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    public function findByIds(array $ids)
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.appointment', 'appointment')
            ->leftJoin('i.client', 'client')
            ->leftJoin('i.user', 'user')
            ->select('i')
            ->addSelect('appointment')
            ->addSelect('client')
            ->addSelect('user')
            ->andWhere('i.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param FilterService $filterService
     * @param bool $showAll
     * @return Invoice[] Returns an array of Invoice objects
     */
    public function findInvoices(FilterService $filterService, $showAll = false)
    {

        $query = $this->createQueryBuilder('i')
            ->leftJoin('i.appointment', 'appointment')
            ->leftJoin('i.client', 'client')
            ->leftJoin('i.user', 'user')
            ->addSelect('appointment')
            ->addSelect('client')
            ->addSelect('user')
        ;

        $query->setFirstResult($filterService->page > 1 ? (($filterService->page - 1)*$filterService->limit) : $filterService->page - 1);
        if(!$showAll){
            $query->setMaxResults($filterService->limit);
        }


        $this->addFilters($filterService, $query);
        $this->addOrders($filterService, $query);

        // Pagination process
        $paginator = new Paginator($query, $fetchJoinCollection = true);

        $totalRegisters = $paginator->count();
        $result         = [];
        foreach ($paginator as $verification) {
            $result[] = $verification;
        }

        if($filterService->limit){
            $lastPage = (integer)ceil($totalRegisters / $filterService->limit);
        }else{
            $lastPage = 1;
        }


        return [
            'totalRegisters' => $totalRegisters,
            'data'           => $result,
            'lastPage'       => $lastPage
        ];
    }

    public function addFilters(FilterService $filterService, QueryBuilder $query)
    {
        if (count($filterService->getFilters()) > 0) {

            if($filterService->getFilterValue('info') != null){
                $query->andWhere('
                    i.name LIKE :info
                    OR i.dni LIKE :info
                    OR i.address LIKE :info
                    OR i.invoiceNumber LIKE :info
                ')
                ->setParameter('info', "%".$filterService->getFilterValue('info')."%");
            }
            if ($filterService->getFilterValue('dateRange') != null) {

                $dates = explode(' ', $filterService->getFilterValue('dateRange'));
                if(sizeof($dates) > 1){
                    $timeFrom = UTCDateTime::create('d-m-Y', $dates[0])->setTime(0, 0);
                    $timeTo   = UTCDateTime::create('d-m-Y', $dates[2])->setTime(23, 59);
                    $query->andWhere('i.invoiceDate BETWEEN :timeFrom AND :timeTo')
                        ->setParameter('timeFrom', $timeFrom)
                        ->setParameter('timeTo', $timeTo);
                }else{
                    $timeFrom = UTCDateTime::create('d-m-Y', $dates[0])->setTime(0, 0);
                    $timeTo   = UTCDateTime::create('d-m-Y', $dates[0] , new \DateTimeZone('UTC'))->setTime(23, 59);
                    $query->andWhere('i.invoiceDate BETWEEN :timeFrom AND :timeTo')
                        ->setParameter('timeFrom', $timeFrom)
                        ->setParameter('timeTo', $timeTo);
                };

            }

            if($filterService->getFilterValue('iva') != null){
                $query->andWhere('i.iva LIKE :iva')
                    ->setParameter('iva', "%".$filterService->getFilterValue('iva')."%");
            }

            if($filterService->getFilterValue('entity') != null){
                $query->andWhere('i.entity LIKE :entity')
                    ->setParameter('entity', "%".$filterService->getFilterValue('entity')."%");
            }

            if($filterService->getFilterValue('client') != null){
                $query->andWhere('client.id LIKE :client')
                    ->setParameter('client', "%".$filterService->getFilterValue('client')."%");
            }

            if($filterService->getFilterValue('user') != null){
                $query->andWhere('user.id LIKE :user')
                    ->setParameter('user', "%".$filterService->getFilterValue('user')."%");
            }

        }
    }

    public function addOrders(FilterService $filterService, QueryBuilder $query)
    {
        if (count($filterService->getOrders()) > 0) {
            foreach ($filterService->getOrders() as $order) {
                switch ($order['field']) {
                    case "name":
                        $query->orderBy('i.name', $order['order']);
                        break;
                    case "dni":
                        $query->orderBy('i.dni', $order['order']);
                        break;
                    case "phone":
                        $query->orderBy('i.phone', $order['order']);
                        break;
                    case "address":
                        $query->orderBy('i.address', $order['order']);
                        break;
                    case "invoiceNumber":
                        $query->orderBy('i.invoiceNumber', $order['order']);
                        break;
                    case "invoicePosition":
                        $query->orderBy('i.invoicePosition', $order['order']);
                        break;
                    case "amount":
                        $query->orderBy('i.amount', $order['order']);
                        break;
                    case "invoiceDate":
                        $query->orderBy('i.invoiceDate', $order['order']);
                        break;
                    case "amountWithIva":
                        $query->orderBy('i.amountWithIva', $order['order']);
                        break;

                    case "client":
                        $query->orderBy('client.name', $order['order']);
                        break;

                    case "user":
                        $query->orderBy('user.name', $order['order']);
                        break;
                }
            }
        } else {
            $query->orderBy('i.invoicePosition', 'DESC');
        }
    }

    public function updateInvoice(
        Invoice $invoice,
        string $invoiceNumber,
        ?Appointment $appointment,
        ?Client $client,
        ?User $user,
        string $serie,
        int $invoicePosition,
        Datetime $invoiceDate,
        ?string $socialReason,
        ?string $companyPhone,
        ?string $paymentMethod,
        ?string $billingAddress,
        ?string $cif,
        ?string $name,
        ?string $dni,
        ?string $phone,
        ?string $address,
        ?array $breakdown
    )
    {
        $invoice
            ->setInvoicePosition($invoicePosition)
            ->setSocialReason($socialReason)
            ->setCompanyPhone($companyPhone)
            ->setPaymentMethod($paymentMethod)
            ->setBillingAddress($billingAddress)
            ->setCif($cif)
            ->setUser($user)
            ->setClient($client)
            ->setName($name)
            ->setDni($dni)
            ->setPhone($phone)
            ->setAddress($address)
            ->setInvoiceNumber($invoiceNumber)
            ->setInvoiceDate($invoiceDate)
            ->setSerie($serie)
            ->setBreakdown($breakdown)
            ->setAppointment($appointment)
        ;

        $this->_em->persist($invoice);

        $this->_em->flush();
    }

    public function createInvoice(
        string $invoiceNumber,
        ?Appointment $appointment,
        ?Client $client,
        ?User $user,
        string $serie,
        int $invoicePosition,
        float $amountWithoutIva,
        float $amount,
        Datetime $invoiceDate,
        ?string $socialReason,
        ?string $companyPhone,
        ?string $paymentMethod,
        ?string $billingAddress,
        ?string $cif,
        ?string $name,
        ?string $dni,
        ?string $phone,
        ?string $address,
        ?array $breakdown
    )
    {
        $newInvoice = (new Invoice())
            ->setInvoicePosition($invoicePosition)
            ->setSocialReason($socialReason)
            ->setCompanyPhone($companyPhone)
            ->setPaymentMethod($paymentMethod)
            ->setBillingAddress($billingAddress)
            ->setCif($cif)
            ->setUser($user)
            ->setClient($client)
            ->setName($name)
            ->setDni($dni)
            ->setPhone($phone)
            ->setAddress($address)
            ->setInvoiceNumber($invoiceNumber)
            ->setAmount($amountWithoutIva)
            ->setAmountWithIva($amount)
            ->setInvoiceDate($invoiceDate)
            ->setBreakdown($breakdown)
            ->setSerie($serie)
            ->setAppointment($appointment)
        ;

        $this->_em->persist($newInvoice);

        $this->_em->flush();
    }

    public function deleteInvoice(
        Invoice $invoice
    ){
        $this->_em->remove($invoice);
        $this->_em->flush();
    }



    /*
    public function findOneBySomeField($value): ?Config
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
