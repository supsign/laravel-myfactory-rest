<?php

namespace Supsign\LaravelMfRest;

use Config;
use Exception;
use SimpleXMLElement;

class MyFactoryRestApi
{
    protected
    	$ch = null,
        $client = null,
        $endpoint = '',
        $endpoints = array(),
        $login = null,
        $password = null,
        $request = array(),
        $response = null,
        $url = null;

	public function __construct() 
	{
		$this->login = env('MF_REST_LOGIN');
		$this->password = env('MF_REST_PASSWORD');
		$this->url = env('MF_REST_URL');

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

		curl_setopt($this->ch, CURLOPT_URL, $this->url.$this->endpoint);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'GET');

		return $this;
	}

	public function getProduct($id)
	{
		foreach ($this->getProducts() AS $product) {
			$product = self::getProperties($product);

			if ($product->PK_ArtikelID == $id OR $product->Artikelnummer == $id) {
				return $product;
			}
		}

		return null;
	}

	public function getProducts()
	{
		$this->endpoint = 'Artikel';

		return $this->getResponse();
	}

	public function getSalesOrder($id)
	{
		foreach ($this->getSalesOrders() AS $salesOrder) {
			$salesOrder = self::getProperties($salesOrder);

			if ($salesOrder->PK_BelegID == $id) {
				return $salesOrder;
			}
		}

		return null;
	}

	public function getSalesOrders()
	{
		$this->endpoint = 'Verkaufsbelege';

		return $this->getResponse();
	}

	public function getSalesOrderPosition($id)
	{
		foreach ($this->getSalesOrderPositions() AS $salesOrderPosition) {
			$salesOrderPosition = self::getProperties($salesOrderPosition);

			if ($salesOrderPosition->PK_BelegPosID == $id) {
				return $salesOrderPosition;
			}
		}

		return null;
	}

	public function getSalesOrderPositions($id)
	{
		$positions = array();
		$this->endpoint = 'VerkaufsbelegPositionen';

		foreach ($this->getResponse() AS $salesOrderPosition) {
			$salesOrderPosition = self::getProperties($salesOrderPosition);

			if ($salesOrderPosition->FK_BelegID == $id) {
				$positions[] = $salesOrderPosition;
			}
		}

		return $positions;
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
    		$this->sendRequest();
    	}

    	if (isset($this->response->workspace)) {
    		if (isset($this->response->workspace->collection)) {
    			return $this->response->workspace->collection;
    		}

    		return $this->response->workspace;
    	}

		if (isset($this->response->entry)) {
			return $this->response->entry;
		}

    	return $this->response;
    }

	protected function sendRequest() 
	{
		if (!$this->ch) {
			$this->createRequest();
		}

		$this->response = simplexml_load_string(curl_exec($this->ch));
		curl_close($this->ch);

		return $this;
	}

    protected function setRequestData(array $data)
    {
    	$this
    		->clearRequestData()
    		->request = array_merge($this->request, $data);

    	return $this;
    }

    protected static function toStdClass($element) 
    {
    	return json_decode(json_encode($element));
    }

	public function test() 
	{

		var_dump(
			$this->getProduct('A001272')
		);



		$this->response = null;

		return $this;
	}
}