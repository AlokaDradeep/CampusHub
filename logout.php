<?php
define('APP_ROOT', true);
require_once __DIR__ . '/../includes/functions.php';
session_unset();
session_destroy();
redirect('/campushub/index.php');
