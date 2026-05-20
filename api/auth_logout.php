<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

session_start();
session_unset();
session_destroy();

http_response_code(204);
