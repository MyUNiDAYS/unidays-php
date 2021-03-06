<?php

namespace Unidays;

/**
 * UNiDAYS SDK - Codeless API Helper Class.
 *
 * @category   SDK
 * @package    UNiDAYS
 * @subpackage Codeless URL Verifier
 * @copyright  Copyright (c) 2018 MyUNiDAYS Ltd.
 * @license    MIT License
 * @version    Release: 1.2
 * @link       http://www.myunidays.com
 */
class CodelessUrlVerifier
{
    private $key;

    function __construct($key)
    {
        if (empty($key))
            throw new \InvalidArgumentException('Key cannot be null or empty');

        $this->key = base64_decode($key);
    }

    private function hash_query($query)
    {
        return base64_encode(hash_hmac("sha512", $query, $this->key, true));
    }

    private function encode_urlvariable($urlvariable)
    {
        $first_encode = urlencode($urlvariable);
        $encoded_variable = preg_replace_callback('/%(\d[A-F]|[A-F]\d)/',
            function (array $matches) {
                return strtolower($matches[0]);
            }, $first_encode);
        return $encoded_variable;
    }

    /**
     * Verifies that the url was generated by UNiDAYS and returns the time the url was generated if it is valid
     *
     * @param string $ud_s The ud_s parameter from the url
     * @param string $ud_t The ud_t parameter from the url
     * @param string $ud_h The ud_h parameter from the url
     * @return \DateTime|null The time that the url was generated if the url is verified, else null
     * @throws \Exception Throws exception if the url is not able to be verified
     */
    public function verify_url_params($ud_s, $ud_t, $ud_h)
    {
        $query = '?ud_s=' . ($this->encode_urlvariable($ud_s) ?: '') . '&ud_t=' . ($this->encode_urlvariable($ud_t) ?: '');

        try {
            $hash = $this->hash_query($query);

            if ($ud_h !== $hash)
                return null;

            return new \DateTime("@" . $ud_t, new \DateTimeZone('UTC'));

        } catch (\Exception $ex) {
            throw new \Exception('Unable to verify URL', $ex);
        }
    }

    /**
     * Verifies that the url was generated by UNiDAYS and returns the time the url was generated if it is valid
     *
     * @param string $url The url to be verified
     * @return \DateTime|null The time that the url was generated if the url is verified, else null
     * @throws \Exception Throws exception if any querystring parameter is null or empty
     */
    public function verify_url($url)
    {
        $query = parse_url($url, PHP_URL_QUERY);
        parse_str($query, $params);

        if (empty($params["ud_s"]) || empty($params["ud_t"]) || empty($params["ud_h"]))
            throw new \InvalidArgumentException('URL does not contain the required query parameters');

        return $this->verify_url_params($params["ud_s"], $params["ud_t"], $params["ud_h"]);
    }
}

