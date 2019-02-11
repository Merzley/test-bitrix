<?php
declare(strict_types = 1);

namespace Local\GeoIp;

class GeoIpServiceIpStack extends GeoIpServiceBase {
    private const API_KEY = '418cdc25b3825260f90f397b99ea3def';

    protected function getUniqueCacheId(): string
    {
        return 'IpStack';
    }

    private function doRequest(string $ip): ?string{
        $strUrl = 'http://api.ipstack.com/'.$ip.'?access_key='.self::API_KEY;

        $curl = \curl_init();
        \curl_setopt($curl, CURLOPT_URL, $strUrl);
        \curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

        $response = \curl_exec($curl);

        \curl_close($curl);

        if ($response === false)
            $response = null;

        return $response;
    }

    private function makeCommonResponse(?string $strServiceResponse): GeoIpResponse{
        if ($strServiceResponse === null){
            return (new GeoIpResponse())
                ->setIsSuccess(false)
                ->setErrorMessage('Can\'t send request');
        }

        $arResponse = json_decode($strServiceResponse, true);
        if ($arResponse === null){
            return (new GeoIpResponse())
                ->setIsSuccess(false)
                ->setErrorMessage('Can\'t parse response');
        }

        if (array_key_exists('success', $arResponse)){
            return (new GeoIpResponse())
                ->setIsSuccess(false)
                ->setErrorMessage($strServiceResponse);
        }

        return $this->fillCommonResponse($arResponse);
    }

    private function fillCommonResponse(array $arServiceResponse): GeoIpResponse
    {
        $objCommonResponse = new GeoIpResponse();

        if (
            !array_key_exists('country_name', $arServiceResponse) ||
            !array_key_exists('region_name', $arServiceResponse) ||
            !array_key_exists('city', $arServiceResponse) ||
            !array_key_exists('latitude', $arServiceResponse) ||
            !array_key_exists('longitude', $arServiceResponse)
        )
        {
            $objCommonResponse
                ->setIsSuccess(false)
                ->setErrorMessage(json_encode($arServiceResponse));
        }

        $objCommonResponse
            ->setCountry($arServiceResponse['country_name'])
            ->setRegion($arServiceResponse['region_name'])
            ->setCity($arServiceResponse['city'])
            ->setLatitude($arServiceResponse['latitude'])
            ->setLongitude($arServiceResponse['longitude'])
            ->setIsSuccess(true);

        return $objCommonResponse;
    }

    protected function __getGeoData(string $ip): GeoIpResponse
    {
        return $this->makeCommonResponse($this->doRequest($ip));
    }
}