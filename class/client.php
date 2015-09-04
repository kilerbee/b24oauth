<?php
/**
 * Created by PhpStorm.
 * User: sigurd
 * Date: 04.09.15
 */

namespace Bitrix24;


class OAuthClient
{
	protected $clientId = '';
	protected $clientSecret = '';

	public function __construct($clientId, $clientSecret)
	{
		$this->clientId = $clientId;
		$this->clientSecret = $clientSecret;
	}
}