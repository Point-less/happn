<?php

// HTTP Service

class Service
  {
  private $endpoint = NULL;

  public function __construct ($endpoint)
    {
    $this->endpoint = $endpoint;
    }

  public function exec ($method, $url, $headers = NULL, $body = NULL)
    {
    $u = $this->endpoint . $url;
		$c = curl_init ($u);

    switch ($method)
      {
      case 'POST':
        curl_setopt ($c, CURLOPT_POST, TRUE);
        break;

      case 'PUT':
        curl_setopt ($c, CURLOPT_PUT, TRUE);
        break;

      case 'DELETE':
        curl_setopt ($c, CURLOPT_CUSTOMREQUEST, 'DELETE');
        break;

      }

    if (!is_null ($headers))
      {
      curl_setopt ($c, CURLOPT_HTTPHEADER, $headers);
      }

    if (!is_null ($body))
      {
      curl_setopt ($c, CURLOPT_POSTFIELDS, $body);
      }

		curl_setopt ($c, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt ($c, CURLOPT_SSL_VERIFYPEER, FALSE);

    //curl_setopt ($c, CURLOPT_VERBOSE, TRUE);
    //$f = fopen ('/mnt/home/www/log/curl.log', "a");
    //curl_setopt ($c, CURLOPT_STDERR, $f);

		$r = curl_exec ($c);
		if (!$r) die ('curl_exec:' . curl_error ($c) . '\n');

		curl_close ($c);
    //fclose ($f);
    return $r;
    }
  
  }  // Service

?>
