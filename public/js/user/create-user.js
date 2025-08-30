document.addEventListener('DOMContentLoaded', function() {
    const createUserForm = document.getElementById('createUserForm');
    const roleSelect = document.getElementById('role');
    const schoolSelect = document.getElementById('schoolId'); // Get the new school select element
    const messageDiv = document.getElementById('message');

    // Fetch and populate roles
    fetch('/api/roles')
        .then(response => response.json())
        .then(roles => {
            roles.forEach(role => {
                const option = document.createElement('option');
                option.value = role.name;
                option.textContent = role.name;
                roleSelect.appendChild(option);
            });
        });

    // Fetch and populate schools
    fetch('/api/schools')
        .then(response => response.json())
        .then(schools => {
            schoolSelect.innerHTML = '<option value="">(No School Assigned)</option>';
            schools.forEach(school => {
                const option = document.createElement('option');
                option.value = school.school_id;
                option.textContent = school.name;
                schoolSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error fetching schools:', error);
            messageDiv.textContent = 'Error loading schools.';
            messageDiv.className = 'error';
        });

    createUserForm.addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(createUserForm);

        fetch('/api/users', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            messageDiv.textContent = data.message;
            messageDiv.className = data.success ? 'success' : 'error';
            if (data.success) {
                createUserForm.reset();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            messageDiv.textContent = 'An error occurred. Please try again.';
            messageDiv.className = 'error';
        });
    });
});