<?php
session_start();
$_SESSION['user_id'] = 2; // Mettez l'ID d'un utilisateur existant

$ch = curl_init('http://localhost/bini_dang_market/api/messages.php?action=conversations');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

echo "<h2>Réponse API :</h2>";
echo "<pre>";
print_r(json_decode($response, true));
echo "</pre>";
?>