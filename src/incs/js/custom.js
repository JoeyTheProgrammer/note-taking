const protocol = window.location.protocol === "https:" ? "https" : "http";
const handlerUrl = protocol + "://" + window.location.host + "/handler.php"; ;

$(document).ready(function() {
    // Initialize DataTable
    $('#notesTable').DataTable();

    // Handle Add Note Form Submission
    $('#addNoteForm').submit(function(e) {
        e.preventDefault();
        const formData = $(this).serialize();

        $.ajax({
            url: handlerUrl,
            type: 'POST',
            data: {data: data},
            success: function(response) {
                const res = JSON.parse(response);
                console.log(res);
                if(res.response_code === 0){
                    // Success - reload or update the table
                    location.reload();
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'An unexpected error occurred.', 'error');
            }
        });
    });

    // Handle Edit Note Button Click
    $('.btn-edit-note').click(function() {
        const noteId = $(this).data('id');

        $.ajax({
            url: handlerUrl,
            type: 'POST',
            data: { action : "P1000" },
            success: function(response) {
                const res = JSON.parse(response);
                console.log(res);
                if(res.response_code === 0){
                    $('#editNoteId').val(res.note_data.id);
                    $('#editNoteTitle').val(res.note_data.note_title);
                    $('#editNoteDescription').val(res.note_data.description);
                    $('#editNoteModal').modal('show');
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'An unexpected error occurred.', 'error');
            }
        });
    });

    // Handle Edit Note Form Submission
    $('#editNoteForm').submit(function(e) {
        e.preventDefault();
        const formData = $(this).serialize();

        $.ajax({
            url: handler.php,
            type: 'POST',
            data: formData,
            success: function(response) {
                const res = JSON.parse(response);
                if(res.response_code === 0){
                    // Success - reload or update the table
                    location.reload();
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'An unexpected error occurred.', 'error');
            }
        });
    });

    // Handle Delete Note Button Click
    $('.btn-delete-note').click(function() {
        const noteId = $(this).data('id');
        const csrfToken = $('input[name="csrf_token"]').val();

        Swal.fire({
            title: 'Are you sure?',
            text: "Do you really want to delete this note?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: handlerUrl,
                    type: 'POST',
                    data: { id: noteId, csrf_token: csrfToken },
                    success: function(response) {
                        const res = JSON.parse(response);
                        if(res.response_code === 0){
                            // Success - reload or update the table
                            location.reload();
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'An unexpected error occurred.', 'error');
                    }
                });
            }
        })
    });
});

