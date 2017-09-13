<?php

	/**
	 * PHP version 5.3
	 *
	 * Request class
	 *
	 * @category Integration
	 * @package  WC_Retailcrm_Client
	 * @author   RetailCRM <dev@retailcrm.ru>
	 * @license  https://opensource.org/licenses/MIT MIT License
	 * @link     http://retailcrm.ru/docs/Developers/ApiVersion4
	 */

	if ( ! class_exists( 'WC_Retailcrm_Request' ) ) {
		include_once( __DIR__ . '/class-wc-retailcrm-request.php' );
	}

	if ( ! class_exists( 'WC_Retailcrm_Response' ) ) {
		include_once( __DIR__ . '/class-wc-retailcrm-response.php' );
	}

	class WC_Retailcrm_Client_V4
	{
	    protected $client;

	    /**
	     * Site code
	     */
	    protected $siteCode;

	    /**
	     * Client creating
	     *
	     * @param string $url    api url
	     * @param string $apiKey api key
	     * @param string $site   site code
	     *
	     * @throws \InvalidArgumentException
	     */
	    public function __construct($url, $apiKey, $version = null, $site = null)
	    {
	        if ('/' !== $url[strlen($url) - 1]) {
	            $url .= '/';
	        }

	        $url = $version == null ? $url . 'api' : $url . 'api/' . $version;

	        $this->client = new WC_Retailcrm_Request($url, array('apiKey' => $apiKey));
	        $this->siteCode = $site;
	    }
            
            /**
             * Returns api versions list
             * 
             * @return WC_Retailcrm_Response
             */
            public function apiVersions()
            {
                return $this->client->makeRequest('/api-versions', WC_Retailcrm_Request::METHOD_GET);
            }
            
	    /**
	     * Returns users list
	     *
	     * @param array $filter
	     * @param null  $page
	     * @param null  $limit
	     *
	     * @throws WC_Retailcrm_Exception_Json
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws \InvalidArgumentException
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function usersList(array $filter = array(), $page = null, $limit = null)
	    {
	        $parameters = array();

	        if (count($filter)) {
	            $parameters['filter'] = $filter;
	        }
	        if (null !== $page) {
	            $parameters['page'] = (int) $page;
	        }
	        if (null !== $limit) {
	            $parameters['limit'] = (int) $limit;
	        }

	        return $this->client->makeRequest(
	            '/users',
	            WC_Retailcrm_Request::METHOD_GET,
	            $parameters
	        );
	    }

	    /**
	     * Get user groups
	     *
	     * @param null $page
	     * @param null $limit
	     *
	     * @throws WC_Retailcrm_Exception_Json
	     * @throws WC_Retailcrm_Exception_Curl
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function usersGroups($page = null, $limit = null)
	    {
	        $parameters = array();

	        if (null !== $page) {
	            $parameters['page'] = (int) $page;
	        }
	        if (null !== $limit) {
	            $parameters['limit'] = (int) $limit;
	        }

	        return $this->client->makeRequest(
	            '/user-groups',
	            WC_Retailcrm_Request::METHOD_GET,
	            $parameters
	        );
	    }

	    /**
	     * Returns user data
	     *
	     * @param integer $id user ID
	     *
	     * @throws WC_Retailcrm_Exception_Json
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws \InvalidArgumentException
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function usersGet($id)
	    {
	        return $this->client->makeRequest("/users/$id", WC_Retailcrm_Request::METHOD_GET);
	    }

	    /**
	     * Returns filtered orders list
	     *
	     * @param array $filter (default: array())
	     * @param int   $page   (default: null)
	     * @param int   $limit  (default: null)
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function ordersList(array $filter = array(), $page = null, $limit = null)
	    {
	        $parameters = array();

	        if (count($filter)) {
	            $parameters['filter'] = $filter;
	        }
	        if (null !== $page) {
	            $parameters['page'] = (int) $page;
	        }
	        if (null !== $limit) {
	            $parameters['limit'] = (int) $limit;
	        }

	        return $this->client->makeRequest(
	            '/orders',
	            WC_Retailcrm_Request::METHOD_GET,
	            $parameters
	        );
	    }

	    /**
	     * Create a order
	     *
	     * @param array  $order order data
	     * @param string $site  (default: null)
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function ordersCreate(array $order, $site = null)
	    {
	        if (!count($order)) {
	            throw new \InvalidArgumentException(
	                'Parameter `order` must contains a data'
	            );
	        }

	        return $this->client->makeRequest(
	            '/orders/create',
	            WC_Retailcrm_Request::METHOD_POST,
	            $this->fillSite($site, array('order' => json_encode($order)))
	        );
	    }

	    /**
	     * Save order IDs' (id and externalId) association in the CRM
	     *
	     * @param array $ids order identificators
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function ordersFixExternalIds(array $ids)
	    {
	        if (! count($ids)) {
	            throw new \InvalidArgumentException(
	                'Method parameter must contains at least one IDs pair'
	            );
	        }

	        return $this->client->makeRequest(
	            '/orders/fix-external-ids',
	            WC_Retailcrm_Request::METHOD_POST,
	            array('orders' => json_encode($ids)
	            )
	        );
	    }

	    /**
	     * Returns statuses of the orders
	     *
	     * @param array $ids         (default: array())
	     * @param array $externalIds (default: array())
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function ordersStatuses(array $ids = array(), array $externalIds = array())
	    {
	        $parameters = array();

	        if (count($ids)) {
	            $parameters['ids'] = $ids;
	        }
	        if (count($externalIds)) {
	            $parameters['externalIds'] = $externalIds;
	        }

	        return $this->client->makeRequest(
	            '/orders/statuses',
	            WC_Retailcrm_Request::METHOD_GET,
	            $parameters
	        );
	    }

	    /**
	     * Upload array of the orders
	     *
	     * @param array  $orders array of orders
	     * @param string $site   (default: null)
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function ordersUpload(array $orders, $site = null)
	    {
	        if (!count($orders)) {
	            throw new \InvalidArgumentException(
	                'Parameter `orders` must contains array of the orders'
	            );
	        }

	        return $this->client->makeRequest(
	            '/orders/upload',
	            WC_Retailcrm_Request::METHOD_POST,
	            $this->fillSite($site, array('orders' => json_encode($orders)))
	        );
	    }

	    /**
	     * Get order by id or externalId
	     *
	     * @param string $id   order identificator
	     * @param string $by   (default: 'externalId')
	     * @param string $site (default: null)
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function ordersGet($id, $by = 'externalId', $site = null)
	    {
	        $this->checkIdParameter($by);

	        return $this->client->makeRequest(
	            "/orders/$id",
	            WC_Retailcrm_Request::METHOD_GET,
	            $this->fillSite($site, array('by' => $by))
	        );
	    }

	    /**
	     * Edit a order
	     *
	     * @param array  $order order data
	     * @param string $by    (default: 'externalId')
	     * @param string $site  (default: null)
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function ordersEdit(array $order, $by = 'externalId', $site = null)
	    {
	        if (!count($order)) {
	            throw new \InvalidArgumentException(
	                'Parameter `order` must contains a data'
	            );
	        }

	        $this->checkIdParameter($by);

	        if (!array_key_exists($by, $order)) {
	            throw new \InvalidArgumentException(
	                sprintf('Order array must contain the "%s" parameter.', $by)
	            );
	        }

	        return $this->client->makeRequest(
	            sprintf('/orders/%s/edit', $order[$by]),
	            WC_Retailcrm_Request::METHOD_POST,
	            $this->fillSite(
	                $site,
	                array('order' => json_encode($order), 'by' => $by)
	            )
	        );
	    }

	    /**
	     * Get orders history
	     * @param array $filter
	     * @param null $page
	     * @param null $limit
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function ordersHistory(array $filter = array(), $page = null, $limit = null)
	    {
	        $parameters = array();

	        if (count($filter)) {
	            $parameters['filter'] = $filter;
	        }
	        if (null !== $page) {
	            $parameters['page'] = (int) $page;
	        }
	        if (null !== $limit) {
	            $parameters['limit'] = (int) $limit;
	        }

	        return $this->client->makeRequest(
	            '/orders/history',
	            WC_Retailcrm_Request::METHOD_GET,
	            $parameters
	        );
	    }

	    /**
	     * Returns filtered customers list
	     *
	     * @param array $filter (default: array())
	     * @param int   $page   (default: null)
	     * @param int   $limit  (default: null)
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function customersList(array $filter = array(), $page = null, $limit = null)
	    {
	        $parameters = array();

	        if (count($filter)) {
	            $parameters['filter'] = $filter;
	        }
	        if (null !== $page) {
	            $parameters['page'] = (int) $page;
	        }
	        if (null !== $limit) {
	            $parameters['limit'] = (int) $limit;
	        }

	        return $this->client->makeRequest(
	            '/customers',
	            WC_Retailcrm_Request::METHOD_GET,
	            $parameters
	        );
	    }

	    /**
	     * Create a customer
	     *
	     * @param array  $customer customer data
	     * @param string $site     (default: null)
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function customersCreate(array $customer, $site = null)
	    {
	        if (! count($customer)) {
	            throw new \InvalidArgumentException(
	                'Parameter `customer` must contains a data'
	            );
	        }

	        return $this->client->makeRequest(
	            '/customers/create',
	            WC_Retailcrm_Request::METHOD_POST,
	            $this->fillSite($site, array('customer' => json_encode($customer)))
	        );
	    }

	    /**
	     * Save customer IDs' (id and externalId) association in the CRM
	     *
	     * @param array $ids ids mapping
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function customersFixExternalIds(array $ids)
	    {
	        if (! count($ids)) {
	            throw new \InvalidArgumentException(
	                'Method parameter must contains at least one IDs pair'
	            );
	        }

	        return $this->client->makeRequest(
	            '/customers/fix-external-ids',
	            WC_Retailcrm_Request::METHOD_POST,
	            array('customers' => json_encode($ids))
	        );
	    }

	    /**
	     * Upload array of the customers
	     *
	     * @param array  $customers array of customers
	     * @param string $site      (default: null)
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function customersUpload(array $customers, $site = null)
	    {
	        if (! count($customers)) {
	            throw new \InvalidArgumentException(
	                'Parameter `customers` must contains array of the customers'
	            );
	        }

	        return $this->client->makeRequest(
	            '/customers/upload',
	            WC_Retailcrm_Request::METHOD_POST,
	            $this->fillSite($site, array('customers' => json_encode($customers)))
	        );
	    }

	    /**
	     * Get customer by id or externalId
	     *
	     * @param string $id   customer identificator
	     * @param string $by   (default: 'externalId')
	     * @param string $site (default: null)
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function customersGet($id, $by = 'externalId', $site = null)
	    {
	        $this->checkIdParameter($by);

	        return $this->client->makeRequest(
	            "/customers/$id",
	            WC_Retailcrm_Request::METHOD_GET,
	            $this->fillSite($site, array('by' => $by))
	        );
	    }

	    /**
	     * Edit a customer
	     *
	     * @param array  $customer customer data
	     * @param string $by       (default: 'externalId')
	     * @param string $site     (default: null)
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function customersEdit(array $customer, $by = 'externalId', $site = null)
	    {
	        if (!count($customer)) {
	            throw new \InvalidArgumentException(
	                'Parameter `customer` must contains a data'
	            );
	        }

	        $this->checkIdParameter($by);

	        if (!array_key_exists($by, $customer)) {
	            throw new \InvalidArgumentException(
	                sprintf('Customer array must contain the "%s" parameter.', $by)
	            );
	        }

	        return $this->client->makeRequest(
	            sprintf('/customers/%s/edit', $customer[$by]),
	            WC_Retailcrm_Request::METHOD_POST,
	            $this->fillSite(
	                $site,
	                array('customer' => json_encode($customer), 'by' => $by)
	            )
	        );
	    }

	    /**
	     * Get customers history
	     * @param array $filter
	     * @param null $page
	     * @param null $limit
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function customersHistory(array $filter = array(), $page = null, $limit = null)
	    {
	        $parameters = array();

	        if (count($filter)) {
	            $parameters['filter'] = $filter;
	        }
	        if (null !== $page) {
	            $parameters['page'] = (int) $page;
	        }
	        if (null !== $limit) {
	            $parameters['limit'] = (int) $limit;
	        }

	        return $this->client->makeRequest(
	            '/customers/history',
	            WC_Retailcrm_Request::METHOD_GET,
	            $parameters
	        );
	    }

	    /**
	     * Get orders assembly list
	     *
	     * @param array $filter (default: array())
	     * @param int   $page   (default: null)
	     * @param int   $limit  (default: null)
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function ordersPacksList(array $filter = array(), $page = null, $limit = null)
	    {
	        $parameters = array();

	        if (count($filter)) {
	            $parameters['filter'] = $filter;
	        }
	        if (null !== $page) {
	            $parameters['page'] = (int) $page;
	        }
	        if (null !== $limit) {
	            $parameters['limit'] = (int) $limit;
	        }

	        return $this->client->makeRequest(
	            '/orders/packs',
	            WC_Retailcrm_Request::METHOD_GET,
	            $parameters
	        );
	    }

	    /**
	     * Create orders assembly
	     *
	     * @param array  $pack pack data
	     * @param string $site (default: null)
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function ordersPacksCreate(array $pack, $site = null)
	    {
	        if (!count($pack)) {
	            throw new \InvalidArgumentException(
	                'Parameter `pack` must contains a data'
	            );
	        }

	        return $this->client->makeRequest(
	            '/orders/packs/create',
	            WC_Retailcrm_Request::METHOD_POST,
	            $this->fillSite($site, array('pack' => json_encode($pack)))
	        );
	    }

	    /**
	     * Get orders assembly history
	     *
	     * @param array $filter (default: array())
	     * @param int   $page   (default: null)
	     * @param int   $limit  (default: null)
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function ordersPacksHistory(array $filter = array(), $page = null, $limit = null)
	    {
	        $parameters = array();

	        if (count($filter)) {
	            $parameters['filter'] = $filter;
	        }
	        if (null !== $page) {
	            $parameters['page'] = (int) $page;
	        }
	        if (null !== $limit) {
	            $parameters['limit'] = (int) $limit;
	        }

	        return $this->client->makeRequest(
	            '/orders/packs/history',
	            WC_Retailcrm_Request::METHOD_GET,
	            $parameters
	        );
	    }

	    /**
	     * Get orders assembly by id
	     *
	     * @param string $id pack identificator
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function ordersPacksGet($id)
	    {
	        if (empty($id)) {
	            throw new \InvalidArgumentException('Parameter `id` must be set');
	        }

	        return $this->client->makeRequest(
	            "/orders/packs/$id",
	            WC_Retailcrm_Request::METHOD_GET
	        );
	    }

	    /**
	     * Delete orders assembly by id
	     *
	     * @param string $id pack identificator
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function ordersPacksDelete($id)
	    {
	        if (empty($id)) {
	            throw new \InvalidArgumentException('Parameter `id` must be set');
	        }

	        return $this->client->makeRequest(
	            sprintf('/orders/packs/%s/delete', $id),
	            WC_Retailcrm_Request::METHOD_POST
	        );
	    }

	    /**
	     * Edit orders assembly
	     *
	     * @param array  $pack pack data
	     * @param string $site (default: null)
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function ordersPacksEdit(array $pack, $site = null)
	    {
	        if (!count($pack) || empty($pack['id'])) {
	            throw new \InvalidArgumentException(
	                'Parameter `pack` must contains a data & pack `id` must be set'
	            );
	        }

	        return $this->client->makeRequest(
	            sprintf('/orders/packs/%s/edit', $pack['id']),
	            WC_Retailcrm_Request::METHOD_POST,
	            $this->fillSite($site, array('pack' => json_encode($pack)))
	        );
	    }

	    /**
	     * Get purchace prices & stock balance
	     *
	     * @param array $filter (default: array())
	     * @param int   $page   (default: null)
	     * @param int   $limit  (default: null)
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function storeInventories(array $filter = array(), $page = null, $limit = null)
	    {
	        $parameters = array();

	        if (count($filter)) {
	            $parameters['filter'] = $filter;
	        }
	        if (null !== $page) {
	            $parameters['page'] = (int) $page;
	        }
	        if (null !== $limit) {
	            $parameters['limit'] = (int) $limit;
	        }

	        return $this->client->makeRequest(
	            '/store/inventories',
	            WC_Retailcrm_Request::METHOD_GET,
	            $parameters
	        );
	    }

	    /**
	     * Get store settings
	     *
	     * @param string $code get settings code
	     *
	     * @return WC_Retailcrm_Response
	     * @throws WC_Retailcrm_Exception_Json
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws \InvalidArgumentException
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function storeSettingsGet($code)
	    {
	        if (empty($code)) {
	            throw new \InvalidArgumentException('Parameter `code` must be set');
	        }

	        return $this->client->makeRequest(
	            "/store/setting/$code",
	            WC_Retailcrm_Request::METHOD_GET
	        );
	    }

	    /**
	     * Edit store configuration
	     *
	     * @param array $configuration
	     *
	     * @throws WC_Retailcrm_Exception_Json
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws \InvalidArgumentException
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function storeSettingsEdit(array $configuration)
	    {
	        if (!count($configuration) || empty($configuration['code'])) {
	            throw new \InvalidArgumentException(
	                'Parameter `configuration` must contains a data & configuration `code` must be set'
	            );
	        }

	        return $this->client->makeRequest(
	            sprintf('/store/setting/%s/edit', $configuration['code']),
	            WC_Retailcrm_Request::METHOD_POST,
	            array('configuration' => json_encode($configuration))
	        );
	    }

	    /**
	     * Upload store inventories
	     *
	     * @param array  $offers offers data
	     * @param string $site   (default: null)
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function storeInventoriesUpload(array $offers, $site = null)
	    {
	        if (!count($offers)) {
	            throw new \InvalidArgumentException(
	                'Parameter `offers` must contains array of the offers'
	            );
	        }

	        return $this->client->makeRequest(
	            '/store/inventories/upload',
	            WC_Retailcrm_Request::METHOD_POST,
	            $this->fillSite($site, array('offers' => json_encode($offers)))
	        );
	    }

	    /**
	     * Upload store prices
	     *
	     * @param array  $prices prices data
	     * @param string $site   default: null)
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function storePricesUpload(array $prices, $site = null)
	    {
	        if (!count($prices)) {
	            throw new \InvalidArgumentException(
	                'Parameter `prices` must contains array of the prices'
	            );
	        }

	        return $this->client->makeRequest(
	            '/store/prices/upload',
	            WC_Retailcrm_Request::METHOD_POST,
	            $this->fillSite($site, array('prices' => json_encode($prices)))
	        );
	    }

	    /**
	     * Get products
	     *
	     * @param array $filter (default: array())
	     * @param int   $page   (default: null)
	     * @param int   $limit  (default: null)
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function storeProducts(array $filter = array(), $page = null, $limit = null)
	    {
	        $parameters = array();

	        if (count($filter)) {
	            $parameters['filter'] = $filter;
	        }
	        if (null !== $page) {
	            $parameters['page'] = (int) $page;
	        }
	        if (null !== $limit) {
	            $parameters['limit'] = (int) $limit;
	        }

	        return $this->client->makeRequest(
	            '/store/products',
	            WC_Retailcrm_Request::METHOD_GET,
	            $parameters
	        );
	    }

	    /**
	     * Get delivery settings
	     *
	     * @param string $code
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function deliverySettingsGet($code)
	    {
	        if (empty($code)) {
	            throw new \InvalidArgumentException('Parameter `code` must be set');
	        }

	        return $this->client->makeRequest(
	            "/delivery/generic/setting/$code",
	            WC_Retailcrm_Request::METHOD_GET
	        );
	    }

	    /**
	     * Edit delivery configuration
	     *
	     * @param array $configuration
	     *
	     * @throws WC_Retailcrm_Exception_Json
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws \InvalidArgumentException
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function deliverySettingsEdit(array $configuration)
	    {
	        if (!count($configuration) || empty($configuration['code'])) {
	            throw new \InvalidArgumentException(
	                'Parameter `configuration` must contains a data & configuration `code` must be set'
	            );
	        }

	        return $this->client->makeRequest(
	            sprintf('/delivery/generic/setting/%s/edit', $configuration['code']),
	            WC_Retailcrm_Request::METHOD_POST,
	            array('configuration' => json_encode($configuration))
	        );
	    }

	    /**
	     * Delivery tracking update
	     *
	     * @param string $code
	     * @param array  $statusUpdate
	     *
	     * @throws WC_Retailcrm_Exception_Json
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws \InvalidArgumentException
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function deliveryTracking($code, array $statusUpdate)
	    {
	        if (empty($code)) {
	            throw new \InvalidArgumentException('Parameter `code` must be set');
	        }

	        if (!count($statusUpdate)) {
	            throw new \InvalidArgumentException(
	                'Parameter `statusUpdate` must contains a data'
	            );
	        }

	        return $this->client->makeRequest(
	            sprintf('/delivery/generic/%s/tracking', $code),
	            WC_Retailcrm_Request::METHOD_POST,
	            array('statusUpdate' => json_encode($statusUpdate))
	        );
	    }

	    /**
	     * Returns available county list
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function countriesList()
	    {
	        return $this->client->makeRequest(
	            '/reference/countries',
	            WC_Retailcrm_Request::METHOD_GET
	        );
	    }

	    /**
	     * Returns deliveryServices list
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function deliveryServicesList()
	    {
	        return $this->client->makeRequest(
	            '/reference/delivery-services',
	            WC_Retailcrm_Request::METHOD_GET
	        );
	    }

	    /**
	     * Edit deliveryService
	     *
	     * @param array $data delivery service data
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function deliveryServicesEdit(array $data)
	    {
	        if (!array_key_exists('code', $data)) {
	            throw new \InvalidArgumentException(
	                'Data must contain "code" parameter.'
	            );
	        }

	        return $this->client->makeRequest(
	            sprintf('/reference/delivery-services/%s/edit', $data['code']),
	            WC_Retailcrm_Request::METHOD_POST,
	            array('deliveryService' => json_encode($data))
	        );
	    }

	    /**
	     * Returns deliveryTypes list
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function deliveryTypesList()
	    {
	        return $this->client->makeRequest(
	            '/reference/delivery-types',
	            WC_Retailcrm_Request::METHOD_GET
	        );
	    }

	    /**
	     * Edit deliveryType
	     *
	     * @param array $data delivery type data
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function deliveryTypesEdit(array $data)
	    {
	        if (!array_key_exists('code', $data)) {
	            throw new \InvalidArgumentException(
	                'Data must contain "code" parameter.'
	            );
	        }

	        return $this->client->makeRequest(
	            sprintf('/reference/delivery-types/%s/edit', $data['code']),
	            WC_Retailcrm_Request::METHOD_POST,
	            array('deliveryType' => json_encode($data))
	        );
	    }

	    /**
	     * Returns orderMethods list
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function orderMethodsList()
	    {
	        return $this->client->makeRequest(
	            '/reference/order-methods',
	            WC_Retailcrm_Request::METHOD_GET
	        );
	    }

	    /**
	     * Edit orderMethod
	     *
	     * @param array $data order method data
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function orderMethodsEdit(array $data)
	    {
	        if (!array_key_exists('code', $data)) {
	            throw new \InvalidArgumentException(
	                'Data must contain "code" parameter.'
	            );
	        }

	        return $this->client->makeRequest(
	            sprintf('/reference/order-methods/%s/edit', $data['code']),
	            WC_Retailcrm_Request::METHOD_POST,
	            array('orderMethod' => json_encode($data))
	        );
	    }

	    /**
	     * Returns orderTypes list
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function orderTypesList()
	    {
	        return $this->client->makeRequest(
	            '/reference/order-types',
	            WC_Retailcrm_Request::METHOD_GET
	        );
	    }

	    /**
	     * Edit orderType
	     *
	     * @param array $data order type data
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function orderTypesEdit(array $data)
	    {
	        if (!array_key_exists('code', $data)) {
	            throw new \InvalidArgumentException(
	                'Data must contain "code" parameter.'
	            );
	        }

	        return $this->client->makeRequest(
	            sprintf('/reference/order-types/%s/edit', $data['code']),
	            WC_Retailcrm_Request::METHOD_POST,
	            array('orderType' => json_encode($data))
	        );
	    }

	    /**
	     * Returns paymentStatuses list
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function paymentStatusesList()
	    {
	        return $this->client->makeRequest(
	            '/reference/payment-statuses',
	            WC_Retailcrm_Request::METHOD_GET
	        );
	    }

	    /**
	     * Edit paymentStatus
	     *
	     * @param array $data payment status data
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function paymentStatusesEdit(array $data)
	    {
	        if (!array_key_exists('code', $data)) {
	            throw new \InvalidArgumentException(
	                'Data must contain "code" parameter.'
	            );
	        }

	        return $this->client->makeRequest(
	            sprintf('/reference/payment-statuses/%s/edit', $data['code']),
	            WC_Retailcrm_Request::METHOD_POST,
	            array('paymentStatus' => json_encode($data))
	        );
	    }

	    /**
	     * Returns paymentTypes list
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function paymentTypesList()
	    {
	        return $this->client->makeRequest(
	            '/reference/payment-types',
	            WC_Retailcrm_Request::METHOD_GET
	        );
	    }

	    /**
	     * Edit paymentType
	     *
	     * @param array $data payment type data
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function paymentTypesEdit(array $data)
	    {
	        if (!array_key_exists('code', $data)) {
	            throw new \InvalidArgumentException(
	                'Data must contain "code" parameter.'
	            );
	        }

	        return $this->client->makeRequest(
	            sprintf('/reference/payment-types/%s/edit', $data['code']),
	            WC_Retailcrm_Request::METHOD_POST,
	            array('paymentType' => json_encode($data))
	        );
	    }

	    /**
	     * Returns productStatuses list
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function productStatusesList()
	    {
	        return $this->client->makeRequest(
	            '/reference/product-statuses',
	            WC_Retailcrm_Request::METHOD_GET
	        );
	    }

	    /**
	     * Edit productStatus
	     *
	     * @param array $data product status data
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function productStatusesEdit(array $data)
	    {
	        if (!array_key_exists('code', $data)) {
	            throw new \InvalidArgumentException(
	                'Data must contain "code" parameter.'
	            );
	        }

	        return $this->client->makeRequest(
	            sprintf('/reference/product-statuses/%s/edit', $data['code']),
	            WC_Retailcrm_Request::METHOD_POST,
	            array('productStatus' => json_encode($data))
	        );
	    }

	    /**
	     * Returns sites list
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function sitesList()
	    {
	        return $this->client->makeRequest(
	            '/reference/sites',
	            WC_Retailcrm_Request::METHOD_GET
	        );
	    }

	    /**
	     * Edit site
	     *
	     * @param array $data site data
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function sitesEdit(array $data)
	    {
	        if (!array_key_exists('code', $data)) {
	            throw new \InvalidArgumentException(
	                'Data must contain "code" parameter.'
	            );
	        }

	        return $this->client->makeRequest(
	            sprintf('/reference/sites/%s/edit', $data['code']),
	            WC_Retailcrm_Request::METHOD_POST,
	            array('site' => json_encode($data))
	        );
	    }

	    /**
	     * Returns statusGroups list
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function statusGroupsList()
	    {
	        return $this->client->makeRequest(
	            '/reference/status-groups',
	            WC_Retailcrm_Request::METHOD_GET
	        );
	    }

	    /**
	     * Returns statuses list
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function statusesList()
	    {
	        return $this->client->makeRequest(
	            '/reference/statuses',
	            WC_Retailcrm_Request::METHOD_GET
	        );
	    }

	    /**
	     * Edit order status
	     *
	     * @param array $data status data
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function statusesEdit(array $data)
	    {
	        if (!array_key_exists('code', $data)) {
	            throw new \InvalidArgumentException(
	                'Data must contain "code" parameter.'
	            );
	        }

	        return $this->client->makeRequest(
	            sprintf('/reference/statuses/%s/edit', $data['code']),
	            WC_Retailcrm_Request::METHOD_POST,
	            array('status' => json_encode($data))
	        );
	    }

	    /**
	     * Returns stores list
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function storesList()
	    {
	        return $this->client->makeRequest(
	            '/reference/stores',
	            WC_Retailcrm_Request::METHOD_GET
	        );
	    }

	    /**
	     * Edit store
	     *
	     * @param array $data site data
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function storesEdit(array $data)
	    {
	        if (!array_key_exists('code', $data)) {
	            throw new \InvalidArgumentException(
	                'Data must contain "code" parameter.'
	            );
	        }

	        if (!array_key_exists('name', $data)) {
	            throw new \InvalidArgumentException(
	                'Data must contain "name" parameter.'
	            );
	        }

	        return $this->client->makeRequest(
	            sprintf('/reference/stores/%s/edit', $data['code']),
	            WC_Retailcrm_Request::METHOD_POST,
	            array('store' => json_encode($data))
	        );
	    }

	    /**
	     * Get prices types
	     *
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function pricesTypes()
	    {
	        return $this->client->makeRequest(
	            '/reference/price-types',
	            WC_Retailcrm_Request::METHOD_GET
	        );
	    }

	    /**
	     * Edit price type
	     *
	     * @param array $data
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function pricesEdit(array $data)
	    {
	        if (!array_key_exists('code', $data)) {
	            throw new \InvalidArgumentException(
	                'Data must contain "code" parameter.'
	            );
	        }

	        if (!array_key_exists('name', $data)) {
	            throw new \InvalidArgumentException(
	                'Data must contain "name" parameter.'
	            );
	        }

	        return $this->client->makeRequest(
	            sprintf('/reference/price-types/%s/edit', $data['code']),
	            WC_Retailcrm_Request::METHOD_POST,
	            array('priceType' => json_encode($data))
	        );
	    }

	    /**
	     * Get telephony settings
	     *
	     * @param string $code
	     *
	     * @throws WC_Retailcrm_Exception_Json
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws \InvalidArgumentException
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function telephonySettingsGet($code)
	    {
	        if (empty($code)) {
	            throw new \InvalidArgumentException('Parameter `code` must be set');
	        }

	        return $this->client->makeRequest(
	            "/telephony/setting/$code",
	            WC_Retailcrm_Request::METHOD_GET
	        );
	    }

	    /**
	     * Edit telephony settings
	     *
	     * @param string  $code        symbolic code
	     * @param string  $clientId    client id
	     * @param boolean $active      telephony activity
	     * @param mixed   $name        service name
	     * @param mixed   $makeCallUrl service init url
	     * @param mixed   $image       service logo url(svg file)
	     *
	     * @param array   $additionalCodes
	     * @param array   $externalPhones
	     * @param bool    $allowEdit
	     * @param bool    $inputEventSupported
	     * @param bool    $outputEventSupported
	     * @param bool    $hangupEventSupported
	     * @param bool    $changeUserStatusUrl
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function telephonySettingsEdit(
	        $code,
	        $clientId,
	        $active = false,
	        $name = false,
	        $makeCallUrl = false,
	        $image = false,
	        $additionalCodes = array(),
	        $externalPhones = array(),
	        $allowEdit = false,
	        $inputEventSupported = false,
	        $outputEventSupported = false,
	        $hangupEventSupported = false,
	        $changeUserStatusUrl = false
	    )
	    {
	        if (!isset($code)) {
	            throw new \InvalidArgumentException('Code must be set');
	        }

	        $parameters['code'] = $code;

	        if (!isset($clientId)) {
	            throw new \InvalidArgumentException('client id must be set');
	        }

	        $parameters['clientId'] = $clientId;

	        if (!isset($active)) {
	            $parameters['active'] = false;
	        } else {
	            $parameters['active'] = $active;
	        }

	        if (!isset($name)) {
	            throw new \InvalidArgumentException('name must be set');
	        }

	        if (isset($name)) {
	            $parameters['name'] = $name;
	        }

	        if (isset($makeCallUrl)) {
	            $parameters['makeCallUrl'] = $makeCallUrl;
	        }

	        if (isset($image)) {
	            $parameters['image'] = $image;
	        }

	        if (isset($additionalCodes)) {
	            $parameters['additionalCodes'] = $additionalCodes;
	        }

	        if (isset($externalPhones)) {
	            $parameters['externalPhones'] = $externalPhones;
	        }

	        if (isset($allowEdit)) {
	            $parameters['allowEdit'] = $allowEdit;
	        }

	        if (isset($inputEventSupported)) {
	            $parameters['inputEventSupported'] = $inputEventSupported;
	        }

	        if (isset($outputEventSupported)) {
	            $parameters['outputEventSupported'] = $outputEventSupported;
	        }

	        if (isset($hangupEventSupported)) {
	            $parameters['hangupEventSupported'] = $hangupEventSupported;
	        }

	        if (isset($changeUserStatusUrl)) {
	            $parameters['changeUserStatusUrl'] = $changeUserStatusUrl;
	        }

	        return $this->client->makeRequest(
	            "/telephony/setting/$code/edit",
	            WC_Retailcrm_Request::METHOD_POST,
	            array('configuration' => json_encode($parameters))
	        );
	    }

	    /**
	     * Call event
	     *
	     * @param string $phone phone number
	     * @param string $type  call type
	     * @param array  $codes
	     * @param string $hangupStatus
	     * @param string $externalPhone
	     * @param array  $webAnalyticsData
	     *
	     * @return WC_Retailcrm_Response
	     * @internal param string $code additional phone code
	     * @internal param string $status call status
	     *
	     */
	    public function telephonyCallEvent(
	        $phone,
	        $type,
	        $codes,
	        $hangupStatus,
	        $externalPhone = null,
	        $webAnalyticsData = array()
	    )
	    {
	        if (!isset($phone)) {
	            throw new \InvalidArgumentException('Phone number must be set');
	        }

	        if (!isset($type)) {
	            throw new \InvalidArgumentException('Type must be set (in|out|hangup)');
	        }

	        if (empty($codes)) {
	            throw new \InvalidArgumentException('Codes array must be set');
	        }

	        $parameters['phone'] = $phone;
	        $parameters['type'] = $type;
	        $parameters['codes'] = $codes;
	        $parameters['hangupStatus'] = $hangupStatus;
	        $parameters['callExternalId'] = $externalPhone;
	        $parameters['webAnalyticsData'] = $webAnalyticsData;


	        return $this->client->makeRequest(
	            '/telephony/call/event',
	            WC_Retailcrm_Request::METHOD_POST,
	            array('event' => json_encode($parameters))
	        );
	    }

	    /**
	     * Upload calls
	     *
	     * @param array $calls calls data
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function telephonyCallsUpload(array $calls)
	    {
	        if (!count($calls)) {
	            throw new \InvalidArgumentException(
	                'Parameter `calls` must contains array of the calls'
	            );
	        }

	        return $this->client->makeRequest(
	            '/telephony/calls/upload',
	            WC_Retailcrm_Request::METHOD_POST,
	            array('calls' => json_encode($calls))
	        );
	    }

	    /**
	     * Get call manager
	     *
	     * @param string $phone   phone number
	     * @param bool   $details detailed information
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function telephonyCallManager($phone, $details)
	    {
	        if (!isset($phone)) {
	            throw new \InvalidArgumentException('Phone number must be set');
	        }

	        $parameters['phone'] = $phone;
	        $parameters['details'] = isset($details) ? $details : 0;

	        return $this->client->makeRequest(
	            '/telephony/manager',
	            WC_Retailcrm_Request::METHOD_GET,
	            $parameters
	        );
	    }

	    /**
	     * Edit marketplace configuration
	     *
	     * @param array $configuration
	     *
	     * @throws WC_Retailcrm_Exception_Json
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws \InvalidArgumentException
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function marketplaceSettingsEdit(array $configuration)
	    {
	        if (!count($configuration) || empty($configuration['code'])) {
	            throw new \InvalidArgumentException(
	                'Parameter `configuration` must contains a data & configuration `code` must be set'
	            );
	        }

	        return $this->client->makeRequest(
	            sprintf('/marketplace/external/setting/%s/edit', $configuration['code']),
	            WC_Retailcrm_Request::METHOD_POST,
	            array('configuration' => json_encode($configuration))
	        );
	    }

	    /**
	     * Update CRM basic statistic
	     *
	     * @throws \InvalidArgumentException
	     * @throws WC_Retailcrm_Exception_Curl
	     * @throws WC_Retailcrm_Exception_Json
	     *
	     * @return WC_Retailcrm_Response
	     */
	    public function statisticUpdate()
	    {
	        return $this->client->makeRequest(
	            '/statistic/update',
	            WC_Retailcrm_Request::METHOD_GET
	        );
	    }

	    /**
	     * Return current site
	     *
	     * @return string
	     */
	    public function getSite()
	    {
	        return $this->siteCode;
	    }

	    /**
	     * Set site
	     *
	     * @param string $site site code
	     *
	     * @return void
	     */
	    public function setSite($site)
	    {
	        $this->siteCode = $site;
	    }

	    /**
	     * Check ID parameter
	     *
	     * @param string $by identify by
	     *
	     * @throws \InvalidArgumentException
	     *
	     * @return bool
	     */
	    protected function checkIdParameter($by)
	    {
	        $allowedForBy = array(
	            'externalId',
	            'id'
	        );

	        if (!in_array($by, $allowedForBy, false)) {
	            throw new \InvalidArgumentException(
	                sprintf(
	                    'Value "%s" for "by" param is not valid. Allowed values are %s.',
	                    $by,
	                    implode(', ', $allowedForBy)
	                )
	            );
	        }

	        return true;
	    }

	    /**
	     * Fill params by site value
	     *
	     * @param string $site   site code
	     * @param array  $params input parameters
	     *
	     * @return array
	     */
	    protected function fillSite($site, array $params)
	    {
	        if ($site) {
	            $params['site'] = $site;
	        } elseif ($this->siteCode) {
	            $params['site'] = $this->siteCode;
	        }

	        return $params;
	    }
	}
