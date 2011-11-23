<?php
/**
 * profile.php
 *
 * stuff
 */

// INCLUDES

require_once dirname(__FILE__).'/../lib/battle-royale/constants.php';
require_once dirname(__FILE__).'/../lib/battle-royale/database.php';

// PAGE FUNCTIONS

function page_do_plumbing() {
  $fb_user = facebook_get_user();
  $user = find_person(array('facebook_id' => $fb_user['id']));

  if (empty($_REQUEST['code'])) {
    header('Location: http://battleroyale.mobi/');
  }

  $game = find_game(array('code' => $_REQUEST['code']));
  if (!$game) {
    header('Location: http://battleroyale.mobi/');
  }

  $player = find_participant(array('user_id' => $user['_id'],
				   'game_id' => $game['_id']));
  if (!$player && $user['_id'] != $game['admin_id']) {
    header('Location: http://battleroyale.mobi/');
  }

  return array(
    'game' => $game,
    'player' => $player,
    'is_admin' => $user['_id'] != $game['_id'],
  );
}


function page_get_scripts() {
  return array(
  );
}

function page_get_styles() {
  return array(
    'css/site.css',
  );
}

function page_has_topbar() {
  return true;
}

function page_has_popups() {
  return true;
}

function page_requires_user() {
  return true;
}

function page_get_title($data) {
  return $data['game']['title'].' | Battle Royale';
}

function page_get_header($data) {
?>
  <div class='row'>
    <div class='span11'>
      <h1>
        <?php echo $data['game']['title']; ?>
        <small>
        <?php echo $data['game']['code']; ?>
        </small>
      </h1>
    </div>
    <div class='span1' style='padding-top:10px;'>
      <span class='label'><?php echo ucfirst($data['game']['status']); ?></span>
    </div>
  </div>
<?php
}

function game_page_render_table_row($count, $player) {
  return
    '<tr>'.
    '  <td>'.$count .'</td>'.
    '  <td>'.
    '    <img'.
    '      class="thumbnail"'.
    '      style="width:36px;height:36px;"'.
    '      src="http://graph.facebook.com/'.$player['facebook_id'].'/picture?type=square"'.
    '    >'.
    '  </td>'.
    '  <td>'.$player['name'].'</td>'.
    '  <td>'.$player['kills'].'</td>'.
    '  <td>'.$player['kills'].'</td>'.
    '  <td>'.$player['escapes'].'</td>'.
    '  <td>'.strtoupper($player['status']).'</td>'.
    '</tr>';
}

function page_get_body($data) {
?>
  <div class='row'>
    <div class='span8'>
      <?php if ($data['game']['status'] != GAME_STATUS_PENDING) {  ?>
        <div class='well' style='margin:0 20px 0 0'>
          <h2>
            You will be notified when the game starts.
          </h2>
        </div>
      <?php } else { ?>
        <table class="bordered-table zebra-striped" style="">
          <thead>
            <tr>
              <th>#</th>
              <th></th>
              <th>Name</th>
              <th><a rel='twipsy' data-original-title='Points'>P</a></th>
              <th><a rel='twipsy' data-original-title='Kills'>K</a></th>
              <th><a rel='twipsy' data-original-title='Evades'>E</a></th>
              <th>Status</th>
            </tr>
          </thead>
          <?php
              $winner = find_participant(array('game_id' => $data['game']['_id'],
					       'status' => PARTICIPANT_STATUS_WINNER));
              $alive = find_participants(array('game_id' => $data['game']['_id'],
					       'status' => PARTICIPANT_STATUS_ALIVE));
              $dead = find_participants(array('game_id' => $data['game']['_id'],
					      'status' => PARTICIPANT_STATUS_DEAD));
              $counter = 0;
	      if ($winner) echo game_page_render_table_row($counter, $winner);
              foreach ($alive as $player) {
                $counter++;
                echo game_page_render_table_row($counter, $player);
              }
              foreach ($dead as $player) {
                $counter++;
                echo game_page_render_table_row($counter, $player);
              }
          ?>
        </table>
      <?php
        }
      ?>
    </div>
    <div class='span4'>
      <h3>Description</h3>
      <p><?php echo $data['game']['description']?><p>
      <h3>Rules</h3>
      <p><?php echo $data['game']['rules']?><p>
      <h3>Start Date</h3>
      <p><?php echo $data['game']['startDate']?>10/1/2011<p>
      <h3>End Date</h3>
      <p><?php echo $data['game']['endDate']?>10/11/2011<p>
      <h3>Players</h3>
      <ul class="facepile" style="margin-left:0px;">
        <?php
          $cursor = find_participants(array('game_id' => $data['game']['_id']));
          foreach ($cursor as $person) {
            echo '<li>';
            echo '<a href="http://facebook.com/'.$person['facebook_id'].'"
                     rel="twipsy" data-original-title="'.$person['name'].'">';
            echo '<img class="thumbnail" src="http://graph.facebook.com/'.$person['facebook_id'].'/picture?type=square">';
            echo '</a>';
            echo '</li>';
          }
        ?>
      </ul>
      <script>
        $(function () {
          $("a[rel=twipsy]").twipsy({
            live: true
          })
        })
      </script>
    </div>
  </div>
<?php
}

// INCLUDE TEMPLATE
include('template.php');

?>
