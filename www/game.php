<?php
/**
 * profile.php
 *
 * stuff
 */

// PAGE FUNCTIONS

function page_do_plumbing() {
  $user = br_get_current_user();

  if (empty($_REQUEST['code'])) {
    header('Location: http://battleroyale.mobi/');
  }

  $game = br_get_game_by_code($_REQUEST['code']);
  if (!$game) {
    header('Location: http://battleroyale.mobi/');
  }

  $player = br_get_user_in_game($user['_id'], $game['_id']);

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
      <?php
        switch ($data['game']['status']) {
	case GAME_STATUS_PENDING: $class = 'warning'; break;
	case GAME_STATUS_ACTIVE: $class = 'success'; break;
	case GAME_STATUS_COMPLETE:
	default: $class = ''; break;
	}
      ?>
      <span class='label <?php echo $class;?>'>
        <?php echo ucfirst($data['game']['status']); ?>
      </span>
    </div>
  </div>
<?php
}

/*
 *  http://www.youtube.com/watch?v=SbjXmBmHAdk
 */
function game_page_render_info_card($count, $player, $players) {

  $photo =
    //    '<div class ="row" style="margin-left:0px">'.
    '<img class="thumbnail" src="http://graph.facebook.com/'.
    $player['facebook_id'].'/picture?type=normal" align="left">';

  switch ($player['status']) {
  case PLAYER_STATUS_ALIVE: $color = 'green'; break;
  case PLAYER_STATUS_DEAD: $color = 'red'; break;
  case PLAYER_STATUS_WINNER:
  default: $color = 'blue'; break;
  }

  $status = 
    '<h4 style="color:'.$color.';text-align:center;">'.
    strtoupper($player['status']).
    '</h4>';

  $killed =
    '<div class="row" style="margin-left:0px">'.
    '<h4>Kills: '.$player['kills'].'</h4>'.
    '<ul class="facepile">';
  foreach ($player['killed'] as $player_id) {
    $victim = $players[$player_id->{'$id'}];
    $killed .= 
      '<li><a href="http://facebook.com/'.$victim['facebook_id'].'" '.
      'rel="twipsy" data-original-title="'.$victim['name'].'">'.
      '<img class="thumbnail" src="http://graph.facebook.com/'.
      $victim['facebook_id'].'/picture?type=square"></a></li>';
  }
  $killed .= '</ul></div>';

  $evaded =
    '<div class="row" style="margin-left:0px">'.
    '<h4>Evades: '.$player['evades'].'</h4>'.
    '<ul class="facepile">';
  foreach ($player['evaded'] as $player_id) {
    $misser = $players[$player_id->{'$id'}];
    $evaded .= 
      '<li><a href="http://facebook.com/'.$misser['facebook_id'].'" '.
      'rel="twipsy" data-original-title="'.$misser['name'].'">'.
      '<img class="thumbnail" src="http://graph.facebook.com/'.
      $misser['facebook_id'].'/picture?type=square"></a></li>';
  }
  $evaded .= '</ul></div>';

  $killed_by = '';
  $killed_on = '';
  if (!empty($player['killed_by'])) {
    $killer = $players[$player['killed_by']->{'$id'}];
    $killed_by = 
      '<div class="row" style="margin-left:0px;text-align:center;">'.
      '<h5 style="color:red;">Killed by </h5>'.
      '<img src="http://graph.facebook.com/'.
      $killer['facebook_id'].'/picture?type=square"></div>';
    $killed_on =
      '<p style="color:red;text-align:center;">'.
      date('M d, Y g:i A', $player['killed_on']->sec).
      '</p>';

  }

  return
  '<div class="info_card">'.
  '<div class="row">'.
  '<div class="span2">'.
  $photo.
  $status.
  $killed_on.
  $killed_by.
  '</div>'.
  '<div class="span2" style="width:120px">'.
  $killed.
  $evaded.
  '</div>'.
  '</div>'.
  '</div>';
}

