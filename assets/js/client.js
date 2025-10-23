// OUTSINC - Client Management JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Add note form submission
    const addNoteForm = document.getElementById('addNoteForm');
    if (addNoteForm) {
        addNoteForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(addNoteForm);
            
            try {
                const response = await fetch('/modules/clients/add_note.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Reload the page to show new note
                    location.reload();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                alert('An error occurred while adding the note');
                console.error(error);
            }
        });
    }
});
