<?php

use MH1\CronBundle\Helper\DateTimeHelper;
use Symfony\Bridge\PhpUnit\ClockMock;

require dirname(__DIR__) . '/vendor/autoload.php';

ClockMock::register(DateTimeHelper::class);
