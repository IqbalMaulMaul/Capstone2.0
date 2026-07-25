<?php

// Fix Vercel REQUEST_URI and SCRIPT_NAME issues
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/../public/index.php';

// Directory creation is handled by bootstrap/app.php — no need to duplicate here

require __DIR__ . '/../public/index.php';
