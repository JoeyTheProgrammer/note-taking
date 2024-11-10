<?php
    include_once(__DIR__ . "/class.notes.php");
    include_once(__DIR__ . "/logger.php");
    include_once(__DIR__ . "/encryption.php");

    class notesView{
	    private $noteInstance;
        private $log;
        private $protocol;
        private $baseUrl;
        private $fullUrl;
        private $encryptionInstance;

        public function __construct(){
	        $this->noteInstance = new notes();
            $this->log = new log("NOTE_VIEW");
            $this->protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $this->baseUrl = $protocol . "://" . $_SERVER['HTTP_HOST'];
            $this->fullUrl = $this->protocol . $this->baseUrl;
            $this->encryptionInstance = new encryption();
        }
        
        /*
        *   Page Header
        * */
        public function getHeader(){
            $header = "
            <!DOCTYPE html>
                <html lang='en'>
                <head>
                    <meta charset='UTF-8'>
                    <title>Note-Taking App Template</title>
                    <!-- Bootstrap CSS -->
                    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
                    <!-- DataTables CSS -->
                    <link href='https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css' rel='stylesheet'>
                    <!-- SweetAlert2 CSS -->
                    <link href='https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css' rel='stylesheet'>
                </head>
                <body>
            ";
            
            return $header;
        }
        
    	
        /*
        *   Gets page body 
        *
        * */
	    public function getBody(){    
            $built_body = "
                <div class='container my-5'>
                    <!-- Header -->
                    <div class='d-flex justify-content-between align-items-center mb-4'>
                        <h1>Note-Taking App</h1>
                        <button class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#addNoteModal'>
                            <i class='bi bi-plus-lg'></i> Add New Note
                        </button>
                    </div>
                    <table id='notesTable' class='table table-striped table-bordered'>
                            <thead>
                                <tr>
                                    <th>Note Title</th>
                                    <th>Preview</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
            ";
            
            
            $getNotes = $this->noteInstance->getAllNotes();
            if($getNotes["response_code"] != 0){
                $this->log->activity("<" . __FUNCTION__ . "> in line: " . __LINE__ . " Something went wrong while attempting to get all notes, response: \n" . json_encode($getNotes));
                $built_body .= "<tr><td class='text-center' colspan=3>Something went wrong, try again or contact your system admin</td></tr>";
            }else{
                $noteData = isset($getNotes["note_data"]) ? $getNotes["note_data"] : NULL ;

                if(!$noteData){
                    // Normally something like this would be displayed as a failsafe if datatables were not used
                    // $built_body .= "<tr><td class='text-center' colspan=3>Oh no.. it seems you have no notes yet!</td></tr>";
                }else{
                    foreach($noteData as $data){
                        $truncatedDescription = substr($data["description"], 0, 15) . (strlen($data["description"]) > 15 ? '...' : '');
                        $encryptedId = $this->encryptionInstance->encrypt($data["id"]);
                        
                        $built_body .= "
                            <tr>
                                <td>" . $data["note_title"] . "</td>
                                <td>" . $truncatedDescription . "</td>
                                <td class='text-center'>
                                    <button class='btn btn-sm btn-secondary btn-edit-note me-2' data-id='" . $encryptedId["encrypted_data"] . "'>
                                        <i class='bi bi-pencil-fill'></i>
                                    </button>
                                    <button class='btn btn-sm btn-danger btn-delete-note' data-id='" . $encryptedId["encrypted_data"] . "'>
                                        <i class='bi bi-trash-fill'></i>
                                    </button>
                                </td>
                            </tr>";
                    }
                }
                $this->log->activity(json_encode($getNotes));
            }
             
            $built_body .= "
                        </tbody>
                    </table>
                </div>
            ";
	        
            
	        return $built_body;
	    }
        
        /*
        *   Page Footer
        *
        **/
        public function getFooter(){
            $footer = "
                <!-- Add Note Modal -->
                <div class='modal fade' id='addNoteModal' tabindex='-1' aria-labelledby='addNoteModalLabel' aria-hidden='true'>
                <div class='modal-dialog'>
                    <div class='modal-content'>
                    <!-- Modal Header -->
                        <div class='modal-header'>
                            <h5 class='modal-title' id='addNoteModalLabel'>Add New Note</h5>
                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                        </div>
                    <!-- Modal Body -->
                        <div class='modal-body'>
                            <input id='csrfToken' type='hidden' value='" . $_SESSION["csrf_token"] . "'>
                            <div class='mb-3'>
                                <label for='addNoteTitle' class='form-label'>Note Title</label>
                                <input id='addNoteTitle' type='text' class='form-control'>
                            </div>
                            <div class='mb-3'>
                                <label for='addNoteDescription' class='form-label'>Description</label>
                                <textarea id='addNoteDescription' class='form-control' rows='3' ></textarea>
                            </div>
                            <button id= 'btnAddNote' type='submit' class='btn btn-primary'>Add Note</button>
                        </div>
                    </div>
                </div>
                </div>

                <!-- Edit Note Modal -->
                <div class='modal fade' id='editNoteModal' tabindex='-1' aria-labelledby='editNoteModalLabel' aria-hidden='true'>
                <div class='modal-dialog'>
                    <div class='modal-content'>
                    <!-- Modal Header -->
                        <div class='modal-header'>
                            <h5 class='modal-title' id='editNoteModalLabel'>Edit Note</h5>
                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                        </div>
                    <!-- Modal Body -->
                    <div class='modal-body'>
                        <input id='csrfToken' type='hidden' value='" . $_SESSION["csrf_token"] . "'>
                        <input type='hidden' id='editNoteId'>
                        <div class='mb-3'>
                            <label for='editNoteTitle' class='form-label'>Note Title</label>
                            <input id='editNoteTitle' type='text' class='form-control' >
                        </div>
                        <div class='mb-3'>
                            <label for='editNoteDescription' class='form-label'>Description</label>
                            <textarea id='editNoteDescription' class='form-control' rows='3' ></textarea>
                        </div>
                        <button id='btnSaveChanges' type='submit' class='btn btn-success'>Save Changes</button>
                    </div>
                    </div>
                </div>
                </div>

                <!-- jQuery (required for DataTables) -->
                <script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
                <!-- Bootstrap JS and dependencies (Popper.js) -->
                <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
                <!-- Bootstrap Icons -->
                <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css'>
                <!-- DataTables JS -->
                <script src='https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js'></script>
                <script src='https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js'></script>
                <!-- SweetAlert2 JS -->
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                <!-- Custom JS -->
                <script src='" . $this->fullUrl . "/incs/js/custom.js?v1.0'></script>
                </body>
                </html>
            
            ";
            
            return $footer;
        }
    	
    }



?>
