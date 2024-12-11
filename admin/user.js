document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const roleFilter = document.getElementById('roleFilter');
    const userTableBody = document.getElementById('userTableBody');
    const rows = userTableBody.getElementsByTagName('tr');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedRole = roleFilter.value.toLowerCase();

        for (let row of rows) {
            const cells = row.getElementsByTagName('td');
            let showRow = false;

            if (cells.length > 0) {
                const role = cells[6].textContent.toLowerCase();
                if (selectedRole === '' || role === selectedRole) {
                    for (let cell of cells) {
                        if (cell.textContent.toLowerCase().indexOf(searchTerm) > -1) {
                            showRow = true;
                            break;
                        }
                    }
                }
            }

            row.style.display = showRow ? '' : 'none';
        }
    }

    searchInput.addEventListener('input', filterTable);
    roleFilter.addEventListener('change', filterTable);

});