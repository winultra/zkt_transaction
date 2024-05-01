<?php

use Bitrix\Main\UI\Extension;
use Bitrix\UI\Toolbar\Facade\Toolbar;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

CJSCore::Init(["popup"]);

Extension::load([
    'ui.buttons',
    'ui.dialogs.messagebox',
    'ui.entity-selector',
    'ui.entity-editor',
    'jquery3'
]);

$component = $this->getComponent();

Toolbar::addFilter([
    'FILTER_ID' => $arResult['GRID_ID'],
    'GRID_ID' => $arResult['GRID_ID'],
    'FILTER' => $arResult['GRID_FILTER'],
    'ENABLE_LIVE_SEARCH' => true,
    'ENABLE_LABEL' => true
]);

foreach ($arResult["TOOLBAR_BUTTONS"] as $button) {
    Toolbar::addButton($button);
}

$nav = $arResult['NAV'];

$APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
    'GRID_ID' => $arResult['GRID_ID'],
    'COLUMNS' => $arResult['GRID_COLUMNS'],
    'ROWS' => $arResult['ROWS'],
    'NAV_OBJECT' => $nav,
    'DEFAULT_PAGE_SIZE' => $nav->getPageSize(),
    'PAGE_SIZES' => $nav->getPageSizes(),
    'TOTAL_ROWS_COUNT' => $nav->getRecordCount(),
    'NAV_PARAM_NAME' => $nav->getId(),
    'CURRENT_PAGE' => $nav->getCurrentPage(),
    'PAGE_COUNT' => $nav->getPageCount(),
    'ACTION_PANEL' => $arResult['ACTION_PANEL'],
    'AJAX_MODE' => 'Y',
    'AJAX_ID' => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
    'AJAX_OPTION_JUMP' => 'N',
    'SHOW_ROW_CHECKBOXES' => false,
    'SHOW_CHECK_ALL_CHECKBOXES' => false,
    'SHOW_ROW_ACTIONS_MENU' => true,
    'SHOW_GRID_SETTINGS_MENU' => true,
    'SHOW_NAVIGATION_PANEL' => true,
    'SHOW_PAGINATION' => true,
    'SHOW_SELECTED_COUNTER' => true,
    'SHOW_TOTAL_COUNTER' => false,
    'SHOW_PAGESIZE' => true,
    'SHOW_ACTION_PANEL' => true,
    'ALLOW_COLUMNS_SORT' => true,
    'ALLOW_COLUMNS_RESIZE' => true,
    'ALLOW_HORIZONTAL_SCROLL' => true,
    'ALLOW_SORT' => true,
    'ALLOW_PIN_HEADER' => true,
    'AJAX_OPTION_HISTORY' => 'N',
    "ENABLE_COLLAPSIBLE_ROWS" => true
], $component); ?>

<script>
    BX.ready(function () {
        let componentParams = {
            gridId: '<?= $arResult["GRID_ID"] ?>',
            signedParameters: '<?= $component->getSignedParameters() ?>',
        }
        BX.Custom.ZKTConnect.Transaction.List.init(componentParams)
    })
</script>
