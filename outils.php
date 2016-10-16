<?php

    require_once("constantes.php");
    require_once("outilsHistogramme.php");

    function debut($titre) {
        $enTete = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta http-equiv="Content-Script-Type" content="text/javascript" />

  <link rel="stylesheet" type="text/css" href="style.css" />

  <title>'.$titre.'</title>
</head>

<body>';
        $enTete .= "\n\n";
        $enTete .= "<a href=\"index.php\">index</a><br/>\n";
        return($enTete);
    }

    function fin($afficherRetour){
        $enPied = "\n\n<div style=\"clear:both\" class=\"enPied\">\n";
        $enPied .= "\t<br /><br/><hr />\n";
        $enPied .= $afficherRetour ? "\t<a href=\"/~projet/index.php\">Retour au menu principal</a><br/>\n" : "";
        $enPied .= "\t<a href=\"/~projet/upload.php\">Uploader une image</a>\n";
        $enPied .= "</div>\n";
        $enPied .= "\n\n</body>\n</html>";
        return($enPied);
    }

    // affichage tableau recursif
    function affichageTableau($tableau, $niveauIndentation) {
        $indentInit = "";
        for($i = 0; $i < 3*$niveauIndentation; $i++){
            $indentInit = $indentInit."\t";
        }
        echo $indentInit."<table border=\"1px solid\" >\n";

        foreach($tableau as $cle => $valeur) {
            echo $indentInit."\t<tr>\n";
            echo $indentInit."\t\t<td>".$cle."</td>\n";

            if(is_array($valeur)){
                echo $indentInit."\t\t<td>\n";
                affichageTableau($valeur,$niveauIndentation+1);
                echo $indentInit."\t\t</td>\n";
            }else{
                echo $indentInit."\t\t<td>".$valeur."</td>\n";
            }
            echo $indentInit."\t</tr>\n";
        }

        echo $indentInit."</table>\n";
    }

    /* Connection mySQL */
    function connecter($serveur, $login,$mdp,$base) {
        $mysqli = new mysqli($serveur, $login,$mdp,$base);
        if($mysqli->connect_errno){
            die("Erreur de connection: ".$mysqli->connect_errno);
        }
        return($mysqli);
    }

    //************ Ajout d'image
    function ajouterImageTable($nomFichier,$extension,$mysqli){
        $nomTable = "images";
        $requete = "INSERT INTO ".$nomTable;
        $requete .= " VALUES(NULL,\"".$nomFichier."\",\"".$extension."\")";
        $resultat = $mysqli->query($requete);
        return($resultat);
    }

    function ajouterImage($nomFichier,$mysqli){

        $extensionImage = pathinfo($_FILES['fileToUpload']['name'], PATHINFO_EXTENSION);
        $ajoutEffectue = ajouterImageTable($_FILES['fileToUpload']['name'],$extensionImage,$mysqli);
        if(!$ajoutEffectue) {
            return(array(FALSE,"probleme ajout image dans base"));
        }

        //***** Recuperation de l'id de l'image dans la base
        $idImage = $mysqli->insert_id;
        $nouveauNomImage = "image".$idImage;
        $nouveauAdresseImage = getImagePath($idImage,$extensionImage);
        $nouveauUrlImage = getImageUrl($idImage,$extensionImage);

        //***** Ajout de l'image des fichiers temporaires vers le dossier des images
        $uploadReussi = move_uploaded_file($_FILES['fileToUpload']['tmp_name'],$nouveauAdresseImage);
        if(!$uploadReussi){
            return(array(FALSE,"Upload echoué"));
        }

        //***** Creation du thumbnail
        //$commandeThumbnail = "mogrify -format jpg -path ".PATH_DOSSIER_THUMBNAILS." -thumbnail 200x200 ".PATH_DOSSIER_IMAGES."/".$nouveauNomImage.".".$extensionImage;
        $commandeThumbnail = "convert -define jpeg:size=200x200 ".$nouveauAdresseImage." -thumbnail 200x200^ -gravity center -extent 200x200 ".getThumbnailPath($idImage);
        $retourExec = exec($commandeThumbnail);

        //******* Calcul et ajout de l'histogramm
        $retourHisto = construireAjouterHistogramme($idImage,$nouveauAdresseImage);

        return(array(TRUE,$idImage,$extensionImage));
    }

    function recupererImages($mysqli) {
        $nomTable = "images";
        $requete = "SELECT * FROM ".$nomTable;
        $donnees = array();
        $resultat = $mysqli->query($requete);
        while($ligne=$resultat->fetch_object()){
            $donnees[] = array("id"=>$ligne->id,"nomFichier"=>$ligne->nomFichier,"extension"=>$ligne->extension);
        }
        return $donnees;
    }

    function affichageImageTable($mysqli){
        // affichage des images de la table
        $donnees = recupererImages($mysqli);
        foreach($donnees as $image){
            echo "\n".getThumbnailClickable($image["id"],200);
        }
    }

    function getImageUrl($id,$extension){
        return(URL_DOSSIER_IMAGES."/image".$id.".".$extension);
    }

    function getImagePath($id,$extension){
        return(PATH_DOSSIER_IMAGES."/image".$id.".".$extension);
    }

    function getThumbnailUrl($id){
        return(URL_DOSSIER_THUMBNAILS."/image".$id.".".EXTENSION_THUMBNAIL);
    }

    function getThumbnailPath($id){
        return(PATH_DOSSIER_THUMBNAILS."/image".$id.".".EXTENSION_THUMBNAIL);
    }

    function afficherFormulaireUpload(){
        echo '<form action="upload.php" method="post" enctype="multipart/form-data">
    Select image to upload:
    <input type="file" name="fileToUpload" id="fileToUpload">
    <input type="submit" value="Upload Image" name="submit">
</form>'."\n";
    }

    function recupererInformationImage($id,$mysqli){
        $nomTable = "images";
        $requete = "SELECT * FROM ".$nomTable." WHERE id=".$id;
        $donnees = array();
        $resultat = $mysqli->query($requete);
        if($resultat){
            while($ligne=$resultat->fetch_object()){
                $donnees[] = array("id"=>$ligne->id,"nomFichier"=>$ligne->nomFichier,"extension"=>$ligne->extension);
            }
        }else{
            echo "PROBLEME ".$requete;
        }
        return $donnees[0];
    }

    function descriptionImage($id,$mysqli){
        $informationImage = recupererInformationImage($id,$mysqli);
        echo "\n<div class=\"infoImage\">";
        echo "\n<div class=\"infoImageImage\" style=\"float:left;margin-right:10px;\">";
        echo "\n\t<a href=\"".getImageUrl($informationImage["id"],$informationImage["extension"])."\" ><img src=\"".getImageUrl($informationImage["id"],$informationImage["extension"])."\" height=\"500px\"/></a>";
        echo "\n\t</div>";
        echo "\n<div class=\"infoImageText\">";
        echo "\n\tId: ".$id."<br />";
        echo "\n\tNom fichier: ".$informationImage["nomFichier"]."<br />";
        echo "\n\tExtension: ".$informationImage["extension"]."<br />";
        echo "\n\t<img src=\"bargraph.php?histogramme=".recupererHistogramme($id)."\"/><br />";
        echo "\n\t</div>";
        echo "\n</div>";
    }

    function getThumbnailClickable($id,$largeur){
        return("<a href=\"image.php?id=".$id."\"><img width=\"".$largeur."\" src=\"".getThumbnailUrl($id)."\" alt=\"thumbnail:image".$id."\"/></a>");
    }

    function afficherSeparateurInvisible(){
        echo "\n<div class=\"sperateur\" style=\"clear:both;\"></div>\n";
    }

    function afficherSeparateurVisible(){
        echo "\n<div class=\"sperateur\" style=\"clear:both;\"><hr/></div>\n";
    }

?>
