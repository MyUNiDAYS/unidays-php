<?php

namespace Unidays;

class WhenRequestingASignedScriptUrlWithAllParamsSetTest extends TestCaseBase
{
    var $url;

    public function initialise()
    {
        $details = new DirectTrackingDetailsBuilder('a partner Id', 'the transaction id', 'GBP');
        $details->withOrderTotal(209.00);
        $details->withItemsUnidaysDiscount(13.00);
        $details->withCode("a code");
        $details->withItemsTax(34.50);
        $details->withShippingGross(5.00);
        $details->withShippingDiscount(3.00);
        $details->withItemsGross(1000.00);
        $details->withItemsOtherDiscount(10.00);
        $details->withUnidaysDiscountPercentage(10.00);
        $details->withNewCustomer(true);
        $builtDetails = $details->build();

        $helper = new TrackingHelper($builtDetails);

        $key = 'AAAAAA==';
        $this->url = $helper->create_signed_script_url($key);
    }

    /**
     * @test
     */
    public function TheSchemeShouldBeHttps()
    {
        $scheme = parse_url($this->url, PHP_URL_SCHEME);
        $this->assertEquals('https', $scheme);
    }

    /**
     * @test
     */
    public function TheHostShouldBeCorrect()
    {
        $host = parse_url($this->url, PHP_URL_HOST);
        $this->assertEquals('api.myunidays.com', $host);
    }

    /**
     * @test
     */
    public function ThePathShouldBeV1_2RedemptionJs()
    {
        $path = parse_url($this->url, PHP_URL_PATH);
        $this->assertEquals('/tracking/v1.2/redemption/js', $path);
    }

    /**
     * @test
     *
     * @param $parameter
     * @param $result
     *
     * @testWith    ["PartnerId", "a partner Id"]
     *              ["TransactionId", "the transaction id"]
     *              ["Currency", "GBP"]
     *              ["OrderTotal", "209.00"]
     *              ["ItemsUNiDAYSDiscount", "13.00"]
     *              ["Code", "a code"]
     *              ["ItemsTax", "34.50"]
     *              ["ShippingGross", "5.00"]
     *              ["ShippingDiscount", "3.00"]
     *              ["ItemsGross", "1000.00"]
     *              ["ItemsOtherDiscount", "10.00"]
     *              ["UNiDAYSDiscountPercentage", "10.00"]
     *              ["NewCustomer", "True"]
     *              ["Signature", "CJUr1kkv/426vsLQPGYlrt08xhkQ20ySx7262oGxRFFWbr7190A5Obalm6D67A45efC/MJAkGQeUgQjzIWXbZA=="]
     */
    public function TheParameterShouldBeCorrect($parameter, $result)
    {
        $query = parse_url($this->url, PHP_URL_QUERY);
        parse_str($query,$params);

        $this->assertEquals($result, $params[$parameter]);
    }
}
