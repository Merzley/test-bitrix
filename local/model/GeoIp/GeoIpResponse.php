<?php
declare(strict_types=1);

namespace Local\GeoIp;

class GeoIpResponse{
    /** @var string|null */
    public $strCountry;
    /** @var string|null */
    public $strRegion;
    /** @var string|null */
    public $strCity;
    /** @var float|null */
    public $fLatitude;
    /** @var float|null */
    public $fLongitude;

    /** @var boolean */
    public $bIsSuccess;
    /** @var string|null */
    public $strErrorMessage;

    /**
     * @param string|null $strCountry
     * @return GeoIpResponse
     */
    public function setCountry(?string $strCountry): GeoIpResponse
    {
        $this->strCountry = $strCountry;
        return $this;
    }

    /**
     * @param string|null $strRegion
     * @return GeoIpResponse
     */
    public function setRegion(?string $strRegion): GeoIpResponse
    {
        $this->strRegion = $strRegion;
        return $this;
    }

    /**
     * @param string|null $strCity
     * @return GeoIpResponse
     */
    public function setCity(?string $strCity): GeoIpResponse
    {
        $this->strCity = $strCity;
        return $this;
    }

    /**
     * @param float|null $fLatitude
     * @return GeoIpResponse
     */
    public function setLatitude(?float $fLatitude): GeoIpResponse
    {
        $this->fLatitude = $fLatitude;
        return $this;
    }

    /**
     * @param float|null $fLongitude
     * @return GeoIpResponse
     */
    public function setLongitude(?float $fLongitude): GeoIpResponse
    {
        $this->fLongitude = $fLongitude;
        return $this;
    }

    /**
     * @param bool $bIsSuccess
     * @return GeoIpResponse
     */
    public function setIsSuccess(bool $bIsSuccess): GeoIpResponse
    {
        $this->bIsSuccess = $bIsSuccess;
        return $this;
    }

    /**
     * @param string|null $strErrorMessage
     * @return GeoIpResponse
     */
    public function setErrorMessage(?string $strErrorMessage): GeoIpResponse
    {
        $this->strErrorMessage = $strErrorMessage;
        return $this;
    }


}