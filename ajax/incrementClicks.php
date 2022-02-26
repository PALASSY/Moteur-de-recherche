<?php
  include("../config.php");


  //Faire une requête et incrémente le clicks de 1
  //On utilise $_POST parce que c'est ce qui a été stipuler dans JS
  if(isset($_POST['linkId'])){
    $query = $db->prepare("UPDATE site SET clicks=clicks+1 WHERE id=:id");
    $query->bindParam(":id",$_POST['linkId']);
    $query->execute();
  }else{
    echo 'Aucun lien correspondant';
  }


 ?>
