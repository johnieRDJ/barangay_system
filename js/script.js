function validateRegister(event) {
    const form = event && event.target ? event.target : document.querySelector('form');
    const isRegisterForm = form && form.matches('form[action="register.php"]');
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirm_password").value;
    const emailInput = document.querySelector('[name="email"]');
    const email = emailInput ? emailInput.value.trim() : '';
    const role = document.querySelector('[name="role"]')?.value || '';
    const birthdate = document.querySelector('[name="birthdate"]')?.value || '';
    const validIdInput = document.querySelector('[name="valid_id_file"]');

    if (password.length < 6 && !isRegisterForm) {
        alert("Password must be at least 6 characters.");
        return false;
    }

    if (password !== confirmPassword && !isRegisterForm) {
        alert("Passwords do not match.");
        return false;
    }

    if (role === 'complainant' && birthdate) {
        const today = new Date();
        const birth = new Date(birthdate + 'T00:00:00');
        let age = today.getFullYear() - birth.getFullYear();
        const monthDiff = today.getMonth() - birth.getMonth();

        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
            age--;
        }

        if (age < 18) {
            alert("Complainants must be 18 years old or above.");
            return false;
        }
    }

    if (validIdInput && validIdInput.files && validIdInput.files[0]) {
        const file = validIdInput.files[0];
        const maxBytes = 50 * 1024 * 1024;
        const allowedTypes = ['image/jpeg', 'image/png'];

        if (!allowedTypes.includes(file.type) || file.size > maxBytes) {
            alert('Valid ID must be a JPG/JPEG or PNG image up to 50MB.');
            return false;
        }
    }

    if (isRegisterForm) {
        if (event) {
            event.preventDefault();
        }

        const submitAfterValidId = function() {
            prepareRegisterValidId(form)
                .then(function() {
                    form.submit();
                })
                .catch(function(error) {
                    alert(error.message || 'The Valid ID could not be prepared. Please choose another JPG/PNG image.');
                });
        };

        if (form.dataset.emailChecked === email) {
            submitAfterValidId();
            return false;
        }

        fetch('check_email.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
            },
            body: new URLSearchParams({ email: email })
        })
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('Email check failed.');
                }
                return response.json();
            })
            .then(function(data) {
                if (!data.ok || data.exists) {
                    alert(data.message || 'That email is already registered. Please log in or use another email.');
                    if (emailInput) {
                        emailInput.focus();
                    }
                    return;
                }

                form.dataset.emailChecked = email;
                submitAfterValidId();
            })
            .catch(function() {
                alert('The system could not check this email right now. Please try again.');
            });

        return false;
    }

    return true;
}

function prepareRegisterValidId(form) {
    return new Promise(function(resolve, reject) {
        const fileInput = form.querySelector('[name="valid_id_file"]');
        const dataInput = form.querySelector('[name="valid_id_data"]');
        const nameInput = form.querySelector('[name="valid_id_name"]');
        const file = fileInput && fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;

        if (!dataInput || !nameInput) {
            reject(new Error('The Valid ID field is missing.'));
            return;
        }

        if (!file) {
            if (dataInput.value !== '') {
                resolve();
                return;
            }

            if (fileInput && fileInput.required) {
                reject(new Error('Please attach a valid ID for admin review.'));
                return;
            }

            resolve();
            return;
        }

        const maxBytes = 50 * 1024 * 1024;
        const allowedTypes = ['image/jpeg', 'image/png'];
        const fileKey = [file.name, file.size, file.lastModified].join(':');

        if (!allowedTypes.includes(file.type) || file.size > maxBytes) {
            reject(new Error('Valid ID must be a JPG/JPEG or PNG image up to 50MB.'));
            return;
        }

        if (form.dataset.validIdPrepared === fileKey && dataInput.value !== '') {
            resolve();
            return;
        }

        const reader = new FileReader();
        reader.onerror = function() {
            reject(new Error('The Valid ID could not be read.'));
        };
        reader.onload = function() {
            const image = new Image();
            image.onerror = function() {
                reject(new Error('Valid ID must be a readable JPG/JPEG or PNG image.'));
            };
            image.onload = function() {
                const maxDimension = 2200;
                const scale = Math.min(1, maxDimension / Math.max(image.width, image.height));
                const canvas = document.createElement('canvas');
                canvas.width = Math.max(1, Math.round(image.width * scale));
                canvas.height = Math.max(1, Math.round(image.height * scale));

                const context = canvas.getContext('2d');
                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, canvas.width, canvas.height);
                context.drawImage(image, 0, 0, canvas.width, canvas.height);

                dataInput.value = canvas.toDataURL('image/jpeg', 0.88);
                nameInput.value = file.name.replace(/\.(png|jpe?g)$/i, '.jpg');
                form.dataset.validIdPrepared = fileKey;
                resolve();
            };
            image.src = reader.result;
        };
        reader.readAsDataURL(file);
    });
}

document.addEventListener('input', function(event) {
    const input = event.target;

    if (!(input instanceof HTMLInputElement)) {
        return;
    }

    if (input.hasAttribute('data-digits-only')) {
        const maxLength = parseInt(input.getAttribute('maxlength') || '99', 10);
        input.value = input.value.replace(/\D/g, '').slice(0, maxLength);
    }

    if (input.hasAttribute('data-alpha-only')) {
        input.value = input.value.replace(/[^A-Za-z .'-]/g, '');
    }

    if (input.hasAttribute('data-address-only')) {
        input.value = input.value.replace(/[^A-Za-z0-9 #\-\/\.,]/g, '');
    }
});

document.addEventListener('change', function(event) {
    const input = event.target;

    if (!(input instanceof HTMLInputElement) || input.type !== 'file') {
        return;
    }

    if (!['valid_id', 'valid_id_file', 'image', 'signature', 'complainant_signature'].includes(input.name)) {
        return;
    }

    const file = input.files && input.files[0] ? input.files[0] : null;
    const maxBytes = 50 * 1024 * 1024;
    const allowedTypes = ['image/jpeg', 'image/png'];

    if (!file) {
        return;
    }

    if (!allowedTypes.includes(file.type) || file.size > maxBytes) {
        input.value = '';
        alert('Please choose a JPG/JPEG or PNG image up to 50MB.');
    }
});
