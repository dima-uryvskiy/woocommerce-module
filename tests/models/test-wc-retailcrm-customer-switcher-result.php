<?php
/**
 * PHP version 5.3
 *
 * @category Integration
 * @author   RetailCRM <integration@retailcrm.ru>
 * @license  http://retailcrm.ru Proprietary
 * @link     http://retailcrm.ru
 * @see      http://help.retailcrm.ru
 */

class WC_Retailcrm_Customer_Switcher_Result_Test extends WC_Retailcrm_Test_Case_Helper
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_invalid_both()
    {
        new WC_Retailcrm_Customer_Switcher_Result(new stdClass(), new stdClass());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_invalid_customer()
    {
        new WC_Retailcrm_Customer_Switcher_Result(new stdClass(), new WC_Order());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_invalid_order()
    {
        new WC_Retailcrm_Customer_Switcher_Result(new WC_Customer(), new stdClass());
    }

    public function test_valid()
    {
        $result = new WC_Retailcrm_Customer_Switcher_Result(new WC_Customer(), new WC_Order());
        $this->assertInstanceOf('\WC_Customer', $result->getWcCustomer());
        $this->assertInstanceOf('\WC_Order', $result->getWcOrder());
    }

    public function test_valid_no_customer()
    {
        $result = new WC_Retailcrm_Customer_Switcher_Result(null, new WC_Order());
        $this->assertEmpty($result->getWcCustomer())
;        $this->assertInstanceOf('\WC_Order', $result->getWcOrder());
    }
}
