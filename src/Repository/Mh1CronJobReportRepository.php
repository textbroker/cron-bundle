<?php
declare(strict_types=1);

namespace MH1\CronBundle\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use MH1\CronBundle\Entity\Mh1CronJob;
use MH1\CronBundle\Entity\Mh1CronJobReport;

/**
 * @extends EntityRepository<Mh1CronJobReport>
 */
class Mh1CronJobReportRepository extends EntityRepository
{
    /**
     * @param Mh1CronJob $cronJob
     *
     * @return Mh1CronJobReport|null
     * @throws NonUniqueResultException
     */
    public function findLastExecutionByJob(Mh1CronJob $cronJob): ?Mh1CronJobReport
    {
        return $this->createQueryBuilder('cjr')
                    ->andWhere('cjr.cronJob = :cronJob')
                    ->setParameter('cronJob', $cronJob)
                    ->orderBy('cjr.endTime', Criteria::DESC)
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult()
        ;
    }
}
