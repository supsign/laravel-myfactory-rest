<?php

namespace Supsign\LaravelMfApi;

use Config;

class MyFactoryApi
{
    protected
    	$ch = null,
        $client = null,
        $endpoint = '',
        $login = 'supsignHub',
        $password = 'kH5bI7sT3oJ3iN0k',
        $request = array(),
        $response = null,
        $url = 'https://cloud.myfactory-ondemand.ch/saas/odata_howecekavikubisojoqa33/';

	public function __construct() 
	{

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

	public function getEndPoints()
	{
		$this->endpoint = '';

		$workspace = $this->getResponse()->workspace;

		var_dump(
			$workspace->collection
		);




		// $results = $this->getResponse()->response->workspace->collection;

		// foreach ($results AS $result) {
		// 	var_dump(
		// 		$result->{'@attributes'}->href
		// 	);
		// }



	}

    public function getResponse() 
    {
    	if (!$this->response) {
    		$this->sendRequest();
    	}

        return $this->response;
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

    protected function setRequestData(array $data)
    {
    	$this
    		->clearRequestData()
    		->request = array_merge($this->request, $data);

    	return $this;
    }

	public function test() 
	{
		$this->getEndPoints();

		return $this;
	}
}