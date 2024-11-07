<?php

include_once(__DIR__ . "/class.notes.php");
include_once(__DIR__ . "/logger.php");

//$noteInstance = new notes();
$request = isset($_POST) ? $_POST : NULL ;

$action = isset($request["action"]) ? $request["action"] : NULL ;

Switch($action){
    case "P1000":
        $noteInstance = new notes();
        $response = $noteInstance->getAllNotes();
    break;
    case "P1001":
        $noteInstance = new notes();
        $response = $noteInstance->addNote($request);
    break;
    case "P1002":
        $noteInstance = new notes();
        $response = $noteInstance->updateNote($request);
    break;
    case "P1003":
        $noteInstance = new notes();
        $response = $noteInstance->deleteNote();
    break;
    default:
        $response["response_code"] = -1;
        $response["message"] = "Something went wrong, kindly contact your system admin for further assistance"; 
}

echo json_encode($response);

?>
