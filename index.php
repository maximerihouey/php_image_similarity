<?php
    // affichage des erreurs
	ini_set('display_errors', 1);

    // chargement des outils
    require_once("outils.php");
    
    // en-tete HTML
    echo debut("Moteur de recherche d'images");

    // Titre
    echo "<h1>Moteur de recherche d'images</h1><hr/>\n";

    // Base de donnees
    $nomServeur = "localhost";
    $nomUtilisateur = "projet";
    $mdpUtilisateur = "projetmniprojet";
    $nomBase = "projet";

    // connection
    $mysqli = connecter($nomServeur,$nomUtilisateur,$mdpUtilisateur,$nomBase);

    // affichage des images de la table
    echo "<h3>Liste des images de la base de données</h3>\n";
    affichageImageTable($mysqli);

    // en-pied HTML
    echo fin(false);
?>
