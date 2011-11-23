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
  return array();
}

function page_get_title() {
  return 'Profile | Battle Royale';
}

function page_get_scripts() {
  return array();
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

function page_get_header() {
  $fb_user = facebook_get_user();
  $points = 1000000;
?>
  <div class='row'>
    <div class='span1'>
      <img src='http://graph.facebook.com/<?php echo $fb_user['id']; ?>/picture?type=small' />
    </div>
    <div class='span9' style='margin-top:18px'>
      <h1>
        <?php echo $fb_user['name']; ?>
	<small>
	  <?php echo $points.'pts'; ?>
	</small>
      </h1>
    </div>
  </div>
<?php
}

function page_get_body() {
  $fb_user = facebook_get_user();
  $user = find_person(array('facebook_id' => $fb_user['id']));

  $points = 1000000;

  $admin_current_game = find_game(array('$or' => array(
						       array('admin_id' => $user['_id'],
							     'status' => GAME_STATUS_PENDING),
						       array('admin_id' => $user['_id'],
							     'status' => GAME_STATUS_ACTIVE))));
  $admin_past_games = find_game(array('admin_id' => $user['_id'],
					'status' => GAME_STATUS_COMPLETE));
  $current_game = find_participant(array('user_id' => $user['_id'],
					  'status' => PARTICIPANT_STATUS_ALIVE));
  $past_games = find_participants(array('$or' => array(array('user_id' => $user['_id'],
							     'status' => PARTICIPANT_STATUS_DEAD),
						       array('user_id' => $user['_id'],
							     'status' => PARTICIPANT_STATUS_WINNER))));

  if ($current_game) {
?>
  <div class='row'>
    <div class='span11'>
      <h2>
        Current Game: 
      </h2>
    </div>
  </div>
  <div class='row'>
    <div class='span11'>
      <div class='well'>
        <h3>
          <?php echo $current_game['title']; ?>
        </h3>
        <p>
          <?php echo $current_game['description']; ?>
        </p>
      </div>
    </div>
  </div>
<?php
  } else if ($admin_current_game) {
?>
  <div class='row'>
    <div class='span11'>
      <h2>
        Current Game: 
      </h2>
    </div>
  </div>
  <div class='row'>
    <div class='span11'>
      <a href="game.php?code=<?php echo $admin_current_game['code'];?>">
      <div class='well'>
        <h3>
          <?php echo $admin_current_game['title']; ?>
        </h3>
        <p>
          <?php echo $admin_current_game['description']; ?>
        </p>
      </div>
     </a>
    </div>
  </div>
<?php
  } else {
?>
  <div class='row' style="text-align:center; margin:0 10px;">
    <div class='span5'>
      <div class='well'>
        <h2>
          Join a game.
        </h2>
        <p>
          Enter the code that corresponds to the game you want to join below.
        </p>
        <form>
          <div class="inline-inputs">
            <input class="small" />
            <button name="join" class="btn primary">Join</button>
          </div>
        </form>
      </div>
    </div>
    <div class='span1'></div>
    <div class='span5'>
      <div class='well'>
        <h2>
          Create a game.
        </h2>
        <p>
          Click here to be the administrator of a new game.
        </p>
        <form action="create.php">
          <button type="submit" name="create" class="btn primary">Create</button>
        </form>
      </div>
    </div>
  </div>
<?php
  }

  if ($past_games->hasNext()) {  
?>
  <div class='row'>
    <div class='span11'>
      <h2>
        Past Games: 
      </h2>
    </div>
  </div>
<?php
  }

  foreach ($past_games as $past_game) {
?>
  <div class='row'>
    <div class='span11'>
      <div class='well'>
        <h3>
          <?php $past_game['title']; ?>
        </h3>
        <p>
          <?php $past_game['description']; ?>
        </p>
      </div>
    </div>
  </div>
<?php
  }
}

// TEMPLATE INCLUDE

include('template.php');

?>

