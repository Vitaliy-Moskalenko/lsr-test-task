<?php

namespace lsr;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields;
use \Bitrix\Main\Entity;
use \Bitrix\Main\Type;


class WebformTable extends DataManager {
    public static function getTableName() {
        return 'form_requests';
    }
	
    public static function getMap() {
        return [
            new Fields\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
                'title' => Loc::getMessage('FORM_ID'),
            ]),
            new Fields\StringField('NAME', [
                'required' => true,
                'title' => Loc::getMessage('FORM_NAME'),
            ]),
            new Fields\StringField('PHONE', [
                'required' => true,
                'title' => Loc::getMessage('FORM_PHONE'),
            ]),
            new Fields\StringField('EMAIL', [
				'required' => true,
                'title' => Loc::getMessage('FORM_EMAIL'),
            ])
        ];
    }
}