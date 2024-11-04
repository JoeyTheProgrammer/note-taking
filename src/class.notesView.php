<?php
    include_once(__DIR__ . "/class.notes.php");
    include_once(__DIR__ . "/logger.php");

    class notesView{
	    private $noteInstance;
        private $log;

        public function __construct(){
	        $this->noteInstance = new notes();
            $this->log = new log("NOTE_VIEW");
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
                    $built_body .= "<tr><td class='text-center' colspan=3>Oh no.. it seems you have no notes yet!</td></tr>";
                }else{
                    foreach($noteData as $data){
                        $truncatedDescription = substr($data["description"], 0, 15) . (strlen($data["description"]) > 15 ? '...' : '');
                        
                        $built_body .= "
                            <tr>
                                <td>" . $data["note_title"] . "</td>
                                <td>" . $truncatedDescription . "</td>
                                <td class='text-center'>
                                    <button class='btn btn-sm btn-secondary me-2' onclick='editNote()' data-id='" . $data["id"] . "'>
                                        <i class='bi bi-pencil-fill'></i>
                                    </button>
                                    <button class='btn btn-sm btn-danger' onclick='deleteNote()' data-id='" . $data["id"] . "'>
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
                        <form id='addNoteForm'>
                            <div class='mb-3'>
                                <label for='addNoteTitle' class='form-label'>Note Title</label>
                                <input type='text' class='form-control' id='addNoteTitle' required>
                            </div>
                            <div class='mb-3'>
                                <label for='addNoteDescription' class='form-label'>Description</label>
                                <textarea class='form-control' id='addNoteDescription' rows='3' required></textarea>
                            </div>
                            <button type='submit' class='btn btn-primary'>Add Note</button>
                        </form>
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
                        <form id='editNoteForm'>
                            <input type='hidden' id='editNoteId'>
                            <div class='mb-3'>
                                <label for='editNoteTitle' class='form-label'>Note Title</label>
                                <input type='text' class='form-control' id='editNoteTitle' required>
                            </div>
                            <div class='mb-3'>
                                <label for='editNoteDescription' class='form-label'>Description</label>
                                <textarea class='form-control' id='editNoteDescription' rows='3' required></textarea>
                            </div>
                            <button type='submit' class='btn btn-success'>Save Changes</button>
                        </form>
                    </div>
                    </div>
                </div>
                </div>

                <!-- Bootstrap JS and dependencies (Popper.js) -->
                <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
                <!-- Bootstrap Icons -->
                <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css'>
                <!-- DataTables JS -->
                <script src='https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js'></script>
                <script src='https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js'></script>
                <!-- jQuery (required for DataTables) -->
                <script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
                <!-- SweetAlert2 JS -->
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>

                <!-- Optional JavaScript for Demo Purpose -->
                <script>
                    // This script is for demonstration purposes.
                    // Replace this with your own logic to manipulate notes.

                    $(document).ready(function() {
                        $('#notesTable').DataTable({
                            responsive: true
                        });
                    });

                    function editNote() {
                        // Open the edit note modal
                        $('#editNoteModal').modal('show');
                    }

                    function deleteNote() {
                        // Show SweetAlert confirmation before deleting
                        Swal.fire({
                            title: 'Are you sure?',
                            text: 'Do you really want to delete this note?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Yes, delete it!',
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Perform delete action here
                                Swal.fire(
                                    'Deleted!',
                                    'Your note has been deleted.',
                                    'success'
                                )
                            }
                        })
                    }

                    // Handle Add Note Form Submission
                    $('#addNoteForm').submit(function(e) {
                        e.preventDefault();
                        // Get form data
                        const title = $('#addNoteTitle').val();
                        const description = $('#addNoteDescription').val();
                        // Add new note to the table (this is just for demo; replace with your logic)
                        const table = $('#notesTable').DataTable();
                        table.row.add([
                            title,
                            description,
                            `<div class='text-center'>
                                <button class='btn btn-sm btn-secondary me-2' onclick='editNote()'>
                                    <i class='bi bi-pencil-fill'></i>
                                </button>
                                <button class='btn btn-sm btn-danger' onclick='deleteNote()'>
                                    <i class='bi bi-trash-fill'></i>
                                </button>
                            </div>`
                        ]).draw(false);
                        // Reset form and close modal
                        $('#addNoteForm')[0].reset();
                        $('#addNoteModal').modal('hide');
                    });

                    // Handle Edit Note Form Submission
                    $('#editNoteForm').submit(function(e) {
                        e.preventDefault();
                        // Get form data and update the note (implement your logic here)
                        $('#editNoteModal').modal('hide');
                    });
                </script>

                </body>
                </html>
            
            ";
            
            return $footer;
        }
    	
    }



?>
