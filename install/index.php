<?php

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Type\DateTime;

class custom_zktconnect extends CModule
{
    var $MODULE_ID = "custom.zktconnect";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;

    var string $ROOT_MENU_TYPE;

    var array $COMPONENTS_PATH;

    var array $TEMPLATES_PATH;

    var array $EXTENSIONS_PATH;

    var array $PUBLIC_PATH;

    function __construct()
    {
        $arModuleVersion = [];
        include(__DIR__ . "/version.php");

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("ZKTCONNECT_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("ZKTCONNECT_MODULE_DESC");

        $this->ROOT_MENU_TYPE = 'top';

        $this->COMPONENTS_PATH = [__DIR__ . "/components/" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/"];

        $this->TEMPLATES_PATH = [__DIR__ . "/templates/" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/templates/"];

        $this->EXTENSIONS_PATH = [__DIR__ . "/js/" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/js/"];

        $this->PUBLIC_PATH = [__DIR__ . "/public/" => $_SERVER["DOCUMENT_ROOT"] . "/"];
    }

    public function DoInstall()
    {
        global $APPLICATION;

        if ($this->isVersionD7()) {

            ModuleManager::registerModule($this->MODULE_ID);

            Loader::includeModule($this->MODULE_ID);

            $this->installFiles();
            $this->installEvents();
            $this->installOptions();
            $this->installDB();
            $this->addMenuItem();

            $arSteps = [
                'add_user_userfields',
                'sql_requests'
            ];

            foreach ($arSteps as $step) {
                $stageDir = realpath(__DIR__ . '/steps/');
                $stepPath = $stageDir . '/' . $step . '.php';

                if (!file_exists($stepPath)) {
                    continue;
                }

                try {
                    require_once $stepPath;
                } catch (\Exception $e) {

                }
            }

        } else {
            $APPLICATION->ThrowException(Loc::getMessage("ZKTCONNECT_INSTALL_ERROR_VERSION"));
        }
    }

    public function DoUninstall()
    {
        global $APPLICATION;

        Loader::includeModule($this->MODULE_ID);

        $this->unInstallFiles();
        $this->unInstallEvents();
        $this->removeMenuItem();

        $step = $_REQUEST['step'];

        $step = (int)$step;

        if ($step < 2) {
            $APPLICATION->IncludeAdminFile(Loc::getMessage("ZKTCONNECT_REMOVE_MODULE_PAGE_TITLE"),
                __DIR__ . "/unstep1.php");
        } else if ($step == 2) {
            if (!$_REQUEST["savedata"]) {
                $this->unInstallDB();
                $this->unInstallOptions();
            }
        }

        ModuleManager::unRegisterModule($this->MODULE_ID);
    }


    public function isVersionD7(): ?bool
    {
        return version_compare(ModuleManager::getVersion('main'), '20.00.00') >= 0;
    }

    private function getEntities(): array
    {
        return [
            \Custom\ZKTConnect\Internal\TransactionTable::class,
        ];
    }

    /**
     * Installs module files.
     *
     * @return void
     */
    public function installFiles(): void
    {
        $paths = [$this->COMPONENTS_PATH, $this->TEMPLATES_PATH, $this->EXTENSIONS_PATH, $this->PUBLIC_PATH];

        foreach ($paths as $path) {

            $from = array_key_first($path);
            $to = current($path);

            if (\Bitrix\Main\IO\Directory::isDirectoryExists($from)) {
                CopyDirFiles($from, $to, true, true);
            }
        }
    }

    /**
     * Registers event handlers.
     *
     * @return void
     */
    public function installEvents(): void
    {

    }

    /**
     * Removes event handlers.
     *
     * @return void
     */
    public function unInstallEvents(): void
    {

    }

    /**
     * Sets module options.
     *
     * @return void
     */
    public function installOptions(): void
    {
        $canUpdateGroups = Option::get($this->MODULE_ID, 'can_update_groups', []);

        if (empty($canUpdateGroups)) {
            Option::set($this->MODULE_ID, 'can_update_groups', json_encode([1]));
        }
    }

    /**
     * Removes module options.
     *
     * @return void
     */
    public function unInstallOptions(): void
    {
        $options = Option::getForModule($this->MODULE_ID);

        foreach ($options as $name => $value) {
            Option::delete($this->MODULE_ID, ["name" => $name]);
        }
    }

    /**
     * Creates tables.
     *
     * @return void
     */
    public function installDB()
    {
        $entities = $this->getEntities();

        foreach ($entities as $entity) {
            if (!Application::getConnection($entity::getConnectionName())->isTableExists($entity::getTableName())) {
                Base::getInstance($entity)->createDbTable();
            }
        }
    }

    /**
     * Deletes tables.
     *
     * @return void
     */
    public function unInstallDB()
    {
        $connection = \Bitrix\Main\Application::getConnection();

        $entities = $this->getEntities();

        foreach ($entities as $entity) {
            if (Application::getConnection($entity::getConnectionName())->isTableExists($entity::getTableName())) {
                $connection->dropTable($entity::getTableName());
            }
        }
    }

    public function addMenuItem()
    {
        $siteId = CSite::GetDefSite();
        $siteInfo = CSite::GetByID($siteId)->Fetch();

        $slash = (version_compare(ModuleManager::getVersion('main'), '23.500.0') > 0) ? "/" : "";

        $menuFile = $siteInfo["DIR"] . "." . $this->ROOT_MENU_TYPE . ".menu.php";
        $menuItem = [
            'ZKT: Транзакции',
            $slash . 'zkt/transaction/list/',
            [],
            [],
            "IsModuleInstalled('custom.zktconnect') && CModule::includeModule('custom.zktconnect') && (Bitrix\Main\Engine\CurrentUser::get()->isAdmin())"
        ];
        $pos = -1;

        if (CModule::IncludeModule("fileman")) {
            $arResult = CFileMan::GetMenuArray(Application::getDocumentRoot().$menuFile);
            $arMenuItems = $arResult["aMenuLinks"];
            $menuTemplate = $arResult["sMenuTemplate"];

            $bFound = false;
            foreach($arMenuItems as $item) {
                if($item[1] == $menuItem[1]) {
                    $bFound = true;
                    break;
                }
            }

            if(!$bFound) {
                if($pos < 0 || $pos >= count($arMenuItems))
                    $arMenuItems[] = $menuItem;
                else {
                    for($i = count($arMenuItems); $i > $pos; $i--)
                        $arMenuItems[$i] = $arMenuItems[$i - 1];

                    $arMenuItems[$pos] = $menuItem;
                }

                CFileMan::SaveMenu(array($siteId, $menuFile), $arMenuItems, $menuTemplate);
            }
        }
    }

    public function removeMenuItem()
    {
        $siteId = CSite::GetDefSite();
        $siteInfo = CSite::GetByID($siteId)->Fetch();

        $menuFile = $siteInfo["DIR"] . "." . $this->ROOT_MENU_TYPE . ".menu.php";
        $menuLink = $siteInfo["DIR"] . 'zkt/transaction/list/';

        if (CModule::IncludeModule("fileman")) {
            $arResult = CFileMan::GetMenuArray(Application::getDocumentRoot().$menuFile);
            $arMenuItems = $arResult["aMenuLinks"];
            $menuTemplate = $arResult["sMenuTemplate"];

            foreach($arMenuItems as $key => $item) {
                if($item[1] == $menuLink) unset($arMenuItems[$key]);
            }

            CFileMan::SaveMenu(array($siteId, $menuFile), $arMenuItems, $menuTemplate);
        }
    }
}
