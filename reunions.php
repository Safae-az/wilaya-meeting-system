<?php
$host = 'localhost';
$dbname = 'reunion_db';
$user = 'root'; // change selon ta config
$pass = '';     // mot de passe de ton MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Requête pour récupérer les réunions d'aujourd'hui avec participants et ordre du jour
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
  <link rel="stylesheet" href="reunions_du_jour.css"> <!-- Ton fichier CSS -->
  <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
</head>
<body>
  <div class="container">
    <h1>Réunions prévues aujourd'hui</h1>

    <?php if (count($reunions) > 0): ?>
      <?php foreach ($reunions as $reunion): ?>
        <div class="reunion-card">
          <h2><?= htmlspecialchars($reunion['titre']) ?></h2>
          <p><strong>Heure :</strong>
            <?= date('H:i', strtotime($reunion['date_heure'])) ?> -
            <?= date('H:i', strtotime($reunion['date_heure'] . ' +1 hour')) ?>
          </p>
          <p><strong>Lieu :</strong> <?= htmlspecialchars($reunion['libelle_salle']) ?></p>

          <?php if (!empty($reunion['ordre_du_jour'])): ?>
            <p><strong>Ordre du jour :</strong> <?= htmlspecialchars($reunion['ordre_du_jour']) ?></p>
          <?php endif; ?>

          <?php if (!empty($reunion['participants'])): ?>
            <p><strong>Participants :</strong> <?= htmlspecialchars($reunion['participants']) ?></p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p>Aucune réunion prévue aujourd’hui.</p>
    <?php endif; ?>
  </div>
</body>
</html>
