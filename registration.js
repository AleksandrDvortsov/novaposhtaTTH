function clickRegistration() {
    let loginValue = document.getElementById('inputId').value;
    let passwordValue = document.getElementById('inputPass').value;
    let textError = document.getElementById('textError');
    if (loginValue == '' || passwordValue == '') {
        textError.innerHTML = 'Заполните все поля!';
        textError.style.visibility = 'visible';
    } else {
        fetch('http://localhost/phpTZ0407/index.php', {
            method: "POST",
            body: JSON.stringify({ status: 'registration', login: loginValue, password: passwordValue }),
            headers: {
                "Content-Type": "application/json"
            }
        }).then(response => { return response.json() }).then(obj => {
            let param = JSON.parse(obj);

            if(!param.isUserAvtorization){
                 textError.innerHTML = 'Пользователь с таким ником уже есть!';
                 textError.style.visibility = 'visible';
            }else{
                textError.style.visibility = 'hidden';
                isAvtorization = true;
                USER_LOGIN = param.login;
                USER_ID = param.id_u
                localStorage.setItem('login', USER_LOGIN);
                localStorage.setItem('id', USER_ID)
                localStorage.setItem('isAvtorization', isAvtorization)
                location.href = 'http://localhost/phpTZ0407/novaposhtaTTH.html';
            }
        })
    }
}