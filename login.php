<?php
session_start();
require_once('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $pass = $_POST['pass'] ?? $_POST['password'] ?? '';

    // Requête préparée
    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['mot_pass'] === $pass) {
        $role = strtolower(trim($user['role']));

        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['prenom'] = $user['prenom'];

        // 🔁 Redirection dynamique selon le rôle
        if ($role === 'admin') {
            header('Location: dashboard.php');
            exit();
        } elseif (str_contains($role, 'responsable')) {
            header('Location: ajouter_reunion.php'); // ou ton dashboard Responsable
            exit();
        } else {
            echo "<p style='color:red; text-align:center;'>Accès refusé. Vous n'avez pas de rôle autorisé.</p>";
        }
    } else {
        echo "<p style='color:red; text-align:center;'>Email ou mot de passe incorrect.</p>";
    }
}
?>
