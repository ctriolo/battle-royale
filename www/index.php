<?php
  require_once(dirname(__FILE__).'/../lib/battle-royale/br.php');

  if (br_get_current_user()) header('Location: http://battleroyale.mobi/profile.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<head>
  <title>
    Battle Royale
  </title>
  <link rel="stylesheet" href="http://twitter.github.com/bootstrap/1.4.0/bootstrap.min.css" />
  <link rel='stylesheet' href="css/home.css" />
</head>
<body>
  <div class='container'>
    <div class='hero-unit' style='text-align:center'>
      <h1>BATTLE ROYALE</h1>
      <p>
        Play or manage a game of Assassins (a.k.a. Battle Royal, KAOS, Juggernaut, Paranoia, Killer, Tag, Elimination, or Circle of Death)! Receive your targets through SMS and phone calls. See game stats and activity through the leaderboards.
      </p>
      <a href="<?php echo br_get_log_in_url(); ?>" class='btn primary large'>Log In Using Facebook &raquo;</a>
    </div>
  </div>
  <footer class="footer">
    <p>Designed and built by Rafael Romero and Christopher Triolo.</p>
  </footer>
</body>

