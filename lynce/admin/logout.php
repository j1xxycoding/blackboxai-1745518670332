<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Clear all session data
session_destroy();

// Redirect to login page
redirect('login.php');
