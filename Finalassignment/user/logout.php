<?php
session_start();
session_destroy();
header("Location: ../frontpage/index.php");
exit;
?>
