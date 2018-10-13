<?php

namespace App\Repository;


use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class EventRepository extends ServiceEntityRepository {

    /**
     * EventRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Event::class);
    }

    public function lastSevenDaysEvents() {
        $date = new \DateTime('now');
        $date->modify('- 7 days');
        $country = ['US'];

        $qb = $this->createQueryBuilder('e')
            ->andWhere('e.date >= :date')
            ->setParameter('date', $date)
            ->andWhere('e.country IN :country')
            ->setParameter('country', $country)
            ->getQuery();

        return $qb->execute();

    }

}