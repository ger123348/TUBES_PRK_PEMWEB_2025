<?php
function time_ago($timestamp) {
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;
    $minutes      = round($seconds / 60);           // value 60 is seconds
    $hours        = round($seconds / 3600);         // value 3600 is 60 minutes * 60 sec
    $days         = round($seconds / 86400);        // value 86400 is 24 hours * 60 minutes * 60 sec
    $weeks        = round($seconds / 604800);       // 7*24*60*60;
    $months       = round($seconds / 2629440);      // ((365+365+365+365+366)/5/12)*24*60*60
    $years        = round($seconds / 31553280);     // (365+365+365+365+366)/5 * 24 * 60 * 60

    if ($seconds <= 60) return "Baru saja";
    else if ($minutes <= 60) return "$minutes menit yang lalu";
    else if ($hours <= 24) return "$hours jam yang lalu";
    else if ($days <= 7) return "$days hari yang lalu";
    else if ($weeks <= 4.3) return "$weeks minggu yang lalu";
    else if ($months <= 12) return "$months bulan yang lalu";
    else return "$years tahun yang lalu";
}
?>