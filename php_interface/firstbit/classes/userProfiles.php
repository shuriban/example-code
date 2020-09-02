<?php

namespace FirstBit;

class UserProfiles
{
        public static function GetConfirmProfiles(&$arResult, $USER)
        {
            foreach ($arResult['ORDER_PROP']['USER_PROFILES'] as $profile){
                $parameters =  array(
                    'filter' => array(
                        "ID" => $profile['ID'],
                        "USER_ID" => $USER->GetID()
                    ),
                    'select' => ['*']
                );
                $profileProperties = \Bitrix\Sale\OrderUserProperties::getList($parameters);

                while ($arUserProfiles = $profileProperties->fetch()){

                    $db_propValues = \CSaleOrderUserPropsValue::GetList(
                        array("ID" => "ASC"),
                        array(
                            "USER_PROPS_ID" => $profile['ID'],
                            "PROP_CODE" => "VERIFIED_PROFILE"
                        ),
                        false,
                        false,
                        ['PROP_CODE', 'VALUE']
                    );
                    while ($arPropValues = $db_propValues->Fetch())
                    {
                        if (($arPropValues['PROP_CODE'] == 'VERIFIED_PROFILE') && ($arPropValues['VALUE'] !== 'Y')){
                            unset($arResult['JS_DATA']['USER_PROFILES'][$arUserProfiles['ID']]);
                            unset($arResult['ORDER_PROP']['USER_PROFILES'][$arUserProfiles['ID']]);
                            //Проверяем, были ли они изменены
                            //var_dump($arPropValues);
                        }
                    }
                }


            }

            foreach ($arResult['JS_DATA']['ORDER_PROP']['properties'] as $key => $prop){
                if ($prop['PROPS_GROUP_ID']==NOT_EDIT_PROPS_GROUP){
                    unset($arResult['JS_DATA']['ORDER_PROP']['properties'][$key]);

                    if ($prop['CODE'] == 'VERIFIED_PROFILE' && $prop['VALUE'][0]== 'N'){

                    }
                }
                if ($prop['PROPS_GROUP_ID']==CLEAR_VALUE_PROPS_GROUP){
                    $arResult['JS_DATA']['ORDER_PROP']['properties'][$key]['VALUE'][0] = '';
                }
            }
            //Пренудительный выбор только подтвержденного профился, если такой имеется
            if (!empty($arResult['ORDER_PROP']['USER_PROFILES'])){
                $arCurrentProfile = array_shift($arResult['ORDER_PROP']['USER_PROFILES']);
                if ($arCurrentProfile['CHECKED'] !== 'Y'){
                    \Redsign\B2BPortal\Services\Sale\PersonalProfile::setSelectedProfile($arCurrentProfile['ID']);
                }
            }
        }
}
