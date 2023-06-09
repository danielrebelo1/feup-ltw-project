const editButton = document.querySelector('[type=button]');
const saveButton = document.querySelector('[type=submit]');
const inputs = document.querySelectorAll('input');
const password = document.querySelector('input[name=password]');
const passwordLabel = document.querySelector('label[for=password]');
const newPassword = document.querySelector('input[name=new-password]');
const newPasswordLabel = document.querySelector('label[for=new-password]');
const confirmNewPassword = document.querySelector('input[name=confirm-new-password]');
const confirmNewPasswordLabel = document.querySelector('label[for=confirm-new-password]');
const checkbox = document.querySelector('input[type=checkbox]');
const checkboxLabel = document.querySelector('label[for=change-password]');
const deleteButton = document.querySelector('.delete-button');

window.onload = () => {
    fillInputsWithDatabaseInfo();
}

editButton.addEventListener('click', () => {
    for (var input of inputs)
        input.toggleAttribute('readonly');
    password.toggleAttribute('hidden');
    passwordLabel.toggleAttribute('hidden');
    checkbox.toggleAttribute('hidden');
    checkboxLabel.toggleAttribute('hidden');
    editButton.toggleAttribute('hidden');
    saveButton.toggleAttribute('hidden');
});

saveButton.addEventListener('click', () => {
    for (var input of inputs)
        input.toggleAttribute('readonly');
    password.toggleAttribute('hidden');
    passwordLabel.toggleAttribute('hidden');
    newPassword.setAttribute('hidden', true);
    newPasswordLabel.setAttribute('hidden', true);
    confirmNewPassword.setAttribute('hidden', true);
    confirmNewPasswordLabel.setAttribute('hidden', true);
    checkbox.toggleAttribute('hidden');
    checkboxLabel.toggleAttribute('hidden');
    saveButton.toggleAttribute('hidden');
    editButton.toggleAttribute('hidden');
});

checkbox.addEventListener('change', () => {
    newPassword.toggleAttribute('hidden');
    newPasswordLabel.toggleAttribute('hidden');
    confirmNewPassword.toggleAttribute('hidden');
    confirmNewPasswordLabel.toggleAttribute('hidden');
});

async function fillInputsWithDatabaseInfo() {
    const _name = document.getElementById('name');
    const _username = document.getElementById('username');
    const _email = document.getElementById('email');
    const _pass = document.getElementById('pass');

    const response = await fetch('../pages/api_user.php?func=getAccountInfo');
    const data = await response.json();

    _name.value = data.name;
    _username.value = data.username;
    _email.value = data.email;
}

deleteButton.addEventListener('click', () => {
    if (confirm('Are you sure you want to delete your account?')) {
        fetch('../pages/api_user.php?func=deleteAccount').then(response => {
            if (response.ok) {    
                window.location.href = '../actions/action_logout.php';
            }
        });
    }
});