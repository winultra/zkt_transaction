<?php

use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

?>

<form action="<? echo $APPLICATION->GetCurPage() ?>">
    <?= bitrix_sessid_post() ?>
    <input type="hidden" name="lang" value="<? echo \LANG ?>">
    <input type="hidden" name="id" value="custom.zktconnect">
    <input type="hidden" name="uninstall" value="Y">
    <input type="hidden" name="step" value="2">
    <p><? echo GetMessage("ZKTCONNECT_UNINSTALL_SAVE") ?></p>
    <p>
        <input type="checkbox" name="savedata" id="savedata" value="Y" checked>
        <label for="savedata"><? echo GetMessage("ZKTCONNECT_UNINSTALL_SAVE_TABLES") ?></label>
    </p>
    <input type="submit" name="inst" value="<? echo GetMessage("ZKTCONNECT_UNINSTALL_DEL") ?>">
</form>
