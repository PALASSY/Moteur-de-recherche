<?php
    include("config.php");
    include("classes/SiteResultsProvider.php");
    include("classes/ImageResultsProvider.php");

  /////////////////Detecter si le term est présent dans URL, Si il y en a on récupère la valeur du term /////////////////
  if(isset($_GET['term'])){
     $term = $_GET['term'];
  }else{
    exit("Vous devez entrer un terme de recherche");
  }
  /////////////////Detecter si le type présent dans URL, Si il y en on affiche la valeur du type(images/sites) sinon on affiche une valeur par défaut(sites)/////////////////
  $type = isset($_GET['type'])?$_GET['type']:"sites";
  /////////////////Detecter si la page est présente dans URL,Si il y en on affiche la valeur de la page(1/2/3...) sinon afficher 1 par défaut/////////////////
  $page = isset($_GET['page']) ? $_GET['page'] : 1;
 ?>
 <!DOCTYPE html>
 <html lang="fr" dir="ltr">
   <head>
     <meta charset="utf-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <meta http-equiv="X-UA-Compatible" content="ie=edge">
     <title>Bienvenu sur votre moteur de recherche SQUERY</title>
     <!--  IMAGES -->
     <link rel="icon" href="#">
     <!--  ICONS -->
     <script src="https://kit.fontawesome.com/bb0dbb8b96.js" crossorigin="anonymous"></script>
     <!-- BOOTSTRAP-->
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
     <!--MATERIALIZE
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">-->
     <!--Import Google Icon Font-->
     <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
     <link rel="preconnect" href="https://fonts.gstatic.com">
     <link href="https://fonts.googleapis.com/css2?family=Luckiest+Guy&display=swap" rel="stylesheet">
     <!--CSS-->
     <link rel="stylesheet" href="assets/css/style.css">
     <!--JS-->
     <script src="assets/js/subscribe.js"></script>
   </head>
   <body>
     <main class="container-fluid searchPage">
         <header class="jumbotron">
           <div class="titleContainer">
             <h1><a href="index.php">SQUERY</a></h1>
             <div class="formContainer">
               <form class="" action="search.php" method="GET">
                 <!--ce champ gère le type(sites/images)-->
                 <input type="hidden" name="type" value="<?= $type ?>">
                 <input type="search" name="term"  placeholder="Mot-clé" class="form-control" value="<?= $term ?>"><br>
                 <button type="submit" class=""><img src="assets/images/search.png" alt="loupe de recheche"></button>
               </form>
             </div>
           </div>
           <div class="tabs">
             <ul id="tabList" class="">
               <!--si $type est (sites/images) on met la class active
                  càd quoi qu'il en soit le type(sites) est par défaut active-->
               <li class="<?= $type == 'sites' ? 'active' : ''  ?>"><a href="<?= 'search.php?term='.$term.'&type=sites' ?>">Sites</a></li>
               <li class="<?= $type == 'images' ? 'active' : ''  ?>"><a href="<?= 'search.php?term='.$term.'&type=images' ?>">Images</a></li>
             </ul>
           </div>
         </header>

         <section class="resultat">
           <!--resultats de la recherche-->
           <?php
              //Faire une condition si le type est (site) ou (images)
            if($type == 'sites'){
              $resultProvider = new SiteResultsProvider($db);
              //La limite de nombre de lien à afficher par page
              $fromLimit = 10;
            }else{
              $resultProvider = new ImageResultsProvider($db);
              //La limite de nombre de lien à afficher par page
              $fromLimit = 30;
            }
              //////////////////////////Afficher la totalité(nombre) de term trouvé dans site(BDD)//////////////////////
              $numResult = $resultProvider->getNumResults($term);
              $textNumResult = ($numResult <= 1) ? "résultat" : "résultats";
              echo "<h4 class='nbrResult'>Environ $numResult $textNumResult</h4>";
              //////////////////////////Affichage détails(id/url/title/description/keywords/clicks) récupérés dans site(BDD)/////////////////////////
              //1er param c'est la page courante
              //2em param c'est le nombre de limite accodé(10)
              //3em param c'est les termes de recherche
              echo $resultProvider->getNumResultsHtml($page,$fromLimit,$term);
            ?>

         </section>
         <section class="pagination">
           <div class="pageBtn">
             <div class="pageNumberContainer">
                <img src="assets/images/startLogo.png" alt="Lettres SQ">
             </div>
              <?php
                ///////////////////////////////pageInation calcul//////////////////
                //La limite de nombre de page ou de (U) à afficher (par page ou dans la page ou dans l'image)
                $pageToShow = 10;
                //Pour trouvé la totalité de nombre de page(page1/page2/page3...)
                //il faut: la totalité(nombre) de term trouvé dans site(BDD) divisé par la limite de nombre de lien à afficher par page(paquet de 1O)
                //Forcement il y aura de % alors on utiise ceil() pour arrondir la fraction à la valeur supérieure
                $numPage = ceil($numResult/$fromLimit);
                /////////////////////Affichage de <div> ou le (U) se fait en plusieurs fois selon le nombre(vrai) de $pagesLeft/////////////
                //L'autre page (UUU...) : c'est la plus petite valeur entre(10 et la totalité de nombre de page) => (10/9/8...) mais pas au-dela de 10
                $pagesLeft = min($pageToShow,$numPage);
                //La page courante par défaut(1)
                $currentPage = $page - floor($pageToShow/2);
                /////////////////////redefinir la page courante par défaut(1) ////////////////////
                // si elle est inférieur à 1 affiché 1
                if($currentPage < 1){$currentPage = 1;}
                // si elle et l'autre page(UUU..) sont supérieur à la totalité de nombre de page(page1/page2/page3...) + 1
                  if($currentPage + $pagesLeft > $numPage + 1){
                    $currentPage = $numPage + 1 - $pagesLeft;
                  }
                  //echo $currentPage;
                ///////////////////Tant que l' autre page est # de 0 (càd vrai), soit elle est la page active soit elle ne l'est pas/////////////
                //Remarque:  pourque $pagesLeft sera 0 (càd faux) pourqu'il s'arrête, il faut le décrémenté
                while($pagesLeft != 0){
                    //////////////Si la page courante par défaut(1) a le même chiffre que valeur de la page dans URL(page=1/2/3...)par défaut(1)///////////////
                    // elle est la page active
                    //On affiche l'image active
                    if($currentPage == $page){
                      echo "<div class='pageNumberContainer'>
                                <a class='pageNumber' href='serach.php?term=$term&type=$type&page=$currentPage'>
                                    <div class='pageNumberContainer  U X'>
                                        <img src='assets/images/secondU.png' alt='Lettre U active'>
                                        <span class='pageNumber' href=''>$currentPage</span>
                                    </div>
                                 </a>
                            </div>";

                    }else{
                      //C'est n'est pas la page active
                      //On affiche l'image non active
                      echo "<div class='pageNumberContainer'>
                              <a class='pageNumber' href='search.php?term=$term&type=$type&page=$currentPage'>
                                <div class='pageNumberContainer U '>
                                  <img src='assets/images/firstU.png' alt='Lettre U'>
                                  <span class='pageNumber' href=''>$currentPage</span>
                                </div>
                              </a>
                            </div>";
                    }
                    //On incrémente(quand l'autres page est décrémenté)
                    $currentPage++;
                    //On décrémente l'autre page(quand la page courante par défaut est incrémentée)
                    $pagesLeft--;
                }
               ?>
             <div class="pageNumberContainer">
                <img src="assets/images/endLogo.png" alt="Lettres ERY">
             </div>
           </div>
         </section>
       </main>


       <!--jquery-->
       <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
       <script type="text/javascript" src="assets/js/script.js"></script>
   </body>
   </html>
