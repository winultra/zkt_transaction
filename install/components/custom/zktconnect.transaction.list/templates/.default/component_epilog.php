<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Web\Json;

$langFileItems = Loc::loadLanguageFile(__DIR__ . '/script.php');

if (!empty($langFileItems)) {
    Asset::getInstance()->AddString("<script type=\"text/javascript\">BX.message(" . Json::encode($langFileItems) . ")</script>");
}
