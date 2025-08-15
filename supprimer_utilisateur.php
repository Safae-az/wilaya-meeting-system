<?php
// Inclure le fichier de connexion
include 'db.php';

// Vérifier si un ID est passé dans l'URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Préparer la requête de suppression
    $stmt = $pdo->prepare("DELETE FROM utilisateur WHERE id_user = ?");
    $stmt->execute([$id]);
}

// Rediriger vers la liste des utilisateurs
header("Location: utilisateurs.php");
exit();
?>
