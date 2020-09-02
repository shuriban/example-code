<?php

namespace FirstBit;

class Prices
{
   public static function getUserPrices($arUserGroups){
       $arPrices = [];
       if(\CModule::IncludeModule("catalog")) {

           $obPrice = \CCatalogGroup::GetGroupsList(
               ['GROUP_ID' => $arUserGroups]
           );
           while ($arPrice = $obPrice->fetch()) {
               if (!in_array($arPrice['CATALOG_GROUP_ID'], $arPrices)) {
                   $arPrices[] = $arPrice['CATALOG_GROUP_ID'];
               }
           }
           $obPricesCode = \CCatalogGroup::GetList(
               ['SORT' => 'ASC'],
               ['ID' => $arPrices],
               false,
               false,
               false
           );
           $arPricesCode = [];

           while ($arPriceCode = $obPricesCode->fetch()) {
               if ($arPriceCode['NAME']!='BASE'){
                   $arPricesCode[] = $arPriceCode['NAME'];
               }
           }

           define('USER_PRICES', $arPricesCode);

       }
   }
}
