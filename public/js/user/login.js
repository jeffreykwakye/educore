const loginButton = document.getElementById('loginButton');
const phoneNumberInput = document.getElementById('phone_number');
const passwordInput = document.getElementById('password');
const messageDiv = document.getElementById('message');
const loginForm = document.getElementById('login-form');

// Check for lockout state on page load
checkLockoutState();

loginButton.addEventListener('click', function() {
    const phoneNumber = phoneNumberInput.value;
    const password = passwordInput.value;

    if (!phoneNumber || !password) {
        messageDiv.textContent = 'Phone Number and Password are required.';
        messageDiv.className = 'error';
        return;
    }

    const formData = new FormData();
    formData.append('phone_number', phoneNumber);
    formData.append('password', password);

    fetch('/login', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageDiv.textContent = data.message;
            messageDiv.className = 'success';
            // Optional: Redirect the user on success
            // window.location.href = '/dashboard';
        } else {
            messageDiv.textContent = data.message;
            messageDiv.className = 'error';
            
            // Check if the lockout message is received
            if (data.message.includes('locked')) {
                // Store lockout state in local storage
                localStorage.setItem('accountLocked', 'true');
                localStorage.setItem('lockoutTimestamp', Date.now());
                disableLoginForm();
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        messageDiv.textContent = 'An error occurred during login.';
        messageDiv.className = 'error';
    });
});

function checkLockoutState() {
    const isLocked = localStorage.getItem('accountLocked');
    const lockoutTimestamp = localStorage.getItem('lockoutTimestamp');
    const lockoutDuration = 15 * 60 * 1000; // 15 minutes in milliseconds

    if (isLocked && lockoutTimestamp) {
        if (Date.now() - lockoutTimestamp < lockoutDuration) {
            disableLoginForm();
        } else {
            // Lockout period has expired, so clear the state
            localStorage.removeItem('accountLocked');
            localStorage.removeItem('lockoutTimestamp');
        }
    }
}

function disableLoginForm() {
    phoneNumberInput.disabled = true;
    passwordInput.disabled = true;
    loginButton.disabled = true;
    messageDiv.textContent = 'Your account is temporarily locked. Please try again later.';
    messageDiv.className = 'error';
    loginForm.style.pointerEvents = 'none'; // Disables all clicks on the form
    loginForm.style.opacity = '0.6';
}