<?php
function convertToIndianDate($date_time_string)
{
    if ($date_time_string == "Not Available") {
        return $date_time_string;
    }
    // Convert string to timestamp
    $timestamp = strtotime($date_time_string);

    // Format the timestamp to display only the date in the "dd/mm/yyyy" format
    $indian_date = date("d/m/Y", $timestamp);

    // Return the formatted date
    return $indian_date;
}
