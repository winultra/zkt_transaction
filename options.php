<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\GroupTable;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

global $APPLICATION;

$request = HttpApplication::getInstance()->getContext()->getRequest();
$module_id = htmlspecialchars($request['mid'] != '' ? $request['mid'] : $request['id']);

Loader::includeModule($module_id);

\Bitrix\Main\UI\Extension::load([
    'calendar',
]);

try {
    $canUpdateGroups = Json::decode(Option::get($module_id, 'can_update_groups', []));
} catch (Throwable $e) {
    $canUpdateGroups = [];
}

$groupsOption = [];
$groups = GroupTable::getList([
    "select" => [
        "ID",
        "NAME",
    ],
])->fetchAll();

foreach ($groups as $group) {
    $groupsOption[] = [
        "ID" => $group["ID"],
        "NAME" => $group["NAME"],
    ];
}

if ($request->isPost() && check_bitrix_sessid()) {

    $host = $request->getPost("api_host");
    Option::set($module_id, "api_host", $host);

    $username = $request->getPost("api_username");
    Option::set($module_id, "api_username", $username);

    $password = $request->getPost("api_password");
    Option::set($module_id, "api_password", $password);

    $canUpdateGroups = isset($request['can_update_groups']) ? $request['can_update_groups'] : [];
    Option::set($module_id, 'can_update_groups', Json::encode($canUpdateGroups));

    $terminalIn = $request->getPost("terminal_in");
    $terminalIn = str_replace(' ', '', $terminalIn);
    Option::set($module_id, "terminal_in", $terminalIn);

    $terminalOut = $request->getPost("terminal_out");
    $terminalOut = str_replace(' ', '', $terminalOut);
    Option::set($module_id, "terminal_out", $terminalOut);

    $timediff = $request->getPost("time_diff");
    Option::set($module_id, "time_diff", $timediff);
}

$aTabs = [
    [
        "DIV" => "edit",
        "TAB" => Loc::getMessage('MAIN_TAB'),
        "TITLE" => Loc::getMessage('MAIN_TAB'),
        "OPTIONS" => [
            Loc::getMessage('MAIN_TAB'),
        ]
    ],
];

$tabControl = new CAdminTabControl(
    "tabController",
    $aTabs
);

$tabControl->Begin();

?>
<form action="<?php echo($APPLICATION->GetCurPage()); ?>?mid=<?= $module_id ?>&lang=<?= LANG ?>" method="post">
    <div class="adm-detail-content" id="edit">
        <div class="adm-detail-title"><?= Loc::getMessage('MAIN_TAB_TITLE') ?></div>
        <div class="adm-detail-content-item-block">
            <table class="adm-detail-content-table edit-table" id="edit_edit_table">
                <tbody>
                <tr class="heading">
                    <td colspan="2"><?= Loc::getMessage('SECTION_CONN') ?></td>
                </tr>
                <tr>
                    <td class="adm-detail-content-cell-l">
                        <label for="api_host"><?= Loc::getMessage('OPTION_HOST') ?>:</label>
                    </td>
                    <td class="adm-detail-content-cell-r">
                        <input type="text" size="" maxlength="255" id="api_host"
                               name="api_host"
                               value="<?= Option::get($module_id, "api_host") ?>">
                    </td>
                </tr>
                <tr>
                    <td class="adm-detail-content-cell-l">
                        <label for="api_username"><?= Loc::getMessage('OPTION_USERNAME') ?>:</label>
                    </td>
                    <td class="adm-detail-content-cell-r">
                        <input type="text" size="" maxlength="255" id="api_username"
                               name="api_username"
                               value="<?= Option::get($module_id, "api_username") ?>">
                    </td>
                </tr>
                <tr>
                    <td class="adm-detail-content-cell-l">
                        <label for="api_password"><?= Loc::getMessage('OPTION_PASSWORD') ?>:</label>
                    </td>
                    <td class="adm-detail-content-cell-r">
                        <input type="password" size="" maxlength="255" id="api_password"
                               name="api_password"
                               value="<?= Option::get($module_id, "api_password") ?>">
                    </td>
                </tr>

                <tr class="heading">
                    <td colspan="2"><?= Loc::getMessage('SECTION_RIGHTS') ?></td>
                </tr>
                <tr>
                    <td class="adm-detail-content-cell-l">
                        <?= Loc::getMessage("CAN_UPDATE_OPTION") ?>:
                    </td>
                    <td class="adm-detail-content-cell-r">
                        <select id="groups_select" name="can_update_groups[]" multiple="" size="6">
                            <?php foreach ($groupsOption as $group) { ?>
                                <option value="<?= $group["ID"] ?>" <?= in_array($group["ID"], $canUpdateGroups) ? "selected" : "" ?>>
                                    <?= $group["NAME"] ?> [<?= $group["ID"] ?>]
                                </option>
                            <? } ?>
                        </select>
                    </td>
                </tr>

                <tr class="heading">
                    <td colspan="2"><?= Loc::getMessage('SECTION_OTHER') ?></td>
                </tr>
                <tr>
                    <td class="adm-detail-content-cell-l">
                        <?= Loc::getMessage("TERMINAL_IN_OPTION") ?>:
                    </td>
                    <td class="adm-detail-content-cell-r">
                        <input type="text" size="" maxlength="255" id="terminal_in"
                               name="terminal_in"
                               value="<?= Option::get($module_id, "terminal_in") ?>">
                    </td>
                </tr>
                <tr>
                    <td class="adm-detail-content-cell-l">
                        <?= Loc::getMessage("TERMINAL_OUT_OPTION") ?>:
                    </td>
                    <td class="adm-detail-content-cell-r">
                        <input type="text" size="" maxlength="255" id="terminal_out"
                               name="terminal_out"
                               value="<?= Option::get($module_id, "terminal_out") ?>">
                    </td>
                </tr>
                <tr>
                    <td class="adm-detail-content-cell-l">
                        <?= Loc::getMessage("TIME_DIFF_OPTION") ?>:
                    </td>
                    <td class="adm-detail-content-cell-r">
                        <input type="text" size="" maxlength="255" id="time_diff"
                               name="time_diff"
                               value="<?= Option::get($module_id, "time_diff") ?>">
                    </td>
                </tr>

                </tbody>
            </table>
        </div>
    </div>
    <div class="adm-detail-content-btns-wrap adm-detail-content-btns-pin" id="tabControl_buttons_div">
        <div class="adm-detail-content-btns">
            <input type="submit" name="apply" value="<?= Loc::getMessage('APPLY_BUTTON') ?>" class="adm-btn-save">
        </div>
    </div>
    <?= bitrix_sessid_post() ?>
</form>

<div hidden>
    <?php
    $tabControl->End();
    ?>
</div>

<style>
    .adm-detail-content-table tbody td {
        width: 50%;
    }

    #groups_select {
        width: 380px;
    }
</style>
