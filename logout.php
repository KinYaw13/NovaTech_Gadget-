<?php
require_once __DIR__ . '/includes/db.php';
session_destroy();
redirect('index.php');

