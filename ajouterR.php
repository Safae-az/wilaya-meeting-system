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

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titre = $_POST['titre'] ?? '';
    $date = $_POST['date'] ?? '';
    $heure = $_POST['heure'] ?? '';
    $id_salle = $_POST['id_salle'] ?? '';
    $ordre = $_POST['ordre'] ?? '';
    $type = $_POST['type'] ?? '';
    $participants = json_decode($_POST['participants_list'] ?? '[]', true); // décoder JSON reçu

    $statut = 'Prévue';
    $id_service = 1;
    $id_user = $_SESSION['id_user']; // Utiliser l'ID de l'utilisateur connecté

    $date_heure = $date . ' ' . $heure;
    $date_fin = date('Y-m-d H:i:s', strtotime($date_heure . ' +1 hour')); // durée 1h

    // Vérifier si la salle est déjà prise
    $check = $pdo->prepare("
        SELECT * FROM reunion 
        WHERE id_salle = ? 
        AND (
            (date_heure <= ? AND DATE_ADD(date_heure, INTERVAL 1 HOUR) > ?)
        )
    ");
    $check->execute([$id_salle, $date_heure, $date_heure]);

    if ($check->rowCount() > 0) {
        $message = "❌ Cette salle est déjà réservée à cette heure-là.";
    } else {
        // Insertion réunion
        $stmt = $pdo->prepare("INSERT INTO reunion (titre, date_heure, type, statut, id_service, id_salle, id_user)
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$titre, $date_heure, $type, $statut, $id_service, $id_salle, $id_user]);

        // Récupérer l'id_reunion inséré
        $id_reunion = $pdo->lastInsertId();

        // Insertion participants (table 'participer'), participants est un tableau d'objets {type, id}
        $stmt_part = $pdo->prepare("INSERT INTO participer (id_user, id_reunion) VALUES (?, ?)");

        foreach ($participants as $part) {
            if ($part['type'] === 'interne') {
                $stmt_part->execute([$part['id'], $id_reunion]);
            } elseif ($part['type'] === 'externe') {
                // Gestion participant externe si besoin
            }
        }

        header("Location: liste_reunions.php");
        exit();
    }
}

