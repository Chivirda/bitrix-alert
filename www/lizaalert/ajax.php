<?

use Bitrix\Main\Loader;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
Loader::includeModule('iblock');

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    switch ($action) {
        case 'getElementById':
            echo json_encode(getElementById($id));
            break;
        case 'fetchPoints':
            echo json_encode(fetchPoints());
            break;
        default:
            break;
    }
}

function getElementById($id): array
{
    $arSelectFields = ['ID', 'NAME', 'PROPERTY_TYPE', 'PROPERTY_GALLERY', 'PROPERTY_STATUS', 'PROPERTY_COORDS', 'DATE_CREATE'];
    $res = CIBlockElement::GetList(['ID' => $id, 'ACTIVE' => 'Y'], ['IBLOCK_ID' => 9], false, false, $arSelectFields);

    $arResult = [];
    if ($ob = $res->GetNextElement()) {
        $arFields = $ob->GetFields();
        $arFields['PROPERTIES'] = $ob->GetProperties();
        $arResult = $arFields;
    }

    return $arResult;
}

function fetchPoints(): array
{
    $arSelectFields = ['ID', 'NAME', 'PROPERTY_TYPE', 'PROPERTY_GALLERY', 'PROPERTY_STATUS', 'PROPERTY_COORDS', 'DATE_CREATE'];
    $res = CIBlockElement::GetList(['ACTIVE' => 'Y'], ['IBLOCK_ID' => 9], false, false, $arSelectFields);

    $arResult = [];
    while ($ob = $res->GetNextElement()) {
        $arFields = $ob->GetFields();
        $arResult[] = $arFields;
    }

    return $arResult;
}
