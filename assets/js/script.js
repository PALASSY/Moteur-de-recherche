$(document).ready(function(){
  $(".resultTitle").on("click",function(){
    console.log('lien clické');
    //On va chercher un ID(site)récupérer dans(siteResultProvider)
      var id = $(this).attr("data-linkId");
    //On va chercher l'url(href)(siteResultProvider)
      var url = $(this).attr("href");
      console.log(url);
      console.log(id);
    //test, à faire une vérif
      if(!id){
        alert('ID du site est introuvable');
      }
    //incrémenté dans la BDD
      increaseClicks(id,url);
      //return false; //permet de ne pas suivre le lien
  });
});

  /********************function(function(incrémenté id dans BDD))**********************/
  //1er param c'est l'id (site)
  //2èm param c'est l'url(site)
  function increaseClicks(linkId,url){
    //On utilise AJAX pour n'est pas recharger la page
    //Passer par la methode post pour charger un fichier AJAX(incrementClicks.php) et on lance(.done)la localisation du lien
    //1er param c'est le lien vers ajax
    //2èm param c'est l'objet, l'Id du site(rélié les 2 avec :)
    $.post("ajax/incrementClicks.php",{linkId:linkId})
    //1er param de la function(.done)
    .done(function(result){
        if(result != ""){
          alert(result);
          //retourne la methode
          return;
        }
          alert(result);
        //Pour suivre le lien
        window.location.href=url
    });
  }
