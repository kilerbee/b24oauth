<?php
/**
 * Created by PhpStorm.
 * User: sigurd
 * Date: 04.09.15
 * Time: 13:11
 */

namespace Bitrix24;


class OAuthClientException extends \Exception
{
	const NO_CODE = 1;
	const WRONG_CID = 2;
	const CURL_REQUIRED = 4;
}