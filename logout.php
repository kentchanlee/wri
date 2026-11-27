<?php
require_once __DIR__ . '/includes/functions.php';
$_SESSION = [];
session_destroy();
header('Location: /cedar-wellness-hub/index.php?loggedout=1');
exit;
