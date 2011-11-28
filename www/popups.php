<?php

if ($user) {
  // enter a new phone number
  if (!empty($_REQUEST['phone_first'])) {
    $phone = '+1'.$_REQUEST['phone_first'].
      $_REQUEST['phone_middle'].$_REQUEST['phone_last'];
    $error = br_user_new_phone($user['_id'], $phone);
  }

  // resend verification
  if (!empty($_REQUEST['resend_code'])) {
    br_user_request_verification($user['_id']);
  }

  // try verifying
  if (!empty($user['phone']) && !empty($_REQUEST['phone_code'])) {
    $error = br_user_verify_phone($user['_id'], $_REQUEST['phone_code']);
  }

  // refresh user
  $user = br_get_user($user['_id']);

  if (empty($user['phone'])) {
?>
    <div id="first_phone_modal" class="modal hide fade">
      <div class="modal-header">
        <a href="#" class="close">x</a>
        <h3>Cell Phone Number</h3>
      </div>
      <form style='margin-bottom:0px'>
        <div class="modal-body">
          <p><strong>Battle Royale</strong> needs your cell phone number for specific game actions. Without it you will be unable to enter any game. Please enter it below.</p>
          <div class='inline-inputs' style='text-align:center;font-size:18px;'>
            ( <input maxlength="3" name='phone_first' class='mini'/> ) <input name='phone_middle' class='mini'/> - <input name='phone_last' class='mini'/>
          </div>
        </div>
        <div class="modal-footer">
          <button href="#" class="btn primary">Submit</button>
        </div>
      </form>
    </div>
    <script>
      $("#first_phone_modal").modal({backdrop: "static"});
      $("#first_phone_modal").modal("show");
    </script>
  <?php
    } else if (empty($user['phone_verified'])) {
  ?>
    <div id="phone_code_modal" class="modal hide fade">
      <div class="modal-header">
        <a href="#" class="close">x</a>
        <h3>Verify Your Phone Number</h3>
      </div>
      <form style='margin-bottom:0px'>
        <div class="modal-body">
          <p><strong>Battle Royale</strong> has sent you a verification code, please enter it below.</p>
          <div class='inline-inputs' style='text-align:center;font-size:18px;'>
            <input name='phone_code' class='small' />
          </div>
        </div>
        <div class="modal-footer">
          <button href="#" class="btn primary">Submit</button>
          <button href="#" type='submit' name='resend_code' value="true" class="btn info">Resend Code</button>
        </div>
      </form>
    </div>
    <script>
      $("#phone_code_modal").modal({backdrop: "static"});
      $("#phone_code_modal").modal("show");
    </script>
<?php
  } 
}
?>
 