<?php

namespace Supsign\LaravelMfRest;

use Illuminate\Support\Facades\Cache;
use Supsign\Laravel\BaseApi;

class MyFactoryRestApi extends BaseApi
{
	protected array $headers = [
		'Accept' => 'application/json'
	];
	protected int $perPage = 5000;

	public function __construct() 
	{
		$this->clientId = env('MF_REST_LOGIN');
		$this->clientSecret = env('MF_REST_PASSWORD');
		$this->baseUrl = env('MF_REST_URL');

		return $this->useBasicAuth();
	}

	public function getAddresses(): array
	{
		return $this->depaginate('Adressen');
	}

	public function getAddressGroups(): array
	{
		return $this->depaginate('Adressgruppen');
	}

	public function getCustomEndpoint(string $endpoint): array
	{
		return $this->depaginate($endpoint);
	}

	public function getCustomers(): array
	{
		return $this->depaginate('Kunden');
	}

	public function getCustomerGroups(): array
	{
		return $this->depaginate('Kundengruppen');
	}

	public function getProducts(): array
	{		
		$products = $this
			->useCache()
			->depaginate('Artikel');

		$this->useCache = false;

		return $products;
	}

	public function getProductGroups(): array
	{
		return $this->depaginate('Artikelgruppen');
	}

	public function getProjects(): array
	{
		return $this->depaginate('Projekte');
	}

	public function getProjectGroups(): array
	{
		return $this->depaginate('Projektgruppen');
	}

	public function getRessources(): array
	{
		return $this->depaginate('Ressource');
	}

	public function getSalesOrders(): array
	{
		return $this->depaginate('Verkaufsbelege');
	}

	public function getSalesOrderPositions(): array
	{
		return $this->depaginate('VerkaufsbelegPositionen');
	}

	public function getSupportTicketActions(): array
	{
		return $this->depaginate('Supportfallaktionen');
	}

	public function getSupportTickets(): array
	{
		return $this->depaginate('Supportfaelle');
	}

	public function getTargetWorkingTime(): array
	{
		return $this->depaginate('idcMitarbeiterSollArbeitszeiten');
	}

	public function getTimeEntries(): array
	{
		return $this->depaginate('Zeiteintraege');
	}

	public function getUsers()
	{
		return $this->depaginate('Benutzer');
	}

    protected function cacheResponse(): self
    {
        return $this;
    }

    protected function getCacheKey(): string
    {
        if (empty($this->endpoint)) {
            throw new \Exception('no endpoint was specified');
        }

        return static::class.':'.$this->endpoint;
    }

    protected function loadResponseFromCache(): bool
    {
        return false;
    }

	protected function depaginate(string $endpoint): array
	{
		$this->setEndpoint($endpoint);

		if ($this->useCache && Cache::has($this->getCacheKey())) {
			return Cache::get($this->getCacheKey());
		}

		$page = 0;
		$result = [];

		while (true) {
			$response = $this->makeCall($endpoint, ['$skip' => $page]);
			$result = array_merge($result, $response->value);

			if (empty($response->{'odata.nextLink'})) {
				break;
			}

			$page += $this->perPage;
		}

		if ($this->useCache) {
			Cache::put($this->getCacheKey(), $result, $this->cacheLifetime * 60);
		}

		return $result;
	}
}