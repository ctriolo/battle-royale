<?php if ($fb_user) { ?>
  <div class='topbar' data-dropdown="dropdown">
    <div class='topbar-inner'>
      <div class='container'>
        <div class='fill'>
          <h3>
            <a href="index.php">Battle Royale</a>
          </h3>
          <ul class="nav secondary-nav">
            <li class="menu">
              <a href="#" class="menu">
                <img style="height:40px;width:40px;margin:-16px 0;" src='http://graph.facebook.com/<?php echo $fb_user['id']; ?>/picture?type=square' />    
                <?php echo $fb_user['name']; ?>
              </a>
              <ul class="menu-dropdown">
                <li>
  <div id="change_phone_modal" class="modal hide fade">
    <div class="modal-header">
      <a href="#" class="close">x</a>
      <h3>Cell Phone Number</h3>
    </div>
    <form style='margin-bottom:0px'>
    <div class="modal-body">
       <p><strong>Battle Royale</strong> needs your cell phone number for specific game actions. Without it you will be unable to enter any game. Please enter it below.</p>
      <div class='inline-inputs' style='text-align:center;font-size:18px;'>
        ( <input name='phone_first' class='mini'/> ) <input name='phone_middle' class='mini'/> - <input name='phone_last' class='mini'/>
      </div>
    </div>
    <div class="modal-footer">
        <button href="#" class="btn primary">Submit</a>

	 </div>
      </form>
  </div>

                  <a data-controls-modal='change_phone_modal'
                     data-backdrop='static'>
                     Change Phone Number
                  </a>
                </li>
                <li class="divider"></li>
                <li><a href="<?php echo facebook_get_log_out_url(); ?>">Log Out</a></li>
              </ul>
            </li>
          </ul>  
        </div>
      </div>
    </div>
  </div>
<?php } else { ?>
  <div class='topbar' data-dropdown="dropdown">
    <div class='topbar-inner'>
      <div class='container'>
        <div class='fill'>
          <h3>
            <a href="index.php">Battle Royale</a>
          </h3>
<a class="btn pull-right primary" style="margin:5px 0 0" href="<?php echo facebook_get_log_in_url(); ?>">Log In</a>          <ul class="nav secondary-nav">

          </ul>
        </div>
      </div>
    </div>
  </div>
<?php } ?>