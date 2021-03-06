<?php 

$response= array();

if(isset($_POST['search'])&&isset($_POST['admin_id'])){


require_once 'db_connect.php';

$search=$_POST['search'];
$admin_id=$_POST['admin_id'];
$status_open=0;
$status_have_to_accept=3;
$status_unfriended=1;
$status_friended=2;

 $check= $db->prepare("SELECT * FROM friends WHERE ((user_a,user_b)= (:admin_id,:userb_id) OR (user_a,user_b)= (:userb1_id,:admin1_id)) AND status=:status");

if($search==""){
      if($result= $db->prepare("SELECT * FROM user WHERE user_id NOT LIKE :admin_id")){
        $db->beginTransaction();
        $result->bindParam(':admin_id', $admin_id);
        $result->execute();   
      }else{
        $response["status"]=400;
        $response["message"]="Oops. Versuchen Sie es später noch einmal.";
        echo json_encode($response);
      }
}else{
      if($result= $db->prepare("SELECT * FROM user WHERE (name= :search OR prename=:search1 OR email=:search2) AND user_id NOT LIKE :admin_id")){
        $db->beginTransaction();
        $result->bindParam(':admin_id', $admin_id);
        $result->bindParam(':search', $search);
        $result->bindParam(':search1', $search);
        $result->bindParam(':search2', $search);
        $result->execute();  
      }else{
        $response["status"]=400;
        $response["message"]="Oops. Versuchen Sie es später noch einmal.";
        echo json_encode($response);
      }
}

if(($result->rowCount())>0){
   $response["users"] = array();

   foreach ($result as $row) {
    $user = array();
    $user["user_id"] = $row["user_id"];
    $userb_id=$row["user_id"];
    $user["name"] = $row["name"];
    $user["prename"] = $row["prename"];
    $user["email"] = $row["email"];

    if($check){
      //Check if status Open
      $check->bindParam(':admin_id', $admin_id);
      $check->bindParam(':userb_id', $userb_id);
      $check->bindParam(':admin1_id', $admin_id);
      $check->bindParam(':userb1_id', $userb_id);
      $check->bindParam(':status', $status_open);
      $check->execute();  

      if(($check->rowCount())>0){
        if($checkIfYouHaveToAccept= $db->prepare("SELECT * FROM friends WHERE (user_a,user_b)= (:userb_id,:admin_id) AND status=:status")){
            //Check if you have to accept request
            $checkIfYouHaveToAccept->bindParam(':admin_id', $admin_id);
            $checkIfYouHaveToAccept->bindParam(':userb_id', $userb_id);
            $checkIfYouHaveToAccept->bindParam(':status', $status_open);
            $checkIfYouHaveToAccept->execute(); 

            if(($checkIfYouHaveToAccept->rowCount())>0){
                $user["status"] = $status_have_to_accept;
            }else{
                $user["status"] = $status_open;
            }
          }else{
            $db->rollBack();
            $response["status"]=400;
            $response["message"]="Oops. Versuchen Sie es später noch einmal.";
            echo json_encode($response);
          }
      }else{
      //Check if status Friended
      $check->bindParam(':admin_id', $admin_id);
      $check->bindParam(':userb_id', $userb_id);
      $check->bindParam(':admin1_id', $admin_id);
      $check->bindParam(':userb1_id', $userb_id);
      $check->bindParam(':status', $status_friended);
      $check->execute();  

      if(($check->rowCount())>0){
        $user["status"] = $status_friended;
      }else{
        $user["status"] = $status_unfriended;
      }
      }
    }else{
      $db->rollBack();
      $response["status"]=400;
      $response["message"]="Oops. Versuchen Sie es später noch einmal.";
      echo json_encode($response);
    }
    array_push($response["users"], $user);
   }
$db->commit();
$response["status"] = 200;
$response["message"] = "Freundeslisteliste aktualisiert.";
echo json_encode($response);
}else{
  $response["status"] = 400;
  $response["message"] = "Suche erfolglos!";
  echo json_encode($response);
}
$db=null;
}else{
  $response["status"]=400;
  $response["message"]="Es wurden nicht alle Datensätze übertragen!";
  echo json_encode($response);
}
?>