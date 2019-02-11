<?php

class GeoIpForm extends \CBitrixComponent {
    public function executeComponent(){
        $objGeoIpFactory = new \Local\GeoIp\GeoIpFactory();

        if ((!array_key_exists('GEO_IP_SERVICE_ID', $this->arParams)) ||
            (!$objGeoIpFactory->isAvailableId(intval($this->arParams['GEO_IP_SERVICE_ID']))))
        {
            $this->setTemplateName('unknown-service-id');
        }


        $this->includeComponentTemplate();
    }
}
