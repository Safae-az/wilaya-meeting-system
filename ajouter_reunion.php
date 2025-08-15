<?php
session_start(); // ⚠️ Toujours démarrer la session

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_user'])) {
    // Redirige vers la page de login
    header("Location: respoconnect.php");
    exit();
}

$id_responsable = $_SESSION['id_user']; // ✅ Tu as l'ID ici, prêt à insérer

// Variables pour le menu
$prenom = $_SESSION['prenom'] ?? '';
$nom = $_SESSION['nom'] ?? '';
$role = strtolower(trim($_SESSION['role'] ?? ''));
$texteRole = ucfirst($role);
$current_page = basename($_SERVER['PHP_SELF']);

include 'db.php';

// Récupération des salles depuis la base
$stmt_salles = $pdo->query("SELECT id_salle, libelle_salle, capacite_salle FROM salle");
$salles = $stmt_salles->fetchAll(PDO::FETCH_ASSOC);
// Récupérer les services
$stmt_services = $pdo->query("SELECT id_service, nom_service FROM service");
$services = $stmt_services->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titre = $_POST['titre'] ?? '';
    $date = $_POST['date'] ?? '';
    $heure = $_POST['heure'] ?? '';
    $type = $_POST['type'] ?? '';
    $id_salle = $_POST['id_salle'] ?? null;
    $participants = $_POST['participants'] ?? '';
    $ordre = $_POST['ordre'] ?? '';

    $statut = 'Prévue';
    $id_service = $_POST['id_service'] ?? null;

    $date_heure = $date . ' ' . $heure;

    // Insertion dans la table reunion
    $stmt = $pdo->prepare("INSERT INTO reunion (titre, date_heure, type, statut, id_service, id_salle, id_user)
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$titre, $date_heure, $type, $statut, $id_service, $id_salle, $id_responsable]);

    // Récupération de l'ID de la réunion insérée
    $id_reunion = $pdo->lastInsertId();

    // Traitement des participants (IDs séparés par virgule)
    $participant_ids = array_filter(explode(',', $participants));
    foreach ($participant_ids as $id_user_participant) {
        $stmt_part = $pdo->prepare("INSERT INTO participer (id_user, id_reunion) VALUES (?, ?)");
        $stmt_part->execute([$id_user_participant, $id_reunion]);
    }

    header("Location: reunions.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Ajouter une Réunion</title>
  <link rel="stylesheet" href="ajouter_reunion.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Inter', sans-serif;
      background-color: #f4f6f9;
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

    .nav-brand {
      display: flex;
      align-items: center;
      padding: 15px 0;
    }

    .nav-brand h2 {
      color: white;
      margin: 0;
      font-size: 20px;
      font-weight: 600;
      text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
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
      
      .nav-brand h2 {
        font-size: 16px;
        text-align: center;
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
      max-width: 800px;
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
      <div class="nav-brand">
        <h2>Système de Gestion des Réunions</h2>
      </div>
      
      <ul class="nav-menu">
        <li class="nav-item">
          <a href="dashboard.php" class="nav-link <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Tableau de bord</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="utilisateurs.php" class="nav-link <?= (in_array($current_page, ['utilisateurs.php', 'ajouter_utilisateur.php'])) ? 'active' : '' ?>">
            <i class="fas fa-users"></i>
            <span>Utilisateurs</span>
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
            <i class="fas fa-archive"></i>
            <span>Archives</span>
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
    <h1>Ajouter une Réunion</h1>
    <form method="post" action="">
      <div class="form-group">
        <label for="titre">Titre de la réunion</label>
        <input type="text" id="titre" name="titre" required>
      </div>

      <div class="form-group">
        <label for="date">Date</label>
        <input type="date" id="date" name="date" required>
      </div>

      <div class="form-group">
        <label for="heure">Heure</label>
        <input type="time" id="heure" name="heure" required>
      </div>
      
      <div class="form-group">
        <label for="id_service">Service</label>
        <select id="id_service" name="id_service" required>
          <option value="">-- Sélectionner un service --</option>
          <?php foreach ($services as $service): ?>
            <option value="<?= $service['id_service'] ?>">
              <?= htmlspecialchars($service['nom_service']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="type">Type de réunion</label>
        <select id="type" name="type" required>
          <option value="">-- Sélectionner un type --</option>
          <option value="Technique">Technique</option>
          <option value="RH">RH</option>
          <option value="Stratégie">Stratégie</option>
          <option value="Financière">Financière</option>
          <option value="Autre">Autre</option>
        </select>
      </div>

      <div class="form-group">
        <label for="id_salle">Salle</label>
        <select id="id_salle" name="id_salle" required>
          <option value="">-- Sélectionner une salle --</option>
          <?php foreach ($salles as $salle): ?>
            <option value="<?= $salle['id_salle'] ?>">
              <?= htmlspecialchars($salle['libelle_salle']) ?> (<?= $salle['capacite_salle'] ?> places)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="participant-search">Participants</label>
        <input type="text" id="participant-search" placeholder="Rechercher un participant">
        <div id="participants-list"></div>
        <input type="hidden" name="participants" id="participants-hidden">
      </div>

      <div class="form-group">
        <label for="ordre">Ordre du jour</label>
        <textarea id="ordre" name="ordre"></textarea>
      </div>

      <button type="submit">Ajouter la réunion</button>
    </form>
  </div>

<script>
let participants = [];

$(function() {
  $("#participant-search").autocomplete({
    source: "search_users.php",
    select: function(event, ui) {
      if (!participants.includes(ui.item.value)) {
        participants.push(ui.item.value);
        $("#participants-list").append(
          "<div data-id='" + ui.item.value + "'>" + ui.item.label +
          " <button type='button' onclick='removeParticipant(" + ui.item.value + ")'>x</button></div>"
        );
        $("#participants-hidden").val(participants.join(','));
      }
      $(this).val('');
      return false;
    }
  });
});

function removeParticipant(id) {
  participants = participants.filter(pid => pid !== id);
  $("div[data-id='" + id + "']").remove();
  $("#participants-hidden").val(participants.join(','));
}
</script>
</body>
</html>