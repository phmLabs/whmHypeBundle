<?php
namespace whm\HypeBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Amilio\CoreBundle\Entity\User;
use Amilio\CoreBundle\Entity\Channel;

class HypeRepository extends EntityRepository
{
    public function matchByType($user, $type, array $ids)
    {
        $qb = $this->createQueryBuilder('hype');

        $qb->where($qb->expr()->andX('hype.user = :user', 'hype.type = :type', 'hype.identifier IN (:ids)'));

        $qb->setParameter('user', $user->getId());
        $qb->setParameter('type', $type);
        $qb->setParameter('ids', $ids);

        $results = $qb->getQuery()->getResult();

        $matches = array();

        foreach ($results as $result) {
            $matches[] = $result->getIdentifier();
        }

        return $matches;
    }

    public function getCountsByType($type, array $ids)
    {
        $qb = $this->createQueryBuilder('hype');

        $qb->select('count(hype.identifier) as countById, hype.identifier');

        $qb->where($qb->expr()->andX('hype.type = :type', 'hype.identifier IN (:ids)'));

        $qb->groupBy("hype.identifier");

        $qb->setParameter('type', $type);
        $qb->setParameter('ids', $ids);

        return $qb->getQuery()->getResult();
    }
}
