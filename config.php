<?php
///////////////////// On essaie Connexion à la bdd avec PDO-->/////////////////////
ob_start(); //Enclenche la temporisation de sortie
session_start(); //Démarrer une session
date_default_timezone_set("Europe/Paris"); //Définit le décalage horaire par défaut de toutes les fonctions date/heure

try{
$db = new PDO('mysql:host=localhost;port=3308;dbname=squery; charset=utf8', 'root', 'root');
///////////////////////autre façon d'afficher les exceptions/////////////////////
//setAttribute => configure un attribute PDO
//(PDO::ATTR_=> rappors d'erreurs
//PDO::ERRMODE_EXCEPTION => émét une alerte E_WARGNING
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
////////////////////catch le warning et stocké dans $e//////////////
//Si on n'arrive pas a se connecter, on attrape des erreurs retournées
}catch(PDOException $e){
//////////////Affichage les messages d'erreurs///////////////////////
echo "La connexion a échoué :". $e -> getMessage();
}



 ?>
