<?php
class DomDocumentParser {

  //param c'est URL
  public function __construct($url){
    //Récupération des données
    $options = array('http' => array(
                                      'method' => 'GET',
                                      'header' => 'User-Agent: SqueryBot/1.0\n'
                                     )
                     );
    //Crée et retourne un contexte de flux, avec les paramètres fournis par options.
    $context = stream_context_create($options);
    ///////////////////////Chargement du document HTML:///////////////////////
    //Représente un document HTML ou XML entier ; ce sera la racine de l'arbre document.
    $this->_doc = new DOMDocument();
    ////////////////////////On applique la commande ici pour récupérer toutes les données d'une page web////////////////////////
    //DOMDocument :: loadHTML - Charge du HTML à partir d'une chaîne
    //file_get_contents - Lit le fichier entier dans une chaîne
    //1er param c'est l'URL
    //2èm param c'est false
    //3èm param c'est le context de flux
    //@ faire disparaître les warning(ce ne sont pas des erreurs mais c'est une info de php)
    @$this->_doc->loadHTML(file_get_contents($url,false,$context));
  }

  /*************************function(function(Récupération des liens de la page web avec un getter))*************************/
  public function getLinks(){
    //Recherche toutes les balises <a>
    return $this->_doc->getElementsByTagName("a");
  }

  /*************************function(function(Récupération des titres de la page web avec un getter))*************************/
  public function getTitleTags(){
    //Recherche toutes les balises <h1/h2/h3/h4/h5/h6>
    return $this->_doc->getElementsByTagName("title");
  }

  /*************************function(function(Récupération des <meta>(description/keywords) de la page web avec un getter))*************************/
  public function getMetaTags(){
    //Recherche toutes les meta
    return $this->_doc->getElementsByTagName("meta");
  }

  /*************************function(function(Récupération des <img>(src/alt/title) de la page web avec un getter))*************************/
  public function getImageTags(){
    //Recherche toutes les images(src/alt/title)
    return $this->_doc->getElementsByTagName("img");
  }







}

 ?>
