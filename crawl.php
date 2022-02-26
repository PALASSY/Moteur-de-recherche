<?php
include("classes/DomDocumentParser.php");
include("config.php");

//////////////////////////////tableau des doublons du lien convertit//////////////////////////
//Déclarer les tableau pour qu'ils existent et non pas 0,pour que cela ne génère pas d'erreur
$alreadycrawled = array();
$crawling = array();
$alreadyFoundImages = array();


/*******************************function(function(Eviter le doubleau dans l'insertion des liens dans la BDD))*************************/
//param c'est le lien
function linkExists($url){
  global $db;
  //////////////////////////Vérifier si l'URL est dans la BDD///////////////////
  $query = $db->prepare("SELECT * FROM site WHERE url=:url");
  $query->bindParam(':url',$url);
  $query->execute();
  return $query->rowCount() != 0;
}
/*******************************function(function(Insertion des données dans la BDD))*************************/
//1er param c'est le lien
//2èm param c'est le titre obtenu dans <title>
//3èm param c'est la description obtenu dans <meta>
//4èm param c'est le keywords obtenu dans <meta>
function insertLink($url,$title,$description,$keywords){
  //Importer la connection PDO
  global $db;
  $query = $db->prepare("INSERT INTO site(url,title,description,keywords) VALUES(:url,:title,:description,:keywords)");
  $query->bindParam(":url",$url);
  $query->bindParam(":title", $title);
  $query->bindParam(":description", $description);
  $query->bindParam(":keywords", $keywords);
  return $query->execute();
}
/*******************************function(function(Insertion des images dans la BDD))*************************///1er param c'est le lien
//1er param c'est le lien
//2èm param c'est le lien de l'image obtenu dans <img>
//3èm param c'est alt obtenu dans <img>
//4èm param c'est le titre obtenu dans <img>
function insertLinkImages($url,$src,$alt,$title){
  //Importer la connection PDO
  global $db;
  $query = $db->prepare("INSERT INTO images(siteUrl,imageUrl,alt,title) VALUES(:siteUrl,:imageUrl,:alt,:title)");
  $query->bindParam(":siteUrl", $url);
  $query->bindParam(":imageUrl",$src);
  $query->bindParam(":alt", $alt);
  $query->bindParam(":title", $title);
  return $query->execute();
}

/*************************function(function(Création de lien à partir les données extraites dans le site parent))*************************/
//1er param c'est la source (lien obtenu à partir des données extraites dans le site parent)
//2èm param c'est le site(parent)
function createLinks($src, $url){
  //Vérification
  //echo "La source est: $src"."</br>";
  //echo "Le site parent est: $url"."</br></br>";
  /////////////////////convertir un chemin relatif à un lien absoulu//////////////////
  //Récupération de l'attribut(scheme)
  //parse_url — Analyse une URL et retourne ses composants
  //param c'est le site parent->récupère un de ses composant(scheme)
  //resutlat: http/https
  $scheme = parse_url($url)["scheme"];
  //Recupération de nom de domaine dans l'attribut(host)
  //param c'est le site parent->récupère un de ses composant(host)
  //resutlat: www.monsite.fr
  $host = parse_url($url)["host"];
  ////////////////////////////Redefinir la source//////////////////////
  //Vérifier si les 2 permiers caracères sont des (//)
  //On rajoute http + : + lien
  if(substr($src,0,2) == "//"){
    $src = $scheme.":".$src;
  }
  //Vérifier si le permier caracère est (/)
  //On rajoute http + :// + nom de domaine + lien
  else if(substr($src,0,1) == "/"){
    $src = $scheme."://".$host.$src;
  }
  //Vérifier si le dossier est monté d'un cran(ressort dans le dossier)
  //On rajoute http + :// + le nom de domaine + dirname(Renvoie le chemin du dossier parent) du composant(path) + / on met ce / à la fin parceque le dossier parent n'a pas de / à la fin
  else if(substr($src,0,2) == "./"){
    $src = $scheme."://".$host.dirname(parse_url($url)["path"]).substr($src,1);
  }
  //Vérifier si le dossier est monté d'un cran(ressort dans le dossier)
  //On rajoute http + :// + le nom de domaine + / + lien
  else if(substr($src,0,3) == "../"){
    $src = $scheme."://".$host."/".$src;
  }
  //Vérifier si c'est different de http ou https
  //On rajoute http + :// + le nom de domaine + / + lien
  else if(substr($src,0,5) !== "https" && substr($src,0,4) !== "http"){
    $src = $scheme."://".$host."/".$src;
  }

  return $src;
}