function game_page_render_table_row($count, $player, $players) {
  return
    '<tr rel="popover" '.
    "    data-original-title='".$player['name']."' ".
    "    data-content='".game_page_render_info_card($count, $player, $players)."'>".
    '  <td>'.$count .'</td>'.
    '  <td>'.
    '    <img'.
    '      class="thumbnail"'.
    '      style="width:36px;height:36px;"'.
    '      src="http://graph.facebook.com/'.$player['facebook_id'].'/picture?type=square"'.
    '    >'.
    '  </td>'.
    '  <td>'.$player['name'].'</td>'.
    '  <td>'.$player['points'].'</td>'.
    '  <td>'.$player['kills'].'</td>'.
    '  <td>'.$player['evades'].'</td>'.
    '  <td>'.strtoupper($player['status']).'</td>'.
    '</tr>';
}

function page_get_body($data) {
  $game = $data['game'];
  $cursor_players = br_get_game_players($game['_id']);
  $players = array();
  foreach ($cursor_players as $player) {
    $players[$player['_id']->{'$id'}] = $player;
  }
?>
  <div class='row'>
    <div class='span8'>
      <?php if ($data['game']['status'] == GAME_STATUS_PENDING) {  ?>
        <div class='well' style='margin:0 0px 20px 0;text-align:center;'>
          <h2>
            You will be notified when the game starts.
          </h2>
        </div>
      <?php } /*else {*/ ?>
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
	      $winner = br_get_winner($game['_id']);
              $alive = br_get_alive($game['_id'])->sort(array('points' => -1));
              $dead = br_get_dead($game['_id'])->sort(array('killed_on' => -1));

              $counter = 0;
	      if ($winner) {
		$counter++;
		echo game_page_render_table_row($counter, $winner, $players);		
	      }
	      foreach ($alive as $player) {
                $counter++;
                echo game_page_render_table_row($counter, $player, $players);
              }
              foreach ($dead as $player) {
                $counter++;
                echo game_page_render_table_row($counter, $player, $players);
              }
          ?>
        </table>
      <?php
        /*}*/
      ?>
    </div>
    <div class='span4'>
      <h3>Description</h3>
      <p><?php echo $data['game']['description']?><p>
      <h3>Rules</h3>
      <p><?php echo $data['game']['rules']?><p>
      <?php if (!empty($data['game']['startDate'])) { ?>
      <h3>Start Date</h3>
      <p>
        <?php echo date('F d, Y \a\t gA', $data['game']['startDate']->sec);?>
      </p>
      <?php 
        }
        if (!empty($data['game']['endDate'])) { 
      ?>
      <h3>End Date</h3>
      <p>
        <?php echo date('F d, Y \a\t gA', $data['game']['startDate']->sec);?>
      </p>
      <?php } ?>
      <div class="row">
      <div class="span4">
      <h3>Players</h3>
      <ul class="facepile" style="margin-left:0px;">
        <?php
          $cursor = br_get_game_players($game['_id']);
          foreach ($cursor as $player) {
            echo '<li>';
            echo '<a href="http://facebook.com/'.$player['facebook_id'].'"
                     rel="twipsy" data-original-title="'.$player['name'].'">';
            echo '<img class="thumbnail" src="http://graph.facebook.com/'.$player['facebook_id'].'/picture?type=square">';
            echo '</a>';
            echo '</li>';
          }
        ?>
      </ul>
      </div>
      </div>
      <div class="row">
      <div class="span4">
      <h3>Admins</h3>
      <ul class="facepile" style="margin-left:0px;">
        <?php
          $admin = br_get_user($data['game']['admin_id']);
          echo '<li>';
          echo '<a href="http://facebook.com/'.$admin['facebook_id'].'"
                   rel="twipsy" data-original-title="'.$admin['name'].'">';
          echo '<img class="thumbnail" src="http://graph.facebook.com/'.$admin['facebook_id'].'/picture?type=square">';
          echo '</a>';
          echo '</li>';
        ?>
      </ul>
      </div>
      </div>
    </div>
  </div>
<?php
}

// INCLUDE TEMPLATE
include('template.php');

?>
