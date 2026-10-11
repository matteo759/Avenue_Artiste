<?php
require_once __DIR__ . '/includes/auth.php';
logout_user();
session_start();
flash('Vous êtes déconnecté. À bientôt !');
redirect('index.php');
