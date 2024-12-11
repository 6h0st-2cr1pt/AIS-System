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
});