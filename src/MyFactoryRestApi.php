<?php

namespace Supsign\LaravelMfRest;

use Config;
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

	public function getSalesOrders()
	{
		$this->endpoint = 'Verkaufsbelege';

		return $this->getResponse();
	}

	public function getSalesOrderPositions()
	{
		$this->endpoint = 'VerkaufsbelegPositionen';

		return $this->getResponse();
	}

    public function getResponse() 
    {
    	if (!$this->response) {
    		$this->sendRequest();
    	}

    	// var_dump($this->response);

    	// $response = self::toStdClass($this->response);
    	$response = $this->response;

    	if (isset($response->workspace)) {
    		if (isset($response->workspace->collection)) {
    			return $response->workspace->collection;
    		}

    		return $response->workspace;
    	}

		if (isset($response->entry)) {
			return $response->entry;
		}

    	return $response;
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
		$i = 0;
		// var_dump(
		// 	$this->getSalesOrderPositions()
		// );

		foreach ($this->getSalesOrderPositions() AS $pos) {


			var_dump($pos->content->children('m', true)->properties->children('d', true));

			// foreach ($pos->title AS $key => $value) {
			// 	var_dump($key, $value);
			// }


			if ($i++ === 10)
				break;
		}

		$this->response = null;

		return $this;
	}
}