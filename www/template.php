<?php

require_once dirname(__FILE__).'/../lib/battle-royale/br.php';

$user = br_get_current_user();

if (page_requires_user()) {
  if (!$user) header('Location: http://battleroyale.mobi/');
}

$data = page_do_plumbing();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<head>
  <!-- meta -->
  <title>
    <?php 
      echo page_get_title($data);
    ?>
  </title>
  <!-- script -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
  <script src='http://twitter.github.com/bootstrap/1.4.0/bootstrap-tabs.js'></script>
  <script src='http://twitter.github.com/bootstrap/1.4.0/bootstrap-twipsy.js'></script>
  <script src='http://twitter.github.com/bootstrap/1.4.0/bootstrap-popover.js'></script>
  <script src='http://twitter.github.com/bootstrap/1.4.0/bootstrap-modal.js'></script>
  <script src='http://twitter.github.com/bootstrap/1.4.0/bootstrap-buttons.js'></script>
  <script src='http://twitter.github.com/bootstrap/1.4.0/bootstrap-dropdown.js'></script>
  <?php
    foreach (page_get_scripts() as $script) {
      echo '<script src="'.$script.'"></script>';
    }
  ?>
  <!-- style -->
  <link rel="stylesheet" href="http://twitter.github.com/bootstrap/1.4.0/bootstrap.min.css">
  <?php
    foreach (page_get_styles() as $style) {
      echo '<link rel="stylesheet" href="'.$style.'">';
    }
  ?>
</head>
<body>
  <?php 
    if (page_has_topbar()) {
      include('topbar.php'); 
    }
  ?>
  <div class='container'>
    <div class='main-content'>
      <div class='page-header'>
        <?php page_get_header($data); ?>
      </div>
      <?php page_get_body($data); ?>
    </div>
  </div>
  <footer>
    <p>Designed and built by Rafael Romero and Christopher Triolo.</p>
  </footer>
  <script>
    $(function () {
      $("a[rel=twipsy]").twipsy({
	live: true
      })
    });
    $(function () {
      $("[rel=popover]").popover({
	offset: 10,
	html: true
      })
    });          
  </script>
  <?php 
    if (page_has_popups()) {
      include('popups.php');
    }
  ?>
</body>
