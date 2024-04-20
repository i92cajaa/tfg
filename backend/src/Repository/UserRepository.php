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
use Doctrine\ORM\AbstractQuery;
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


    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO GET A USER'S DATA
     * ES: FUNCIÓN PARA OBTENER LOS DATOS DE UN USUARIO
     *
     * @param string $id
     * @param bool $array
     * @return array|User|null
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    public function findById(string $id, bool $array): array|User|null
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.imgProfile', 'imgProfile')
            ->leftJoin('u.center', 'center')
            ->leftJoin('u.roles', 'userHasRoles')
            ->leftJoin('userHasRoles.role', 'role')
            ->leftJoin('u.permissions', 'userHasPermissions')
            ->leftJoin('userHasPermissions.permission', 'permission')
            ->leftJoin('u.notifications', 'notifications')
            ->leftJoin('u.documents', 'userHasDocuments')
            ->leftJoin('userHasDocuments.document', 'document')
            ->leftJoin('u.lessons', 'userHasLessons')
            ->leftJoin('userHasLessons.lesson', 'lesson')
            ->leftJoin('u.schedules', 'schedules')
            ->addSelect('imgProfile')
            ->addSelect('center')
            ->addSelect('userHasRoles')
            ->addSelect('role')
            ->addSelect('userHasPermissions')
            ->addSelect('permission')
            ->addSelect('notifications')
            ->addSelect('userHasDocuments')
            ->addSelect('document')
            ->addSelect('userHasLessons')
            ->addSelect('lesson')
            ->addSelect('schedules')
            ->andWhere('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult($array ? AbstractQuery::HYDRATE_ARRAY : AbstractQuery::HYDRATE_OBJECT);
    }
    // ----------------------------------------------------------------

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

    public function updateUserTokenByIdNull(string $id): void
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

   
    public function findUsers(FilterService $filterService, $showAll = false): array
    {

        $query = $this->createQueryBuilder('u')
            ->leftJoin('u.imgProfile', 'imgProfile')
            ->leftJoin('u.center', 'center')
            ->leftJoin('u.roles', 'userHasRoles')
            ->leftJoin('userHasRoles.role', 'role')
            ->leftJoin('u.permissions', 'userHasPermissions')
            ->leftJoin('userHasPermissions.permission', 'permission')
            ->leftJoin('u.notifications', 'notifications')
            ->leftJoin('u.documents', 'userHasDocuments')
            ->leftJoin('userHasDocuments.document', 'document')
            ->leftJoin('u.lessons', 'userHasLessons')
            ->leftJoin('userHasLessons.lesson', 'lesson')
            ->leftJoin('u.schedules', 'schedules')
            ->addSelect('imgProfile')
            ->addSelect('center')
            ->addSelect('userHasRoles')
            ->addSelect('role')
            ->addSelect('userHasPermissions')
            ->addSelect('permission')
            ->addSelect('notifications')
            ->addSelect('userHasDocuments')
            ->addSelect('document')
            ->addSelect('userHasLessons')
            ->addSelect('lesson')
            ->addSelect('schedules');



        if (count($filterService->getFilters()) > 0) {

            if($filterService->getFilterValue('info') != ""){
                $query->andWhere("CONCAT(u.name, ' ', COALESCE(u.surnames, ''), ' ', COALESCE(u.email, '')) LIKE :info")
                    ->setParameter('info', "%" . $filterService->getFilterValue('info') . "%");
            }
            if($filterService->getFilterValue('roles') != null){
                $query->andWhere('userHasRoles.role IN (:roles)')
                    ->setParameter('roles', $filterService->getFilterValue('roles'));
            }
            if($filterService->getFilterValue('center') != null){
                $query->andWhere('center.id LIKE :center')
                    ->setParameter('center',"%" . $filterService->getFilterValue('center') . "%");
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

    public function changeStatus(User $user, bool $status){
        $user->setStatus($status);
        $this->save($this->_em, $user);
    }

    public function removeAllRoles(User $user)
    {
        foreach ($user->getRolesCollection() as $role) {
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

    public function remove(User $user): void
    {
        $user->removeAllRoles();
        $this->delete($this->_em, $user);
    }
}
