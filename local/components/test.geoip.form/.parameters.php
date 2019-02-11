<?php
$objGeoIpFactory = new \Local\GeoIp\GeoIpFactory();

$arGeoIpServices = [
    0 => ''
];
foreach ($objGeoIpFactory->getServices() as $arService){
    $arGeoIpServices[$arService['ID']] = $arService['DESCRIPTION'];
}

$arComponentParameters = [
    'GROUPS' => [
        'SETTINGS' => [
            'NAME' => 'Параметры'
        ]
    ],
    'PARAMETERS' => [
        'GEO_IP_SERVICE_ID' => [
            'PARENT' => 'SETTINGS',
            'NAME' => 'GeoIp сервис',
            'TYPE' => 'LIST',
            'VALUES' => $arGeoIpServices
        ]
    ]
];