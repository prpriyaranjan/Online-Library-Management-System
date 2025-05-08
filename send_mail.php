<?php
function sendMail($to, $subject, $message) {
    $headers = "From: The Shiv Library <noreply@shivlibrary.com>\r\n";
    $headers .= "Content-type: text/plain; charset=UTF-8\r\n";
    mail($to, $subject, $message, $headers);
}
?>
