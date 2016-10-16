
<?php

    require_once("constantes.php");

    //******************* Calcul de l'histogramme
    function getColorStatistics($histogramElements, $colorChannel) {
        $colorStatistics = [];

        foreach ($histogramElements as $histogramElement) {
            $color = $histogramElement->getColorValue($colorChannel);
            $color = intval($color * 255);
            $count = $histogramElement->getColorCount();

            if (array_key_exists($color, $colorStatistics)) {
                $colorStatistics[$color] += $count;
            }
            else {
                $colorStatistics[$color] = $count;
            }
        }

        ksort($colorStatistics);
        
        return $colorStatistics;
    }

    function getColorStatisticsReduit($histogramElements, $colorChannel) {
        $colorStatistics = [0=>0,1=>0,2=>0,3=>0];

        foreach ($histogramElements as $histogramElement) {
            $color = $histogramElement->getColorValue($colorChannel);
            $color = intval($color * 255);
            $count = $histogramElement->getColorCount();

            $index = intval($color/64);
            $colorStatistics[$index] += $count;
        }

        //ksort($colorStatistics);
        
        return $colorStatistics;
    }
    
    function getImageHistogramReduit($imagePath) {

        $imagick = new \Imagick(realpath($imagePath));
        $histogramElements = $imagick->getImageHistogram();

        $histoGrammReduit =     $colorValues = [
            'red' => getColorStatisticsReduit($histogramElements, \Imagick::COLOR_RED),
            'lime' => getColorStatisticsReduit($histogramElements, \Imagick::COLOR_GREEN),
            'blue' => getColorStatisticsReduit($histogramElements, \Imagick::COLOR_BLUE),
        ];

        return($histoGrammReduit);
    }

    function recupererHistogramme($id){
        
        $f = fopen(PATH_FICHIER_HISTOGRAMMES,"r");
        if($f){
            while(!feof($f)){
                $ligne = trim(fgets($f));
                if(!empty($ligne)){
                    $donneesCourante = split(":",$ligne);
                    if($donneesCourante[0]==$id){
                        fclose($f);
                        return($donneesCourante[1]);
                    }
                }
            }
        }
        fclose($f);
        
        return("");
    }

    //******************* Ajout de l'histogramme
    function construireAjouterHistogramme($id,$imageAdresse){
        $histogramme = getImageHistogramReduit($imageAdresse);
        $retourHisto = ajouterHistogramme($id,$histogramme);
    }

    function ajouterHistogramme($id,$histogramme){
        $f = fopen(PATH_FICHIER_HISTOGRAMMES,"a");
        if($f){
            fwrite($f,construireLigneHistogramme($id,$histogramme));
            fclose($f);
            return TRUE;
        }
        return FALSE;
    }

    function construireLigneHistogramme($id,$histogramme){
        $histogrammeString = $id.":";
        foreach($histogramme["red"] as $element){
            $histogrammeString .= $element.";";
        }
        foreach($histogramme["lime"] as $element){
            $histogrammeString .= $element.";";
        }
        foreach($histogramme["blue"] as $element){
            $histogrammeString .= $element.";";
        }
        $histogrammeString = substr($histogrammeString,0,-1)."\n";
        return($histogrammeString);
    }

    function recupererTableauHistogramme(){
        $tableauHistogramme = array();
        
        $f = fopen(PATH_FICHIER_HISTOGRAMMES,"r");
        if($f){
            while(!feof($f)){
                $ligne = trim(fgets($f));
                if(!empty($ligne)){
                    $donneesCourante = split(":",$ligne);
                    $tableauHistogramme[$donneesCourante[0]] = split(";",$donneesCourante[1]);
                }
            }
        }
        fclose($f);
        
        return($tableauHistogramme);
    }

?>

