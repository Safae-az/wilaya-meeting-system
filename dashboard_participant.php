<?php
session_start();
require_once 'db.php';

// Vérifie que l'utilisateur est bien connecté
if (!isset($_SESSION['id_user']) || !isset($_SESSION['role'])) {
    header('Location: partconnect.php');
    exit();
}

$id_user = $_SESSION['id_user'];
$prenom = $_SESSION['prenom'] ?? '';
$nom = $_SESSION['nom'] ?? '';
$role = $_SESSION['role'] ?? 'Participant';

$titreBienvenue = "Bienvenue " . htmlspecialchars($prenom . ' ' . $nom);

// Récupère les réunions du participant
$sql = "SELECT r.id_reunion, r.titre, r.date_heure, r.type, r.statut
        FROM reunion r
        JOIN participer p ON r.id_reunion = p.id_reunion
        WHERE p.id_user = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_user]);
$reunions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Compte rendus disponibles
$sql_cr = "SELECT r.titre, cr.date_validation, cr.contenu
           FROM compte_rendu cr
           JOIN reunion r ON cr.id_reunion = r.id_reunion
           JOIN participer p ON r.id_reunion = p.id_reunion
           WHERE p.id_user = ?";
$stmt = $pdo->prepare($sql_cr);
$stmt->execute([$id_user]);
$comptes_rendus = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Participant</title>
    <link rel="stylesheet" href="dashboardstyle.css">
</head>
<body>
    <div class="container">
        <nav>
            <ul style="list-style:none;">
                <li><a href="#" class="logo">
                    <img src="Logo_Wilaya.jpg" alt="Logo">
                    <span class="nav-item">Participant</span>
                </a></li>
                <li><a href="#" class="active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="nav-item">Tableau de bord</span>
                </a></li>
                <li><a href="logout.php" class="logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="nav-item">Déconnexion</span>
                </a></li>
            </ul>
        </nav>

        <section class="main">
            <div class="main-top">
                <h1><?= $titreBienvenue ?></h1>
                <i class="fas fa-user"></i>
            </div>

            <div class="cards">
                <div class="card">
                    <i class="fas fa-calendar-alt"></i>
                    <h3>Réunions à venir</h3>
                    <p><?= count($reunions) ?> réunions prévues</p>
                </div>
                <div class="card">
                    <i class="fas fa-file-alt"></i>
                    <h3>Comptes Rendus</h3>
                    <p><?= count($comptes_rendus) ?> disponibles</p>
                </div>
            </div>

            <section class="data-section">
                <div class="data-box">
                    <h2>Mes Réunions</h2>
                    <ul>
                        <?php foreach ($reunions as $r): ?>
                            <li><?= htmlspecialchars($r['titre']) ?> – <?= date('d/m/Y H:i', strtotime($r['date_heure'])) ?> – <?= $r['statut'] ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="data-box">
                    <h2>Mes Comptes Rendus</h2>
                    <ul>
                        <?php foreach ($comptes_rendus as $cr): ?>
                            <li>
                                <strong><?= htmlspecialchars($cr['titre']) ?></strong><br>
                                <em><?= $cr['date_validation'] ?></em><br>
                                <?= nl2br(htmlspecialchars($cr['contenu'])) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </section>
        </section>
    </div>
</body>
</html>
