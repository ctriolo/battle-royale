  <div class='container'>
    <div class='row' style='margin-top:18px'>
      <div class='span16'>
        <div class='well'>
          <h2><?php echo $game['title']; ?></h2>
          <?php 
            if ($game['status'] == GAME_STATUS_PENDING) {
              echo '<h6><span style=\'color:#444444\' class=\'label\'>pending</span></h6>';
            } else if ($game['status'] == GAME_STATUS_ACTIVE) {
	      echo '<h6><span style=\'color:#448844\' class=\'label success\'>active</span></h6>';
	    } else if ($game['status'] == GAME_STATUS_COMPLETE) {
	      echo '<h6><span style=\'color:#444488\' class=\'label notice\'>complete</span></h6>';
	    }
          ?>
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
	  <p> Want to join this game? Text <strong>JOIN <?php echo $game['code']; ?></strong> to <strong><?php echo TWILIO_PHONE_NUMBER_PRETTY;?></strong>.</p> 
        </div>
      </div>
    </div>
  </div>
