<?php
/**
 * लॉगआउट पृष्ठ
 */
session_start();
session_destroy();
header('Location: index.php');
exit;
