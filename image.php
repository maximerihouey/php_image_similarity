<?php
    // affichage des erreurs
	ini_set('display_errors', 1);

    // chargement des outils
    require_once("constantes.php");
    require_once("outils.php");
    require_once("outilsRecherche.php");
    
    // en-tete HTML
    echo debut("Moteur de recherche d'images");

    /***************************/
    /* SCRIPT */
    /***************************/

    // Base de donnees
    $nomServeur = "localhost";
    $nomUtilisateur = "projet";
    $mdpUtilisateur = "projetmniprojet";
    $nomBase = "projet";

    // connection
    $mysqli = connecter($nomServeur,$nomUtilisateur,$mdpUtilisateur,$nomBase);

    // Id de l'image
    $idImage = null;
    if(isset($_GET["id"])){
        $idImage = $_GET["id"];
    }

    // Titre
    echo "<h1>Image</h1><hr/>\n";

    // description de l'image
    descriptionImage($idImage,$mysqli);
    afficherSeparateurVisible();

    // affichage distance euclidienne
    echo "<h3>Distance Euclidienne</h3>\n";
    afficherDistance($idImage,"euclide");
    afficherSeparateurInvisible();

    echo "<h3>Distance Manhattan</h3>\n";
    afficherDistance($idImage,"manhattan");
    afficherSeparateurInvisible();

    echo "<h3>Distance Quadratique</h3>\n";
    afficherDistance($idImage,"quadratique");
    afficherSeparateurInvisible();

    // en-pied HTML
    echo fin(true);

?>
