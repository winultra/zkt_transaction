<?php

namespace Custom\ZKTConnect\Internal;

use Bitrix\Main\ORM\Data;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;

class TransactionTable extends Data\DataManager
{

    public static function getTableName()
    {
        return 'c_zktconnect_transaction';
    }

    public static function getMap()
    {
        return [
            (new IntegerField('ID'))
                ->configurePrimary()
                ->configureAutocomplete(),
            (new IntegerField('TRANSACTION_ID')),
            (new IntegerField('EMP_ID')),
            (new StringField('EMP_CODE')),
            (new StringField('FIRST_NAME')),
            (new StringField('LAST_NAME')),
            (new StringField('DEPARTMENT')),
            (new StringField('POSITION')),
            (new DatetimeField('PUNCH_TIME')),
            (new StringField('PUNCH_STATE')),
            (new StringField('PUNCH_STATE_DISPLAY')),
            (new IntegerField('VERIFY_TYPE')),
            (new StringField('VERIFY_TYPE_DISPLAY')),
            (new StringField('WORK_CODE')),
            (new StringField('GPS_LOCATION')),
            (new StringField('AREA_ALIAS')),
            (new StringField('TERMINAL_SN')),
            (new IntegerField('TEMPERATURE')),
            (new StringField('IS_MASK')),
            (new StringField('TERMINAL_ALIAS')),
            (new DatetimeField('UPLOAD_TIME')),
        ];
    }
}
