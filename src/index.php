<?php
    include_once(__DIR__ . "/class.notesView.php");
    
    $noteView = new notesView();

    echo json_encode($noteView->getHomeScreen());

?>
