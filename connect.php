<?php
session_start();
require_once('db.php'); // Connexion via PDO

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Vérifier si l'utilisateur existe
    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Vérifier mot de passe (non haché)
        if ($user['mot_pass'] === $password) {
            if (strtolower($user['role']) === 'admin') {
                // Authentification OK : démarrer session
                $_SESSION['id_user'] = $user['id_user'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['nom'] = $user['nom'];
                $_SESSION['prenom'] = $user['prenom'];

                // Redirection vers le dashboard admin
                header('Location: dashboard.php');
                exit();
            } else {
                echo "<p style='color: red; text-align: center;'>Accès refusé. Vous n'êtes pas un administrateur.</p>";
            }
        } else {
            echo "<p style='color: red; text-align: center;'>Mot de passe incorrect.</p>";
        }
    } else {
        echo "<p style='color: red; text-align: center;'>Aucun compte trouvé avec cet email.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="login.css" />
    <title>Login</title>
</head>
<body>
    <div class="container">
        <div class="box">
            <div class="header">
                <header><img src="Logo_Wilaya.jpg" alt="Logo Wilaya" /></header>
                <p>Espace Administrateur</p>
            </div>


            <form method="post" action="connect.php">
                <div class="input-box">
                    <label for="email">E-Mail</label>
                    <input type="email" class="input-field" id="email" name="email" required />
                    <i class="bx bx-envelope"></i>
                </div>
                <div class="input-box">
                    <label for="pass">Mot de passe</label>
                    <input type="password" class="input-field" id="pass" name="password" required />
                    <i class="bx bx-lock"></i>
                </div>
                <div class="input-box">
                    <input type="submit" class="input-submit" value="SIGN IN" />
                </div>
            </form>

            <div class="bottom">
                <span><a href="#">Se connecter</a></span>
                <span><a href="#">Mot de passe oublié ?</a></span>
            </div>
        </div>
        <div class="wrapper"></div>
    </div>
</body>
</html>
