<?php
session_start();
require_once 'db.php';

// Redirection si l'utilisateur n'est pas connecté
if (!isset($_SESSION['id_user']) || !isset($_SESSION['role'])) {
    header('Location: connect.php');
    exit();
}

$prenom = $_SESSION['prenom'] ?? '';
$nom = $_SESSION['nom'] ?? '';
$role = strtolower(trim($_SESSION['role']));

$titreBienvenue = "Bienvenue " . htmlspecialchars($prenom . ' ' . $nom);
$texteRole = ucfirst($role);

// Statistiques :
$nbUtilisateurs = $pdo->query("SELECT COUNT(*) FROM utilisateur")->fetchColumn();
$nbReunions = $pdo->query("SELECT COUNT(*) FROM reunion")->fetchColumn();
$nbServices = $pdo->query("SELECT COUNT(*) FROM service")->fetchColumn();

// Activités récentes
$activites = $pdo->query("  
    SELECT * FROM (
        SELECT 'Ajout utilisateur' AS type, nom, prenom, NULL AS titre, NULL AS date_event
        FROM utilisateur
        UNION ALL
        SELECT 'Ajout réunion' AS type, '' AS nom, '' AS prenom, titre, date_heure AS date_event
        FROM reunion
    ) AS all_activities
    ORDER BY date_event DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Tableau de bord</title>
  <link rel="stylesheet" href="dashboardstyle.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
</head>
<body>
  <div class="container">
    <nav>
      <ul style="list-style-type:none;">
        <li><a href="#" class="logo">
          <img src="Logo_Wilaya.jpg" alt="Logo">
          <span class="nav-item"><?= htmlspecialchars($texteRole) ?></span>
        </a></li>
        <li><a href="#" class="active">
          <i class="fas fa-tachometer-alt"></i>
          <span class="nav-item"><b>Tableau de bord</b></span>
        </a></li>
        <li><a href="utilisateurs.php">
          <i class="fas fa-user-plus"></i>
          <span class="nav-item"><b>Gestion des utilisateurs</b></span>
        </a></li>
        <li><a href="ajouterR.php">
          <i class="fas fa-calendar-plus"></i>
          <span class="nav-item"><b>Nouvelle Réunion</b></span>
        </a></li>
        <li><a href="réunions.php">
          <i class="fas fa-calendar-check"></i>
          <span class="nav-item"><b>Réunions du jour</b></span>
        </a></li>
        <li><a href="archive_reunions.php">
          <i class="fas fa-file-alt"></i>
          <span class="nav-item"><b>Réunions</b></span>
        </a></li>
        <li><a href="logout.php" class="logout">
          <i class="fas fa-sign-out-alt"></i>
          <span class="nav-item"><b>Déconnexion</b></span>
        </a></li>
      </ul>
    </nav>

    <section class="main">
      <div class="main-top">
        <h1><?= $titreBienvenue ?></h1>
        <i class="fas fa-user-cog"></i>
      </div>

      <div class="cards">
        <div class="card">
          <i class="fas fa-users"></i>
          <h3>Utilisateurs</h3>
          <p><?= $nbUtilisateurs ?> enregistrés</p>
        </div>
        <div class="card">
          <i class="fas fa-calendar-check"></i>
          <h3>Réunions</h3>
          <p><?= $nbReunions ?> prévues</p>
        </div>
        <div class="card">
          <i class="fas fa-building"></i>
          <h3>Services</h3>
          <p><?= $nbServices ?> services actifs</p>
        </div>
      </div>

      <section class="data-section">
        <div class="data-box">
          <h2>Dernières Activités</h2>
          <ul>
            <?php foreach ($activites as $act) : ?>
              <li>
                <?= htmlspecialchars($act['type']) ?> :
                <?php
                  if ($act['type'] === 'Ajout utilisateur') {
                    echo htmlspecialchars($act['nom'] . ' ' . $act['prenom']);
                  } elseif ($act['type'] === 'Ajout réunion') {
                    echo htmlspecialchars($act['titre'] . ' - ' . date('d/m/Y H:i', strtotime($act['date_event'])));
                  }
                ?>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </section>
    </section>
  </div>
</body>
</html>
