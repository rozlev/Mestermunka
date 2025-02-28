<?php
$hashed_password = password_hash("Admin1234", PASSWORD_BCRYPT);
echo "Új hash: " . $hashed_password;
?>
