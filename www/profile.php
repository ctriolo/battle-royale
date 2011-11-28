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
  $errors = array();
  $user = br_get_current_user();
  if (!empty($_REQUEST['join'])) {
    $error = br_join_game($user['_id'], $_REQUEST['join']);
    if ($error) {
      $errors[] = $error;
    } else {
      header("Location: http://battleroyale.mobi/game.php?code=".$_REQUEST['join']);
    }
  }
    
  return array(
    'errors' => $errors,
  );
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
  $user = br_get_current_user();

?>
  <div class='row'>
    <div class='span1'>
      <img src='http://graph.facebook.com/<?php echo $user['facebook_id']; ?>/picture?type=small' />
    </div>
    <div class='span9' style='margin-top:18px'>
      <h1>
        <?php echo $user['name']; ?>
	<small>

	</small>
      </h1>
    </div>
  </div>
<?php
}

function page_profile_render_join_game() {
?>
  <div class='span4'>
    <div class='well'>
      <h2>
        Join a game.
      </h2>
      <p>
        Enter the code that corresponds to the game you want to join below.
      </p>
      <form>
        <div class="inline-inputs">
          <input name="join" class="small" />
          <button type="submit" class="btn primary">Join</button>
        </div>
      </form>
    </div>
  </div>
<?php
}

function page_profile_render_create_game() {
?>
  <div class='span6'>
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
<?php
}


function page_profile_render_game($game) {
?>
  <div class='span4'>
    <a href="game.php?code=<?php echo $game['code'];?>">
    <div class='well'>
      <h3>
        <?php echo $game['title']; ?>
      </h3>
      <p>
        <?php echo ucfirst($game['status']); ?>
      </p>

      <p>
        <?php echo $game['description']; ?>
      </p>
    </div>
   </a>
  </div>
<?php
}

function page_get_body($data) {
  $user = br_get_current_user();

  $admin_current_game = br_get_active_admin($user['_id']);
  $admin_past_games = br_get_past_admins($user['_id']);
  $current_game = br_get_active_game($user['_id']);
  $past_games = br_get_past_games($user['_id']);

  if ($data['errors']) {
    foreach ($data['errors'] as $error) {
      if ($error) {
?>
  <div class="alert-message error">
      <a class="close" href="#">&times;</a>
    <p><?php echo $error ?></p>
  </div>
<?php
      }
    }
  }

?>
  <div class='row'>
<?php

  if ($current_game) {
    page_profile_render_game($current_game);
  } else if ($admin_current_game) {
    page_profile_render_game($admin_current_game);
  } else {
    page_profile_render_join_game();
    page_profile_render_create_game();
  }

  foreach ($admin_past_games as $past_game) {
    page_profile_render_game($past_game);
  }

  foreach ($past_games as $past_game) {
    page_profile_render_game($past_game);
  }
?>
  </div>
<?php
}

// TEMPLATE INCLUDE

include('template.php');

?>

