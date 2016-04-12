<?php

require_once ('api.php');


// Session helpers

session_start ();

function session_get ($name, $def = NULL)
  {
  if (!isset ($_SESSION [$name])) return $def;
  return $_SESSION [$name];
  }

function session_set ($name, $val)
  {
  $_SESSION [$name] = $val;
  }

function session_reset ($name)
  {
  unset ($_SESSION [$name]);
  }


// GET helpers

function get_test ($name, $val)
  {
  if (!isset ($_GET [$name])) return FALSE;
  return ($_GET [$name] == $val);
  }

function get_get ($name, $def = NULL)
  {
  if (!isset ($_GET [$name])) return $def;
  return $_GET [$name];
  }


header ('Content-Type: text/html; charset=utf-8');

// Document is HTML5

?>

<!doctype html>
<html lang="fr">
  <head>
    <meta charset=utf-8">
    <title>Happn Hack</title>
    <script src="/jquery.js"></script>
  </head>

  <body>

<?php

// API initialization

$serv = new Happn ();
$r = $serv->auth (60);  // 1 minute
if (!$r) die ("Authentication failure !\n");


// Page select

$page = get_get ('page');

switch ($page)
  {
  // User information

  case 'self':

    $f = 'id,gender,age,birth_date,first_name,job,workplace,about,profiles.fields(url),matching_preferences.fields(female,male,distance,age_min,age_max),notification_settings.fields(charms,messages,reminders,dates,near,visit,match),unread_notifications.types(468,471,524,525,526,529,530,531,565,791,792),unread_conversations';
    $r = $serv->user_get ($f);
    //echo '<p>' . var_dump ($r) . '</p>';

    $u = $r ['data'];

    echo '<h1>' . $u ['first_name'] . '</h1>';
    echo '<p>ID : ' . $u ['id'] .  '</p>';
    echo '<p>Gender: ' . $u ['gender'] . '</p>';
    echo '<p>Birth: ' . $u ['birth_date'] . ' (' . $u ['age'] . ')</p>';
    echo '<p>Job: ' . $u ['job'] . ' - Workplace: ' . $u ['workplace'] . '</p>';
    echo '<p>About: ' . $u ['about'] . '</p>';
    echo '<p><a href="https://www.facebook.com/' . $u ['fb_id'] . '">Facebook</a></p>';

    echo '<p>';
    foreach ($u ['profiles'] as $p)
      {
      echo '<img src="' . $p ['url'] . '">';
      }

    echo '</p>';

    $p = $u ['matching_preferences'];

    echo '<h2>Preferences</h2>';
    echo '<p>Gender: female=' . $p ['female'] . ' male=' . $p ['male'] . '</p>';
    echo '<p>Age: min=' . $p ['age_min'] . ' max=' . $p ['age_max'] . '</p>';
    echo '<p>Distance: ' . $p ['distance'] . ' m </p>';

    break;


  case 'other':

    $id = get_get ('id');
    if (is_null ($id)) break;

    $f = 'id,gender,age,first_name,job,workplace,about,profiles.fields(url)';
    $r = $serv->user_get ($f, $id);
    //echo '<p>' . var_dump ($r) . '</p>';
    $u = $r ['data'];

    echo '<h1>' . $u ['first_name'] . '</h1>';
    echo '<p>ID : ' . $id .  '</p>';
    echo '<p>Gender: ' . $u ['gender'] . ' - Age: ' . $u ['age'] . '</p>';
    echo '<p>Job: ' . $u ['job'] . ' - Workplace: ' . $u ['workplace'] . '</p>';
    echo '<p>About: ' . $u ['about'] . '</p>';
    echo '<p><a href="https://www.facebook.com/' . $u ['fb_id'] . '">Facebook</a></p>';

    echo '<p>';
    foreach ($u ['profiles'] as $p)
      {
      echo '<img src="' . $p ['url'] . '">';
      }

    echo '</p>';

    echo '<p><a href="index.php?page=accept&id=' . $id . '">Accept</a></p>';
    echo '<p><a href="index.php?page=unaccept&id=' . $id . '">Unaccept</a></p>';
    echo '<p><a href="index.php?page=reject&id=' . $id . '">Reject</a></p>';
    echo '<p><a href="index.php?page=unreject&id=' . $id . '">Unreject</a></p>';

    break;


  // Notifications

  case 'notif':

    $offset = session_get ('notif_offset', 0);
    if (is_null ($offset)) $offset = 0;

    if (get_test ('op', 'first')) $offset = 0;
    if (get_test ('op', 'next' )) $offset += 100;
    if (get_test ('op', 'prev' )) $offset -= 100;

    session_set ('notif_offset', $offset);

    echo '<p>Offset : ' . $offset . '</p>';

    $f = 'id,nb_times,modification_date,notifier.fields(id,gender,age,first_name,about,distance,my_relation,is_accepted,is_charmed,profiles.fields(url))';
    $r = $serv->notif ($f, $offset, 100);
    //echo '<p>' . var_dump ($r) . '</p>';

    $d = $r ['data'];

    ?>

    <table>
      <thead>
        <tr>
          <th>First name</th>
          <th>Gender</th>
          <th>Age</th>
          <th>Times</th>
          <th>Modified</th>
          <th>Distance</th>
          <th>Has accepted</th>
          <th>Relation</th>
        </tr>
      </thead>
      <tbody>

      <?php

      foreach ($d as $n)
        {
        $u = $n ['notifier'];
        $uid = $u ['id'];

        $a = $u ['is_accepted'] ? 'YES' : 'no';

        switch ($u ['my_relation'])
          {
          case 0: $r = 'none'; break;
          case 1: $r = 'oneway'; break;
          case 4: $r = 'MUTUAL'; break;
          default: $r = '?';
          }

        ?>

        <tr>
          <td><a href="index.php?page=other&id=<?= $uid ?>"><?= $u ['first_name'] ?></a></td>
          <td><?= $u ['gender'] ?></td>
          <td><?= $u ['age'] ?></td>
          <td><?= $n ['nb_times'] ?></td>
          <td><?= substr ($n ['modification_date'], 0, 10) ?></td>
          <td><?= $u ['distance'] ?></td>
          <td><?= $a ?></td>
          <td><?= $r ?></td>
        </tr>

        <?php

        }

      ?>

      </tbody>
    </table>

    <?php

    break;


  // Accepting
  
  case 'accept':
    $id = get_get ('id');
    if (is_null ($id)) break;

    $r = $serv->accept ($id);
    //echo '<p>' . var_dump ($r) . '</p>';

    $d = $r ['data'];
    echo '<p>' . $d ['message'] . '</p>';
    break;


  case 'unaccept':
    $id = get_get ('id');
    if (is_null ($id)) break;

    $r = $serv->unaccept ($id);
    //echo '<p>' . var_dump ($r) . '</p>';

    $d = $r ['data'];
    echo '<p>' . $d ['message'] . '</p>';
    break;


  case 'accepted':

    $f = 'id,first_name,gender,age';

    $r = $serv->accepted ($f, 0, 100);
    //echo '<p>' . var_dump ($r) . '</p>';

    $d = $r ['data'];

    ?>

    <table>
      <thead>
        <tr>
          <th>First name</th>
          <th>Gender</th>
          <th>Age</th>
        </tr>
      </thead>
      <tbody>

      <?php

      foreach ($d as $u)
        {

        ?>

        <tr>
          <td><a href="index.php?page=other&id=<?= $u ['id'] ?>"><?= $u ['first_name'] ?></a></td>
          <td><?= $u ['gender'] ?></td>
          <td><?= $u ['age'] ?></td>
        </tr>

        <?php

        }

      ?>

      </tbody>
    </table>

    <?php

    break;


  // Rejecting

  case 'reject':
    $id = get_get ('id');
    if (is_null ($id)) break;

    $r = $serv->reject ($id);
    //echo '<p>' . var_dump ($r) . '</p>';

    $d = $r ['data'];
    echo '<p>' . $d ['message'] . '</p>';
    break;


  case 'unreject':
    $id = get_get ('id');
    if (is_null ($id)) break;

    $r = $serv->unreject ($id);
    //echo '<p>' . var_dump ($r) . '</p>';

    $d = $r ['data'];
    echo '<p>' . $d ['message'] . '</p>';
    break;


  case 'rejected':

    $f = 'id,first_name,gender,age';

    $r = $serv->rejected ($f, 0, 100);
    //echo '<p>' . var_dump ($r) . '</p>';

    $d = $r ['data'];

    ?>

    <table>
      <thead>
        <tr>
          <th>First name</th>
          <th>Gender</th>
          <th>Age</th>
        </tr>
      </thead>
      <tbody>

      <?php

      foreach ($d as $u)
        {

        ?>

        <tr>
          <td><a href="index.php?page=other&id=<?= $u ['id'] ?>"><?= $u ['first_name'] ?></a></td>
          <td><?= $u ['gender'] ?></td>
          <td><?= $u ['age'] ?></td>
        </tr>

        <?php

        }

      ?>

      </tbody>
    </table>

    <?php

    break;


  // Conversations

  case 'conv':

    $offset = session_get ('conv_offset', 0);
    if (is_null ($offset)) $offset = 0;

    if (get_test ('op', 'first')) $offset = 0;
    if (get_test ('op', 'next' )) $offset += 10;
    if (get_test ('op', 'prev' )) $offset -= 10;

    session_set ('conv_offset', $offset);

    echo '<p>offset : ' . $offset . '</p>';

    $f = 'id,participants.fields(user.fields(id,first_name,is_moderator,profiles.mode(0).width(59).height(59).fields(width,height,mode,url))),is_read,creation_date,modification_date,is_read,last_message.fields(creation_date,message,sender),messages.fields(id,sender,message)';

    $r = $serv->conv ($f, $offset, 10);

    $d = $r ['data'];

    foreach ($d as $c)
      {
      echo '<h1>';
      foreach ($c ['participants'] as $p)
        {
        $u = $p ['user'];
        echo $u ['first_name'] . ' / ';
        }

      echo '</h1>';
      echo '<p>modifié : ' . $c ['modification_date'] . '</p>';

      foreach ($c ['messages'] as $m)
        {
        echo '<p>' . var_dump ($m) . '</p>';
        }
      }
    
    break;

  // Default page

  default:

    ?>

    <h1>Main page</h1>

    <ul>
      <li><a href="index.php?page=self">Self information</a></li>
      <li><a href="index.php?page=notif&op=first">Notifications</a></li>
      <li><a href="index.php?page=accepted">Accepted users</a></li>
      <li><a href="index.php?page=rejected">Rejected users</a></li>
      <li><a href="index.php?page=conv&op=first">Conversations</a></li>
    </ul>

    <?php
  }


?>

  </body>
</html>
