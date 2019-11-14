<?php
$error = array(
    	E_ERROR,
        E_PARSE,
        E_CORE_ERROR,
        E_CORE_WARNING,
        E_COMPILE_ERROR,
        E_COMPILE_WARNING,
        E_STRICT,
);
echo E_ERROR & $error;
echo get_magic_quotes_gpc();
?>
