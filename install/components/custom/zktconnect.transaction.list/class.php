<?php

namespace Custom\Components;

use Bitrix\Main\Config\Option;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Grid;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UI;
use Bitrix\UI\Buttons\Button;
use Bitrix\UI\Buttons\JsCode;
use Custom\ZKTConnect\AccessManager;
use Custom\ZKTConnect\Internal\TransactionTable;
use Custom\ZKTConnect\Service;

class ZKTConnectTransactionListComponent extends \CBitrixComponent implements Controllerable, Errorable
{

    const GRID_ID = 'ZKTCONNECT_TRANSACTION_LIST';

    protected int $userId;

    protected Service $service;
    protected AccessManager $accessManager;

    use ErrorableImplementation;

    public function __construct($component = null)
    {
        $this->errorCollection = new ErrorCollection();

        if (!Loader::includeModule('custom.zktconnect')) {
            $this->errorCollection->setError(
                new Error('custom.zktconnect module not included.')
            );
        }

        global $USER;

        $this->userId = $USER->getId();

        $serviceLocator = ServiceLocator::getInstance();

        try {
            $this->service = $serviceLocator->get('custom.zktconnect.service');
            $this->accessManager = $serviceLocator->get('custom.zktconnect.accessManager');
        } catch (\Throwable $e) {
            $this->errorCollection->setError(
                new Error('ZKTConnect Service not found.')
            );
        }

        parent::__construct($component);
    }

    protected function listKeysSignedParameters(): array
    {
        return [];
    }

    public function configureActions(): array
    {
        return [];
    }

    public function executeComponent()
    {
        global $USER;
        if (!$USER->IsAdmin()) {
            $this->errorCollection->setError(
                new Error(Loc::getMessage('ACCESS_DENIED'))
            );
        }

        if ($this->hasErrors()) {
            ShowError($this->getErrors()[0]);
            return;
        }

        global $APPLICATION;

        $grid_id = self::GRID_ID;

        $grid_options = new Grid\Options($grid_id);

        $grid_filter = $this->getFilterFields();

        $entity_repository = $this->getEntityRepository();

        $filter = $this->getEntityFilter($grid_id, $grid_filter);

        $select = $this->getEntitySelect();

        $sort = $this->getSorting($grid_options);

        $nav = $this->initNav($grid_options);

        $columns = $this->getGridColumns();

        $toolbar_buttons = $this->getToolbarButtons();

        $elements = $entity_repository::getList([
            'filter' => $filter,
            'select' => $select,
            "order" => $sort,
            'count_total' => true,
            'offset' => $nav->getOffset(),
            'limit' => $nav->getLimit()

        ]);

        $nav->setRecordCount($elements->getCount());

        $grid_rows = [];

        foreach ($elements as $element) {
            $prepared_element = $this->getPreparedElement($element);

            $actions = $this->getElementActions($element);

            $row = [
                'id' => $element['ID'],
                'data' => $element,
                'columns' => $prepared_element,
                'editable' => 'Y',
                'actions' => $actions
            ];

            $grid_rows[] = $row;
        }

        $this->arResult['NAV'] = $nav;

        $this->arResult['GRID_ID'] = $grid_id;
        $this->arResult['GRID_FILTER'] = $grid_filter;
        $this->arResult['GRID_COLUMNS'] = $columns;
        $this->arResult['ROWS'] = $grid_rows;
        $this->arResult["TOOLBAR_BUTTONS"] = $toolbar_buttons;
        $this->arResult["ACTION_PANEL"] = $this->getActionPanel();

        $this->includeComponentTemplate();

        $APPLICATION->SetTitle(Loc::getMessage('TRANSACTION_LIST_TITLE'));
    }

    public function getEntityRepository()
    {
        return new TransactionTable();
    }

    public function initNav($grid_options)
    {
        $navParams = $grid_options->GetNavParams();

        $grid_id = $grid_options->getid();

        $nav = new UI\PageNavigation($grid_id);

        $pageSizes = [];
        foreach (["5", "10", "20", "30", "50", "100"] as $index) {
            $pageSizes[] = ['NAME' => $index, 'VALUE' => $index];
        }

        $nav->allowAllRecords(true)
            ->setPageSize($navParams['nPageSize'])
            ->setPageSizes($pageSizes)
            ->initFromUri();

        return $nav;
    }