$internes = $pdo->query("SELECT id_user, nom, prenom FROM utilisateur ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
$externes = $pdo->query("SELECT id_service_ext, nom_service_ext FROM service_exterieur ORDER BY nom_service_ext")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Ajouter une Réunion</title>
  <link rel="stylesheet" href="ajouter_reunion.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
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

    label {
      font-weight: 600;
      display: block;
      margin-top: 15px;
      margin-bottom: 6px;
      text-align: left;
    }

    input[type="text"],
    input[type="date"],
    input[type="time"],
    select,
    textarea {
      width: 100%;
      padding: 8px 12px;
      border-radius: 5px;
      border: 1px solid #ddd;
      font-size: 1rem;
      transition: border-color 0.3s ease;
      box-sizing: border-box;
    }

    input[type="text"]:focus,
    input[type="date"]:focus,
    input[type="time"]:focus,
    select:focus,
    textarea:focus {
      border-color: #9a4d55;
      outline: none;
    }

    .form-group {
      margin-bottom: 15px;
    }

    .participant-section {
      margin-top: 20px;
      text-align: left;
    }

    .participant-tag {
      display: inline-block;
      background: #9a4d55;
      color: white;
      padding: 5px 12px;
      border-radius: 20px;
      margin: 4px 4px 0 0;
      cursor: default;
      user-select: none;
      font-size: 0.9rem;
      position: relative;
    }

    .participant-tag .remove-tag {
      position: absolute;
      top: 2px;
      right: 6px;
      font-weight: bold;
      cursor: pointer;
      font-size: 1.1rem;
      line-height: 1;
      color: #f8d7da;
      transition: color 0.3s ease;
    }

    .participant-tag .remove-tag:hover {
      color: #fff;
    }

    .participant-list {
      border: 1px solid #ccc;
      max-height: 140px;
      overflow-y: auto;
      margin-top: 5px;
      padding: 5px;
      border-radius: 5px;
      opacity: 1;
      transition: opacity 0.3s ease;
    }

    .participant-item {
      padding: 7px 10px;
      cursor: pointer;
      border-radius: 4px;
      font-size: 0.9rem;
    }

    .participant-item:hover {
      background: #f2dede;
    }

    .hidden {
      display: none;
    }

    button[type="submit"] {
      background-color: #9a4d55;
      color: white;
      padding: 12px 24px;
      border: none;
      border-radius: 6px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.3s ease;
      margin-top: 20px;
    }

    button[type="submit"]:hover {
      background-color: #7e3b44;
    }

    textarea {
      min-height: 100px;
      resize: vertical;
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
    
    <h1>Ajouter une Réunion</h1>

    <?php if (!empty($message)) echo "<p style='color:red; font-weight:bold;'>" . htmlspecialchars($message) . "</p>"; ?>

    <form method="post" action="" id="form-reunion">

      <div class="form-group">
        <label for="titre">Titre de la réunion</label>
        <input type="text" id="titre" name="titre" required />
      </div>

      <div class="form-group">
        <label for="date">Date</label>
        <input type="date" id="date" name="date" required />
      </div>

      <div class="form-group">
        <label for="heure">Heure</label>
        <input type="time" id="heure" name="heure" required />
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
          <option value="">-- Choisir une salle --</option>
          <option value="1">Salle 201, Bâtiment Principal</option>
          <option value="2">Salle 105</option>
          <option value="3">Salle de Conférence</option>
        </select>
      </div>

      <div class="form-group participant-section">
        <label>Type de participant</label>
        <select id="participant-type" aria-label="Type de participant">
          <option value="">-- Choisir un type --</option>
          <option value="interne">Interne</option>
          <option value="externe">Externe</option>
        </select>
      </div>

      <div id="participant-list" class="participant-list hidden"></div>

      <div id="participant-tags" class="participant-section"></div>

      <input type="hidden" name="participants_list" id="participants_list" />

      <div class="form-group">
        <label for="ordre">Ordre du jour</label>
        <textarea id="ordre" name="ordre"></textarea>
      </div>

      <button type="submit">Ajouter la réunion</button>
    </form>
  </div>

<script>
  const internes = <?php echo json_encode($internes); ?>;
  const externes = <?php echo json_encode($externes); ?>;

  const participantTypeSelect = document.getElementById('participant-type');
  const participantListDiv = document.getElementById('participant-list');
  const participantTagsDiv = document.getElementById('participant-tags');
  const participantsInput = document.getElementById('participants_list');

  let selectedParticipants = [];

  function renderParticipantList(type) {
    participantListDiv.innerHTML = '';
    if (!type) {
      participantListDiv.classList.add('hidden');
      return;
    }

    let list = type === 'interne' ? internes : externes;
    if (!list.length) {
      participantListDiv.innerHTML = '<em>Aucun participant disponible</em>';
      participantListDiv.classList.remove('hidden');
      return;
    }

    list.forEach(item => {
      const div = document.createElement('div');
      div.classList.add('participant-item');
      if (type === 'interne') {
        div.textContent = item.nom + ' ' + item.prenom;
        div.dataset.id = item.id_user;
      } else {
        div.textContent = item.nom_service_ext;
        div.dataset.id = item.id_service_ext;
      }
      div.dataset.type = type;
      div.addEventListener('click', () => addParticipant(type, div.dataset.id, div.textContent));
      participantListDiv.appendChild(div);
    });

    participantListDiv.classList.remove('hidden');
  }

  function addParticipant(type, id, displayName) {
    if (selectedParticipants.find(p => p.type === type && p.id == id)) return;

    selectedParticipants.push({ type, id });
    renderParticipantTags();
  }

  function removeParticipant(index) {
    selectedParticipants.splice(index, 1);
    renderParticipantTags();
  }

  function renderParticipantTags() {
    participantTagsDiv.innerHTML = '';
    selectedParticipants.forEach((p, i) => {
      const tag = document.createElement('span');
      tag.className = 'participant-tag';
      let displayName = '';
      if (p.type === 'interne') {
        const user = internes.find(u => u.id_user == p.id);
        displayName = user ? user.nom + ' ' + user.prenom : 'Utilisateur';
      } else {
        const serv = externes.find(s => s.id_service_ext == p.id);
        displayName = serv ? serv.nom_service_ext : 'Service Externe';
      }
      tag.textContent = displayName;

      const removeBtn = document.createElement('span');
      removeBtn.className = 'remove-tag';
      removeBtn.textContent = '×';
      removeBtn.title = 'Retirer';
      removeBtn.onclick = () => removeParticipant(i);
      tag.appendChild(removeBtn);

      participantTagsDiv.appendChild(tag);
    });

    participantsInput.value = JSON.stringify(selectedParticipants);
  }

  participantTypeSelect.addEventListener('change', e => {
    renderParticipantList(e.target.value);
  });

  document.getElementById('form-reunion').addEventListener('submit', () => {
    if (!selectedParticipants.length) {
      participantsInput.value = JSON.stringify([]);
    }
  });
</script>
</body>
</html>