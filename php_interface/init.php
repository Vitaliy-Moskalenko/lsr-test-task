<?php

use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;
use Bitrix\Main\Application;
use lsr\WebformTable;

Loader::registerNamespace('lsr', Loader::getDocumentRoot().'/local/components/lsr/webform');

// Создать новую таблицу в базе данных если такой еще нет
if (!Application::getConnection()->isTableExists(WebformTable::getTableName())) {
	WebformTable::getEntity()->createDbTable();
}

// Скопировать файл отображения результатов вебформы для вывода в админке
if(!is_readable($_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin/lsrwebformresult.php")){
	if(!CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/local/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin", true, true)) exit('fail');
} 

// Добавиить новый пункт меню в админке в разделе Контент
function ModifyAdminMenu($adminMenu, &$moduleMenu) {
	$moduleMenu[] = [
		'parent_menu' => 'global_menu_content',
		'sort' => 100,
		'text' => "Форма обратной связи",
		"items_id" => "menu_lsrform",
		"icon" => "form_menu_icon",
		"items" => [
			[
			'text' => 'Форма обратной связи',
			'url' => 'lsrwebformresult.php',
			"icon" => "iblock_menu_icon_iblocks"
			]
		]
	];
}

$eventManager = EventManager::getInstance();
$eventManager->addEventHandler('main', 'OnBuildGlobalMenu', 'ModifyAdminMenu');