    public function getSorting($grid)
    {
        $sort = $grid->GetSorting([
            'sort' => [
                'ID' => 'DESC'
            ],
            'vars' => [
                'by' => 'by',
                'order' => 'order'
            ]
        ]);

        return $sort['sort'];
    }

    public function getEntityFilter($grid_id, $grid_filter)
    {
        return $this->prepareFilter($grid_id, $grid_filter);
    }

    public function getEntitySelect()
    {
        return ['*'];
    }

    public function getPreparedElement($fields)
    {
        return $fields;
    }

    public function getElementActions($fields)
    {
        $actions = [];

        return $actions;
    }

    private function getFilterFields(): array
    {
        $filterFields = [
            [
                'id' => 'FIRST_NAME',
                'name' => Loc::getMessage('COLUMN_FIRST_NAME'),
                'type' => 'string',
                'default' => true
            ],
            [
                'id' => 'LAST_NAME',
                'name' => Loc::getMessage('COLUMN_LAST_NAME'),
                'type' => 'string',
                'default' => true
            ],
            [
                'id' => 'DEPARTMENT',
                'name' => Loc::getMessage('COLUMN_DEPARTMENT'),
                'type' => 'string',
                'default' => true
            ],
            [
                'id' => 'PUNCH_STATE_DISPLAY',
                'name' => Loc::getMessage('COLUMN_PUNCH_STATE_DISPLAY'),
                'type' => 'string',
                'default' => true
            ],
            [
                'id' => 'VERIFY_TYPE_DISPLAY',
                'name' => Loc::getMessage('COLUMN_VERIFY_TYPE_DISPLAY'),
                'type' => 'string',
                'default' => true
            ],
            [
                'id' => 'AREA_ALIAS',
                'name' => Loc::getMessage('COLUMN_AREA_ALIAS'),
                'type' => 'string',
                'default' => true
            ],
            [
                'id' => 'TERMINAL_SN',
                'name' => Loc::getMessage('COLUMN_TERMINAL_SN'),
                'type' => 'string',
                'default' => true
            ],
            [
                'id' => 'IS_MASK',
                'name' => Loc::getMessage('COLUMN_IS_MASK'),
                'type' => 'string',
                'default' => true
            ],
            [
                'id' => 'UPLOAD_TIME',
                'name' => Loc::getMessage('COLUMN_UPLOAD_TIME'),
                'type' => 'date',
                'default' => true
            ],
            [
                'id' => 'LATE',
                'name' => Loc::getMessage('FILTER_LATE'),
                'type' => 'list',
                'items' => [
                    'Y' => Loc::getMessage('FILTER_LATE_Y'),
                    'N' => Loc::getMessage('FILTER_LATE_N'),
                ],
                'default' => true
            ],
        ];

        return $filterFields;
    }

    private function getGridColumns(): array
    {
        $columns = [
            [
                'id' => 'FIRST_NAME',
                'name' => Loc::getMessage('COLUMN_FIRST_NAME'),
                'sort' => 'FIRST_NAME',
                'default' => true
            ],
            [
                'id' => 'LAST_NAME',
                'name' => Loc::getMessage('COLUMN_LAST_NAME'),
                'sort' => 'LAST_NAME',
                'default' => true
            ],
            [
                'id' => 'DEPARTMENT',
                'name' => Loc::getMessage('COLUMN_DEPARTMENT'),
                'sort' => 'DEPARTMENT',
                'default' => true
            ],
            [
                'id' => 'PUNCH_STATE_DISPLAY',
                'name' => Loc::getMessage('COLUMN_PUNCH_STATE_DISPLAY'),
                'sort' => 'PUNCH_STATE_DISPLAY',
                'default' => true
            ],
            [
                'id' => 'VERIFY_TYPE_DISPLAY',
                'name' => Loc::getMessage('COLUMN_VERIFY_TYPE_DISPLAY'),
                'sort' => 'VERIFY_TYPE_DISPLAY',
                'default' => true
            ],
            [
                'id' => 'AREA_ALIAS',
                'name' => Loc::getMessage('COLUMN_AREA_ALIAS'),
                'sort' => 'AREA_ALIAS',
                'default' => true
            ],
            [
                'id' => 'TERMINAL_SN',
                'name' => Loc::getMessage('COLUMN_TERMINAL_SN'),
                'sort' => 'TERMINAL_SN',
                'default' => true
            ],
            [
                'id' => 'IS_MASK',
                'name' => Loc::getMessage('COLUMN_IS_MASK'),
                'sort' => 'IS_MASK',
                'default' => true
            ],
            [
                'id' => 'UPLOAD_TIME',
                'name' => Loc::getMessage('COLUMN_UPLOAD_TIME'),
                'sort' => 'UPLOAD_TIME',
                'default' => true
            ],
        ];

        return $columns;
    }

