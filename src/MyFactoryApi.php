<?php

namespace Supsign\LaravelMfApi;

use Config;

class MyFactoryApi
{
    protected
        $client = null,
        $request = array(),
        $response = null;

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

    public function getResponse() 
    {
        return $this->response;
    }

    protected function setRequestData(array $data)
    {
    	$this
    		->clearRequestData()
    		->request = array_merge($this->request, $data);

    	return $this;
    }
}