<?php

namespace Supsign\LaravelMfRest;

use Config;
use SimpleXMLElement;

class MyFactoryRestApi
{
    protected
    	$ch = null,
        $client = null,
        // $endpoint = '',
        $endpoints = array(),
        $login = 'supsignHub',
        $password = 'kH5bI7sT3oJ3iN0k',
        $request = array(),
        $response = null,
        $url = 'https://cloud.myfactory-ondemand.ch/saas/odata_howecekavikubisojoqa33/';

	public function __construct() 
	{
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

		curl_setopt($this->ch, CURLOPT_URL, $this->url.$this->endpoint);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'GET');

		return $this;
	}

	protected function getEndPoints()
	{
		return $this->endpoints ?: $this->queryEndPoints()->endpoints;
	}

    public function getResponse() 
    {
    	if (!$this->response) {
    		$this->sendRequest();
    	}

        return $this->response;
    }

    protected function queryEndPoints()
	{
		$this->endpoint = '';
		$workspace = self::toStdClass($this->getResponse()->workspace);

		foreach ($workspace->collection AS $endpoint) {
			$this->endpoints[] = $endpoint->{'@attributes'}->href;
		}

		return $this;
	}

	protected function sendRequest() 
	{
		if (!$this->ch) {
			$this->createRequest();
		}

		$this->response = simplexml_load_string(curl_exec($this->ch) );
		curl_close($this->ch);

		return $this;
	}

	protected function setEndPoint($endpoint) 
	{
		if (!in_array($endpoint, $this->getEndPoints())) {
			throw new \Exception('invalid Endpoint', 1);
		}

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

    protected static function toStdClass($element) 
    {
    	return json_decode(json_encode($element));
    }

	public function test() 
	{
		$this->setEndPoint('test');

		return $this;
	}
}