<?php
declare(strict_types=1);

namespace MH1\CronBundle\Helper;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;

class DateTimeHelper
{
    /**
     * @return DateTime
     */
    public static function getUTCDateTime(): DateTime
    {
        $timezone = new DateTimeZone('UTC');
        $format = 'Y-m-d H:i:s';

        /** @var DateTime $dateTimeObject */
        $dateTimeObject = DateTime::createFromFormat($format, date($format));

        return $dateTimeObject->setTimezone($timezone);
    }

    /**
     * @return DateTimeImmutable
     */
    public static function getUTCDateTimeImmutable(): DateTimeImmutable
    {
        $timezone = new DateTimeZone('UTC');
        $format = 'Y-m-d H:i:s';

        /** @var DateTimeImmutable $dateTimeObject */
        $dateTimeObject = DateTimeImmutable::createFromFormat($format, date($format));

        return $dateTimeObject->setTimezone($timezone);
    }
}