/*************************function(function(Afficher tous les détails(titres/meta/images) obtenus à partir d'un site web))*************************/
function getDetails($url){
  ////////////////////Déclarer en SUPER GLOBAL l'array() de doubon pour pouvoir les utilisé/////////////////////
  //Remarque, on indique juste le nom mais plus la peine d'apporter précison que c'est un tableau
  global $alreadyFoundImages;
  ////////////////////Lancer la class pour récupérer toutes les données d'une page web/////////////////////
  $parser = new DomDocumentParser($url);
  //////////////////////Récupèration les titres////////////////////
  $titleArray = $parser->getTitleTags();
  ///////////////////////Si dans le tablau il n'y a aucun titre ou si 1er titre est vide(rien)////////////////////////
  //Si l'un des 2 cas est vrai ou les 2 cas sont vrais, on retourne le tableau
  if(sizeof($titleArray) == 0 || $titleArray->item(0) == NULL){
    return;
  }
  /////////////////////item(0) => On veut récupérer le premier élément du tableau(nœud)///////////////
  //nodeValue(attribut de DOMDocument) => On met une valeur à ce nœud au lieu de la valeur soit NULL
  $title = $titleArray->item(0)->nodeValue;
  ////////////////////////Suppression des sauts de lignes(filtrage)///////////////////
  $title = str_replace("\n","",$title);
  ////////////////////////Si il n'y a pas de titre, que faire?//////////////////
  //Ignorer le lien et retourne le titre
  if($title == ""){
    return;
  }
  //echo "Url: $url"."<br>";
  //echo "Titre: $title"."<br><br>";
  ///////////////////////Description et meta/////////////////
  $description = "";
  $keywords = "";
  ///////////////////////Afficher tous les meta obtenues à partir d'un site web///////////////////////
  $metaArray = $parser->getMetaTags();
  foreach ($metaArray as $meta) {
    // Petite precision : ici il y 2 différents meta dans le site, il y a:(description, keyword)
    //On dit : si l'attribut de $meta est description on insère dans le tableau le contenu de ce $meta
    if($meta->getAttribute("name") == "description"){
        $description = $meta->getAttribute('content');
    }
    if($meta->getAttribute("name") == "keywords"){
        $keywords = $meta->getAttribute('content');
    }
  }
  ///////////////////////Suppression des sauts des lignes dans le tableau $description/$keywords/////////////////
  $description = str_replace('\n',"",$description);
  $keywords = str_replace('\n',"",$keywords);
  //////////////////////////////////Vérification de doubleau dans l'insertion des liens dans la BDD////////////////////////
  //Si l'URL existse dans BDD on affiche un message wrong
  if(linkExists($url)){
    echo "$url est déjà dans la BDD <br>";
  }
  ////////////////////////Insérer les liens/titres/descriptions/mots-clés dans la BDD et afficher un message success////////////////
  //1er param c'est le lien
  //2èm param c'est le titre obtenu dans <title>
  //3èm param c'est la description obtenu dans <meta>
  //4èm param c'est le keywords obtenu dans <meta>
  else if(insertLink($url,$title,$description,$keywords)){
    echo "success,lien insérer dans BDD";
    //Si ni l'un ni l'autre on affiche un message wrong2
  }else{
    echo "Erreur lors de l'insertion du lien dans BDD";
  }
  //echo "Url: $url<br>Titre: $title <br> Description: $description <br> Mot-clés: $keywords<br><br>";
  ////////////////////////Récupération des images///////////////////////////
  $imageArray = $parser->getImageTags();
  foreach ($imageArray as $image) {
    $src = $image->getAttribute("src");
    $alt = $image->getAttribute("alt");
    $title = $image->getAttribute("title");
    //Si  le titre et le alt sont manquants, on les zappe
    if(!$title && !$alt){
      continue;
    }
    //Si le src est un chemin est relatif, on crée un chémin absolu
    //1er param c'est la source (lien obtenu à partir des données extraites dans le site parent)
    //2èm param c'est le site(parent)
    $src = createLinks($src,$url);
    //Vérifier qu'il n'y a de doublon (images)
    //Vérifier si image n'existe pas dans le tableau
    if(!in_array($src,$alreadyFoundImages)){
      //On ajoute dans le tableau l'image
      $alreadyFoundImages[] = $src;
      /////////////////////On insère les images dans la BDD//////////////////
      //1er param c'est le lien
      //2èm param c'est le lien de l'image obtenu dans <img>
      //3èm param c'est alt obtenu dans <img>
      //4èm param c'est le titre obtenu dans <img>
      insertLinkImages($url,$src,$alt,$title);
    }

  }
}


