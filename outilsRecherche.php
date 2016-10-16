
<?php

    require_once("constantes.php");
    require_once("outilsHistogramme.php");
    require_once("outils.php");

    //******************* Calcul de distance
    function distanceEuclidienne($histogramme1,$histogramme2){
        $distance = 0;
        for($i = 0; $i < count($histogramme1); $i++){
            $distance += ($histogramme1[$i]-$histogramme2[$i])*($histogramme1[$i]-$histogramme2[$i]);
        }
        return sqrt($distance);
    }
    
    function distanceManhattan($histogramme1,$histogramme2){
        $distance = 0;
        for($i = 0; $i < count($histogramme1); $i++){
            $distance += abs($histogramme1[$i]-$histogramme2[$i]);
        }
        return $distance;
    }

    // Distance Quadratique: d^2 = (x-y)' A (x-y)
    function distanceQuadratique($x,$y){
        $n = 12;
        $m = 4;

        //********** DEBUT MATRICE A
        $matriceA = array();
        /*
        for($i=0;$i<$n;$i++){
            $matriceA[$i] = array();
            for($j=0;$j<$n;$j++){
                if($i==$j){
                    $matriceA[$i][$j] = 1;
                }else if(abs($i-$j)==1){
                    $matriceA[$i][$j] = 0.8;
                }else if(abs($i-$j)==2){
                    $matriceA[$i][$j] = 0.5;
                }else if(abs($i-$j)==3){
                    $matriceA[$i][$j] = 0.3;
                }else{
                    $matriceA[$i][$j] = 0;
                }
            }
        }
        */
        for($i=0;$i<$n;$i++){
            $matriceA[$i] = array();
            for($j=0;$j<$n;$j++){
                $matriceA[$i][$j] = 0;
            }
        }

        for($k=0;$k<3;$k++){
            for($i=$k*$m;$i<($k+1)*$m;$i++){
                for($j=$k*$m;$j<($k+1)*$m;$j++){
                    if($i==$j){
                        $matriceA[$i][$j] = 1;
                    }else if(abs($i-$j)==1){
                        $matriceA[$i][$j] = 0.8;
                    }else if(abs($i-$j)==2){
                        $matriceA[$i][$j] = 0.5;
                    }else if(abs($i-$j)==3){
                        $matriceA[$i][$j] = 0.3;
                    }else{
                        $matriceA[$i][$j] = 0;
                    }
                }
            }
        }

        //********** FIN MATRICE A

        $z = array();
        for($i=0;$i<$n;$i++){
            $z[$i] = $x[$i]-$y[$i];
        }
        
        $distance = 0;
        for($i=0;$i<$n;$i++){
            $sommeTmp = 0;
            for($j=0;$j<$n;$j++){
                $sommeTmp += $z[$j]*$matriceA[$i][$j];
            }
            $distance += $z[$i]*$sommeTmp;
        }
        return $distance;
    }

    function afficherDistance($idImage,$typeDistance){
        $tableauHistogramme = recupererTableauHistogramme();
        $tableauDistance = array();
        $histogrammeImageCourante = $tableauHistogramme[$idImage];
        unset($tableauHistogramme[$idImage]);

        if($typeDistance=="manhattan"){
            foreach($tableauHistogramme as $key => $value){
                $tableauDistance[$key] = distanceManhattan($histogrammeImageCourante,$value);
            }
        }else if($typeDistance=="quadratique"){
            foreach($tableauHistogramme as $key => $value){
                $tableauDistance[$key] = distanceQuadratique($histogrammeImageCourante,$value);
            }
        }else{
            // euclide
            foreach($tableauHistogramme as $key => $value){
                $tableauDistance[$key] = distanceEuclidienne($histogrammeImageCourante,$value);
            }
        }
        asort($tableauDistance);

        afficherTableauDistance($tableauDistance);
    }
    //*************** Affichage tableau distance
    /*
    function afficherTableauDistance($tableauDistance){
        echo "<table border=\"1px solid black\">\n";
        $i = 1;
        foreach($tableauDistance as $key => $value){
            echo "<tr>\n";
            echo "\t<td>".$i."</td>\n";
            echo "\t<td><img width=\"100px\" src=\"".getThumbnailUrl($key)."\" alt=\"thumbnail".$i."\" /></td>\n";
            echo "\t<td>".$value."</td>\n";
            echo "</tr>\n";
            $i++;
        }
        echo "</table>\n";
    }
    */

    function afficherTableauDistance($tableauDistance){
        $i = 1;
        foreach($tableauDistance as $key => $value){
            echo "<div style=\"float:left;border:1px solid black;padding:3px;margin-right:3px;\" class=\"blocResultatDistance\">\n";
            echo "\t".$i."<br/>\n";
            echo "\t".getThumbnailClickable($key,100)."<br/>\n";
            echo "\t".intval($value)."\n";
            echo "</div>\n";
            $i++;
        }
    }
?>

