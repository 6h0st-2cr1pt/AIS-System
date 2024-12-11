document.getElementById('categoryFilter').addEventListener('change', filterTable);
document.getElementById('statusFilter').addEventListener('change', filterTable);
document.getElementById('searchInput').addEventListener('input', filterTable);

function filterTable() {
    const categoryFilter = document.getElementById('categoryFilter').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const table = document.getElementById('inventoryTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

    for (let row of rows) {
        const cells = row.getElementsByTagName('td');
        const name = cells[1].textContent.toLowerCase();
        const category = cells[2].textContent.toLowerCase();
        const quantity = parseInt(cells[3].textContent);
        
        let status = '';
        if (quantity === 0) {
            status = 'Out of Stock';
        } else if (quantity <= 10) {
            status = 'Low Stock';
        } else {
            status = 'In Stock';
        }

        // Check filter conditions
        const matchesCategory = categoryFilter === '' || category.includes(categoryFilter);
        const matchesStatus = statusFilter === '' || status === statusFilter;
        const matchesSearch = name.includes(searchInput);

        // Set row display based on filter matches
        if (matchesCategory && matchesStatus && matchesSearch) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
}