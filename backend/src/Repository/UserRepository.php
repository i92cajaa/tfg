<?php

namespace App\Repository;

use App\Entity\Role\Role;
use App\Entity\User\User;
use App\Entity\Token\PasswordResetToken;
use App\Service\FilterService;
use App\Shared\Classes\UTCDateTime;
use App\Shared\Traits\DoctrineStorableObject;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{

    use DoctrineStorableObject;
    private $entityManager;

    public function __construct(
        ManagerRegistry $registry,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
        parent::__construct($registry, User::class,PasswordResetToken::class);
    }

    public function getUserById(string $userId, ?bool $array = false)
    {
        try {
            return $this->createQueryBuilder('u')
                ->leftJoin('u.roles', 'role')
                ->addSelect('role')
                ->leftJoin('u.services', 'service')
                ->addSelect('service')
                ->leftJoin('u.schedules', 'schedules')
                ->addSelect('schedules')
                ->leftJoin('u.imgProfile', 'imgProfile')
                ->addSelect('imgProfile')
                ->where('u.id LIKE :id')
                ->setParameter('id', $userId)
                ->getQuery()->getOneOrNullResult($array ? Query::HYDRATE_ARRAY : Query::HYDRATE_OBJECT);
        } catch (NonUniqueResultException $e) {
            return $this->createQueryBuilder('u')
                ->leftJoin('u.roles', 'role')
                ->addSelect('role')
                ->leftJoin('u.services', 'service')
                ->addSelect('service')
                ->leftJoin('u.schedules', 'schedules')
                ->addSelect('schedules')
                ->leftJoin('u.imgProfile', 'imgProfile')
                ->addSelect('imgProfile')
                ->where('u.id LIKE :id')
                ->setParameter('id', $userId)
                ->getQuery()->getResult($array ? Query::HYDRATE_ARRAY : Query::HYDRATE_OBJECT)[0];
        }
    }


    public function findUserByEmail(string $email)
    {
       
        return $this->createQueryBuilder('u')
            ->where('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
            
    }

    public function updateUserTokenById(string $id, string $token)
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);

        $user->setToken($token);

        $this->entityManager->flush();
    }

    public function updateUserTokenByIdNull(string $id)
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);

        $user->setToken(null);

        $this->entityManager->flush();
    }

    public function findUserByToken(string $token)
    {
        return $this->createQueryBuilder('u')
            ->where('u.token = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }

   
    public function findUsers(FilterService $filterService, $showAll = false)
    {

        $query = $this->createQueryBuilder('u')
            ->leftJoin('u.center', 'center')
            ->leftJoin('u.roles', 'userHasRole')
            ->leftJoin('userHasRole.role', 'role');



        if (count($filterService->getFilters()) > 0) {

            if($filterService->getFilterValue('info') != ""){
                $query->andWhere("CONCAT(u.name, ' ', COALESCE(u.surnames, ''), ' ', COALESCE(u.email, '')) LIKE :info")
                    ->setParameter('info', "%" . $filterService->getFilterValue('info') . "%");
            }
            if($filterService->getFilterValue('roles') != null){
                $query->andWhere('userHasRole.role IN (:roles)')
                    ->setParameter('roles', $filterService->getFilterValue('roles'));
            }
            if($filterService->getFilterValue('center') != null){
                $query->andWhere('center.id LIKE :center')
                    ->setParameter('center',"%" . $filterService->getFilterValue('center') . "%");
            }

            if ($filterService->getFilterValue('service') != null) {
                $where = 'service.id LIKE ';
                $services = [];
                foreach ($filterService->getFilterValue('service') as $index => $service) {
                    if ($index == 0) {
                        $where = $where . ':service' . $index;
                    } else {
                        $where = $where . ' OR service.id LIKE :service' . $index;
                    }
                    array_push($services, $service);
                }
                $query->orWhere($where);
                foreach ($services as $index => $service) {
                    $query->setParameter('service' . $index, "%" . $service . "%");
                }
            }

            if ($filterService->getFilterValue('status') != null) {
                $query->andWhere('u.status = :status')
                    ->setParameter('status', $filterService->getFilterValue('status'));
            }
        }

        if (count($filterService->getOrders()) > 0) {
            foreach ($filterService->getOrders() as $order) {
                switch ($order['field']) {
                    case "id":
                        $query->orderBy('u.id', $order['order']);
                        break;
                    case "name":
                        $query->orderBy('u.name', $order['order']);
                        break;
                    case "surnames":
                        $query->orderBy('u.surnames', $order['order']);
                        break;
                    case "email":
                        $query->orderBy('u.email', $order['order']);
                        break;
                    case "status":
                        $query->orderBy('u.status', $order['order']);
                        break;
                    case "center":
                        $query->orderBy('center.name', $order['order']);
                        break;
                    case "appointmentColor":
                        $query->orderBy('u.appointmentColor', $order['order']);
                        break;
                    case "roles":
                        $query->orderBy('role.name', $order['order']);
                        break;
                    case "created_at":
                        $query->orderBy('u.created_at', $order['order']);
                        break;
                }
            }
        } else {
            $query->orderBy('u.id', 'DESC');
        }

        if(!$showAll){
            $query->setFirstResult($filterService->page > 1 ? (($filterService->page - 1) * $filterService->limit) : $filterService->page - 1);
            $query->setMaxResults($filterService->limit);
        }

        // Pagination process
        $paginator = new Paginator($query, $fetchJoinCollection = true);

        $totalRegisters = $paginator->count();
        $result         = [];
        foreach ($paginator as $verification) {
            $result[] = $verification;
        }

        $lastPage = (int)ceil($totalRegisters / $filterService->limit);
        //$users = $query->getQuery()->getResult();

        return [
            'totalRegisters' => $totalRegisters,
            'users'          => $result,
            'lastPage'       => $lastPage
        ];
    }

    /**
     * @param array $serviceIds
     * @return User[] Returns an array of User objects
     */
    public function findUsersByServices(array $serviceIds): array
    {
        $query =  $this->createQueryBuilder('u')
            ->join('u.services', 'userHasService')
            ->join('userHasService.service', 'service')
            ->join('u.schedules', 'schedule')
            ->leftJoin('u.festives', 'festive')
            ->join('u.roles', 'userHasRole')
            ->join('userHasRole.role', 'role')
            ->addSelect('userHasService')
            ->addSelect('service')
            ->addSelect('schedule')
            ->addSelect('festive')
            ->where("service.id IN(:serviceIds)")
            ->andWhere("(
                    SELECT COUNT(s1.id) 
                    FROM App\Entity\Service\Service as s1 
                    LEFT JOIN s1.professionals as uhs1
                    LEFT JOIN uhs1.user as p1
                    WHERE s1.id IN(:serviceIds) AND p1.id LIKE u.id
                ) >= :servicesLength
            ")
            ->andWhere('role.id != :superadmin')
            ->andWhere('u.status = 1')
            ->setParameter('superadmin', Role::ROLE_SUPERADMIN)
            ->setParameter('serviceIds', $serviceIds)
            ->setParameter('servicesLength', count($serviceIds));

        return $query->getQuery()
            ->getResult();
    }

    /**
     * @param array $serviceIds
     * @return User[] Returns an array of User objects
     */
    public function findProfessionalByServices(array $serviceIds): array
    {
        $query =  $this->createQueryBuilder('u')
            ->join('u.services', 'service')
            ->join('u.roles', 'role')
            ->addSelect('service')
            ->where("(
                    SELECT COUNT(s1.id) 
                    FROM App\Entity\Service\Service as s1 
                    LEFT JOIN s1.professionals as p1
                    WHERE s1.id IN(:services) AND p1.id = u.id
                ) = :servicesLength
            ")
            ->andWhere('role.id != :superadmin')
            ->andWhere('u.status = 1')
            ->setParameter('superadmin', Role::ROLE_SUPERADMIN)
            ->setParameter('services', $serviceIds)
            ->setParameter('servicesLength', sizeof($serviceIds));


        return $query->getQuery()
            ->getArrayResult();
    }

    /**
     * @param array $serviceIds
     * @return User[] Returns an array of User objects
     */
    public function findProfessionalByServicesAndDate(array $serviceIds, string $date): array
    {
        $datetime = UTCDateTime::setUTC(UTCDateTime::create('Y-m-d', $date));
        $query =  $this->createQueryBuilder('u')
            ->join('u.roles', 'userHasRole')
            ->join('userHasRole.role', 'role')
            ->join('u.services', 'service')
            ->addSelect('service')
            ->join('u.schedules', 'schedules', 'WITH', 'schedules.week_day = :weekDay')
            ->where("(
                    SELECT COUNT(s1.id) 
                    FROM App\Entity\Service\Service as s1 
                    LEFT JOIN s1.professionals as p1
                    WHERE s1.id IN(:services) AND p1.id = u.id
                ) = :servicesLength
            ")
            ->andWhere('u.status = 1')
            ->setParameter('weekDay', $datetime->format('w'))
            ->setParameter('services', $serviceIds)
            ->setParameter('servicesLength', sizeof($serviceIds));


        return $query->getQuery()
            ->getArrayResult();
    }

    public function findNonAdminUsers()
    {
        return $this->createQueryBuilder('u')
            ->join('u.roles', 'userHasRole')
            ->join('userHasRole.role', 'role')
            ->join('u.services', 'service')
            ->andWhere('role.admin = 0')
            ->andWhere('u.status = 1')
            ->getQuery()
            ->getResult();
    }

    public function findMentorUsers()
    {
        return $this->createQueryBuilder('u')
            ->join('u.roles', 'userHasRole')
            ->join('userHasRole.role', 'role')
            ->andWhere('role.id = 3')
            ->andWhere('u.status = 1')
            ->getQuery()
            ->getResult();
    }

    public function findMentorUsersByArea($id){
        return $this->createQueryBuilder('u')
            ->join('u.roles', 'userHasRole')
            ->join('userHasRole.role', 'role')
            ->join('u.areas','userHasArea')
            ->join('userHasArea.area','area')
            ->andWhere('area.id IN(:ids)')
            ->setParameter('ids', $id)
            ->andWhere('role.id = 3')
            ->andWhere('u.status = 1')
            ->getQuery()
            ->getResult();

    }

    public function findDirector($centerId){
        return $this->createQueryBuilder('u')
            ->join('u.roles', 'userHasRole')
            ->join('userHasRole.role', 'role')
            ->join('u.center','center')
            ->andWhere('center.id IN(:ids)')
            ->setParameter('ids', $centerId)
            ->andWhere('role.id = 2')
            ->andWhere('u.status = 1')
            ->getQuery()
            ->getResult();
    }

    public function changeStatus(User $user, bool $status){
        $user->setStatus($status);
        $this->save($this->_em, $user);
    }

    public function removeAllRoles(User $user)
    {
        foreach ($user->getRoles(false) as $role) {
            $this->delete($this->_em, $role);
        }
    }

    public function removeAllAreas(User $user)
    {
        foreach ($user->getAreas(false) as $area) {
            $this->delete($this->_em, $area);
        }
    }

    public function removeAllPermissions(User $user)
    {
        foreach ($user->getPermissions() as $permission) {
            $this->delete($this->_em, $permission);
        }
    }


    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }
        $newHashedPassword = $this->userPasswordHasher->hashPassword($user, $newHashedPassword);
        $user->setPassword($newHashedPassword);

        $this->save($this->_em, $user);
    }

    public function persist(User $user): void
    {
        $this->save($this->_em, $user);
    }

    public function remove(User $user)
    {
        $user->removeAllFestives();
        $user->removeAllServices();
        $user->removeAllRoles();
        $this->delete($this->_em, $user);
    }
}
