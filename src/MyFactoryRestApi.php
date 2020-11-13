<?php

namespace Supsign\LaravelMfRest;

use Config;
use Exception;
use SimpleXMLElement;

class MyFactoryRestApi
{
    protected
    	$cache = array(),
    	$ch = null,
        $client = null,
        $endpoint = '',
        $endpoints = array(),
        $login = null,
        $parameters = array(),
        $password = null,
        $request = array(),
        $response = null,
        $responseRaw = array(),
        $skipStep = 5000,
        $url = null;

	public function __construct() 
	{
		$this->login = env('MF_REST_LOGIN');
		$this->password = env('MF_REST_PASSWORD');
		$this->url = env('MF_REST_URL');

		return $this;
	}

	protected function clearCache()
	{
		$this->cache = array();

		return $this;
	}

	public function clearResponse()
	{
		$this->response = null;
		$this->responseRaw = array();
		$this->parameters = array();

		return $this;
	}

	protected function clearRequestData() 
	{
		foreach ($this->request AS $key => $value) {
			unset($this->request[$key]);
		}

		return $this;
	}

	protected function createRequest() 
	{
		$this->ch = curl_init();

		if ($this->endpoint) {
			curl_setopt($this->ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($this->ch, CURLOPT_USERPWD, $this->login.':'.$this->password);
		}

		curl_setopt($this->ch, CURLOPT_URL, $this->url.$this->endpoint.$this->getParamterString());
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'GET');

		return $this;
	}

	public function getAddresses()
	{
		if (isset($this->cache['addresses'])) {
			return $this->cache['addresses'];
		}

		$this->endpoint = 'Adressen';

		return $this->cache['addresses'] = $this->clearResponse()->getResponse();
	}

	public function getAddressGroups()
	{
		if (isset($this->cache['addressGroups'])) {
			return $this->cache['addressGroups'];
		}

		$this->endpoint = 'Adressgruppen';

		return $this->cache['addressGroups'] = $this->clearResponse()->getResponse();
	}

	public function getCustomers()
	{
		if (isset($this->cache['customers'])) {
			return $this->cache['customers'];
		}

		$this->endpoint = 'Kunden';

		return $this->cache['customers'] = $this->clearResponse()->getResponse();
	}

	public function getCustomerGroups()
	{
		if (isset($this->cache['customerGroups'])) {
			return $this->cache['customerGroups'];
		}

		$this->endpoint = 'Kundengruppen';

		return $this->cache['customerGroups'] = $this->clearResponse()->getResponse();
	}

	public function getEndpoint() {
		return $this->endpoint;
	}

	public function getProduct($id)
	{
		foreach ($this->getProducts() AS $product) {
			if ($product->PK_ArtikelID == $id OR $product->Artikelnummer == $id) {
				return $product;
			}
		}

		return null;
	}

	public function getProducts()
	{		
		if (isset($this->cache['products'])) {
			return $this->cache['products'];
		}

		$this->endpoint = 'Artikel';
		return $this->cache['products'] = $this->clearResponse()->getResponse();
	}

	public function getProductGroup($id)
	{
		foreach ($this->getProductGroups() AS $productGroup) {
			if ($productGroup->PK_ArtikelgruppeKurz == $id) {
				return $productGroup;
			}
		}

		throw new \Exception('productGroup not found', 1);
	}

	public function getProductGroups()
	{
		if (isset($this->cache['productsGroups'])) {
			return $this->cache['productsGroups'];
		}

		$this->endpoint = 'Artikelgruppen';
		return $this->cache['productsGroups'] = $this->clearResponse()->getResponse();
	}

	public function getProjects()
	{
		if (isset($this->cache['projects'])) {
			return $this->cache['projects'];
		}

		$this->endpoint = 'Projekte';

		return $this->cache['projects'] = $this->clearResponse()->getResponse();
	}

	public function getProjectGroups()
	{
		if (isset($this->cache['projectGroups'])) {
			return $this->cache['projectGroups'];
		}

		$this->endpoint = 'Projektgruppen';

		return $this->cache['projectGroups'] = $this->clearResponse()->getResponse();
	}

	public function getSalesOrder($id)
	{
		foreach ($this->getSalesOrders() AS $salesOrder) {
			if ($salesOrder->PK_BelegID == $id) {
				return $salesOrder;
			}
		}

		return null;
	}

