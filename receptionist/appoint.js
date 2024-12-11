document.addEventListener('DOMContentLoaded', function() {
    const petTypeOther = document.getElementById('pet_type_other');
    const petTypeOtherSpecify = document.getElementById('pet_type_other_specify');
    const editPetTypeOther = document.getElementById('edit_pet_type_other');
    const editPetTypeOtherSpecify = document.getElementById('edit_pet_type_other_specify');

    function toggleOtherPetType(otherRadio, otherInput) {
        otherInput.style.display = otherRadio.checked ? 'block' : 'none';
    }

    function setupPetTypeToggle(otherRadio, otherInput, radioName) {
        otherRadio.addEventListener('change', () => toggleOtherPetType(otherRadio, otherInput));
        document.querySelectorAll(`input[name="${radioName}"]`).forEach(function(radio) {
            radio.addEventListener('change', () => toggleOtherPetType(otherRadio, otherInput));
        });
        toggleOtherPetType(otherRadio, otherInput);
    }

    setupPetTypeToggle(petTypeOther, petTypeOtherSpecify, 'pet_type');
    setupPetTypeToggle(editPetTypeOther, editPetTypeOtherSpecify, 'pet_type');

    // Edit appointment functionality
    const editModal = new bootstrap.Modal(document.getElementById('editAppointmentModal'));
    const editForm = document.getElementById('editAppointmentForm');
    const saveEditBtn = document.getElementById('saveEditBtn');

    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const appointmentId = this.getAttribute('data-id');
            fetch(`get_appointment.php?id=${appointmentId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_id').value = data.id;
                    document.getElementById('edit_owner_name').value = data.owner_name;
                    document.getElementById('edit_contact_number').value = data.contact_number;
                    document.getElementById('edit_email').value = data.email;
                    document.getElementById('edit_home_address').value = data.home_address;
                    document.getElementById('edit_pet_name').value = data.pet_name;
                    document.querySelector(`input[name="pet_type"][value="${data.pet_type}"]`).checked = true;
                    document.getElementById('edit_pet_type_other_specify').value = data.pet_type_other;
                    document.getElementById('edit_breed').value = data.breed;
                    document.getElementById('edit_age').value = data.age;
                    document.querySelector(`input[name="service_type"][value="${data.service_type}"]`).checked = true;
                    document.getElementById('edit_appointment_date').value = data.appointment_date;
                    toggleOtherPetType(editPetTypeOther, editPetTypeOtherSpecify);
                });
        });
    });

    saveEditBtn.addEventListener('click', function() {
        editForm.submit();
    });
});

