<?php

$loginUser = '';
$passwordUser = '';
$data = json_decode(file_get_contents('php://input'), true);
$isMySQLOpen = false;

switch ($data['status']) {
    case "login":
        // запрос в bd на проверку логина
        $loginUser = $data['login'];
        $passwordUser = $data['password'];
        verificationUser($loginUser, $passwordUser);
        break;
    case 'registration':
        // если пользователь я таким ником уже есть/тоошибка
        $loginUser = $data['login'];
        $passwordUser = $data['password'];
        registrationUser($loginUser, $passwordUser);
        // если нет/то регестрируем нового
        break;

    case 'addTTH':
        // requestToApi();
        addTTH($data['id_u'], $data['tth']);
        break;

    default:
        echo 'error';
        break;
}
function connectFun()
{
    global $conn;
    global $isMySQLOpen;
    $servername = "localhost";
    $username = "root";
    $password = "";
    // $conn = new PDO("mysql:host=$servername;dbname=test", $username, $password);
    try {
        $isMySQLOpen = true;
        $conn = new PDO("mysql:host=$servername;dbname=test", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        // print_r("Connection failed: " . $e->getMessage());
        $isMySQLOpen = false;
    }
}

// ---+++--- проверка, есть ли такой пользователь ---+++---
function verificationUser($login, $pass)
{
    // запрос к бд
    global $conn;
    global $isMySQLOpen;
    connectFun();

    if ($isMySQLOpen) {
        $sql = 'SELECT login, password, id_u FROM users';
        $arrayTTH = [];
        $id_u = '';
        $tth = [];
        $isUserAvtorization = false;
        foreach ($conn->query($sql) as $row) {
            if ($row['login'] == $login && $row['password'] == $pass) {
                $isUserAvtorization = true;
                $id_u = $row['id_u'];
            }
        };
        if ($isUserAvtorization) {
            // если такой пользователь есть то в json нужно добавить все его TTH
            $sql = 'SELECT user_id, tth FROM documents';
            foreach ($conn->query($sql) as $row) {
                if ($row['user_id'] == $id_u) {
                    array_push($tth, $row['tth']);
                }
            };
            $json = '{"isUserAvtorization":true,"arrTTH":[' . implode(",", $tth) . '],"login":"' . $login . '", "id_u": "' . $id_u . '"}';
        } else {
            $json = '{"isUserAvtorization":false}';
        }
      
    } else {
        $json = '{"mySQL":false}';
    }
    echo json_encode($json);
}
// ---+++--- end ---+++---

// ---///--- регистрация нового пользователя в bd ---///---
function registrationUser($login, $pass)
{
    connectFun();
    global $conn;

    $sql = 'SELECT login, id_u FROM users';
    $isSuchUser = false;
    $id_u = '';
    foreach ($conn->query($sql) as $row) {
        if ($row['login'] == $login) {
            //'такой пользователь есть';
            $isSuchUser = true;
            $json = '{"isUserAvtorization":false, "param":"UserIsAlreadyDB"}';
            $id_u = $row['id_u'];
        }
    };

    if (!$isSuchUser) {
        $sql = 'INSERT INTO users (login, password) VALUE ("' . $login . '","' . $pass . '")';
        $conn->query($sql);
        $sql = 'SELECT login, id_u FROM users';
        foreach ($conn->query($sql) as $row) {
            if ($row['login'] == $login) {
                $id_u = $row['id_u'];
                $json = '{"isUserAvtorization":true,"login":"' . $login . '", "id_u": "' . $id_u . '"}';
            }
        };
    }
    echo json_encode($json);
}
// ---///--- end ---///---
