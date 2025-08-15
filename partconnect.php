<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $mot_de_passe = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $mot_de_passe === $user['mot_pass']) { // ⚠️ à sécuriser avec hash plus tard
        // Stocker les infos dans la session
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['prenom'] = $user['prenom'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['role'] = $user['role'];

        header('Location: dashboard_participant.php');
        exit();
    } else {
        $erreur = "Email ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Participant</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="container">
        <div class="box">
            <div class="header">
                <header><img src="Logo_Wilaya.jpg" alt=""></header>
                <p>Espace participant</p>
            </div>

            <?php if (isset($erreur)): ?>
                <div style="color: red; text-align: center; margin-bottom: 10px;">
                    <?= htmlspecialchars($erreur) ?>
                </div>
            <?php endif; ?>

            <form action="partconnect.php" method="post">
                <div class="input-box">
                    <label for="email">E-Mail</label>
                    <input type="email" class="input-field" id="email" name="email" required>
                    <i class="bx bx-envelope"></i>
                </div>
                <div class="input-box">
                    <label for="password">Mot de passe</label>
                    <input type="password" class="input-field" id="password" name="password" required>
                    <i class="bx bx-lock"></i>
                </div>
                <div class="input-box">
                    <input type="submit" class="input-submit" value="SIGN IN">
                </div>
            </form>

            <div class="bottom">
                <span><a href="#">Mot de passe oublié ?</a></span>
                <span><a href="register_participant.html">S'inscrire</a></span>
            </div>
        </div>
        <div class="wrapper"></div>
    </div>
</body>
</html>
