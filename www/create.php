<?php
/**
 * profile.php
 *
 * stuff
 */

// PAGE FUNCTIONS

function page_do_plumbing() {
  $errors = array();
  $user = br_get_current_user();

  if (!empty($_REQUEST['title'])) {
    $errors[] = br_create_game(
      $user['_id'], $_REQUEST['title'],
      $_REQUEST['description'], $_REQUEST['rules']);

    if (!$errors[0]) {
      $game = br_get_active_admin($user['_id']);
      header("Location: http://battleroyale.mobi/game.php?code=".$game['code']);
    }
  }

  if (!empty($_REQUEST['submit']) && empty($_REQUEST['title'])) {
    $errors[] = 'Your game needs a title.';
  }

  return array(
    'errors' => $errors,
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
  $title = isset($_REQUEST['title']) ? $_REQUEST['title'] : '';
  $description = isset($_REQUEST['description']) ? $_REQUEST['description'] : '';
  $rules = isset($_REQUEST['rules']) ? $_REQUEST['rules'] : '';

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
  <form action="create.php" method="get">
    <fieldset>
      <div class="clearfix">
        <label>Title</label>
        <div class="input">
          <input class="xlarge" id="title" name="title" size="30" type="text" value="<?php echo $title; ?>">
        </div>
      </div>
      <div class="clearfix">
        <label for="description">Description</label>
        <div class="input">
          <textarea class="xlarge" id="description" name="description" rows="3"><?php echo $description; ?></textarea>
        </div>
      </div>
      <div class="clearfix">
        <label for="rules">Rules</label>
        <div class="input">
          <textarea class="xlarge" id="rules" name="rules" rows="3"><?php echo $rules; ?></textarea>
          <span class="help-block">
            Remember, it is up to you to enforce the rules!
          </span>
        </div>
      </div>
      <div class="actions" style="margin:0 -20px -38px">
        <input name="submit" type="submit" class="btn primary" value="Submit">
      </div>
    </fieldset>
  </form>
<?php
}

// TEMPLATE INCLUDE

include('template.php');

?>
