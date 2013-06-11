<?php 
/**
  * Copyright (C) 2012 b-sm@rk ltd. <besmark@b-smark.com>
  *
  * Permission is hereby granted, free of charge, to any person
  * obtaining a copy of this software and associated documentation files
  * (the "Software"), to deal in the Software without restriction,
  * including without limitation the rights to use, copy, modify, merge,
  * publish, distribute, sublicense, and/or sell copies of the Software,
  * and to permit persons to whom the Software is furnished to do so,
  * subject to the following conditions: 
  *
  * The above copyright notice and this permission notice shall be
  * included in all copies or substantial portions of the Software. 
  *
  * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
  * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
  * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
  * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
  * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
  * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
  * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE. 
 **/

include_once 'OAuth2Exception.php';
include_once 'OAuth2Client.php';

class MysmSDK extends OAuth2Client {

	function __construct( $client_id, $client_secret ) {
		$config = array(
			"base_uri" => "https://www.mysmark.com/api", 
			"client_id" => $client_id,
			"client_secret" => $client_secret, 
			"access_token_uri" => 
                            "https://www.mysmark.com/oauth2/token" );
		parent::__construct( $config );
	}

	public function getAccessToken() {
		$session = $this->getSession();
		if( isset($session['access_token']) )
		return $session['access_token'];
		$response = json_decode(
		$this->makeRequest(
		$this->conf['access_token_uri'],
				"POST", 
		array(
					"grant_type" => "client_credentials",
					"client_id" => $this->conf['client_id'], 
					"client_secret" => $this->conf['client_secret'] )
		), TRUE );
		if( !is_array($response) || 
			!array_key_exists( "access_token", $response ) ) {
			throw new OAuth2Exception( $response );
		}
		$this->setSession( $this->getSessionObject( $response ), FALSE );
		return $response["access_token"];
	}
}

?>
