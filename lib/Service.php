<?php

namespace Custom\ZKTConnect;

use Bitrix\Main\Config\Option;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Type\DateTime;
use Custom\ZKTConnect\Internal\TransactionTable;
use Custom\ZKTConnect\Rest\Client;

class Service implements Errorable
{
    private Client $client;

    use ErrorableImplementation;


    public function __construct()
    {
        $this->errorCollection = new ErrorCollection();

        $serviceLocator = ServiceLocator::getInstance();

        try {
            $this->client = $serviceLocator->get('custom.zktconnect.client');
        } catch (\Throwable $e) {
            return;
        }

    }

    public function updateTransactionList(?string $dateStart = null, ?string $dateEnd = null)
    {
        $transactions = $this->client->getTransactionList($dateStart, $dateEnd);

        if (empty($transactions)) {
            return;
        }

        foreach ($transactions as $context) {
            $this->updateTransaction($context);
        }
    }

    public function updateTransaction(array $context)
    {
        $saveData = [];

        if (!isset($context['id'])) {
            return;
        }

        $timediff = Option::get('custom.zktconnect', 'time_diff');

        $saveData['TRANSACTION_ID'] = (int)$context['id'];

        if (isset($context['emp'])) {
            $saveData['EMP_ID'] = (int)$context['emp'];
        }

        if (isset($context['emp_code'])) {
            $saveData['EMP_CODE'] = (string)$context['emp_code'];
        }

        if (isset($context['first_name'])) {
            $saveData['FIRST_NAME'] = (string)$context['first_name'];
        }

        if (isset($context['last_name'])) {
            $saveData['LAST_NAME'] = (string)$context['last_name'];
        }

        if (isset($context['department'])) {
            $saveData['DEPARTMENT'] = (string)$context['department'];
        }

        if (isset($context['position'])) {
            $saveData['POSITION'] = (string)$context['position'];
        }

        if (isset($context['punch_time'])) {
            try {
                $saveData['PUNCH_TIME'] = new DateTime($context['punch_time'], 'Y-m-d H:i:s');
            } catch (\Throwable $e) {
                $saveData['PUNCH_TIME'] = new DateTime(date('Y-m-d H:i:s'), 'Y-m-d H:i:s');
            }
        }

        if (isset($context['punch_state'])) {
            $saveData['PUNCH_STATE'] = (string)$context['punch_state'];
        }

        if (isset($context['punch_state_display'])) {
            $saveData['PUNCH_STATE_DISPLAY'] = (string)$context['punch_state_display'];
        }

        if (isset($context['verify_type'])) {
            $saveData['VERIFY_TYPE'] = (int)$context['verify_type'];
        }

        if (isset($context['verify_type_display'])) {
            $saveData['VERIFY_TYPE_DISPLAY'] = (string)$context['verify_type_display'];
        }

        if (isset($context['work_code'])) {
            $saveData['WORK_CODE'] = (string)$context['work_code'];
        }

        if (isset($context['gps_location'])) {
            $saveData['GPS_LOCATION'] = (string)$context['gps_location'];
        }

        if (isset($context['area_alias'])) {
            $saveData['AREA_ALIAS'] = (string)$context['area_alias'];
        }

        if (isset($context['terminal_sn'])) {
            $saveData['TERMINAL_SN'] = (string)$context['terminal_sn'];
        }

        if (isset($context['temperature'])) {
            $saveData['TEMPERATURE'] = (int)$context['temperature'];
        }

        if (isset($context['is_mask'])) {
            $saveData['IS_MASK'] = (string)$context['is_mask'];
        }

        if (isset($context['terminal_alias'])) {
            $saveData['TERMINAL_ALIAS'] = (string)$context['terminal_alias'];
        }

        if (isset($context['upload_time'])) {
            try {
                $saveData['UPLOAD_TIME'] = new DateTime($context['upload_time'], 'Y-m-d H:i:s');
            } catch (\Throwable $e) {
                $saveData['UPLOAD_TIME'] = new DateTime(date('Y-m-d H:i:s'), 'Y-m-d H:i:s');
            }
        }

        if (!empty($timediff)) {
            $dateFields = ['PUNCH_TIME', 'UPLOAD_TIME'];
            foreach ($dateFields as $field) {
                $saveData[$field]->add($timediff . ' hours');
            }
        }

        $transactionExists = TransactionTable::getRow([
            'filter' => [
                '=TRANSACTION_ID' => (int)$context['id']
            ],
            'select' => [
                'ID'
            ]
        ]);

        if (empty($transactionExists)) {
            TransactionTable::add($saveData);
        } else {
            TransactionTable::update($transactionExists['ID'], $saveData);
        }
    }
}
