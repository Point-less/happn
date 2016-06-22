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

$m = json_load ('square.json');
if (!$m) die ("Failed to load area data !\n");

$x0 = $m ['lat'];
$y0 = $m ['long'];
$r0 = $m ['radius'];


// Approximate length from latitude is (in km):
// dx = r1 * PI / 180 * delta (lat)
// where r1 is the earth radius
// and depends on latitude for longitude:
// dy = cos (lat) * r1 * PI / 180 * delta (long)

$i0 = 0.5;  // Happn meet distance (in km)
$r1 = 6370.0;  // Earth radius (in km)


// The square spiral algorithm (sequential)

$ix = $i0 * 180 / (pi () * $r1);
$iy = $ix / cos ($x0 * pi () / 180);
echo "ix=$ix iy=$iy\n";

$m = $r0 / $i0;
$s = 2 * (1 + $m) - 1;
echo "m=$m s=$s\n";
echo "\n";

$x = $x0;
$y = $y0;

pos_set ($x, $y);

for ($p = 1; $p <= $s; $p++)
  {
  $d = ($p & 1) ? 1 : -1;

  $stop = FALSE;

  for ($n = 1; $n <= $p; $n++)
    {
    $x += $d * $ix;

    pos_set ($x, $y);

    // Finished the radius box ?

    if ($p == $s && $n + 1 == $p)
      {
      $stop = TRUE;
      break;
      }
    }

  if (!$stop)
    {
    for ($n = 1; $n <= $p; $n++)
      {
      $y += $d * $iy;

      pos_set ($x, $y);
      }
    }
  }

// Back to center

for ($n = 1; $n <= $m; $n++)
  {
  $x -= $ix;
  $y += $iy;

  pos_set ($x, $y);
  }

?>
