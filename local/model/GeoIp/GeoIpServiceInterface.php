<?php
declare(strict_types=1);

namespace Local\GeoIp;

interface GeoIpServiceInterface{
    public function getGeoData(string $ip): GeoIpResponse;
}