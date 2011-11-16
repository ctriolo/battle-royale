<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<head>
  <title><?php if (!empty($title)) echo $title.' | Battle Royale'; else echo 'Battle Royale'; ?></title>
  <!-- script -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
  <script src='http://twitter.github.com/bootstrap/1.4.0/bootstrap-tabs.js'></script>
  <script src='http://twitter.github.com/bootstrap/1.4.0/bootstrap-buttons.js'></script>
  <!-- style -->
  <link rel="stylesheet" href="http://twitter.github.com/bootstrap/1.4.0/bootstrap.min.css">
  <link href='<?php echo $css; ?>' rel='stylesheet' />
</head>
<body>
  <div class='topbar'>
    <div class='topbar-inner'>
      <div class='container'>
        <div class='fill'>
        <h3>
          <a href="index.php">Battle Royale</a>
        </h3>
        <form action="game.php" class='pull-right'>
          <input name="code" type="text" placeholder="Enter Game Code Here">
        </form>
       </div>
      </div>
    </div>
  </div>
  <div class='container'>
