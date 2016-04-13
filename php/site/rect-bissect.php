<?php

require_once ('api.php');


// API initialization

$serv = new Happn ();
$r = $serv->auth (84600);  // 1 day
if (!$r) die ("Authentication failure !\n");


// Set position

function pos_set ($long, $lat)
  {
  global $serv;

  $long = round ($long, 6);  // same precision as Google Map
  $lat  = round ($lat,  6);

  echo "long= $long lat= $lat\n";

  $m = $serv->pos ($long, $lat);
  echo var_dump ($m) . "\n";

  // Wait 15 minutes between two position updates
  // otherwise Happn complains with HTTP 429

  // Looks like Happn still complains event with that delay
  // Maybe a fake position detection algorithm ?

  sleep (900);
  }


// Load area from JSON configuration file

function load ()
  {
  $m = NULL;

  while (TRUE)
    {
		$p = 'area.json';
		if (!is_file ($p)) break;

		$t = file_get_contents ($p);
		if (!$t) break;

		$m = json_decode ($t, TRUE); // return association
		break;
		}

	return $m;
	}


$m = load ();

$xmin = $m ['lat_min'];
$xmax = $m ['lat_max'];

$ymin = $m ['long_min'];
$ymax = $m ['long_max'];

// Approximate north-south dimension of the square is (in km):
// xsize = 6371.0 * PI / 180 * (xmax - xmin)
// TODO: compute maximum bissect level for 500 m resolution

$bmax = 4;  // max bissect


// The rectangle bissect algorithm (sequential)

pos_set ($xmin, $ymin);

$xsize = $xmax - $xmin;
$ysize = $ymax - $ymin;

for ($b = 0; $b < $bmax; $b++)
  {
  $d = 1 << $b;  // divisor

  $xstep = $xsize / $d;
  $ystep = $ysize / $d;

  $x = $xmin;

  for ($n = 0; $n <= $d; $n++)
    {
    $y = $ymin;

    for ($p = 0; $p <= $d; $p++)
      {
      if (($n & 1) || ($p & 1))  // skip already plotted
        {
        pos_set ($x, $y);
        }

      $y += $ystep;
      }

    $x += $xstep;
    }
  }

?>
