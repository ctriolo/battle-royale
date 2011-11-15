  <div class='container'>
    <div class='row' style='margin-top:18px'>
      <div class='span16'>
        <div class='well jumbotron'>
          <h1>BATTLE ROYALE</h2>
        </div>
      </div>
    </div>
    <div class='row'>
      <div class='span5'>
        <div class='well descriptor'>
          <h1>JOIN</h1>
          <p>Text <?php echo TWILIO_PHONE_NUMBER_PRETTY; ?> with 'JOIN your_code' to join  a game.</p>
        </div>
      </div>
      <div class='span6'>
        <div class='well descriptor'>
          <h1>STATS</h1>
          <p>Enter your game code here to see your leaderboard.</p>
          <form action='/'>
          <input id='code' name='code' type='text'>
        </form>
        </div>
      </div>
      <div class='span5'>
        <div class='well descriptor'>
          <h1>CREATE</h1>
          <p>Text <?php echo TWILIO_PHONE_NUMBER_PRETTY; ?> with 'CREATE' to start a game.</p>
        </div>
      </div>
    </div>
  </div>
