<?php
session_start();
include 'db.php'; // Connexion à la base de données

// Vérification de la session (similaire au dashboard)
if (!isset($_SESSION['id_user']) || !isset($_SESSION['role'])) {
    header('Location: connect.php');
    exit();
}

$prenom = $_SESSION['prenom'] ?? '';
$nom = $_SESSION['nom'] ?? '';
$role = strtolower(trim($_SESSION['role']));
$texteRole = ucfirst($role);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom_user = $_POST['nom'] ?? '';
    $prenom_user = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $mot_pass = $_POST['mot_pass'] ?? '';
    $role_user = $_POST['role'] ?? '';

    // Optionnel : hashage du mot de passe
    // $mot_pass = password_hash($mot_pass, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO utilisateur (nom, prenom, email, mot_pass, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nom_user, $prenom_user, $email, $mot_pass, $role_user]);

    header("Location: utilisateurs.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Ajouter un utilisateur</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f4f6f9;
      margin: 0;
      padding: 0;
    }

    /* Styles pour le menu de navigation */
    .navbar {
      background-color: #9a4d55;
      padding: 0;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .nav-container {
      max-width: 1200px;
      margin: 0 auto;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 20px;
    }

    .nav-logo {
      display: flex;
      align-items: center;
      padding: 10px 0;
      color: white;
      text-decoration: none;
    }

    .nav-logo img {
      height: 40px;
      width: 40px;
      margin-right: 10px;
      border-radius: 50%;
    }

    .nav-logo span {
      font-weight: bold;
      font-size: 18px;
    }

    .nav-menu {
      display: flex;
      list-style: none;
      margin: 0;
      padding: 0;
      align-items: center;
    }

    .nav-item {
      margin: 0 5px;
    }

    .nav-link {
      display: flex;
      align-items: center;
      padding: 15px 12px;
      color: white;
      text-decoration: none;
      transition: background-color 0.3s ease;
      border-radius: 4px;
      font-size: 14px;
    }

    .nav-link:hover {
      background-color: rgba(255, 255, 255, 0.1);
    }

    .nav-link.active {
      background-color: rgba(255, 255, 255, 0.2);
    }

    .nav-link i {
      margin-right: 8px;
      font-size: 16px;
    }

    .nav-link.logout {
      background-color: #7e3e44;
      margin-left: 10px;
    }

    .nav-link.logout:hover {
      background-color: #6b3339;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .nav-container {
        flex-direction: column;
        padding: 10px;
      }
      
      .nav-menu {
        flex-wrap: wrap;
        justify-content: center;
        margin-top: 10px;
      }
      
      .nav-link {
        padding: 10px 8px;
        font-size: 12px;
      }
      
      .nav-link span {
        display: none;
      }
    }

    /* Styles existants pour le formulaire */
    .container {
      max-width: 700px;
      margin: 30px auto;
      background-color: #fff;
      padding: 30px 40px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(154, 77, 85, 0.3);
    }

    h1 {
      text-align: center;
      margin-bottom: 30px;
      color: #555;
      font-weight: bold;
    }

    .form-group {
      margin-bottom: 20px;
    }

    label {
      display: block;
      margin-bottom: 6px;
      font-weight: bold;
      color: #555;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"],
    select {
      width: 100%;
      padding: 10px;
      border: 1px solid rgb(110, 109, 109);
      border-radius: 8px;
      font-size: 16px;
      box-sizing: border-box;
    }

    button {
      background-color: #9a4d55;
      color: white;
      padding: 12px 20px;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      width: 100%;
      transition: background-color 0.3s ease;
    }

    button:hover {
      background-color: #7e3e44;
    }

    .back-link {
      text-align: center;
      margin-top: 20px;
    }

    .back-link a {
      color: #9a4d55;
      text-decoration: none;
    }

    .back-link a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <!-- Menu de navigation -->
  <nav class="navbar">
    <div class="nav-container">
      <a href="dashboard.php" class="nav-logo">
        <span><?= htmlspecialchars($texteRole) ?></span>
      </a>
      
      <ul class="nav-menu">
        <li class="nav-item">
          <a href="dashboard.php" class="nav-link">
            <i class="fas fa-tachometer-alt"></i>
            <span>Tableau de bord</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="utilisateurs.php" class="nav-link active">
            <i class="fas fa-user-plus"></i>
            <span>Gestion utilisateurs</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="ajouterR.php" class="nav-link">
            <i class="fas fa-calendar-plus"></i>
            <span>Nouvelle Réunion</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="réunions.php" class="nav-link">
            <i class="fas fa-calendar-check"></i>
            <span>Réunions du jour</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="archive_reunions.php" class="nav-link">
            <i class="fas fa-file-alt"></i>
            <span>Réunions</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="logout.php" class="nav-link logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Déconnexion</span>
          </a>
        </li>
      </ul>
    </div>
  </nav>

  <!-- Contenu principal -->
  <div class="container">
    <h1>Ajouter un utilisateur</h1>
    <form method="post" action="">
      <div class="form-group">
        <label for="nom">Nom :</label>
        <input type="text" id="nom" name="nom" required />
      </div>
      <div class="form-group">
        <label for="prenom">Prénom :</label>
        <input type="text" id="prenom" name="prenom" required />
      </div>
      <div class="form-group">
        <label for="email">Email :</label>
        <input type="email" id="email" name="email" required />
      </div>
      <div class="form-group">
        <label for="mot_pass">Mot de passe :</label>
        <input type="password" id="mot_pass" name="mot_pass" required />
      </div>
      <div class="form-group">
        <label for="role">Rôle :</label>
        <select id="role" name="role" required>
          <option value="">-- Sélectionner un rôle --</option>
          <option value="admin">Admin</option>
          <option value="responsable">Responsable</option>
          <option value="participant">Participant</option>
        </select>
      </div>
      <button type="submit">Ajouter</button>
    </form>
    <div class="back-link">
      <a href="utilisateurs.php">⬅ Retour à la liste des utilisateurs</a>
    </div>
  </div>
</body>
</html>