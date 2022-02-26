
<?php

class ImageResultsProvider{
  //Tout ce qui est privé peuvent être rajouter un $ devant pour indiquer que c'est privé
  private $_db;

  public function __construct($db){
    $this->_db = $db;
  }


  /*********************function(faire une requête pour récupérer la totalité de term trouvé dans site(BDD))*********************/
  //param c'est le mot clé
  //On va compter tous les éléments dans la table site
  //Quand il trouve un mot qui ressemble au term insérer dans le moteur de recherche
  //ou dans le champs url/keywords/descriptions
  public function getNumResults($term){
    $query = $this->_db->prepare("SELECT COUNT(*) AS total
                                 FROM images WHERE (title LIKE :term
                                 OR alt LIKE :term)
                                 AND broken = 0");
  //On va améliorer :term
  //On accepte un ou des mots avant et après :term pour la recherche
  $searchTerm = "%".$term."%";
  //On relier le param à un nouvel attribut
  $query->bindParam(":term",$searchTerm);
  $query->execute();
  //Mettre les données dans un tableau associatif
  $row = $query->fetch(PDO::FETCH_ASSOC);
  //On return tout ce qui est compter dans la table sites(BDD)
  return $row["total"];
  }

  /*********************function(Affichage toutes les détails(id/url/title/description/keywords/clicks) récupérés dans site(BDD))*********************/
  //1er param c'est la page courante
  //2em param c'est le nombre de limite accodé(10)
  //3em param c'est les termes de recherche
  public function getNumResultsHtml($page,$fromLimit,$term){
    ///////////La pagInation: c'est le nombre des numéros de page à afficher page par page///////////
    //Explication:  page actuelle  => (1-1)*10 = 0    =>  page1  = 0 à 10
    //              deuxième page  => (2-1)*10 = 10   =>  page2  = 11 à 20
    //              troisième page => (3-1)*10 = 30   =>  page3  = 21 à 30...
    //Donc (page actuelle - 1)=>0/11/21...multiplié par le nombre de limite accodé(10)
    $pageSize = ($page-1) * $fromLimit;
    //Faire une requête
    $query = $this->_db->prepare("SELECT * FROM images
                                  WHERE title LIKE :term
                                  OR siteUrl LIKE :term
                                  OR imageUrl LIKE :term
                                  OR alt LIKE :term
                                  ORDER BY clicks DESC
                                  LIMIT :pageSize,:fromLimit");
    //On va améliorer :term
    //On accepte un ou des mots avant et après :term pour la recherche
    $searchTerm = "%".$term."%";
    //Relie les paramètres aux attributs
    $query->bindParam(":term",$searchTerm);
    //Préciser qu'on voudrait des int
    $query->bindParam(":pageSize",$pageSize,PDO::PARAM_INT);
    $query->bindParam(":fromLimit",$fromLimit,PDO::PARAM_INT);
    $query->execute();
    $resultHtml = "<div class='siteResults'>";
    while($row=$query->fetch(PDO::FETCH_ASSOC)){
      $id = $row['id'];
      $title = $row['title'];
      $siteUrl = $row['siteUrl'];
      $imageUrl = $row['imageUrl'];
      $alt = $row['alt'];
      //////Application de limitation de nombre d'affichage de caractère sur le (titre/description)////////:
      $siteUrl = $this->trimField($siteUrl,40);
      $alt = $this->trimField($alt,70);
      //Affichage des détails
      $resultHtml .= "<div class='resultContainerImage'>
                        <a href='$imageUrl' class='imageContent'><img src='$imageUrl' alt='$alt' width='330' ></a>
                        <p class='alt'><a href='$imageUrl'>$alt</a></p>
                        <p class='imageLink'><a href='$siteUrl' data-linkId='$id' class='s'>$siteUrl</a></p>
                      </div>";
    }
    $resultHtml .= "</div>";
    return $resultHtml;
  }

  /***************************functionfunction((Limitation des nombres d'affichage de caractère))***************************/
  //1er param c'est le caractère
  //2ème param c'est le nombre de limite
  private function trimField($string,$characterLimit){
    //Si la longeur de caractère est supérieur au nombre de limite
    $dots = strlen($string) > $characterLimit ? "..." : "";
    //substr — Retourne un segment de chaîne
    //Affichage de caractère de 0 jusqu'à la limite imposée
    return substr($string,0,$characterLimit).$dots;
  }

}
 ?>
