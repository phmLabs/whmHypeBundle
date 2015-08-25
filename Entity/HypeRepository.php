<?php
namespace whm\HypeBundle\Entity;

use Doctrine\ORM\EntityRepository;

class HypeRepository extends EntityRepository
{
    public function findHotElements($type, $count, array $boosts)
    {
        ksort($boosts);

        $ageStart = 0;

        $hotElements = array();

        foreach ($boosts as $age => $boost) {
            $qb = $this->createQueryBuilder('hype');
            $qb->select('count(hype.identifier) as countById, hype.identifier');
            $qb->where($qb->expr()->andX('hype.type = :type', 'hype.created <= :begin', 'hype.created >= :end'));
            $qb->groupBy("hype.identifier");

            $begin = new \DateTime();
            $begin->sub(new \DateInterval('P' . $ageStart . 'D'));

            $end = new \DateTime();
            $end->sub(new \DateInterval('P' . $age . 'D'));

            $qb->setParameter('type', $type);
            $qb->setParameter('begin', $begin);
            $qb->setParameter('end', $end);

            $elements = $qb->getQuery()->getResult();

            foreach ($elements as $element) {
                if (!array_key_exists($element["identifier"], $hotElements)) {
                    $hotElements[$element["identifier"]] = 0;
                }
                $hotElements[$element["identifier"]] += $boost * $element["countById"];
            }

            $ageStart = $age;
        }

        arsort($hotElements);

        return array_slice($hotElements, 0, $count, true);
    }

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
