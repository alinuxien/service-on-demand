<?php
// Affiche le nom de la machine qui execute le script
echo gethostname();

// Affiche l'ip du serveur 
echo getenv("SERVER_ADDR");

// Affiche l'ip du visiteur
echo getenv("REMOTE_ADDR");

// Récupération des informations de connexion depuis les variables d'environnement
$mysql_server = getenv("MYSQL_SERVER");
$mysql_user = getenv("MYSQL_USER");
$mysql_password = getenv("MYSQL_PASSWORD");

// Création de la connexion
$conn = new mysqli($mysql_server, $mysql_user, $mysql_password);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Erreur de Connexion a la BDD: " . $conn->connect_error);
}

// Affichage du résultat de la connexion et des infos sur le serveur de BDD
echo "Connexion reussie a la BDD";
echo "Nom du serveur de BDD : ".$mysql_server;
echo "Version du serveur de BDD :".mysqli_get_server_info($conn);
?>

