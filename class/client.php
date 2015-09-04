<?php
/**
 * Created by PhpStorm.
 * User: sigurd
 * Date: 04.09.15
 */

namespace Bitrix24;

require_once("clientexception.php");

class OAuthClient
{
	const AUTHORIZE_URL = "/oauth/authorize/";
	const TOKEN_URL = "/oauth/token/";

	const PARAM_CODE = "code";
	const PARAM_STATE = "state";

	const CID_STATE_PARAM = "cid";
	const CID_SESSION_PARAM = "bitrix24net_cid";

	protected $portal;
	protected $clientId;
	protected $clientSecret;
	protected $scope = array();

	protected $state = null;

	public function __construct($portalUrl, $clientId, $clientSecret, array $scope)
	{
		$this->clientId = $clientId;
		$this->clientSecret = $clientSecret;
		$this->portal = $portalUrl;
		$this->scope = $scope;

		if(!preg_match("/http[s]{0,1}:\/\//", $this->portal))
		{
			$this->portal = "https://".$this->portal."/";
		}
	}

	public function getAuthorizeUrl(array $stateParams = array())
	{
		$stateParams[static::CID_STATE_PARAM] = $this->getCidValue();

		return $this->buildAuthorizeUrl(http_build_query($stateParams));
	}

	public function buildAuthorizeUrl($state = "")
	{
		$query = array(
			"response_type" => "code",
			"client_id" => $this->clientId,
			"scope" => $this->prepareScope(),
		);

		if($state !== "")
		{
			$query["state"] = $state;
		}

		return $this->portal.static::AUTHORIZE_URL."?".http_build_query($query);
	}

	public function isAuthCodeSent()
	{
		return isset($_REQUEST[static::PARAM_CODE]);
	}

	public function getCode()
	{
		return $_REQUEST[static::PARAM_CODE];
	}

	public function getState()
	{
		if($this->state === null)
		{
			$this->state = array();
			if(isset($_REQUEST[static::PARAM_STATE]))
			{
				parse_str($_REQUEST[static::PARAM_STATE], $this->state);
			}
		}

		return $this->state;
	}

	public function getAuth($refresh_token = null)
	{
		if($refresh_token === null)
		{
			if($this->isAuthCodeSent())
			{
				if($this->checkCidValue())
				{
					$query = array(
						"grant_type" => "authorization_code",
						"client_id" => $this->clientId,
						"client_secret" => $this->clientSecret,
						"code" => $this->getCode(),
					);

					return $this->query($this->portal . static::TOKEN_URL . "?" . http_build_query($query));
				}
				else
				{
					throw new OAuthClientException("Security check failed", OAuthClientException::WRONG_CID);
				}
			}
			else
			{
				throw new OAuthClientException("No auth code", OAuthClientException::NO_CODE);
			}
		}
		else
		{
			$query = array(
				"grant_type" => "refresh_token",
				"client_id" => $this->clientId,
				"client_secret" => $this->clientSecret,
				"refresh_token" => $refresh_token,
			);

			return $this->query($this->portal . static::TOKEN_URL . "?" . http_build_query($query));
		}
	}

	protected function prepareScope()
	{
		return implode(",",$this->scope);
	}

	protected function getCidValue()
	{
		if(!isset($_SESSION[static::CID_SESSION_PARAM]))
		{
			$_SESSION[static::CID_SESSION_PARAM] = array();
		}

		if(!isset($_SESSION[static::CID_SESSION_PARAM][$this->portal]))
		{
			$_SESSION[static::CID_SESSION_PARAM][$this->portal] = md5(session_id().uniqid(rand(), true));
		}

		return $_SESSION[static::CID_SESSION_PARAM][$this->portal];
	}

	protected function checkCidValue()
	{
		print_r($_SESSION);

		$state = $this->getState();

		print_r($state);

		$checkResult = isset($state[static::CID_STATE_PARAM]) && $state[static::CID_STATE_PARAM] == $this->getCidValue();

		$this->clearCidValue();

		return $checkResult;
	}

	protected function clearCidValue()
	{
		unset($_SESSION[static::CID_SESSION_PARAM][$this->portal]);
	}

	protected function query($url)
	{
		if(function_exists("curl_init"))
		{
			$curlOptions = array(CURLOPT_RETURNTRANSFER => true);

			$curl = curl_init($url);
			curl_setopt_array($curl, $curlOptions);
			$result = curl_exec($curl);

			return json_decode($result, 1);
		}
		else
		{
			throw new OAuthClientException("cURL library required", OAuthClientException::CURL_REQUIRED);
		}
	}
}




