document.getElementById('registerButton').addEventListener('click', function() {
    const schoolName = document.getElementById('name').value;
    const phoneNumber = document.getElementById('phone_number').value;
    const address = document.getElementById('address').value;
    const messageDiv = document.getElementById('message');

    if (!schoolName || !phoneNumber) {
        messageDiv.textContent = 'School Name and Phone Number are required.';
        messageDiv.className = 'error';
        return;
    }

    const formData = new FormData();
    formData.append('name', schoolName);
    formData.append('phone_number', phoneNumber);
    formData.append('address', address);

    fetch('/register', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageDiv.textContent = data.message;
            messageDiv.className = 'success';
        } else {
            messageDiv.textContent = data.message;
            messageDiv.className = 'error';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        messageDiv.textContent = 'An error occurred during registration.';
        messageDiv.className = 'error';
    });
});