<?php

/*

A super simple, modal PHP point-of-contact script.
If given no "mode" parameter, then a form is displayed.
If given the correct "mode" parameter value, form input is processed.

This is not meant to be a professionally written program, 
but is instead just meant for educational purposes.

@author Joel Dalley
@version 2014/May/23

*/

$mode = $_REQUEST['mode'] ? $_REQUEST['mode'] : '';

// Print the form.
if (!$mode) {
    ?>
    <h1>Enter Your Action Items</h1>
    <form method="post" action="/insert_addr_form.php">
      <div>
        <label for="email_addr">Email Address:</label> 
        <input name="email_addr" type="text" maxlength="128" size="64"/>
      </div>
      <div>
        <label for="action_step1">Action Step 1:</label>
        <input name="action_step1" type="text" size="64"/>
      </div>
      <div>
        <label for="action_step2">Action Step 2:</label>
        <input name="action_step2" type="text" size="64"/>
      </div>
      <div>
        <label for="action_step3">Action Step 3:</label>
        <input name="action_step3" type="text" size="64"/>
      </div>
      <input type="hidden" name="mode" value="submit_form"/>
      <input type="submit" value="Submit"/>
    </form>
    <?php
}

// Process the form input.
elseif ($mode == 'submit_form') {
    // Form data.
    $email_addr = $_REQUEST['email_addr'];
    $step1  = $_REQUEST['action_step1'];
    $step2  = $_REQUEST['action_step2'];
    $step3  = $_REQUEST['action_step3'];

    // Insert query.
    $query = 'INSERT INTO client_contact '
           . ' (email_addr,action_step1,action_step2,action_step3)'
           . '  VALUES (?,?,?,?)';

    // Connect and execute.
    $link = new mysqli('localhost', 'contactor', '7sdfa3w8', 'test');
    $stmt = $link->prepare($query);
    $stmt->bind_param('ssss', $email_addr, $step1, $step2, $step3);
    $res = $stmt->execute();

    // Show the user if it worked or not.
    if ($res) {
        echo "Success! You have successfully entered your information!";
    }
    else {
        echo "Oops, something went wrong!";
    }

    // WUT? (This is for debugging; get rid of this, for production.)
    if (!$res) {
        echo "INSERT FAILED:\n\n";
        var_dump($res);
        exit;
    }
}