/////////////////////////Afficher toutes les données obtenues à partir d'un site web///////////////////
//param c'est le site(parent)
function followLinks($url){
  ////////////////////Déclarer en SUPER GLOBAL les 2 array() de doubons pour pouvoir les utilisé/////////////////////
  //Remarque, on indique juste le nom mais plus la peine d'apporter précison que c'est un tableau
  global $alreadycrawled;
  global $crawling;
  ////////////////////Lancer la class pour récupérer toutes les données d'une page web/////////////////////
  $parser = new DomDocumentParser($url);
  //////////////////////Récupèration tous les liens <a>////////////////////
  $linkList = $parser->getLinks();
  //On va extraire chaque lien <a>
  foreach ($linkList as $link) {
    /////////////////////Plus précisément, recupération l'attribut(href) de chaque lien<a>////////////////////
    $href = $link->getAttribute('href');
    ///////////////////Faire un filtrage des liens////////////////////////
    //Supprimer les lignes ne comportant que des #
    //strpos — Cherche la position de la première occurrence dans une chaîne
    //Si on trouve # on le zappe
    if(strpos($href, "#") !== false){
      //L'instruction continue est utilisée dans une boucle afin d'éluder les instructions de l'itération courante et de continuer l'exécution à la condition de l'évaluation et donc, de commencer la prochaine itération.
      continue;
    //Supprimer les lignes comportants des JS
    //substr — Retourne un segment de chaîne
    //sinon si on trouve une chaîne qui mesure de 0 jusqu'à 11 caractères dont la chaîne est: (javascript:) on le zappe
    }else if(substr($href,0,11) == "javascript:"){
    //L'instruction continue est utilisée dans une boucle afin d'éluder les instructions de l'itération courante et de continuer l'exécution à la condition de l'évaluation et donc, de commencer la prochaine itération.
      continue;
    //Supprimer les chiffres
    //1er param c'est le masque à chercher
    //2em param c'est le remplacement(vide)
    //3em param c'est la chaîne à chercher et à remplacer
    }else if(preg_replace('/[^0-9]/', '', $href)){
      //L'instruction continue est utilisée dans une boucle afin d'éluder les instructions de l'itération courante et de continuer l'exécution à la condition de l'évaluation et donc, de commencer la prochaine itération.
      continue;
    }
    //echo $href . '<br>';
    ///////////////////////Création de lien à partir les données extraites dans le site parent//////////////////
    //1er param c'est la source (lien obtenu à partir des données extraites dans le site parent)
    //2èm param c'est le site(parent)
    $href = createLinks($href,$url);
    //echo $href."<br>";
    ///////////////////////Vérification si l'URL n'a pas été visité //////////////////
    //Vérifier si $href n'existe pas dans le tableau
    if(!in_array($href,$alreadycrawled)){
      //On ajoute dans le tableau(déjà visité/visité) le lien
      $alreadycrawled[] = $href;
      $crawling[] = $href;
      //Afficher tous les détails(titre/meta/images) obtenus à partir d'un site web
      getDetails($href);
    }
    //On passe à la ligne suivante
    //array_shift — Dépile un élément au début d'un tableau
    array_shift($crawling);
    foreach ($crawling as $site) {
      //Afficher toutes les données obtenues à partir d'un site web sans les doublons
      followLinks($site);
    }
  }
}

///////////////Les sites (parent)/////////////////
//$startUrl = "https://total.direct-energie.com";
//$startUrl = "https://www.westernunion.com";
//$startUrl = "https://www.wikipedia.com";
//$startUrl = "https://twitter.com";
//$startUrl = "https://www.bootstrapcdn.com";
//$startUrl = "https://www.google.com";
//$startUrl = "https://fr.wikipedia.org";
//$startUrl = "https://www.tsenako-tsenanaka.com";
//$startUrl = "https://www.pagesjaunes.fr";
//$startUrl = "https://www.pole-emploi.fr";
//$startUrl = "https://www.futurdigital.fr";
//$startUrl = "https://www.misterfly.com";
//$startUrl = "https://www.airbnb.fr";
$startUrl = "https://www.airbnb.fr/luxury";
//////////////////executer la methode//////////////
followLinks($startUrl);
 ?>