	public function getSalesOrders()
	{
		if (isset($this->cache['salesOrders'])) {
			return $this->cache['salesOrders'];
		}

		$this->endpoint = 'Verkaufsbelege';

		return $this->cache['salesOrders'] = $this->clearResponse()->getResponse();
	}

	public function getSalesOrderPosition($id)
	{
		foreach ($this->getSalesOrderPositions() AS $salesOrderPosition) {
			if ($salesOrderPosition->PK_BelegPosID == $id) {
				return $salesOrderPosition;
			}
		}

		throw new \Exception('position not found', 1);
	}

	public function getSalesOrderPositions($id = null)
	{
		if (isset($this->cache['salesOrderPositions'])) {
			if ($id) {
				foreach ($this->cache['salesOrderPositions'] AS $entry) {
					if ($entry->FK_BelegID == $id) {
						$result[] = $entry;
					}
				}

				return $result;
			}

			return $this->cache['salesOrderPositions'];
		}

		$this->endpoint = 'VerkaufsbelegPositionen';
		$this->cache['salesOrderPositions'] = $this->clearResponse()->getResponse();

		if (!$id) {
			return $this->getResponse();
		}

		foreach ($this->getResponse() AS $entry) {
			if ($entry->FK_BelegID == $id) {
				$result[] = $entry;
			}
		}

		return $result;
	}

	public function getSupportTickets()
	{
		if (isset($this->cache['supperTickets'])) {
			return $this->cache['supperTickets'];
		}

		$this->endpoint = 'Supportfaelle';

		return $this->cache['supperTickets'] = $this->clearResponse()->getResponse();
	}	

	public function getUsers()
	{
		if (isset($this->cache['users'])) {
			return $this->cache['users'];
		}

		$this->endpoint = 'Benutzer';

		return $this->cache['users'] = $this->clearResponse()->getResponse();
	}

	protected function getParamterString()
	{
		if (!$this->parameters) {
			return '';
		}

		foreach ($this->parameters AS $key => $value) {
			$pairs[] = implode('=', [$key, $value]);
		}

		return '?'.implode('&', $pairs);
	}

	protected static function getProperties($element) 
	{
		return $element->content->children('m', true)->properties->children('d', true);
	}

    public function getResponse() 
    {
    	if (!$this->endpoint) {
    		throw new Exception('no endpoint specified', 1);
    	}

    	if (!$this->response) {
    		$this->sendRequests();
    	}

    	return $this->response;
    }

	protected function sendRequest()
	{
		$this->createRequest();
		$this->setResponse(simplexml_load_string(curl_exec($this->ch)));
		curl_close($this->ch);

		return $this;
	}

    protected function sendRequests()
    {
    	do {
    		$this->sendRequest();

			if (!isset($this->parameters['$skip'])) {
				$this->parameters['$skip'] = $this->skipStep;
			} else {
				$this->parameters['$skip'] += $this->skipStep;
			}
    	} while (!$this->requestFinished);

    	$this->response = self::toStdClass($this->responseRaw);

    	return $this;
    }

	public function setEndpoint($endpoint) {
		$this->endpoint = $endpoint;

		return $this;
	}

    protected function setRequestData(array $data)
    {
    	$this
    		->clearRequestData()
    		->request = array_merge($this->request, $data);

    	return $this;
    }

    protected function setResponse($response) 
    {
    	if (isset($response->workspace)) {
    		if (isset($response->workspace->collection)) {
    			$this->response = $response->workspace->collection;
    		} else {
    			$this->response = $response->workspace;
    		}

    		return $this;
    	}

    	if (!isset($response->entry)) {
    		throw new Exception('not entry element found', 1);
    	}

    	$data = array();

    	foreach ($response->entry AS $entry) {
    		$data[] = self::getProperties($entry);
    	}

    	$this->requestFinished = count($data) % $this->skipStep !== 0;
    	$this->responseRaw = array_merge($this->responseRaw, $data);

		return $this;
    }

    protected static function toStdClass($collection) 
    {
    	$collection = json_decode(json_encode($collection));

    	foreach ($collection AS $entry) {
    		foreach ($entry AS $key => $value) {
    			if (is_object($value)) {
    				$entry->$key = null;
    			}

    			switch ($key) {
    				case 'EANNummer':
    					if (!is_numeric($value) OR $value == 0 OR floor(log10($value) + 1) != 13) {
    						$entry->$key = null;
    					}
    					break;	
    			}
    		}
    	}

    	return $collection;
    }
}