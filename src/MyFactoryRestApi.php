<?php

namespace Supsign\LaravelMfRest;

use Supsign\Laravel\BaseApi;

class MyFactoryRestApi extends BaseApi
{
	protected array $headers = [
		'Accept' => 'application/json'
	];

	public function __construct() 
	{
		$this->clientId = env('MF_REST_LOGIN');
		$this->clientSecret = env('MF_REST_PASSWORD');
		$this->baseUrl = env('MF_REST_URL');

		return $this->useBasicAuth();
	}

	public function getAddresses(): array
	{
		return $this->makeCall('Adressen');
	}

	public function getAddressGroups(): array
	{
		return $this->makeCall('Adressgruppen');
	}

	public function getCustomers(): array
	{
		return $this->makeCall('Kunden');
	}

	public function getCustomerGroups(): array
	{
		return $this->makeCall('Kundengruppen');
	}

	public function getProducts(): array
	{		
		return $this->makeCall('Artikel');
	}

	public function getProductGroups(): array
	{
		return $this->makeCall('Artikelgruppen');
	}

	public function getProjects(): array
	{
		return $this->makeCall('Projekte');
	}

	public function getProjectGroups(): array
	{
		return $this->makeCall('Projektgruppen');
	}

	public function getRessources(): array
	{
		return $this->makeCall('Ressource');
	}

	public function getSalesOrders(): array
	{
		return $this->makeCall('Verkaufsbelege');
	}

	public function getSalesOrderPositions(?int $salesOrderId = null): array
	{
		return $this->makeCall('VerkaufsbelegPositionen');
	}

	public function getSupportTickets(): array
	{
		return $this->makeCall('Supportfaelle');
	}

	public function getTargetWorkingTime(): array
	{
		return $this->makeCall('idcMitarbeiterSollArbeitszeiten');
	}

	public function getTimeEntries(): array
	{
		return $this->makeCall('Zeiteintraege');
	}

	public function getUsers()
	{
		return $this->makeCall('Benutzer');
	}

    protected function getResponse(): array|object
    {
        return $this->response->value;
    }
}