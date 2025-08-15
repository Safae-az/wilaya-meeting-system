<?php
session_start();
include 'db.php'; // Connexion à la base de données

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

$stmt = $pdo->query("SELECT * FROM utilisateur ORDER BY id_user");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Gestion des utilisateurs</title>
  <link rel="stylesheet" href="utilisateurs.css">
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

    .container {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      margin: 20px auto;
      max-width: 1200px;
      padding: 30px;
      border-radius: 12px;
    }

    .user-content {
      background: rgba(255, 255, 255, 0.98);
      backdrop-filter: blur(5px);
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
          <a href="utilisateurs.php" class="nav-link <?= (in_array($current_page, ['utilisateurs.php', 'ajouter_utilisateur.php'])) ? 'active' : '' ?>">
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
    <h1>Gestion des utilisateurs</h1>

    <div class="actions">
      <input type="text" placeholder="Rechercher un utilisateur..." class="search-input">
      <a href="ajouter_utilisateur.php" class="btn btn-add">+ Ajouter un utilisateur</a>
    </div>

    <table class="user-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Nom</th>
          <th>Email</th>
          <th>Rôle</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
        <tr>
          <td><?= htmlspecialchars($row['id_user']) ?></td>
          <td><?= htmlspecialchars($row['nom'] . ' ' . $row['prenom']) ?></td>
          <td><?= htmlspecialchars($row['email']) ?></td>
          <td><?= htmlspecialchars($row['role']) ?></td>
          <td>
            <a href="modifier_utilisateur.php?id=<?= $row['id_user'] ?>" class="btn btn-edit">Modifier</a>
            <a href="supprimer_utilisateur.php?id=<?= $row['id_user'] ?>" class="btn btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">Supprimer</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <script>
    const searchInput = document.querySelector('.search-input');
    const tableRows = document.querySelectorAll('.user-table tbody tr');

    searchInput.addEventListener('input', function() {
      const filter = this.value.toLowerCase();

      tableRows.forEach(row => {
        const nom = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        if (nom.includes(filter)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });
  </script>
</body>
</html>