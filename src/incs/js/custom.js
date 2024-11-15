const protocol = window.location.protocol === "https:" ? "https" : "http";
const handlerUrl = protocol + "://" + window.location.host + "/handler.php"; ;

$(document).ready(function() {
    // Initialize DataTable
    let notesTable = $('#notesTable').DataTable({
        ajax: {
            url: handlerUrl,
            type: 'POST',
            data: { 
                action: "P1000"
            },
            // dataSrc: "datatable_data",
            dataSrc: function(json){
                if (json && json.datatable_data && json.datatable_data.length > 0) {
                    return json.datatable_data;
                } else {
                    $('#notesTable tbody').html('<tr><td colspan="3" class="text-center">No data available</td></tr>');
                    return []; 
                }
            }
        },
        columns: [
            {data:'note_title'},
            {data:'description'},
            {data:'actions'}
        ]
        
    });

    /*
    *   Add new note 
    */
    $("body").on("click","#btnAddNote", function(e){
        e.preventDefault();
        let token = $("#csrfToken");
        let addNoteTitle = $("#addNoteTitle");
        let addNoteDescription = $("#addNoteDescription");

        addNoteTitle.removeClass('is-invalid');
        addNoteDescription.removeClass('is-invalid');
        $('.invalid-feedback').remove();

        if (!addNoteTitle.val().trim()) {
            addNoteTitle.addClass('is-invalid');
            addNoteTitle.after('<div class="invalid-feedback">Please enter a title.</div>');
            return;
        }

        if (!addNoteDescription.val().trim()) {
            addNoteDescription.addClass('is-invalid');
            addNoteDescription.after('<div class="invalid-feedback">Please enter a description.</div>');
            return;
        }

        $.ajax({
            url: handlerUrl,
            type: 'POST',
            data: { 
                action : "P1002",
                csrf_token : token.val(),
                note_title: addNoteTitle.val(),
                description: addNoteDescription.val()
             },
            success: function(response) {
                const res = JSON.parse(response);
                // console.log(res);
                if(res.response_code === 0){
                    Swal.fire('Success', 'Successfully added new note!', 'success');
                    $('#addNoteModal').modal('hide');
                    addNoteTitle.val("");
                    addNoteDescription.val("");
                    notesTable.ajax.reload(null, false);
                } else {
                    Swal.fire('Error', 'Oops, something went wrong, kindly try again or contact your system admin', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'An unexpected error occurred.', 'error');
            }
        });

    });

    $("#addNoteTitle").on("keyup", function(e){
        e.preventDefault();
        let addNoteTitle = $("#addNoteTitle");

        addNoteTitle.removeClass('is-invalid');
        addNoteTitle.siblings('.invalid-feedback').remove();

        if (!addNoteTitle.val().trim()) {
            addNoteTitle.addClass('is-invalid');
            addNoteTitle.after('<div class="invalid-feedback">Please enter a title.</div>');
        }
    });

    $("#addNoteDescription").on("keyup", function(e){
        e.preventDefault();
        let addNoteDescription = $("#addNoteDescription");

        addNoteDescription.removeClass('is-invalid');
        addNoteDescription.siblings('.invalid-feedback').remove();

        if (!addNoteDescription.val().trim()) {
            addNoteDescription.addClass('is-invalid');
            addNoteDescription.after('<div class="invalid-feedback">Please enter a Description.</div>');
        }
    });

    /*
    *   Edit Note Js
    */ 
    $("body").on("click", ".btn-edit-note", (function() {
        const noteId = $(this).data('id');
        let token = $("#csrfToken");
        
        $("#editNoteId").val(noteId);

        $.ajax({
            url: handlerUrl,
            type: 'POST',
            data: { 
                action : "P1001",
                note_id : noteId,
                csrf_token : token.val(),

            },
            success: function(response) {
                const res = JSON.parse(response);
                if(res.response_code === 0){
                    $('#editNoteTitle').val(res.note_data.note_title);
                    $('#editNoteDescription').val(res.note_data.description);
                    $('#editNoteModal').modal('show');
                } else {
                    Swal.fire('Error', 'Something went wrong, kindly try again or contact your system admin', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'An unexpected error occurred.', 'error');
            }
        });
    }));

     $('#btnSaveChanges').click(function() {
        let token = $("#csrfToken");
        let editNoteTitle = $("#editNoteTitle");
        let editNoteDescription = $("#editNoteDescription");
        let noteId = $("#editNoteId");

        editNoteTitle.removeClass('is-invalid');
        editNoteDescription.removeClass('is-invalid');
        $('.invalid-feedback').remove();

        if (!editNoteTitle.val().trim()) {
            editNoteTitle.addClass('is-invalid');
            editNoteTitle.after('<div class="invalid-feedback">Please enter a title.</div>');
            return;
        }

        if (!editNoteDescription.val().trim()) {
            editNoteDescription.addClass('is-invalid');
            editNoteDescription.after('<div class="invalid-feedback">Please enter a description.</div>');
            return;
        }

        $.ajax({
            url: handlerUrl,
            type: 'POST',
            data: { 
                action : "P1003",
                csrf_token : token.val(),
                id: noteId.val(),
                note_title: editNoteTitle.val(),
                description: editNoteDescription.val()
             },
            success: function(response) {
                const res = JSON.parse(response);
                // console.log(res);
                if(res.response_code === 0){
                    Swal.fire('Success', 'Successfully edited note!', 'success');
                    $('#editNoteModal').modal('hide');
                    editNoteTitle.val("");
                    editNoteDescription.val("");
                    notesTable.ajax.reload(null, false);
                } else {
                    Swal.fire('Error', 'Oops, something went wrong, kindly try again or contact your system admin', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'An unexpected error occurred.', 'error');
            }
        });
    });

    $("#editNoteTitle").on("keyup", function(e){
        let editNoteTitle = $("#editNoteTitle");

        editNoteTitle.removeClass('is-invalid');
        editNoteTitle.siblings('.invalid-feedback').remove();

        if (!editNoteTitle.val().trim()) {
            editNoteTitle.addClass('is-invalid');
            editNoteTitle.after('<div class="invalid-feedback">Please enter a title.</div>');
        }
    });

    $("#editNoteDescription").on("keyup", function(e){
        let editNoteDescription = $("#editNoteDescription");

        editNoteDescription.removeClass('is-invalid');
        editNoteDescription.siblings('.invalid-feedback').remove();

        if (!editNoteDescription.val().trim()) {
            editNoteDescription.addClass('is-invalid');
            editNoteDescription.after('<div class="invalid-feedback">Please enter a Description.</div>');
        }
    });

    /**
     *  Deletes Note
     * */ 
    $('body').on("click", ".btn-delete-note", (function() {
        const noteId = $(this).data('id');
        let token = $("#csrfToken");

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
                    data: { 
                        action: "P1004",
                        id: noteId, 
                        csrf_token: token.val()
                    },
                    success: function(response) {
                        const res = JSON.parse(response);
                        if(res.response_code === 0){
                            Swal.fire('Success', 'Successfully Deleted a note', 'success');
                            notesTable.ajax.reload(null, false);
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
    }));


});

