
function clickToLogin() {
    let loginValue = document.getElementById('inputId').value;
    let passwordValue = document.getElementById('inputPass').value;
    let textError = document.getElementById('textError');
    if (loginValue == '' || passwordValue == '') {
        textError.innerHTML = 'Заполните все поля!';
        textError.style.visibility = 'visible';
    } else {
        textError.innerHTML = 'loading!';
        textError.style.visibility = 'visible';
        fetch('http://localhost/phpTZ0407/index.php', {
            method: "POST",
            body: JSON.stringify({ status: 'login', login: loginValue, password: passwordValue }),
            headers: {
                "Content-Type": "application/json"
            }
        }).then(response => { return response.json() }).then(obj => {
            let param = JSON.parse(obj);
            if (!param.mySQL && param.mySQL !== undefined) {
                textError.innerHTML = 'mySQL off!';
                textError.style.visibility = 'visible';
                return;
            }
            if (!param.isUserAvtorization) {
                textError.innerHTML = 'Логин или пароль введен не верно!';
                textError.style.visibility = 'visible';
            } else {
                textError.style.visibility = 'hidden';
                isAvtorization = true;
                USER_LOGIN = param.login;
                USER_ID = param.id_u
                console.log(USER_LOGIN, USER_ID);
                localStorage.setItem('login', USER_LOGIN);
                localStorage.setItem('id', USER_ID)
                localStorage.setItem('isAvtorization', isAvtorization)
                localStorage.setItem('arrTTH', JSON.stringify(param.arrTTH))
                location.href = 'http://localhost/phpTZ0407/novaposhtaTTH.html';
            }

        })
    }
}