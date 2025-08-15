<?php
session_start();

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

// Connexion à la base de données
include 'db.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Dossier d'upload pour les fichiers
$uploadDir = __DIR__ . '/compte_rendus/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Upload d'un compte rendu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_reunion']) && isset($_FILES['compte_rendu_file'])) {
    $id_reunion = $_POST['id_reunion'];

    if ($_FILES['compte_rendu_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['compte_rendu_file']['tmp_name'];
        $fileName = $_FILES['compte_rendu_file']['name'];
        $fileSize = $_FILES['compte_rendu_file']['size'];
        $fileType = $_FILES['compte_rendu_file']['type'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['pdf', 'doc', 'docx'];
        if (in_array($fileExtension, $allowedExtensions)) {
            if ($fileSize <= 5000000) {
                $newFileName = 'cr_' . $id_reunion . '_' . time() . '.' . $fileExtension;
                $destPath = $uploadDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    try {
                        // Vérifier si un compte rendu existe déjà pour cette réunion
                        $stmt = $pdo->prepare("SELECT id_compte_rendu, fichier FROM compte_rendu WHERE id_reunion = :id_reunion");
                        $stmt->execute([':id_reunion' => $id_reunion]);
                        $existingCR = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($existingCR) {
                            // Supprimer l'ancien fichier
                            $oldPath = $uploadDir . $existingCR['fichier'];
                            if (file_exists($oldPath)) unlink($oldPath);

                            // Mettre à jour l'entrée
                            $stmt = $pdo->prepare("UPDATE compte_rendu SET fichier = :fichier WHERE id_compte_rendu = :id_cr");
                            $stmt->execute([':fichier' => $newFileName, ':id_cr' => $existingCR['id_compte_rendu']]);
                        } else {
                            // Créer un nouveau compte rendu
                            $stmt = $pdo->prepare("INSERT INTO compte_rendu (id_reunion, fichier) VALUES (:id_reunion, :fichier)");
                            $stmt->execute([':id_reunion' => $id_reunion, ':fichier' => $newFileName]);
                        }

                        header("Location: " . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
                        exit();
                    } catch (PDOException $e) {
                        $error = "Erreur en sauvegardant le compte rendu : " . $e->getMessage();
                    }
                } else {
                    $error = "Impossible de déplacer le fichier uploadé.";
                }
            } else {
                $error = "Le fichier dépasse la taille maximale (5MB).";
            }
        } else {
            $error = "Seuls les fichiers PDF, DOC, DOCX sont autorisés.";
        }
    } else {
        $error = "Erreur lors de l'upload du fichier (code " . $_FILES['compte_rendu_file']['error'] . ").";
    }
}

// Suppression d'un compte rendu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_cr'], $_POST['id_reunion'])) {
    $id_reunion = $_POST['id_reunion'];

    try {
        // Récupérer le compte rendu
        $stmt = $pdo->prepare("SELECT id_compte_rendu, fichier FROM compte_rendu WHERE id_reunion = :id_reunion");
        $stmt->execute([':id_reunion' => $id_reunion]);
        $cr = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cr) {
            $filePath = $uploadDir . $cr['fichier'];
            if (file_exists($filePath)) unlink($filePath);

            // Supprimer l'entrée
            $stmt = $pdo->prepare("DELETE FROM compte_rendu WHERE id_compte_rendu = :id_cr");
            $stmt->execute([':id_cr' => $cr['id_compte_rendu']]);
        }

        header("Location: " . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
        exit();
    } catch (PDOException $e) {
        $error = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

// Filtres de recherche
$conditions = [];
$params = [];

if (!empty($_GET['titre'])) {
    $conditions[] = "r.titre LIKE :titre";
    $params[':titre'] = '%' . $_GET['titre'] . '%';
}
if (!empty($_GET['type'])) {
    $conditions[] = "r.type = :type";
    $params[':type'] = $_GET['type'];
}
if (!empty($_GET['date'])) {
    $conditions[] = "DATE(r.date_heure) = :date";
    $params[':date'] = $_GET['date'];
}
if (!empty($_GET['salle'])) {
    $conditions[] = "s.libelle_salle LIKE :salle";
    $params[':salle'] = '%' . $_GET['salle'] . '%';
}

// Récupération des réunions + compte_rendus
$sql = "
    SELECT 
        r.id_reunion,
        r.titre,
        r.date_heure,
        r.type,
        r.statut,
        s.libelle_salle,
        cr.fichier AS compte_rendu
    FROM reunion r
    LEFT JOIN salle s ON r.id_salle = s.id_salle
    LEFT JOIN compte_rendu cr ON r.id_reunion = cr.id_reunion
";

if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}
$sql .= " ORDER BY r.date_heure DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reunions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Toutes les Réunions</title>
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
            max-width: 1000px;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
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

        /* Formulaire de recherche */
        form {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        form input[type="text"],
        form input[type="date"],
        form select {
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            min-width: 150px;
        }

        form input[type="text"]:focus,
        form input[type="date"]:focus,
        form select:focus {
            border-color: #9a4d55;
            outline: none;
        }

        form button[type="submit"] {
            background-color: #9a4d55;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        form button[type="submit"]:hover {
            background-color: #7e3b44;
        }

        hr {
            border: none;
            height: 2px;
            background: linear-gradient(90deg, transparent, #9a4d55, transparent);
            margin: 20px 0;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        .reunion-card {
            background: rgba(255, 255, 255, 0.9);
            border-left: 5px solid #9a4d55;
            margin: 20px 0;
            padding: 25px;
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

        .btn-compte-rendu {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #9a4d55;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-compte-rendu:hover {
            background: #7e3b44;
        }

        .compte-rendu-content {
            margin-top: 20px;
            padding: 15px;
            background: rgba(154, 77, 85, 0.1);
            border-radius: 6px;
            border: 1px solid rgba(154, 77, 85, 0.2);
        }

        .compte-rendu-content h3 {
            color: #9a4d55;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .file-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .file-icon {
            font-size: 1.2rem;
            color: #9a4d55;
        }

        .file-info a {
            color: #9a4d55;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .file-info a:hover {
            color: #7e3b44;
            text-decoration: underline;
        }

        .delete-cr {
            background: #dc3545;
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .delete-cr:hover {
            background: #c82333;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: white;
            margin: 10% auto;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            position: relative;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }

        .close {
            position: absolute;
            right: 15px;
            top: 15px;
            font-size: 28px;
            font-weight: bold;
            color: #9a4d55;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close:hover {
            color: #7e3b44;
        }

        .modal-content h2 {
            color: #9a4d55;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }

        .modal-content input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 2px dashed #9a4d55;
            border-radius: 6px;
            margin: 10px 0;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }

        .modal-content input[type="file"]:hover {
            border-color: #7e3b44;
        }

        .modal-content button[type="submit"] {
            background-color: #9a4d55;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 100%;
        }

        .modal-content button[type="submit"]:hover {
            background-color: #7e3b44;
        }

        /* Message quand aucune réunion */
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
        <h1><i class="fas fa-list-alt"></i> Liste complète des réunions</h1>

        <form method="GET" action="">
            <input type="text" name="titre" placeholder="Titre" value="<?= htmlspecialchars($_GET['titre'] ?? '') ?>">
            <select name="type">
                <option value="">-- Type --</option>
                <option value="Technique" <?= (($_GET['type'] ?? '') === 'Technique') ? 'selected' : '' ?>>Technique</option>
                <option value="RH" <?= (($_GET['type'] ?? '') === 'RH') ? 'selected' : '' ?>>RH</option>
                <option value="Stratégie" <?= (($_GET['type'] ?? '') === 'Stratégie') ? 'selected' : '' ?>>Stratégie</option>
                <option value="Financière" <?= (($_GET['type'] ?? '') === 'Financière') ? 'selected' : '' ?>>Financière</option>
                <option value="Autre" <?= (($_GET['type'] ?? '') === 'Autre') ? 'selected' : '' ?>>Autre</option>
            </select>
            <input type="date" name="date" value="<?= htmlspecialchars($_GET['date'] ?? '') ?>">
            <input type="text" name="salle" placeholder="Salle" value="<?= htmlspecialchars($_GET['salle'] ?? '') ?>">
            <button type="submit"><i class="fas fa-search"></i> Rechercher</button>
        </form>

        <hr>

        <?php if (isset($error)): ?>
            <div class="error"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (count($reunions) > 0): ?>
            <?php foreach ($reunions as $reunion): ?>
                <div class="reunion-card">
                    <button class="btn-compte-rendu" onclick="openModal('<?= $reunion['id_reunion'] ?>')">
                        <i class="fas fa-file-upload"></i>
                        <?= empty($reunion['compte_rendu']) ? 'Ajouter CR' : 'Modifier CR' ?>
                    </button>
                    <h2><i class="fas fa-users"></i> <?= htmlspecialchars($reunion['titre']) ?></h2>
                    <p><strong><i class="fas fa-tag"></i> Type :</strong> <?= htmlspecialchars($reunion['type']) ?></p>
                    <p><strong><i class="fas fa-calendar-alt"></i> Date et heure :</strong> <?= date('d/m/Y H:i', strtotime($reunion['date_heure'])) ?></p>
                    <p><strong><i class="fas fa-map-marker-alt"></i> Salle :</strong> <?= htmlspecialchars($reunion['libelle_salle']) ?></p>
                    <p><strong><i class="fas fa-info-circle"></i> Statut :</strong> <?= htmlspecialchars($reunion['statut']) ?></p>

                    <?php if (!empty($reunion['compte_rendu'])): ?>
                        <div class="compte-rendu-content">
                            <h3><i class="fas fa-file-alt"></i> Compte rendu :</h3>
                            <div class="file-info">
                                <?php
                                $fileExtension = pathinfo($reunion['compte_rendu'], PATHINFO_EXTENSION);
                                $iconClass = ($fileExtension === 'pdf') ? 'far fa-file-pdf' :
                                             (in_array($fileExtension, ['doc', 'docx']) ? 'far fa-file-word' : 'far fa-file');
                                ?>
                                <i class="<?= $iconClass ?> file-icon"></i>
                                <a href="compte_rendus/<?= htmlspecialchars($reunion['compte_rendu']) ?>" target="_blank">
                                    <i class="fas fa-download"></i> Télécharger le compte rendu
                                </a>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="id_reunion" value="<?= $reunion['id_reunion'] ?>">
                                    <button type="submit" name="delete_cr" class="delete-cr" onclick="return confirm('Supprimer ce compte rendu ?')">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-reunions">
                <i class="fas fa-calendar-times"></i>
                <p>Aucune réunion trouvée.</p>
            </div>
        <?php endif; ?>

        <div id="modalCompteRendu" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h2 id="modalTitle"><i class="fas fa-file-upload"></i> Compte rendu de réunion</h2>
                <form id="formCompteRendu" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_reunion" id="modalReunionId">
                    <p>Veuillez sélectionner un fichier (PDF, DOC, DOCX - max 5MB) :</p>
                    <input type="file" name="compte_rendu_file" accept=".pdf,.doc,.docx" required>
                    <button type="submit"><i class="fas fa-save"></i> Enregistrer</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    function openModal(idReunion) {
        document.getElementById('modalReunionId').value = idReunion;
        document.getElementById('modalCompteRendu').style.display = 'block';
    }
    function closeModal() {
        document.getElementById('modalCompteRendu').style.display = 'none';
        document.getElementById('formCompteRendu').reset();
    }
    window.onclick = function(event) {
        const modal = document.getElementById('modalCompteRendu');
        if (event.target === modal) {
            closeModal();
        }
    }
    </script>
</body>
</html>