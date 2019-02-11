<?php
declare(strict_types = 1);

use Bitrix\Main\Engine\Contract\Controllerable;

class GeoIpAjax extends \CBitrixComponent implements Controllerable {
    public function configureActions(){
        return ['getGeoIp' => ['prefilters' => []]];
    }

    /**
     * @param string $ip
     * @param int $service_id
     * @return string JSON
     * @throws Exception
     */
    public function getGeoIpAction(string $ip, int $service_id){
        $objGeoIpFactory = new \Local\GeoIp\GeoIpFactory();
        $objGeoIpService = $objGeoIpFactory->makeService($service_id);

        if ($objGeoIpService == null)
            throw new Exception('Unknown GeoIp service type');

        $objResponse = $objGeoIpService->getGeoData($ip);

        return json_encode(get_object_vars($objResponse));
    }
}
