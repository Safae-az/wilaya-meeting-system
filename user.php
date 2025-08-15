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

//La requette sql 
$query = "
  SELECT 
    u.id_user,
    u.nom,
    u.prenom,
    u.email,
    u.role,
    s.nom_service
  FROM utilisateur u
  LEFT JOIN service s ON u.id_service = s.id_service
";
$utilisateurs = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Liste des utilisateurs</title>
  <link rel="stylesheet" href="usersshow.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
  <div class="container">
    <h1>Informations des utilisateurs</h1>

    <table class="table-users">
      <thead>
        <tr>
          <th>#</th>
          <th>Nom</th>
          <th>Email</th>
          <th>Rôle</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($utilisateurs as $index => $user): ?>
          <tr>
            <td><?= $index + 1 ?></td>
            <td><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['role']) ?></td>
            
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
