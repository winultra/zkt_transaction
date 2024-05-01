<?php

$requests = [
    "ALTER TABLE c_zktconnect_transaction ADD EMP_CODE VARCHAR(255) NULL"
];

$conn = \Bitrix\Main\Application::getInstance()->getConnection();

foreach ($requests as $index => $request) {
    try {
        $conn->query($request);
    } catch (\Exception $e) {
    }
}
