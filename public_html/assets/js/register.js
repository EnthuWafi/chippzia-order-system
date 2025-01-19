
function togglePassword(fieldId) {
    const passwordField = document.getElementById(fieldId);
    const toggleText = passwordField.nextElementSibling;

    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleText.textContent = 'Hide';
    } else {
        passwordField.type = 'password';
        toggleText.textContent = 'Show';
    }
}

document.querySelector('form').addEventListener('submit', function (event) {
    const password = document.querySelector('input[name="psw"]').value;
    const confirmPassword = document.querySelector('input[name="psw-repeat"]').value;

    if (password !== confirmPassword) {
        alert('Passwords do not match!');
        event.preventDefault(); // Hentikan penghantaran borang
    }
});