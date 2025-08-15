<?php
include 'db.php';

$uploadDir = 'uploads/';
$msg = "";

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = $_POST['titre'];
    $file = $_FILES['fichier'];

    if ($file['error'] === 0) {
        $fileName = basename($file['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $stmt = $pdo->prepare("INSERT INTO compte_rendu (titre, fichier) VALUES (?, ?)");
            $stmt->execute([$titre, $fileName]);
            $msg = "Fichier téléversé avec succès.";
        } else {
            $msg = "Erreur lors du téléchargement du fichier.";
        }
    } else {
        $msg = "Fichier invalide.";
    }
}

// Récupération des fichiers
$files = $pdo->query("SELECT * FROM compte_rendu ORDER BY date_validation DESC")->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Compte Rendu Réunions</title>
  <link rel="stylesheet" href="compte_rendu.css">
</head>
<body>
  <div class="container">
    <h1>Gestion des Comptes Rendus</h1>

    <?php if ($msg): ?>
      <p style="color: green"><?= htmlspecialchars($msg) ?></p>
    <?php endif; ?>

    <div class="upload-section">
      <h2>Téléverser un nouveau compte rendu</h2>
      <form action="" method="post" enctype="multipart/form-data">
        <label for="titre">Titre :</label>
        <input type="text" id="titre" name="titre" placeholder="Ex: Réunion du 02/07/2025" required>

        <label for="fichier">Fichier (PDF, DOC, etc.) :</label>
        <input type="file" id="fichier" name="fichier" accept=".pdf,.doc,.docx" required>

        <button type="submit">Téléverser</button>
      </form>
    </div>

    <div class="liste-section">
      <h2>Comptes rendus précédents</h2>
      <ul class="liste-fichiers">
        <?php foreach ($files as $f): ?>
          <li><a href="uploads/<?= htmlspecialchars($f['fichier']) ?>" target="_blank">📄 <?= htmlspecialchars($f['titre']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</body>
</html>
