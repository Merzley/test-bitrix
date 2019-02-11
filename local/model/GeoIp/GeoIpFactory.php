<?php
declare(strict_types = 1);

namespace Local\GeoIp;

class GeoIpFactory{
    const SERVICE_IP_STACK_ID = 1;
    const SERVICE_IP_API_ID = 2;

    private $arIdToClassMap = [
        self::SERVICE_IP_STACK_ID => GeoIpServiceIpStack::class,
        self::SERVICE_IP_API_ID => GeoIpServiceIpApi::class
    ];

    public function isAvailableId(int $serviceId): bool{
        return array_key_exists($serviceId, $this->arIdToClassMap);
    }

    public function getServices(){
        return [
            [
                'ID' => self::SERVICE_IP_STACK_ID,
                'DESCRIPTION' => 'ipstack.com'
            ],
            [
                'ID' => self::SERVICE_IP_API_ID,
                'DESCRIPTION' => 'ip-api.com'
            ],
        ];
    }

    public function makeService(int $serviceId): ?GeoIpServiceInterface
    {
        if (!$this->isAvailableId($serviceId))
            return null;

        return new $this->arIdToClassMap[$serviceId]();
    }
}