<?php 
$response= array();

if(isset($_POST['task_id'])){

$task_id = $_POST['task_id'];
$status_inactive="offen";
$no_comments="";

require_once 'db_connect.php';

$get_editor_name=$db->prepare("SELECT name FROM user WHERE user_id=:editor_id");

if($result= $db->prepare("SELECT * FROM task WHERE task_id=:task_id")){
	$db->beginTransaction();
    $result->bindParam(':task_id', $task_id);
    $result->execute();  

    if(($result->rowCount())>0){
     	 foreach ($result as $row) {
     	 	$response["event_id"]=$row["event_id"];
     	 	$event_id=$row["event_id"];
     	 	$response["task"]=$row["task"];
     	 	$response["quantity"]=$row["quantity"];
     	 	$response["costs_of_task"]=$row["cost"];
     	 	$response["percentage_of_task"]=$row["percentage"];
            $response["editor_id"]=$row["editor_id"];
     	 	$editor_id=$row["editor_id"];
     	 	if($get_editor_name){
     	 		$get_editor_name->bindParam(':editor_id', $editor_id);
    			$get_editor_name->execute(); 
                if(($get_editor_name->rowCount())>0){
                    foreach ($get_editor_name as $rowEditorName) {
                    $response["editor_name"]=$rowEditorName["name"];
                    }
                }else{
                    $response["editor_name"]=$status_inactive;
                }
    			if($get_event=$db->prepare("SELECT name FROM event WHERE event_id=:event_id")){
					$get_event->bindParam(':event_id', $event_id);
    				$get_event->execute();  

    				foreach ($get_event as $rowEventName) {
    				$response["event_name"]=$rowEventName["name"];
                    }
    			}else{
     	 			$db->rollBack();
     	 			$response["status"]=400;
    				$response["message"]="Oops. Versuchen Sie es später noch einmal.";
    				echo json_encode($response);
     	 	    }
     	 	}else{
     	 		$db->rollBack();
     	 		$response["status"]=400;
    			$response["message"]="Oops. Versuchen Sie es später noch einmal.";
    			echo json_encode($response);
     	 	}
     	 }
     	 $db -> commit ();
     	 $response["status"]=200;
     	 $response["message"]="Aufgabe aktualisiert.";
         echo json_encode($response);  	 
    }        
}else{
	$response["status"]=400;
    $response["message"]="Oops. Versuchen Sie es später noch einmal.";
    echo json_encode($response);
}
$db=null;
}else{
	$response["status"]=400;
    $response["message"]="Es wurden nicht alle Datensätze übertragen!";
    echo json_encode($response);
}
?>