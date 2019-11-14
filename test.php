<?php
//echo addslashes("O'reilly");
if (function_exists("mysql_real_escape_string")) {
    echo mysql_real_escape_string("O\'reilly");
} else echo 'function not exists.'
?>
