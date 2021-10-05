<?php
declare(strict_types=1);

namespace MH1\CronBundle\Repository;

use Doctrine\ORM\EntityRepository;
use MH1\CronBundle\Entity\Mh1CronJob;

/**
 * @extends EntityRepository<Mh1CronJob>
 */
class Mh1CronJobRepository extends EntityRepository
{
    /**
     * @return Mh1CronJob[]
     */
    public function findEnabledJobs(): array
    {
        return $this->createQueryBuilder('cj')
                    ->andWhere('cj.enabled = true')
                    ->getQuery()
                    ->getResult()
        ;
    }
}
