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

  if ($_REQUEST['title'] && $_REQUEST['description'] && $_REQUEST['rules']) {
    $code = $_REQUEST['title'];
    $code = strtolower(preg_replace("/[^A-Za-z0-9]/", "", $code));

    if (find_game(array('code' => $code))) {
      $error = 'Title already in use.';
    } else {
      insert_game(array(
        'title' => $_REQUEST['title'],
	'description' => $_REQUEST['description'],
	'rules' => $_REQUEST['rules'],
	'code' => $code,
	'admin_id' => $user['_id'],
        'status' => GAME_STATUS_PENDING,
      ));

      header("Location: http://battleroyale.mobi/game.php?code=".$code);
    }
  }
  /*
  if (empty($_REQUEST['title'])) {
    $error = 'Your game needs a title.';
  }
  */
  return array(
    'error' => $error,
  );
}


function page_get_scripts() {
  return array(
    'js/jquery-1.6.2.min.js',
    'js/jquery-ui-1.8.16.custom.min.js',
  );
}

function page_get_styles() {
  return array(
    'css/site.css',
    'css/smoothness/jquery-ui-1.8.16.custom.css'
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
  return 'Create | Battle Royale';
}

function page_get_header($data) {
?>
  <h1>
    Create a New Game
  </h1>
<?php
}

function page_get_body($data) {
?>
  <form action="create.php" method="post">
    <fieldset>
      <div class="clearfix">
        <label>Title</label>
        <div class="input">
          <input class="xlarge" id="title" name="title" size="30" type="text">
        </div>
      </div>
      <div class="clearfix">
        <label for="description">Description</label>
        <div class="input">
          <textarea class="xlarge" id="description" name="description" rows="3"></textarea>
        </div>
      </div>
      <div class="clearfix">
        <label for="rules">Rules</label>
        <div class="input">
          <textarea class="xlarge" id="rules" name="rules" rows="3"></textarea>
          <span class="help-block">
            Remember, it is up to you to enforce the rules!
          </span>
        </div>
      </div>
      <div class="actions" style="margin:0 -20px">
        <input type="submit" class="btn primary" value="Save changes">&nbsp;<button type="reset" class="btn">Cancel</button>
      </div>
    </fieldset>
  </form>
<?php
}

// TEMPLATE INCLUDE

include('template.php');

?>
