$(document).ready(function(){
    //initalize data table
    $("#notesTable").DataTable({
        responsive: true
    });

    $("body").on("click", ".btn-edit-note", function() {
        editNote();
    });  
   
    function editNote(){
        // Open the edit note modal
        $("#editNoteModal").modal("show");
    }

    function deleteNote(){
        // Show SweetAlert confirmation before deleting
        Swal.fire({
            title: "Are you sure?",
            text: "Do you really want to delete this note?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Yes, delete it!",
            reverseButtons: true
        }).then((result) => {
            if(result.isConfirmed){
                // Perform delete action here
                Swal.fire(
                    "Deleted!",
                    "Your note has been deleted.",
                    "success"
                )
            }
        })
    }
    
    // Handle Add Note Form Submission
    $("#addNoteForm").submit(function(e) {
        e.preventDefault();
        // Get form data
        const title = $("#addNoteTitle").val();
        const description = $("#addNoteDescription").val();
        // Add new note to the table (this is just for demo; replace with your logic)
        const table = $("#notesTable").DataTable();
        table.row.add([
            title,
            description,
            `<div class="text-center">
                <button class="btn btn-sm btn-secondary me-2" onclick="editNote()">
                    <i class="bi bi-pencil-fill"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteNote()">
                    <i class="bi bi-trash-fill"></i>
                </button>
            </div>`
        ]).draw(false);
        // Reset form and close modal
        $("#addNoteForm")[0].reset();
        $("#addNoteModal").modal("hide");
    });

    // Handle Edit Note Form Submission
    $("#editNoteForm").submit(function(e) {
        e.preventDefault();
        // Get form data and update the note (implement your logic here)
        $("#editNoteModal").modal("hide");
    });
 
});
