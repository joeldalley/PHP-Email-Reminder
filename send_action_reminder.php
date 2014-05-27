<?php

/*

This is meant to be scheduled and run by cron.

This is not meant to be a professionally written program, but is
instead just meant for educational purposes.

@author Joel Dalley
@version 2014/May/27

*/

// How long in between each action step reminder.
define(INTERVAL, 86400*2); // 2 days, in seconds.

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
$step_col_name = "action_step$which_step";
$step_date_col = $step_col_name . '_date_sent';

// Each reminder goes out two days after the last, so calculate:
$when = time() - $which_step * INTERVAL;

// Query to select only the action steps matching the 
// given number, that haven't already been sent out.
// NOTE: the date math can probably be done differently, 
//       but for now, we stick to UNIX epochs.
$query = "SELECT email_addr,$step_col_name FROM client_contact"
       . " WHERE $step_date_col=0"
       . "   AND UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP($when) "
       . "       >= UNIX_TIMESTAMP(date_added)";

// Select matching rows.
$link = new mysqli('localhost', 'contactor', '7sdfa3w8', 'test');
$res = $link->query($query);

while ($row = $res->fetch_array()) {
    list($addr, $step) = $row;

    // Uncomment to actually send an email.
    // mail($addr, 'Action Step Reminder', "Your action step for today: $step.");
    echo "Sent message to `$addr`, for action step `$which_step`, which was `$step`\n";
}
