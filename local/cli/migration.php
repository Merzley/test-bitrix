#!/usr/bin/php
<?php
$_SERVER['DOCUMENT_ROOT'] = __DIR__.'/../..';
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

CModule::IncludeModule('iblock');

const IBLOCK_TYPE_ID = 'Test';

//Список сайтов
$objSitesList = CSite::GetList($by='sort', $order='desc');
$arSitesId = [];
while ($arSite = $objSitesList->GetNext()){
    $arSitesId[] = $arSite['LID'];
}

//Тип инфоблоков
$arIblockTypeFields = [
    'ID' => IBLOCK_TYPE_ID,
    'LANG' => [
        'ru' => [
            'NAME' => 'Тест'
        ],
        'en' => [
            'NAME' => 'Test'
        ]
    ],
];
$objIblockType = new CIBlockType();
$objIblockType->Add($arIblockTypeFields);

//Инфоблок с элементами списков
$arIblockFields = [
    'IBLOCK_TYPE_ID' => IBLOCK_TYPE_ID,
    'SITE_ID' => $arSitesId,
    'NAME' => 'Элементы списка'
];
$objIblock = new CIBlock();
$nListsElementsIblockId = $objIblock->Add($arIblockFields);

//Инфоблок со списками
$arIblockFields = [
    'IBLOCK_TYPE_ID' => IBLOCK_TYPE_ID,
    'SITE_ID' => $arSitesId,
    'NAME' => 'Списки'
];
$objIblock = new CIBlock();
$nListsIblockId = $objIblock->Add($arIblockFields);

//Свойства инфоблока со списками
$arPropertyFields = [
    'IBLOCK_ID' => $nListsIblockId,
    'NAME' => 'Пользователи',
    'CODE' => 'USERS',
    'PROPERTY_TYPE' => 'S',
    'USER_TYPE' => 'UserID',
    'MULTIPLE' => 'Y',
];
$objIblockProperty = new CIBlockProperty();
$objIblockProperty->Add($arPropertyFields);

$arPropertyFields = [
    'IBLOCK_ID' => $nListsIblockId,
    'NAME' => 'Элементы',
    'CODE' => 'ELEMENTS',
    'PROPERTY_TYPE' => 'E',
    'LINK_IBLOCK_ID' => $nListsElementsIblockId,
    'MULTIPLE' => 'Y',
];
$objIblockProperty = new CIBlockProperty();
$objIblockProperty->Add($arPropertyFields);


require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');