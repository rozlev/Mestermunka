<?php
$hashed_password = '$2y$10$pG4nJLPq8wyo1G8hT7blweJHlsNnqYB2KXtI7R4hDRu5p5H2MX1qC'; // IDE MÁSOLD BE AZ ADATBÁZISBÓL A HASH-T
$input_password = "Admin1234";

if (password_verify($input_password, $hashed_password)) {
    echo "✅ A jelszó helyes!";
} else {
    echo "❌ A jelszó NEM megfelelő!";
}
?>
