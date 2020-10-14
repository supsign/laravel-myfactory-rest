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

	protected function clearResponse()
	{
		$this->response = null;

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

	public function getProduct($id)
	{
		$this->clearResponse();

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

		return null;
	}

	public function getSalesOrderPositions($id = null)
	{
		if (isset($this->cache['salesOrderPositions']) AND !$id) {
			return $this->cache['salesOrderPositions'];
		}

		$positions = array();
		$this->endpoint = 'VerkaufsbelegPositionen';

		foreach ($this->getResponse() AS $salesOrderPosition) {
			if (!$id OR $salesOrderPosition->FK_BelegID == $id) {
				$positions[] = $salesOrderPosition;
			}
		}

		if (!$id) {
			$this->cache['salesOrderPositions'] = $positions;
		}

		return $positions;
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

    protected function getResponse() 
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

    protected static function toStdClass($element) 
    {
    	return json_decode(json_encode($element));
    }
}