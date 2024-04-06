<?php
namespace App\Shared\Traits;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;

trait DoctrineStorableObject
{
    private function save(EntityManagerInterface $em, $entity) {
        $em->persist($entity);
        $em->flush();
    }

    private function delete(EntityManagerInterface $em, $entity) {
        $em->remove($entity);
        $em->flush();
    }
}
