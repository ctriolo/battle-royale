<?php
require_once dirname(__FILE__).'/../lib/battle-royale/sms.php';

if ($user) {
  // He requested a new phone number or is entering it
  // for the first time.
  if (!empty($_REQUEST['phone_first'])) {
    $phone = '+1'.
      $_REQUEST['phone_first'].
      $_REQUEST['phone_middle'].
      $_REQUEST['phone_last'];
    $phone_code = base_convert(rand(10e3, 10e4), 10, 36);
    $user['phone'] = $phone;
    $user['phone_verified'] = false;
    $user['phone_code'] = $phone_code;
    update_people(array('facebook_id' => $user['facebook_id']),
		  array('phone' => $phone,
			'phone_verified' => false,
			'phone_code' => $phone_code));
    //$user = find_person(array('facebook_id' => $user['facebook_id'])); //remove this when working
    send_sms($user['phone'], 'Your Battle Royale verification code is: %s', $user['phone_code']);
  }

  // Resend code
  if (!empty($_REQUEST['resend_code'])) {
    send_sms($user['phone'], 'Your Battle Royale verification code is: %s', $user['phone_code']);
  }

  // If the verification code matches 
  if (!empty($user['phone']) && !empty($_REQUEST['phone_code'])) {
    if ($user['phone_code'] == $_REQUEST['phone_code']) {
      $user['phone_verified'] = true;
      update_people(array('facebook_id' => $user['facebook_id']),
		    array('phone_verified' => true));
    }
  }

  if (!empty($user) && empty($user['phone'])) {
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
            ( <input name='phone_first' class='mini'/> ) <input name='phone_middle' class='mini'/> - <input name='phone_last' class='mini'/>
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
 