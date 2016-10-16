<?php
    // affichage des erreurs
	ini_set('display_errors', 1);

    // chargement des outils
    require_once("outils.php");
    
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

    /*** UPLOAD IMAGE ***/
    if(isset($_FILES['fileToUpload']['name'])){

        //***** Ajout de l'entree dans la base
        $ajoutEffectue = ajouterImage($_FILES['fileToUpload']['name'],$mysqli);
        if($ajoutEffectue[0]){
            $urlImage = getImageUrl($ajoutEffectue[1],$ajoutEffectue[2]);
            $urlThumbnail = getThumbnailUrl($ajoutEffectue[1]);
            echo "Upload reussi: <a href=\"".$urlImage."\">image</a> <a href=\"".$urlThumbnail."\">thumbnail</a>";
        }else{
            echo "Upload echoue";
        }
    }
    

    // Titre
    echo "<h1>Moteur de recherche d'images</h1><hr/>\n";

    // fomulaire d'upload
    echo "<h3>Upload d'image</h3>\n";
    afficherFormulaireUpload();

    // affichage des images de la table
    echo "<h3>Liste des images de la base de données</h3>\n";
    affichageImageTable($mysqli);

    // en-pied HTML
    echo fin(true);

?>
