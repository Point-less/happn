<?php

// JSON file helpers


// Load map from JSON file

function json_load ($path)
  {
  $m = NULL;

  while (TRUE)
    {
    if (!is_readable ($path)) break;

    $t = file_get_contents ($path);
    if ($t === FALSE) break;

    $m = json_decode ($t, TRUE); // return association
    break;
    }

  return $m;
  }


// Save map to JSON file

function json_save ($path, $map)
  {
  $r = FALSE;

  while (TRUE)
    {
    if (!is_writable ($path)) break;

    $t = json_encode ($map);
    if ($t === FALSE) break;

    $r = file_put_contents ($path, $t);
    break;
    }

  return $r;
  }

?>
