<?php

namespace Unidays;

/**
 * UNiDAYS SDK - Tracking API Helper Class.
 *
 * @category   SDK
 * @package    UNiDAYS
 * @subpackage Redemption Tracking Helper
 * @copyright  Copyright (c) 2018 MyUNiDAYS Ltd.
 * @license    MIT License
 * @version    Release: 1.2
 * @link       http://www.myunidays.com
 */
class TrackingHelper
{
    private $directTrackingDetails;
    private $tracking_url;
    private $tracking_script_url;
    private $test;

    function __construct(DirectTrackingDetails $directTrackingDetails, $test = null)
    {
        $this->directTrackingDetails = $directTrackingDetails;

        if (empty($this->directTrackingDetails->partnerId))
            throw new \InvalidArgumentException('PartnerId is required');

        if (empty($this->directTrackingDetails->currency))
            throw new \InvalidArgumentException('Currency is required');

        if (empty($this->directTrackingDetails->transactionId))
            throw new \InvalidArgumentException('TransactionId is required');

        $this->test = isset($test) && is_bool($test) ? $test : false;

        $this->tracking_url = 'https://api.myunidays.com/tracking/v1.2/redemption';
        $this->tracking_script_url = $this->tracking_url . '/js';
    }

    /**
     * Generates the Server-to-Server Redemption Tracking URL
     *
     * @param string $key The key for the signature
     * @return string The URL to make a server-to-server request to
     */
    public function create_server_url($key)
    {
        return $this->generate_signed_url($this->tracking_url, $key);
    }

    /**
     * Generates the signed Redemption Tracking URL
     *
     * @param string $key The key for the signature
     * @return string The signed URL to be placed inside a <script /> element in your receipt page. A JSON body will be returned detailing errors, if any
     */
    public function create_signed_script_url($key)
    {
        return $this->generate_signed_url($this->tracking_script_url, $key);
    }

    private function generate_signed_url($base_url, $key)
    {
        $query = $this->generate_query();
        return $this->generate_url($base_url, $query) . $this->generate_signature_parameter($query, $key);
    }

    /**
     * Generates the Redemption Tracking URL
     *
     * @return string The URL to be placed inside a <script /> element in your receipt page. A JSON body will be returned detailing errors, if any
     */
    public function create_script_url()
    {
        $query = $this->generate_query();
        return $this->generate_url($this->tracking_script_url, $query);
    }

    private function generate_query()
    {
        return '?PartnerId=' . urlencode($this->directTrackingDetails->partnerId)
            . '&TransactionId=' . urlencode($this->directTrackingDetails->transactionId)
            . '&MemberId=' . ($this->directTrackingDetails->memberId ? urlencode($this->directTrackingDetails->memberId) : '')
            . '&Currency=' . urlencode($this->directTrackingDetails->currency)
            . '&OrderTotal=' . ($this->directTrackingDetails->orderTotal ? $this->url_encode_number($this->directTrackingDetails->orderTotal) : '')
            . '&ItemsUNiDAYSDiscount=' . ($this->directTrackingDetails->itemsUnidaysDiscount ? $this->url_encode_number($this->directTrackingDetails->itemsUnidaysDiscount) : '')
            . '&Code=' . ($this->directTrackingDetails->code ? urlencode($this->directTrackingDetails->code) : '')
            . '&ItemsTax=' . ($this->directTrackingDetails->itemsTax ? $this->url_encode_number($this->directTrackingDetails->itemsTax) : '')
            . '&ShippingGross=' . ($this->directTrackingDetails->shippingGross ? $this->url_encode_number($this->directTrackingDetails->shippingGross) : '')
            . '&ShippingDiscount=' . ($this->directTrackingDetails->shippingDiscount ? $this->url_encode_number($this->directTrackingDetails->shippingDiscount) : '')
            . '&ItemsGross=' . ($this->directTrackingDetails->itemsGross ? $this->url_encode_number($this->directTrackingDetails->itemsGross) : '')
            . '&ItemsOtherDiscount=' . ($this->directTrackingDetails->itemsOtherDiscount ? $this->url_encode_number($this->directTrackingDetails->itemsOtherDiscount) : '')
            . '&UNiDAYSDiscountPercentage=' . ($this->directTrackingDetails->unidaysDiscountPercentage ? $this->url_encode_number($this->directTrackingDetails->unidaysDiscountPercentage) : '')
            . '&NewCustomer=' . ($this->directTrackingDetails->newCustomer ? $this->parse_boolean($this->directTrackingDetails->newCustomer) : '');
    }

    private function url_encode_number($number)
    {
        return urlencode(number_format($number, 2, '.', ''));
    }

    private function parse_boolean($bool)
    {
        return rawurlencode($bool ? 'True' : 'False');
    }

    private function generate_url($base_url, $query)
    {
        return $base_url . $query . $this->generate_test_parameter();
    }

    private function generate_test_parameter()
    {
        return ($this->test)
            ? '&Test=True'
            : '';
    }

    private function generate_signature_parameter($query, $key)
    {
        $hash = urlencode(base64_encode(hash_hmac("sha512", $query, base64_decode($key), true)));

        return '&Signature=' . $hash;
    }
}
