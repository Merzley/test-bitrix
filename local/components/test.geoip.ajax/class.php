<?php
use Bitrix\Main\Engine\Contract\Controllerable;

class GeoIpAjax extends \CBitrixComponent implements Controllerable {
    const CACHE_TIME = 10; //секунд
    const CACHE_ID_PREFIX = 'GEO_API_AJAX_';

    public function configureActions(){}

    /**
     * @param string $ip
     * @return string JSON
     *
     * ФОРМАТ ОТВЕТА
     *{
     * success: bool,      Удалось ли получить данные по IP
     * error:   string,    Строка с ошибкой. Если ошибок нет - данного ключа в ответе не будет
     * data:    array      Собсвтенно, массив c данными по IP
     *}
     */
    public function getGeoIpAction(string $ip){
        //Проверяем пришедший IP на валидность
        if (!$this->isValidIp($ip)) {
            return json_encode([
                'success' => false,
                'error' => 'Invalid IP address',
                'data' => []
            ]);
        }

        //Получаем данные по IP
        $objCacheEngine = new CPHPCache;
        $strCacheId = self::CACHE_ID_PREFIX . $ip;
        try {
            if ($objCacheEngine->StartDataCache(self::CACHE_TIME, $strCacheId)) {
                //Либо забираем у стороннего сервиса и кешируем
                $arResult = $this->processSoapRequest($ip);
                $objCacheEngine->EndDataCache($arResult);

            } else {
                //Либо берем закешированное
                $arResult = $objCacheEngine->GetVars();
            }
        }
        catch (Exception $e){
            return json_encode([
                'success' => false,
                'error' => $e,
                'data' => []
            ]);
        }

        return json_encode([
            'success' => true,
            'data' => $arResult
        ]);
    }

    /**
     * @param string $ip
     * @return bool
     *
     * Валидатор IP адреса
     */
    private function isValidIp(string $ip){
        $arOctets = explode('.', $ip);

        if (count($arOctets) !== 4)
            return false;

        foreach ($arOctets as $strOctets){
            if ($strOctets == '')
                return false;

            if (intval($strOctets) > 255)
                return false;
        }

        return true;
    }

    /**
     * @param string $ip
     * @return array
     *
     * Заглушка для доступа к недоступному сейчас сервису
     */
    private function processSoapRequest(string $ip){
        return [
            'first' => 'Какая-то',
            'second' => 'информация по IP '.$ip.'.',
            'third' => 'Рандомное число для проверки кеширования: ',
            'fourth' => rand(1, 1000).' ('.self::CACHE_TIME.' секунд)'
        ];
    }
}
