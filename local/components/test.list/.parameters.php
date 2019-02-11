<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var array $arCurrentValues */

if(!CModule::IncludeModule("iblock"))
    return;

$arTypesEx = CIBlockParameters::GetIBlockTypes(array("-"=>" "));

$arIBlocks=array();
$db_iblock = CIBlock::GetList(array("SORT"=>"ASC"), array("SITE_ID"=>$_REQUEST["site"], "TYPE" => ($arCurrentValues["IBLOCK_TYPE"]!="-"?$arCurrentValues["IBLOCK_TYPE"]:"")));
while($arRes = $db_iblock->Fetch())
    $arIBlocks[$arRes["ID"]] = "[".$arRes["ID"]."] ".$arRes["NAME"];

$arComponentParameters = [
    "GROUPS" => [],
    "PARAMETERS" => [
        "IBLOCK_TYPE" =>  [
            "PARENT" => "BASE",
            "NAME" => 'Тип инфоблока',
            "TYPE" => "LIST",
            "VALUES" => $arTypesEx,
            "DEFAULT" => "",
            "REFRESH" => "Y",
        ],
        "IBLOCK_ID" => [
            "PARENT" => "BASE",
            "NAME" => 'Инфоблок*',
            "TYPE" => "LIST",
            "VALUES" => $arIBlocks,
            "DEFAULT" => '',
            "ADDITIONAL_VALUES" => "Y",
            "REFRESH" => "Y",
        ],
        "PAGE_SIZE" => [
            "PARENT" => "BASE",
            "NAME" => 'Элементов на странице*',
            "TYPE" => "STRING",
            "DEFAULT" => "10",
        ],
        "AJAX_PAGE_SIZE" => [
            "PARENT" => "BASE",
            "NAME" => 'Кол-во элементов подгружаемых по нажатию кнопки "Еще"*',
            "TYPE" => "STRING",
            "DEFAULT" => "3",
        ],
        "NAV_TEMPLATE_PATH" => [
            "PARENT" => "BASE",
            "NAME" => 'Путь к шаблону постраничной навигации относительно корня сайта',
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
        "CACHE_TIME"  =>  [
            "DEFAULT"=>36000000
        ],
    ]
];