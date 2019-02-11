<?php
declare(strict_types = 1);

namespace Local\GeoIp;

class GeoIpServiceIpApi extends GeoIpServiceBase {
    protected function getUniqueCacheId(): string
    {
        return 'IpApi';
    }

    private function doRequest(string $ip): ?string{
        $strUrl = 'http://ip-api.com/json/'.$ip;

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
            !array_key_exists('country', $arServiceResponse) ||
            !array_key_exists('regionName', $arServiceResponse) ||
            !array_key_exists('city', $arServiceResponse) ||
            !array_key_exists('lat', $arServiceResponse) ||
            !array_key_exists('lon', $arServiceResponse)
        )
        {
            $objCommonResponse
                ->setIsSuccess(false)
                ->setErrorMessage(json_encode($arServiceResponse));
        }

        $objCommonResponse
            ->setCountry($arServiceResponse['country'])
            ->setRegion($arServiceResponse['regionName'])
            ->setCity($arServiceResponse['city'])
            ->setLatitude($arServiceResponse['lat'])
            ->setLongitude($arServiceResponse['lon'])
            ->setIsSuccess(true);

        return $objCommonResponse;
    }

    protected function __getGeoData(string $ip): GeoIpResponse
    {
        return $this->makeCommonResponse($this->doRequest($ip));
    }
}