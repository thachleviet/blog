<?php

namespace App\Services;


use GuzzleHttp\Client;

class GuzzleHttp
{
    //protected $_client;
    private $_shopDomain;
    private $_accessToken;
    public function __construct()
    {
        //$this->_client = new Client();
    }


    public function getAccessToken($accessToken = '', $shopDomain = '')
    {
        $this->_shopDomain = !empty($shopDomain) ? $shopDomain : session('shopDomain');
        $this->_accessToken = !empty($accessToken) ? $accessToken : session('accessToken');
    }

    /**
     * @param $url
     * @param array $data
     * @return mixed
     */
    public function get($url, array $data = [])
    {
	    $client = new Client();

	    $response = $client->request(
	    	'GET',
		    "https://$this->_shopDomain/admin/$url",
		    [
	    	'headers' => [
	    		'Content-Type' => 'application/json',
			    'X-Shopify-Access-Token' => $this->_accessToken
		    ],
		    'query' => $data
		    ]
	    );
        return json_decode($response->getBody()->getContents());
    }

    /**
     * @param $url
     * @param array $data
     * @return mixed
     */
    public function post($url, $data = [])
    {
	    $client = new Client();

	    $response = $client->request('POST',
            "https://$this->_shopDomain/admin/$url",
            [
                'query' => [
                    'access_token' => $this->_accessToken
                ],
                'form_params' => $data
            ]);

        return json_decode($response->getBody()->getContents());
    }

	/**
	 * @param $url
	 * @param array $data
	 *
	 * @return mixed
	 */
    public function put($url, $data = [])
    {
	    $client = new Client();

	    $response = $client->request('PUT',
		    "https://$this->_shopDomain/admin/$url",
		    [
			    'query' => [
				    'access_token' => $this->_accessToken
			    ],
			    'form_params' => $data
		    ]);

	    return json_decode($response->getBody()->getContents());
    }


	/**
	 * @param $url
	 * @param array $data
	 *
	 * @return mixed
	 */
	public function drop($url)
	{
		$client = new Client();

		$response = $client->request('DELETE',
			"https://$this->_shopDomain/admin/$url",
			[
				'query' => [
					'access_token' => $this->_accessToken
				]
			]);

		return json_decode($response->getBody()->getContents());
	}
}