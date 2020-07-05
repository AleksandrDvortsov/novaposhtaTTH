let idToServ = 0;
let arrTHH = JSON.parse(localStorage.getItem('arrTTH'));

function addToArr(param) {
    let isAdd = true;
    for (let i = 0; i < arrTHH.length; i++) {
        if (parseInt(arrTHH[i]) === parseInt(param)) {
            isAdd = false;
            return;
        };
    }
    if (isAdd) {
        // добавлять в arrTTH
        console.log(arrTHH, ' -arrTTH')
        arrTHH.push(parseInt(param));
        console.log(arrTHH, ' -arrTTH')
        createLiA(param)
    }
    localStorage.setItem('arrTTH', JSON.stringify(arrTHH));
}
function find() {
    let tthFind = document.getElementById('valueTTH').value;
    let errorFind = document.getElementById('errorFind');
    let id_user = localStorage.getItem('id');

    if (tthFind.toString().length !== 14) {
        errorFind.innerHTML = 'Должно быть 14 символов!';
        errorFind.style.visibility = 'visible';
    } else {
        if(idToServ === parseInt(tthFind)) return;
        errorFind.style.visibility = 'hidden';
        console.log(' Запрос ')
        addToArr(tthFind);
        // запрос
        fetch('http://localhost/phpTZ0407/index.php', {
            method: "POST",
            body: JSON.stringify({ status: 'addTTH', id_u: id_user, tth: tthFind.toString() }),
            headers: {
                "Content-Type": "application/json"
            }
        }).then(response => { return response.json() }).then(obj => {
            let param = JSON.parse(obj);
            console.log(param.data[0], ' - ответ от сервера')
            idToServ = parseInt(param.data[0].Number);
            document.getElementById('deliveryStatus').innerHTML = 'Статус доставки: ' + param.data[0].Status;
            document.getElementById('statusSent').innerHTML = 'Отправлено: ' + param.data[0].WarehouseRecipient;
            document.getElementById('statusReceived').innerHTML = 'Получено: ' + param.data[0].WarehouseSender;
        })
    }
}

function createLi() {
    let x = document.getElementById("listTTH");
    for (let i = 0; i < arrTHH.length; i++) {
        createLiA(arrTHH[i])
    }
}

function createLiA(text) {
    let list = document.getElementById('listTTH');
    let li = document.createElement('li');
    let a = document.createElement('a');
    li.appendChild(a);
    a.innerHTML = text;
    a.onclick = function () {
        document.getElementById('valueTTH').value = text;
        find();
    }
    list.appendChild(li);
}

function disconnect() {
    localStorage.clear();
    localStorage.setItem('isAvtorization', 'false')
    location.href = 'http://localhost/phpTZ0407/index.html';
}

function check() {
    let isAvtorization = localStorage.getItem('isAvtorization');
    if (isAvtorization === 'false' || isAvtorization === null) {
        localStorage.setItem('isAvtorization', 'false')
        location.href = 'http://localhost/phpTZ0407/index.html';
    } else {

        arrTHH = JSON.parse(localStorage.getItem('arrTTH'));
        if (arrTHH === null) {
            arrTHH = [];
        };
        createLi();
    }
}
check();