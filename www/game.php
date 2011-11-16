<?php

require_once '../lib/battle-royale/constants.php';
require_once '../lib/battle-royale/database.php';

$game = find_game(array('code' => $_REQUEST['code']));
$title = $game['title'];
$css = 'css/site.css';
include('header.php');
?>
  <div class='content'>
    <div class='page-header'>
      <h1>
        <?php echo $game['title']; ?>
        <small>
  <?php echo ucfirst($game['status']); ?>
        </small>
      </h1>
    </div>
    <div class='row'>
      <div class='span11'>
        <ul class='tabs' data-tabs='tabs'>
          <li class='active'><a href='#rank'>Rank</a></li>
          <li><a href="#activity">Activity</a></li>
          <li><a href="#players">Assassins</a></li>
          <li><a href="#settings">Settings</a></li>
          <li><a href="#contact">Contact</a></li>
        </ul>
        <div class='tab-content'>
          <div id='rank' class='active'>
            <table>
              <thead>
                <tr>
                  <th>#</th>
                  <th>Name</th>
                  <th>Kills</th>
                  <th>Escapes</th>
                  <th>Status</th>
                </tr>
              </thead>
              <?php
                $cursor = find_participants(array('game_id' => $game['_id']));
                $counter = 1;
                foreach ($cursor as $person) {
                  echo '<tr>';
                  echo '<td>' . $counter . '</td>';
                  echo '<td>' . $person['name'] . '</td>';
                  echo '<td>' . $person['kills'] . '</td>';
                  echo '<td>' . $person['escapes'] . '</td>';
                  echo '<td><h6>' . $person['status'] . '</h6></td>';
                  echo '</tr>';
                  $counter++;
                }
              ?>
            </table>
          </div>
          <div id='activity'>
          </div>
          <div id='players'>
          </div>
          <div id='settings'>
          </div>
          <div id='contact'>
          </div>
        </div>
      </div>
    </div>
    <div class='row'>
      <div class='span11'>
        <p>
          Want to join this game? Text <strong>JOIN <?php echo $game['code']; ?></strong> to <strong><?php echo TWILIO_PHONE_NUMBER_PRETTY;?></strong>.
        </p>
      </div>
    </div>
  </div>

<?php include('footer.php'); ?>
