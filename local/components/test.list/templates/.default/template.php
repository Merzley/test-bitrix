<?php
/** @var array $arResult */
/** @var \CDBResult $objNavData */
$objNavData = $arResult['NAV_DATA'];
/** @var array $arLists */
$arLists = $arResult['LISTS'];
/** @var string $strNavTemplatePath */
$strNavTemplatePath = $arResult['NAV_TEMPLATE_PATH'];
/** @var array $arAjaxData */
$arAjaxData = $arResult['AJAX_DATA'];

CJSCore::Init(array('ajax'));
?>

<div id="test-list-container">
<table>
    <tr>
        <th>Название</th>
        <th>Дата изменения</th>
        <th>Пользователи</th>
        <th>Элементы</th>
    </tr>
    <?php foreach ($arLists as $arList):?>
        <?php
        $arElements = [];
        foreach ($arList['ELEMENTS'] as $arElementData){
            $arElements[] = '('.$arElementData['ID'].') '.$arElementData['NAME'];
        }
        ?>
        <tr>
            <td><?=$arList['NAME']?></td>
            <td><?=$arList['TIMESTAMP_X']?></td>
            <td>
                <ul>
                <?php foreach ($arList['USERS_FIO'] as $strFio):?>
                    <li><?=$strFio?></li>
                <?php endforeach;?>
                </ul>
            </td>
            <td>
                <ul>
                    <?php foreach ($arList['ELEMENTS'] as $arElement):?>
                        <li>(<?=$arElement['ID']?>) <?=$arElement['NAME']?></li>
                    <?php endforeach;?>
                </ul>
            </td>
        </tr>
    <?php endforeach;?>
</table>

<button id="button-more"
        type="button"
>
    Еще
</button>

<div class="nav-container">
    <?php
    $objNavData->NavPrint('', false, '', $strNavTemplatePath);
    ?>
</div>

</div>

<script>
    window.testList = {
        mainPageNum: <?=$arAjaxData['MAIN_PAGE_NUM']?>,
        mainPageSize: <?=$arAjaxData['MAIN_PAGE_SIZE']?>,
        ajaxPageSize: <?=$arAjaxData['AJAX_PAGE_SIZE']?>,
        iblockId: <?=$arAjaxData['IBLOCK_ID']?>,
    };
</script>
