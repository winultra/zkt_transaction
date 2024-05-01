<?php

/**
 * 58 23 * * * /usr/bin/php -f /home/bitrix/www/local/modules/custom.zktconnect/console/close_days.php
 */

if (php_sapi_name() != 'cli') {
    die();
}

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use Custom\ZKTConnect\Internal\TransactionTable;

$_SERVER['DOCUMENT_ROOT'] = '/home/bitrix/www';

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("NEED_AUTH", true);

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";

// Include modules
Loader::IncludeModule("custom.zktconnect");
Loader::IncludeModule("timeman");

// Get serial numbers of terminals
$terminalIn = Option::get('custom.zktconnect', 'terminal_in', '');
$arTerminalIn = array_filter(explode(',', $terminalIn));

$terminalOut = Option::get('custom.zktconnect', 'terminal_out', '');
$arTerminalOut = array_filter(explode(',', $terminalOut));

$users = UserTable::getList([
    'select' => [
        'ID',
        'UF_ZKE_ID'
    ]
])->fetchAll();

$curDate = (new \Bitrix\Main\Type\DateTime())->setTime(0, 0);

file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/cron_log.txt', 'Script executed at: ' . (date('d-m-Y H:i:s')), FILE_APPEND);

foreach ($users as $user) {

    if (empty($user['UF_ZKE_ID'])) {
        continue;
    }

    $USER = new \CUser;
    $USER->Authorize($user['ID']);

    $obUser = new CTimeManUser($user['ID']);

    // Register work day
    if ($obUser->isDayOpen() || $obUser->isDayPaused()) {
        $obUser->closeDay();
    } else {
        $obUser->openDay();
        $obUser->closeDay();
    }

    // Get transaction list
    $transactions = TransactionTable::getList([
        'filter' => [
            '=EMP_ID' => (int)$user['UF_ZKE_ID'],
            '>=PUNCH_TIME' => $curDate
        ],
        'select' => [
            'PUNCH_TIME',
            'TERMINAL_SN'
        ],
        'order' => [
            'PUNCH_TIME' => 'ASC'
        ]
    ])->fetchAll();

    // Group transactions by days
    $days = [];
    foreach ($transactions as $transaction) {
        $day = $transaction['PUNCH_TIME']->format("d.m.Y");
        if (!array_key_exists($day, $days)) {
            $days[$day] = [];
        }
        $days[$day][] = $transaction;
    }

    foreach ($days as $day => $transactions) {

        // Get start and finish time
        $firstTransactionTime = $transactions[0]['PUNCH_TIME']->getTimestamp();
        $lastTransactionTime = end($transactions)['PUNCH_TIME']->getTimestamp();
        $dayStart = (new \DateTime($day))->setTime(0, 0)->getTimestamp();

        $timeStart = $firstTransactionTime - $dayStart;
        $timeFinish = $lastTransactionTime - $dayStart;

        // Get break time
        $timeLeaks = 0;
        $lastExitTime = null;
        foreach ($transactions as $transaction) {
            if (in_array($transaction['TERMINAL_SN'], $arTerminalOut) && $lastExitTime === null) {
                $lastExitTime = $transaction['PUNCH_TIME']->getTimestamp();
            } elseif (in_array($transaction['TERMINAL_SN'], $arTerminalIn) && $lastExitTime !== null) {
                $timeLeaks += $transaction['PUNCH_TIME']->getTimestamp() - $lastExitTime;
                $lastExitTime = null;
            }
        }

        $params = [
            'REPORT' => 'Отчет за ' . $day,
            'TIME_START' => $timeStart,
            'DATE_START' => $day,
            'TIME_FINISH' => $timeFinish,
            'DATE_FINISH' => $day,
            'TIME_LEAKS' => $timeLeaks,
            'LAT_CLOSE' => '',
            'LON_CLOSE' => '',
            'DEVICE' => 'browser'
        ];

        $result = $obUser->editDay($params);
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/log.txt', var_export($user['ID'], true), FILE_APPEND);
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/log.txt', var_export($params, true), FILE_APPEND);
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/log.txt', var_export($result, true), FILE_APPEND);
    }
}
