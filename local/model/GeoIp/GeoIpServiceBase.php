<?php
declare(strict_types = 1);

namespace Local\GeoIp;

abstract class GeoIpServiceBase implements GeoIpServiceInterface {
    private const CACHE_TIME = 10; //секунд
    private const CACHE_ID_PREFIX = 'GEO_API_AJAX';

    abstract protected function __getGeoData(string $ip): GeoIpResponse;
    abstract protected function getUniqueCacheId(): string;

    protected function getCacheTime(): int{
        return self::CACHE_TIME;
    }

    public function getGeoData(string $ip): GeoIpResponse
    {
        if (!$this->isValidIp($ip)){
            return (new GeoIpResponse())
                ->setIsSuccess(false)
                ->setErrorMessage('Invalid IP address');
        }

        //Получаем данные по IP
        $objCacheEngine = new \CPHPCache;
        $strCacheId = self::CACHE_ID_PREFIX . '_' . $this->getUniqueCacheId() . '_' . $ip;
        try {
            if ($objCacheEngine->StartDataCache(self::CACHE_TIME, $strCacheId)) {
                //Либо забираем у стороннего сервиса и кешируем
                $objResponse = $this->__getGeoData($ip);

                $objCacheEngine->EndDataCache($objResponse);

            } else {
                //Либо берем закешированное
                $objResponse = $objCacheEngine->GetVars();
            }
        }
        catch (\Exception $e){
            return (new GeoIpResponse())
                ->setIsSuccess(false)
                ->setErrorMessage($e->__toString());
        }

        return $objResponse;
    }

    private function isValidIp(string $ip): bool
    {
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
}