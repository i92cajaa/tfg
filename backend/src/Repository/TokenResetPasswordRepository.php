<?php

namespace App\Repository;

use App\Entity\User\User;
use App\Entity\Token\TokenResetPassword;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Token|null find($id, $lockMode = null, $lockVersion = null)
 * @method Token|null findOneBy(array $criteria, array $orderBy = null)
 * @method Token[]    findAll()
 * @method Token[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TokenResetPasswordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Token::class);
    }

    public function generateAndStoreTokenForUser(User $user)
    {
        $token = bin2hex(random_bytes(32)); 
        $createdAt = new \DateTime();
        $expiresAt = new \DateTime('+1 hour'); 

        $newToken = new TokenResetPassword();
        $newToken->setToken($token);
        $newToken->setUser($user);
        $newToken->setCreatedAt($createdAt);
        $newToken->setExpiresAt($expiresAt);

        $this->_em->persist($newToken);
        $this->_em->flush();

        return $newToken;
    }

    public function removeExpiredTokens()
    {
        $currentDateTime = new \DateTime();

        $qb = $this->createQueryBuilder('t')
            ->delete()
            ->where('t.expiresAt < :currentDateTime')
            ->setParameter('currentDateTime', $currentDateTime);

        $qb->getQuery()->execute();
    }
    

}
