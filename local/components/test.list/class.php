<?php

use Bitrix\Main\Engine\Contract\Controllerable;

class TestList extends \CBitrixComponent implements Controllerable {
    public function __construct(?CBitrixComponent $component = null)
    {
        parent::__construct($component);
        CModule::IncludeModule("iblock");
    }

    public function configureActions(){
        return ['getMore' => ['prefilters' => []]];
    }

    public function getMoreAction(int $nPageNum, int $nPageSize, int $nMainPageNum, int $nMainPageSize, int $nIblockId){
        $nShowedElementsCount = $nMainPageNum * $nMainPageSize;

        $nElementsFromNextPage = $nShowedElementsCount % $nPageSize;

        $nRealPage = floor($nShowedElementsCount / $nPageSize)+$nPageNum;

        $objListsQuery = $this->requestLists($nIblockId, $nRealPage, $nPageSize);
        if ($objListsQuery->NavPageCount < $nRealPage)
            return json_encode([]);

        $arLists = $this->fillListsData($objListsQuery, $nElementsFromNextPage);
        if ($nElementsFromNextPage !== 0){
            $nRealPage++;
            $objListsQuery = $this->requestLists($nIblockId, $nRealPage, $nPageSize);
            if ($objListsQuery->NavPageCount < $nRealPage)
                $arLists2 = [];
            else
                $arLists2 = $this->fillListsData($objListsQuery, 0, $nElementsFromNextPage);

            $arLists = array_merge($arLists, $arLists2);
        }

        return json_encode($arLists);
    }

    private function checkParams(): bool
    {
        if (!isset($this->arParams['PAGE_SIZE']) ||
            !is_numeric($this->arParams['PAGE_SIZE']) ||
            (!intval($this->arParams['PAGE_SIZE']) > 0)
        ){
            return false;
        }

        if (!isset($this->arParams['AJAX_PAGE_SIZE']) ||
            !is_numeric($this->arParams['AJAX_PAGE_SIZE']) ||
            (!intval($this->arParams['AJAX_PAGE_SIZE']) > 0)
        ){
            return false;
        }

        if (!isset($this->arParams['CACHE_TYPE']) || ($this->arParams['CACHE_TYPE'] == null))
            return false;

        if (!isset($this->arParams['CACHE_TIME']) || !is_numeric($this->arParams['CACHE_TIME']))
            return false;

        if (!isset($this->arParams['IBLOCK_ID']) || !is_numeric($this->arParams['IBLOCK_ID']))
            return false;

        return true;
    }

    private function requestLists(int $nIblockId, int $nPageNum, int $nPageSize): \CDBResult{
        $arFilter = [
            'IBLOCK_ID' => $nIblockId
        ];
        $arSelectedFields = [
            'ID',
            'IBLOCK_ID',
            'NAME',
            'TIMESTAMP_X'
        ];

        return \CIBlockElement::GetList(
            [],
            $arFilter,
            false,
            ['nPageSize' => $nPageSize, 'iNumPage' => $nPageNum],
            $arSelectedFields
        );
    }

    private function fillListsData(\CDBResult $objListsQuery, int $nSkip = 0, ?int $nCount = null): array{
        $arLists = [];

        while ($arList = $objListsQuery->GetNext())
        {
            if ($nSkip > 0){
                $nSkip--;
                continue;
            }

            $arUsersId = $this->getMultiplePropertyValues(
                'USERS', intval($arList['IBLOCK_ID']), intval($arList['ID'])
            );
            $arElementsId = $this->getMultiplePropertyValues(
                'ELEMENTS', intval($arList['IBLOCK_ID']), intval($arList['ID'])
            );


            $arList['USERS_FIO'] = $this->getUsersFioArray($arUsersId);
            $arList['ELEMENTS'] = $this->getListElementsArray($arElementsId);

            $arLists[] = $arList;

            if ($nCount !== null){
                $nCount--;
                if ($nCount === 0)
                    break;
            }
        }

        return $arLists;
    }

