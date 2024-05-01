<?php

/**
 * 55 23 * * * /usr/bin/php -f /home/bitrix/www/local/modules/custom.zktconnect/console/update_transaction.php
 */

if (php_sapi_name() != 'cli') {
    die();
}

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Loader;
use Custom\ZKTConnect\Service;

$_SERVER['DOCUMENT_ROOT'] = '/home/bitrix/www';

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("NEED_AUTH", true);

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";

// Include modules
Loader::IncludeModule("custom.zktconnect");
Loader::IncludeModule("timeman");

$USER = new \CUser;
$USER->Authorize(1);

$serviceLocator = ServiceLocator::getInstance();

try {
    /** @var Service $service */
    $service = $serviceLocator->get('custom.zktconnect.service');
} catch (\Throwable $e) {
    return '\\' . __METHOD__ . '();';
}

// Calculate the first and last day of the current week
$firstDayOfWeek = new \DateTime();
$firstDayOfWeek->setISODate((int)$firstDayOfWeek->format('o'), (int)$firstDayOfWeek->format('W'));
$firstDayOfWeek->setTime(0, 0, 0); // Start of the first day

$lastDayOfWeek = clone $firstDayOfWeek;
$lastDayOfWeek->modify('+6 days');
$lastDayOfWeek->setTime(23, 59, 59); // End of the last day

// Format dates
$dateStart = $firstDayOfWeek->format('Y-m-d');
$dateEnd = $lastDayOfWeek->format('Y-m-d');

$service->updateTransactionList($dateStart, $dateEnd);

file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/cron_log.txt', 'Script executed at: ' . (date('d-m-Y H:i:s')), FILE_APPEND);