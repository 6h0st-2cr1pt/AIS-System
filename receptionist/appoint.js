document.addEventListener('DOMContentLoaded', function() {
    const petTypeOther = document.getElementById('pet_type_other');
    const petTypeOtherSpecify = document.getElementById('pet_type_other_specify');

    function toggleOtherPetType() {
        petTypeOtherSpecify.style.display = petTypeOther.checked ? 'block' : 'none';
    }

    petTypeOther.addEventListener('change', toggleOtherPetType);
    document.querySelectorAll('input[name="pet_type"]').forEach(function(radio) {
        radio.addEventListener('change', toggleOtherPetType);
    });

    toggleOtherPetType();

    // Edit appointment functionality
    const editButtons = document.querySelectorAll('.edit-btn');
    const editModal = new bootstrap.Modal(document.getElementById('editAppointmentModal'));
    const editForm = document.getElementById('editAppointmentForm');
    const saveEditBtn = document.getElementById('saveEditBtn');

    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const appointmentId = this.getAttribute('data-id');
            fetch(`get_appointment.php?id=${appointmentId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_id').value = data.id;
                    document.getElementById('edit_owner_name').value = data.owner_name;
                    document.getElementById('edit_pet_name').value = data.pet_name;
                    document.getElementById('edit_service_type').value = data.service_type;
                    document.getElementById('edit_appointment_date').value = data.appointment_date;
                    editModal.show();
                });
        });
    });

    saveEditBtn.addEventListener('click', function() {
        editForm.submit();
    });
});

