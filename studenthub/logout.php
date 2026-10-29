<?php
require_once 'includes/functions.php'; require_once 'includes/db.php';
if (is_logged_in()) audit($pdo, 'logout', current_user()['email']);
logout_user(true);
session_start(); flash('success', 'You have been logged out.');
redirect('login.php');
