<?require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\UI\PageNavigation;
use lsr\WebformTable;

$APPLICATION->SetTitle("Запросы формы обратной связи");

$gridId = 'form_requests';

$grid_options = new GridOptions($gridId);
$sort = $grid_options->GetSorting(['sort' => ['ID' => 'DESC'], 'vars' => ['by' => 'by', 'order' => 'order']]);
$nav_params = $grid_options->GetNavParams();

$nav = new PageNavigation($gridId);
$nav->allowAllRecords(true)->setPageSize($nav_params['nPageSize'])->initFromUri();

$res = WebformTable::getList([
    'select' => [
        'ID',
        "NAME",
        "EMAIL",
        "PHONE",
    ],
    'offset'      => $nav->getOffset(),
    'limit'       => $nav->getLimit(),
    'order'       => $sort['sort'],
]);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

$list = [];
$columns = [];
$columns[] = ['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => true];
$columns[] = ['id' => 'NAME', 'name'  => 'Имя', 'sort' => 'NAME', 'default' => true];
$columns[] = ['id' => 'EMAIL', 'name' => 'Почта', 'sort' => 'EMAIL', 'default' => true];
$columns[] = ['id' => 'PHONE', 'name' => 'Телефон', 'sort' => 'PHONE', 'default' => true];

foreach ($res->fetchAll() as $row) {
    $list[] = [
        'data' => [
            "ID" => $row['ID'],
            "NAME" => $row['NAME'],
            "EMAIL" => $row['EMAIL'],
            "PHONE" => $row['PHONE'],
        ],
    ];
}


$APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
    'GRID_ID' => $gridId,
    'COLUMNS' => $columns,
    'ROWS' => $list,
    'SHOW_ROW_CHECKBOXES' => false,
    'NAV_OBJECT' => $nav,
    'AJAX_MODE' => 'Y',
    'AJAX_ID' => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
    'PAGE_SIZES' => [
        ['NAME' => '20', 'VALUE' => '20'],
        ['NAME' => '50', 'VALUE' => '50'],
        ['NAME' => '100', 'VALUE' => '100']
    ],
    'AJAX_OPTION_JUMP' => 'N',
    'SHOW_CHECK_ALL_CHECKBOXES' => false,
    'SHOW_ROW_ACTIONS_MENU' => true,
    'SHOW_GRID_SETTINGS_MENU' => true,
    'SHOW_NAVIGATION_PANEL' => true,
    'SHOW_PAGINATION' => true,
    'SHOW_SELECTED_COUNTER' => true,
    'SHOW_TOTAL_COUNTER' => true,
    'SHOW_PAGESIZE' => true,
    'SHOW_ACTION_PANEL' => true,
    'ALLOW_COLUMNS_SORT' => true,
    'ALLOW_COLUMNS_RESIZE' => true,
    'ALLOW_HORIZONTAL_SCROLL' => true,
    'ALLOW_SORT' => true,
    'ALLOW_PIN_HEADER' => true,
    'AJAX_OPTION_HISTORY' => 'N'
]);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';