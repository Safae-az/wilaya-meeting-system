<?php
session_start();
require_once('db.php');

// Vérification de la session
if (!isset($_SESSION['id_user']) || !isset($_SESSION['role'])) {
    header('Location: connect.php');
    exit();
}

$prenom = $_SESSION['prenom'] ?? '';
$nom = $_SESSION['nom'] ?? '';
$role = strtolower(trim($_SESSION['role']));
$texteRole = ucfirst($role);
$current_page = basename($_SERVER['PHP_SELF']);

// Récupérer l'utilisateur depuis la base
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE id_user = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("Utilisateur introuvable !");
    }
} else {
    die("ID non fourni !");
}

// Mettre à jour l'utilisateur si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom_user = $_POST['nom'];
    $prenom_user = $_POST['prenom'];
    $email = $_POST['email'];
    $mot_pass = $_POST['mot_pass'];
    $role_user = $_POST['role'];

    $stmt = $pdo->prepare("UPDATE utilisateur SET nom = ?, prenom = ?, email = ?, mot_pass = ?, role = ? WHERE id_user = ?");
    $stmt->execute([$nom_user, $prenom_user, $email, $mot_pass, $role_user, $id]);

    header("Location: utilisateurs.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Modifier utilisateur</title>
  <link rel="stylesheet" href="utilisateurs1.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
  <style>
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

    /* Styles pour le contenu */
    

    .container {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      margin: 20px auto;
      max-width: 800px;
      padding: 30px;
      border-radius: 12px;
    }

    .btn-retour {
      display: inline-block;
      margin-bottom: 20px;
      padding: 10px 16px;
      background-color: #9a4d55;
      color: white;
      text-decoration: none;
      border-radius: 6px;
      font-weight: 600;
      transition: background-color 0.3s ease;
    }

    .btn-retour:hover {
      background-color: #7e3b44;
    }

    h1 {
      color: #9a4d55;
      font-weight: 700;
      font-size: 2.5rem;
      margin-bottom: 30px;
      text-align: center;
      letter-spacing: 1px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #333;
    }

    .form-group input,
    .form-group select {
      width: 100%;
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: 1rem;
      transition: border-color 0.3s ease;
      box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group select:focus {
      border-color: #9a4d55;
      outline: none;
      box-shadow: 0 0 0 2px rgba(154, 77, 85, 0.1);
    }

    .btn {
      padding: 12px 24px;
      border: none;
      border-radius: 6px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      margin-right: 10px;
      transition: background-color 0.3s ease;
    }

    .btn-add {
      background-color: #9a4d55;
      color: white;
    }

    .btn-add:hover {
      background-color: #7e3b44;
    }

    .btn-secondary {
      background-color: #6c757d;
      color: white;
    }

    .btn-secondary:hover {
      background-color: #545b62;
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
    .form-actions {
      margin-top: 30px;
      text-align: center;
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
          <a href="dashboard.php" class="nav-link <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Tableau de bord</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="utilisateurs.php" class="nav-link <?= (in_array($current_page, ['utilisateurs.php', 'ajouter_utilisateur.php', 'modifier_utilisateur.php'])) ? 'active' : '' ?>">
            <i class="fas fa-user-plus"></i>
            <span>Gestion utilisateurs</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="ajouterR.php" class="nav-link <?= ($current_page == 'ajouterR.php') ? 'active' : '' ?>">
            <i class="fas fa-calendar-plus"></i>
            <span>Nouvelle Réunion</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="réunions.php" class="nav-link <?= ($current_page == 'réunions.php') ? 'active' : '' ?>">
            <i class="fas fa-calendar-check"></i>
            <span>Réunions du jour</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="archive_reunions.php" class="nav-link <?= ($current_page == 'archive_reunions.php') ? 'active' : '' ?>">
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
    
    <h1>Modifier un utilisateur</h1>
    
    <form method="post">
      <div class="form-group">
        <label for="nom">Nom</label>
        <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required>
      </div>
      
      <div class="form-group">
        <label for="prenom">Prénom</label>
        <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required>
      </div>
      
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
      </div>
      
      <div class="form-group">
        <label for="mot_pass">Mot de passe</label>
        <input type="password" id="mot_pass" name="mot_pass" value="<?= htmlspecialchars($user['mot_pass']) ?>" required>
      </div>
      
      <div class="form-group">
        <label for="role">Rôle</label>
        <select id="role" name="role" required>
          <option value="Admin" <?= ($user['role'] == 'Admin') ? 'selected' : '' ?>>Admin</option>
          <option value="Manager" <?= ($user['role'] == 'Manager') ? 'selected' : '' ?>>Manager</option>
          <option value="Utilisateur" <?= ($user['role'] == 'Utilisateur') ? 'selected' : '' ?>>Utilisateur</option>
        </select>
      </div>
      
      <div class="form-actions">
        <button type="submit" class="btn btn-add">
          <i class="fas fa-save"></i> Enregistrer
        </button>
        <a href="utilisateurs.php" class="btn btn-secondary">
          <i class="fas fa-times"></i> Annuler
        </a>
      </div>
    </form>
    <div class="back-link">
      <a href="utilisateurs.php">⬅ Retour à la liste des utilisateurs</a>
    </div>
  </div>
</body>
</html>