    private function getMultiplePropertyValues(string $strProperty, int $nIblockId, int $nId): array{
        $arResult = [];

        $objProperties = \CIBlockElement::GetProperty(
            $nIblockId,
            $nId,
            '', '',
            [
                'CODE' => $strProperty
            ]
        );

        while ($arProperty = $objProperties->GetNext()) if ($arProperty['VALUE'] != null){
            $arResult[] = $arProperty['VALUE'];
        }

        return $arResult;
    }

    private function getUsersFioArray(array $arUsersId): array
    {
        if (empty($arUsersId))
            return [];

        $arResult = [];

        $objUsers = \CUser::GetList(
            ($by = ''), ($order = ''),
            ['ID' => implode('|', $arUsersId)]
        );

        while ($arUser = $objUsers->GetNext()) {
            $strFio = '';
            if ($arUser['LAST_NAME'] != '')
                $strFio = $arUser['LAST_NAME'];

            if ($arUser['NAME'] != '') {
                if ($strFio != '')
                    $strFio .= ' ' . $arUser['NAME'];
                else
                    $strFio = $arUser['NAME'];
            }

            if ($arUser['SECOND_NAME'] != '') {
                if ($strFio != '')
                    $strFio .= ' ' . $arUser['SECOND_NAME'];
                else
                    $strFio = $arUser['SECOND_NAME'];
            }

            if ($strFio == '')
                $strFio = "($arUser[ID])";

            $arResult[] = $strFio;
        }

        return $arResult;
    }

    private function getListElementsArray(array $arElementsId): array
    {
        if (empty($arElementsId))
            return [];

        $arResult = [];

        $arElementsFilter = [
            'ID' => $arElementsId
        ];
        $arElementsSelectedFields = [
            'ID',
            'NAME'
        ];

        $objListElements = \CIBlockElement::GetList(
            [], $arElementsFilter, false, false, $arElementsSelectedFields
        );

        while($arListElement = $objListElements->GetNext()){
            $arResult[] = $arListElement;
        }

        return $arResult;
    }

    public function executeComponent(){
        if (!$this->checkParams())
            return;

        $nCacheTime = intval($this->arParams['CACHE_TIME']);
        $nPageSize = intval($this->arParams['PAGE_SIZE']);
        if (!isset($_REQUEST['PAGEN_1']) || !(intval($_REQUEST['PAGEN_1']) > 0))
            $nPageNum = 1;
        else
            $nPageNum = intval($_REQUEST['PAGEN_1']);

        $arAdditionalCacheId = [
            'PAGE_SIZE' => $nPageSize,
            'PAGE_NUM' => $nPageNum
        ];

        if ($this->startResultCache($nCacheTime, $arAdditionalCacheId)){
            $nIblockId = intval($this->arParams['IBLOCK_ID']);
            $nAjaxPageSize = intval($this->arParams['AJAX_PAGE_SIZE']);

            if (!isset($this->arParams['NAV_TEMPLATE_PATH']) || ($this->arParams['NAV_TEMPLATE_PATH'] == ''))
                $strNavTemplatePath = false;
            else
                $strNavTemplatePath = $_SERVER['DOCUMENT_ROOT'].$this->arParams['NAV_TEMPLATE_PATH'];

            $objListsQuery = $this->requestLists($nIblockId, $nPageNum, $nPageSize);
            $arLists = $this->fillListsData($objListsQuery);

            $this->arResult = [
                'NAV_DATA' => $objListsQuery,
                'LISTS' => $arLists,
                'NAV_TEMPLATE_PATH' => $strNavTemplatePath,
                'AJAX_DATA' => [
                    'MAIN_PAGE_NUM' => $objListsQuery->NavPageNomer,
                    'MAIN_PAGE_SIZE' => $objListsQuery->NavPageSize,
                    'AJAX_PAGE_SIZE' => $nAjaxPageSize,
                    'IBLOCK_ID' => $nIblockId,
                ],
            ];

            $this->includeComponentTemplate();
        }
    }
}
