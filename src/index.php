<?php
    include_once(__DIR__ . "/class.notesView.php");
    
    $noteView = new notesView();

    $html = $noteView->getHeader();
    $html .= $noteView->getBody();
    $html .= $noteView->getFooter();
    echo $html;
?>
