<?php

/*

This is meant to be scheduled and run by cron.

This is not meant to be a professionally written program, but is
instead just meant for educational purposes.

@author Joel Dalley
@version 2014/May/27

*/

// Feel free to change this to match your location.
// http://www.php.net/manual/en/timezones.america.php (Assuming America)
date_default_timezone_set('America/Denver');

// How long in between each action step reminder.
define(INTERVAL, 86400*2); // 2 days, in seconds.

// Change these to match your actual address(es):
define(SENT_FROM_ADDR, 'nobody@no.where');
define(REPLY_TO_ADDR, 'nobody@no.where');


// Get command line argument; default to step 1.
//
// (This is probably overkill for a learning exercise, but I want to
//  demonstrate the importance of validating input here, because we here
//  use the input directly in our query, as part two column names we 
//  select; this is super-hazardous, so extra care is essential.)
//
$valid  = array(1, 2, 3);
$which_step = count($argv) > 1
           && $argv[1] == (integer) $argv[1]       // Is it a number?
           && in_array((integer) $argv[1], $valid) // Is it 1, 2 or 3?
            ? (integer) $argv[1] : 1;              // Legit? Or default to 1.

// Translate the integer number (1, 2 or 3) into a column name.
$step_col_name = 'action_step' . $which_step;
$step_date_col = $step_col_name . '_date_sent';

// Each reminder goes out two days after the last, so calculate:
$step_interval = $which_step * INTERVAL;

// Query to select only the action steps matching the 
// given number, that haven't already been sent out.
// NOTE: the date math can probably be done differently, 
//       but for now, we stick to UNIX epochs.
$select_query = "SELECT email_addr,$step_col_name,date_added FROM client_contact"
              . " WHERE $step_date_col=0"
              . "   AND UNIX_TIMESTAMP(NOW()) - $step_interval "
              . "       >= UNIX_TIMESTAMP(date_added)";

// Select matching rows.
$link = new mysqli('localhost', 'contactor', '7sdfa3w8', 'test');
$select_res = $link->query($select_query);


// Iterate over rows, send email, and update the sent 
// date column for the rows corresponding to the emails sent.
while ($row = $select_res->fetch_array()) {
    list($addr, $step_col_value, $date_added) = $row;

    // Send the email, and print out that this happened. (Another approach
    // instead of printing,  would be to blind carbon-copy yourself.)
    $headers = 'From: ' . SENT_FROM_ADDR . "\r\n" 
             . 'Reply-To: ' . REPLY_TO_ADDR . "\r\n";
    mail($addr,                                           // Mail-to address.
         "Action Step Reminder - Step $which_step",       // Subject.
         "Your action step for today: $step_col_value.",  // Body.
         $headers);                                       // Additional headers.
    echo "Sent message to `$addr`, for action step `$which_step`,",
         " which was `$step_col_value`\n";

    // Record the timestamp that it was sent, so it won't get selected next time.
    $update_time = date('Y-m-d H:i:s');
    $update_query = "UPDATE client_contact SET $step_date_col=? "
                  . " WHERE email_addr=? AND $step_col_name=? AND date_added=?";
    $stmt = $link->prepare($update_query);
    $stmt->bind_param('ssss', $update_time, $addr, $step_col_value, $date_added);
    $update_res = $stmt->execute();

    // WUT? (This is for debugging; get rid of this, for production.)
    if (!$update_res) {
        echo "UPDATE FAILED:\n\n";
        var_dump($update_query);
        var_dump($update_time, $addr, $step_col_value, $date_added);
        exit;
    }
}
