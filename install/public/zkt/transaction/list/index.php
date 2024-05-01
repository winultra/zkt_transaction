<?php

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

global $APPLICATION;

try {
    $APPLICATION->IncludeComponent('custom:zktconnect.transaction.list', '', [
        'SEF_MODE' => 'Y',
        'SEF_FOLDER' => '/pms/',
        'SEF_URL_TEMPLATES' => [
            'index' => '',
            'dashboard' => 'dashboard/',
            'detail' => 'dashboard/project/#ID#/',
            'settings' => 'settings/'
        ],
    ]);
} catch (Throwable $e) {
    ShowError($e->getMessage());
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
