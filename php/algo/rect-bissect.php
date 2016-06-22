<?php

require_once ('../lib/api.php');


// TODO: group the common code in an 'algo.php' file

// API initialization

$serv = new Happn ();


// Set position

function pos_set ($long, $lat)
  {
  global $serv;

  $r = $serv->auth (60);  // 1 minute
  if (!$r) die ("Authentication failure !\n");

  echo "long,lat=$long,$lat\n";

  $m = $serv->pos ($long, $lat);
  echo var_dump ($m) . "\n";

  // Wait 30 minutes between two position updates
  // otherwise Happn complains with HTTP 429

  sleep (1800);
  }


// Load area data from JSON configuration file

$m = json_load ('rect.json');
if (!$m) die ("Failed to load area data !\n");

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
