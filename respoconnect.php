<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="login.css">
    <title>Login</title>
</head>
<body>
    <div class="container">
        <div class="box">
            <div class="header">
                <header><img src="Logo_Wilaya.jpg" alt=""></header>
                <p>Espace responsable</p>
            </div>

            <!-- ✅ FORMULAIRE DE CONNEXION -->
            <form action="login.php" method="POST">
                <div class="input-box">
                    <label for="email">E-Mail</label>
                    <input type="email" class="input-field" id="email" name="email" required>
                    <i class="bx bx-envelope"></i>
                </div>
                <div class="input-box">
                    <label for="pass">Mot de passe</label>
                    <input type="password" class="input-field" id="pass" name="pass" required>
                    <i class="bx bx-lock"></i>
                </div>
                <div class="input-box">
                    <input type="submit" class="input-submit" value="SIGN IN">
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