    private function getToolbarButtons(): array
    {
        $buttons = [];

        if ($this->accessManager->can($this->userId, 'UpdateTransactions')) {
            $buttons[] = new Button(
                [
                    "text" => Loc::getMessage('UPDATE_BUTTON'),
                    "className" => "ui-btn ui-btn-light-border ui-btn-themes",
                    "click" => new JsCode("BX.Custom.ZKTConnect.Transaction.List.showUpdatePopup()")
                ]
            );
        }

        return $buttons;
    }

    private function getActionPanel(): array
    {
        $panel = [];

        return $panel;
    }

    private function prepareFilter($grid_id, $grid_filter): array
    {
        $filter = [];

        $filterOption = new \Bitrix\Main\UI\Filter\Options($grid_id);
        $filterData = $filterOption->getFilter([]);

        foreach ($filterData as $k => $v) {
            $filter[$k] = $v;
        }

        $filterPrepared = \Bitrix\Main\UI\Filter\Type::getLogicFilter($filter, $grid_filter);

        if (!empty($filter['FIND'])) {
            $findFilter = [
                '%FIRST_NAME' => $filter['FIND']
            ];

            if (!empty($filterPrepared)) {
                $filterPrepared[] = $findFilter;
            } else {
                $filterPrepared = $findFilter;
            }
        }

        foreach (['FIRST_NAME', 'LAST_NAME'] as $key) {
            if (!empty($filterPrepared[$key])) {
                $filterPrepared['%' . $key] = $filterPrepared[$key];
                unset($filterPrepared[$key]);
            }
        }

        if (isset($filterPrepared['LATE'])) {
            $transactions = TransactionTable::getList([
                'select' => [
                    'ID', 'PUNCH_TIME'
                ]
            ])->fetchAll();

            $timediff = Option::get('custom.zktconnect', 'time_diff');

            foreach ($transactions as $transaction) {
                $punchTime = ($transaction['PUNCH_TIME'])->format('H:i');
                $startTime = ((new DateTime('09:00', 'H:i'))->add($timediff . ' hours'))->format('H:i');
                $isLate = $punchTime > $startTime;

                if (
                    ($filterPrepared['LATE'] == 'Y' && $isLate)
                    || ($filterPrepared['LATE'] == 'N' && !$isLate)
                ) {
                    if (!is_array($filterPrepared['ID'])) {
                        $filterPrepared['ID'] = [];
                    }
                    if (!in_array($transaction['ID'], $filterPrepared['ID'])) {
                        $filterPrepared['ID'][] = $transaction['ID'];
                    }
                }
            }

            unset($filterPrepared['LATE']);
        }

        return $filterPrepared;
    }

    public function updateAction(array $fields): void
    {
        if (isset($fields['DATE_START'])) {
            $dateStartObject = \DateTime::createFromFormat('d.m.Y', $fields['DATE_START']);
            $dateStart = $dateStartObject->format('Y-m-d');
        } else {
            $dateStart = (new DateTime())->format('Y-m-d');
        }

        if (isset($fields['DATE_END'])) {
            $dateEndObject = \DateTime::createFromFormat('d.m.Y', $fields['DATE_END']);
            $dateEnd = $dateEndObject->format('Y-m-d');
        } else {
            $dateEnd = (new DateTime())->format('Y-m-d');
        }

        $this->service->updateTransactionList($dateStart, $dateEnd);
    }
}
