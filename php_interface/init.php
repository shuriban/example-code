<?php
require_once 'firstbit/include.php';

/*
 * Сохранение разделов.
 */
AddEventHandler("iblock", "OnBeforeIBlockElementUpdate", "OnBeforeIBlockElementUpdateHandler");

function OnBeforeIBlockElementUpdateHandler(&$arFields)
{

    if ($arFields['IBLOCK_ID'] != 22) {
        return;
    }

    if (!empty($_GET['mode']) && $_GET['mode'] == 'import') {
        $db_old_groups = CIBlockElement::GetElementGroups($arFields['ID'], true);
        while ($ar_group = $db_old_groups->Fetch()) {
            if (!in_array($ar_group['ID'], $arFields['IBLOCK_SECTION']))
                $arFields['IBLOCK_SECTION'][] = $ar_group['ID'];
        }
    }
}
/*
 * Не активировать раздел
 */
AddEventHandler("iblock", "OnBeforeIBlockSectionUpdate","DoNotUpdateSect");
function DoNotUpdateSect(&$arFields)
{
    if ($_REQUEST['mode']=='import')
    {

        unset($arFields['ACTIVE']);

    }
}