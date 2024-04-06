<?php

namespace App\Repository;

use App\Entity\Token\Token;
use App\Service\FilterService;
use App\Shared\Classes\UTCDateTime;
use App\Shared\Traits\DoctrineStorableObject;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Token|null find($id, $lockMode = null, $lockVersion = null)
 * @method Token|null findOneBy(array $criteria, array $orderBy = null)
 * @method Token[]    findAll()
 * @method Token[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TokenRepository extends ServiceEntityRepository
{

    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Token::class);
    }

    public function createOrUpdateToken(
        string $tag,
        ?string $tokenType,
        ?DateTime $expirationDate,
        ?DateTime $extExpirationDate,
        ?string $token
    ){
        $obj = $this->findOneBy(['tag' => $tag, 'tokenType' => $tokenType]);

        if($obj){
            $newToken = $obj;
        }else{
            $newToken = (new Token());
        }

        $newToken
            ->setTag($tag)
            ->setTokenType($tokenType)
            ->setExpirationDate($expirationDate)
            ->setExtExpirationDate($extExpirationDate)
            ->setToken($token);

        $this->persist($newToken);

        return $newToken;

    }

    public function persist(Token $Token)
    {
        $this->save($this->_em, $Token);
    }

    public function remove(Token $Token)
    {
        $this->delete($this->_em, $Token);
    }


}
