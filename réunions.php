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

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Requête : réunions du jour avec participants et ordre du jour
$query = "
    SELECT 
        r.id_reunion,
        r.titre,
        r.date_heure,
        s.libelle_salle,
        GROUP_CONCAT(DISTINCT CONCAT(u.prenom, ' ', u.nom) SEPARATOR ', ') AS participants,
        GROUP_CONCAT(DISTINCT po.titre_point SEPARATOR ' | ') AS ordre_du_jour
    FROM reunion r
    LEFT JOIN salle s ON r.id_salle = s.id_salle
    LEFT JOIN participer p ON r.id_reunion = p.id_reunion
    LEFT JOIN utilisateur u ON p.id_user = u.id_user
    LEFT JOIN admettre a ON r.id_reunion = a.id_reunion
    LEFT JOIN point_ordre_du_jour po ON a.id_point = po.id_point
    WHERE DATE(r.date_heure) = CURDATE()
    GROUP BY r.id_reunion
    ORDER BY r.date_heure ASC
";
$reunions = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Réunions du Jour</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      
      min-height: 100vh;
      color: #333;
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

    .container {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      margin: 20px auto;
      max-width: 900px;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .user-content {
      background: rgba(255, 255, 255, 0.98);
      backdrop-filter: blur(5px);
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

    /* TITRE entouré net, centré */
    h1 {
      color: #9a4d55; 
      font-weight: 700;
      font-size: 2.5rem;
      margin-bottom: 30px;
      padding: 12px 30px;
      letter-spacing: 2px;
      cursor: default;
      box-shadow: none;
      text-shadow: none;
      text-align: center;
      display: block;
      width: 100%;
    }

    .btn-archive {
      display: block;
      margin: 20px auto;
      background: #9a4d55;
      color: white;
      padding: 12px 24px;
      border-radius: 6px;
      text-align: center;
      text-decoration: none;
      font-weight: 600;
      transition: background-color 0.3s ease;
      width: fit-content;
    }

    .btn-archive:hover {
      background: #7e3b44;
    }

    .reunion-card {
      background: rgba(255, 255, 255, 0.9);
      border-left: 5px solid #9a4d55;
      margin: 20px 0;
      padding: 20px;
      border-radius: 8px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      position: relative;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .reunion-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }

    .reunion-card h2 {
      color: #9a4d55;
      margin-top: 0;
      margin-bottom: 15px;
      font-size: 1.4rem;
      font-weight: 600;
    }

    .reunion-card p {
      margin: 8px 0;
      line-height: 1.5;
      color: #333;
      font-size: 1rem;
    }

    .reunion-card strong {
      color: #9a4d55;
      font-weight: 600;
    }

    .status {
      position: absolute;
      top: 15px;
      right: 15px;
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: bold;
      color: white;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .status.upcoming { 
      background: linear-gradient(135deg, #28a745, #20c997);
    }

    .status.ended { 
      background: linear-gradient(135deg, #dc3545, #e74c3c);
    }

    .no-reunions {
      text-align: center;
      padding: 40px 20px;
      color: #666;
      font-size: 1.1rem;
      background: rgba(255, 255, 255, 0.8);
      border-radius: 8px;
      margin: 20px 0;
    }

    .no-reunions i {
      font-size: 3rem;
      color: #9a4d55;
      margin-bottom: 15px;
      display: block;
    }

    /* Icônes dans les paragraphes */
    .reunion-card p i {
      color: #9a4d55;
      margin-right: 8px;
      width: 16px;
      text-align: center;
    }

    /* Titre avec icône */
    h1 i {
      margin-right: 15px;
      color: #9a4d55;
    }

    .btn-archive i {
      margin-right: 8px;
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
    <h1><i class="fas fa-calendar-day"></i> Réunions prévues aujourd'hui</h1>
    
    <a class="btn-archive" href="archive_reunions.php">
      <i class="fas fa-archive"></i> Voir les archives
    </a>

    <?php if (count($reunions) > 0): ?>
      <?php foreach ($reunions as $reunion): 
        $heureReunion = strtotime($reunion['date_heure']);
        $heureFin = strtotime($reunion['date_heure'] . ' +1 hour');
        $statut = (time() > $heureFin) ? 'Terminée' : 'À venir';
        $statusClass = (time() > $heureFin) ? 'ended' : 'upcoming';
      ?>
        <div class="reunion-card">
          <span class="status <?= $statusClass ?>"><?= $statut ?></span>
          <h2><i class="fas fa-users"></i> <?= htmlspecialchars($reunion['titre']) ?></h2>
          <p><strong><i class="fas fa-clock"></i> Heure :</strong>
            <?= date('H:i', $heureReunion) ?> - <?= date('H:i', $heureFin) ?>
          </p>
          <p><strong><i class="fas fa-map-marker-alt"></i> Lieu :</strong> <?= htmlspecialchars($reunion['libelle_salle']) ?></p>

          <?php if (!empty($reunion['ordre_du_jour'])): ?>
            <p><strong><i class="fas fa-list"></i> Ordre du jour :</strong> <?= htmlspecialchars($reunion['ordre_du_jour']) ?></p>
          <?php endif; ?>

          <?php if (!empty($reunion['participants'])): ?>
            <p><strong><i class="fas fa-user-friends"></i> Participants :</strong> <?= htmlspecialchars($reunion['participants']) ?></p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="no-reunions">
        <i class="fas fa-calendar-times"></i>
        <p>Aucune réunion prévue aujourd'hui.</p>
      </div>
    <?php endif; ?>
  </div>

  <script>
    // Mise à jour dynamique du statut toutes les minutes
    setInterval(() => location.reload(), 60000);
  </script>
</body>
</html>