<?php

require_once ('serv.php');


// Happn REST API

class Happn extends Service
  {
  public function __construct ()
    {
    parent::__construct ('https://api.happn.fr');
    }


  // Authentication data

  public $fb_token       = NULL;

  private $client_id     = 'FUE-idSEP-f7AqCyuMcPr2K-1iCIU_YlvK-M-im3c';
  private $client_secret = 'brGoHSwZsPjJ-lBk0HqEXVtb3UFu-y5l_JcOjD-Ekv';

  private $access_token  = NULL;
  private $refresh_token = NULL;
  private $expires_at    = NULL;

  public $user_id        = NULL;
  public $device_id      = '7afd6a7b-d4db-4b7b-8384-c27e02ef02e2';


  // Load FB data from JSON file

	private function fb_load ()
		{
		$result = FALSE;

		while (TRUE)
			{
			$path = '../lib/fb.json';
			if (!is_file ($path)) break;

			$text = file_get_contents ($path);
			if (!$text) break;

			$map = json_decode ($text, TRUE); // return association
			if (!$map) break;

      if (isset ($map ['fb_token'])) $this->fb_token = $map ['fb_token'];

			$result = TRUE;
			break;
			}

		return $result;
		}


  // Load authentication data from JSON file

	private function auth_load ()
		{
		$result = FALSE;

		while (TRUE)
			{
			$path = '../lib/auth.json';
			if (!is_file ($path)) break;

			$text = file_get_contents ($path);
			if (!$text) break;

			$map = json_decode ($text, TRUE); // return association
			if (!$map) break;

      if (isset ($map ['access_token']))  $this->access_token  = $map ['access_token'];
      if (isset ($map ['refresh_token'])) $this->refresh_token = $map ['refresh_token'];
      if (isset ($map ['expires_at']))    $this->expires_at    = $map ['expires_at'];
      if (isset ($map ['user_id']))       $this->user_id       = $map ['user_id'];

			$result = TRUE;
			break;
			}

		return $result;
		}


  // Save authentication data to JSON file

	private function auth_save ()
		{
		$result = FALSE;

		while (TRUE)
			{
      $map = array ();

      if (!is_null ($this->access_token))  $map ['access_token']  = $this->access_token;
      if (!is_null ($this->refresh_token)) $map ['refresh_token'] = $this->refresh_token;
      if (!is_null ($this->expires_at))    $map ['expires_at']    = $this->expires_at;
      if (!is_null ($this->user_id))       $map ['user_id']       = $this->user_id;

			$text = json_encode ($map);
			if (!$text) break;

			$path = '../lib/auth.json';
			$result = file_put_contents ($path, $text);
			break;
			}

		return $result;
		}


  // Create access & refresh token from FB token

  private function token_create ()
    {
    $h = array (
      'Content-Type: application/x-www-form-urlencoded; charset=utf-8'
      );

    $q = array (
      'client_id'      => $this->client_id,
      'client_secret'  => $this->client_secret,
      'grant_type'     => 'assertion',
      'assertion_type' => 'facebook_access_token',
      'assertion'      => $this->fb_token,
      'scope'          => 'mobile_app'
      );

    $b = http_build_query ($q);

    $r = $this->exec ('POST', '/connect/oauth/token', $h, $b);
    $m = json_decode ($r, TRUE);

    if (isset ($m ['error_code']) && $m ['error_code'] == 0)
      {
      $this->access_token  = $m ['access_token'];
      $this->refresh_token = $m ['refresh_token'];
      $this->expires_at    = time () + $m ['expires_in'];
      $this->user_id       = $m ['user_id'];
      }

    return $m;
    }


  // Refresh access & refresh token

  private function token_refresh ()
    {
    $h = array (
      'Content-Type: application/x-www-form-urlencoded; charset=utf-8'
      );

    $q = array (
      'grant_type'    => 'refresh_token',
      'refresh_token' => $this->refresh_token,
      'client_id'     => $this->client_id,
      'client_secret' => $this->client_secret
      );

    $b = http_build_query ($q);

    $r = $this->exec ('POST', '/connect/oauth/token', $h, $b);
    $m = json_decode ($r, TRUE);

    if (isset ($m ['error_code']) && $m ['error_code'] == 0)
      {
      $this->access_token  = $m ['access_token'];
      $this->refresh_token = $m ['refresh_token'];
      $this->expires_at    = time () + $m ['expires_in'];
      $this->user_id       = $m ['user_id'];
      }

    return $m;
    }


  // Authentication procedure

  public function auth ($delay = 0)
    {
    $res = FALSE;

    while (TRUE)
      {
      $this->auth_load ();

      // First time authentication

      if (is_null ($this->user_id))
        {
        $this->fb_load ();

        if (is_null ($this->fb_token)) break;

        $m = $this->token_create ();
        if (is_null ($this->user_id)) break;

        $this->auth_save ();

        $res = TRUE;
        break;
        }

      // Check expiration time and refresh tokens
      // with delay needed for script completion

      if ($this->expires_at <= time () + $delay)
        {
        $m = $this->token_refresh ();

        $this->auth_save ();

        $res = TRUE;
        break;
        }

      // Tokens are still valid

      $res = TRUE;
      break;
      }

    return $res;
    }


  // API invocation

  public function invoke ($method, $url, $headers = NULL, $body = NULL)
    {
    // Common headers

    $h = array (
      'User-Agent: Happn/19.1.0 AndroidSDK/19',
      'Authorization: OAuth="' . $this->access_token . '"'
      );

    // Issue #11 : new header with device identifier

    if (!is_null ($this->device_id))
      $h [] = 'X-Happn-DID: ' . $this->device_id;

    if (!is_null ($headers))
      {
      $h = array_merge ($h, $headers);
      }

    $r = $this->exec ($method, $url, $h, $body);
    return json_decode ($r, TRUE);
    }


  // Set position (old way)

  public function pos ($latitude, $longitude)
    {
    $h = array (
      'Content-Type: application/json'
      );

    // Looks like Happn hates high precision
    // Use same precision as Google Maps

    $latitude  = round ($latitude,  6);
    $longitude = round ($longitude, 6);

    $m = array (
      'alt'       => 0.0,
      'latitude'  => $latitude,
      'longitude' => $longitude
      );

    $b = json_encode ($m);

    $r = $this->invoke ('POST', '/api/users/' . $this->user_id . '/position/', $h, $b);
    return $r;
    }

  /*
  // TODO: tune this function after understanding how device is created

  public function dev ()
    {
    $headers = array (
      'Content-Type'  => 'application/json'
      );

    $map = array (
      'app_build'   => '19.1.0',
      'country_id'  => 'FR',
      'gps_adid'    => '05596566-c7c7-4bc7-a6c9-729715c9ad98',
      'idfa'        => 'f550c51fa242216c',
      'language_id' => "fr",
      'os_version'  => '19',
      'token'       => 'APA91bE3axREMeqEpvjkIOWyCBWRO1c4Zm69nyH5f5a7o9iRitRq96ergzyrRfYK5hsDa_-8J35ar7zi5AZFxVeA6xfpK77_kCVRqFmbayGuYy7Uppy_krXIaTAe8Vdd7oUoXJBA7q2vVnZ6hj9afmju9C3vMKz-KA,',
      'type'        => 'android'
      );

    $body = json_encode ($map);

    $r = $this->invoke ('PUT', '/api/users/' . $this->user_id . '/devices/' . ??? , $headers, $body);
    return json_decode ($r, TRUE);
    }
    */


  // Get user information

  public function user_get ($fields, $id = NULL)
    {
    $m = array (
      'fields' => $fields
      );

    $q = http_build_query ($m);

    if (is_null ($id)) $id = $this->user_id;

    $r = $this->invoke ('GET', '/api/users/' . $id . '?' . $q);
    return $r;
    }


  // Accepting

  public function accept ($user_id)
    {
    $h = array (
      'Content-Type: application/json'
      );

    $m = array (
      'id' => $user_id
      );

    $b = json_encode ($m);

    $r = $this->invoke ('POST', '/api/users/' . $this->user_id . '/accepted', $h, $b);
    return $r;
    }


  public function unaccept ($user_id)
    {
    $r = $this->invoke ('DELETE', '/api/users/' . $this->user_id . '/accepted/' . $user_id);
    return $r;
    }


  public function accepted ($fields, $offset = 0, $limit = 10)
    {
    $m = array (
      'offset' => $offset,
      'limit' => $limit,
      'fields' => $fields
      );

    $q = http_build_query ($m);

    $r = $this->invoke ('GET', '/api/users/' . $this->user_id . '/accepted/?' . $q);
    return $r;
    }


  // Rejecting

  public function reject ($user_id)
    {
    $h = array (
      'Content-Type: application/json'
      );

    $m = array (
      'id' => $user_id
      );

    $b = json_encode ($m);

    $r = $this->invoke ('POST', '/api/users/' . $this->user_id . '/rejected', $h, $b);
    return $r;
    }


  public function unreject ($user_id)
    {
    $r = $this->invoke ('DELETE', '/api/users/' . $this->user_id . '/rejected/' . $user_id);
    return $r;
    }


  public function rejected ($fields, $offset = 0, $limit = 10)
    {
    $m = array (
      'offset' => $offset,
      'limit' => $limit,
      'fields' => $fields
      );

    $q = http_build_query ($m);

    $r = $this->invoke ('GET', '/api/users/' . $this->user_id . '/rejected/?' . $q);
    return $r;
    }


  // Notifications

  public function notif ($fields, $offset = 0, $limit = 10)
    {
    $m = array (
      'types' => 468,
      'offset' => $offset,
      'limit' => $limit,
      'fields' => $fields
      );

    $q = http_build_query ($m);

    $r = $this->invoke ('GET', '/api/users/' . $this->user_id . '/notifications/?' . $q);
    return $r;
    }


  // Conversations

  public function conv ($fields, $offset = 0, $limit = 10)
    {
    $m = array (
      'offset' => $offset,
      'limit' => $limit,
      'fields' => $fields
      );

    $q = http_build_query ($m);

    $r = $this->invoke ('GET', '/api/users/' . $this->user_id . '/conversations/?' . $q);
    return $r;
    }

  }  // Happn REST API

?>
