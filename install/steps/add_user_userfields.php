<?php

use Bitrix\Main\UserFieldTable;

$arFields = [
    [
        'FIELD_NAME' => 'UF_ZKE_ID',
        'USER_TYPE_ID' => 'integer',
        'XML_ID' => 'XML_ZKE_ID',
        'MULTIPLE' => 'N',
        'MANDATORY' => 'N',
        'SHOW_FILTER' => 'E',
        'SHOW_IN_LIST' => 'Y',
        'EDIT_IN_LIST' => 'Y',
        'IS_SEARCHABLE' => 'N',
        'LABELS' => [
            'ru' => 'ZKE ID',
            'en' => 'ZKE ID'
        ]
    ],
];

$arLabels = [
    'EDIT_FORM_LABEL',
    'LIST_COLUMN_LABEL',
    'LIST_FILTER_LABEL',
    'ERROR_MESSAGE',
    'HELP_MESSAGE',
];

$model = new \CUserTypeEntity();

foreach ($arFields as $arField) {
    $arField['ENTITY_ID'] = 'USER';

    $arFields = UserFieldTable::getRow([
        'select' => ['ID'],
        'filter' => [
            '=ENTITY_ID' => $arField['ENTITY_ID'],
            '=FIELD_NAME' => $arField['FIELD_NAME'],
        ],
    ]);

    foreach ($arLabels as $labelField) {
        $arField[$labelField] = $arField['LABELS'];
    }

    $values = [];

    if (array_key_exists('VALUES', $arField)) {
        $values = $arField['VALUES'];
        unset($arField['VALUES']);
    }

    if ($arFields) {
        $model->Update($arFields['ID'], $arField);
        $fieldId = $arFields['ID'];

        continue;
    }

    $fieldId = $model->Add($arField);
}
