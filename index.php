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

// ---***--- добавление tth в bd ---***---
$isTTHInBD = false;

function findTTHInBD($id, $tth)
{
    global $conn;
    global $isTTHInBD;
    $sql = 'SELECT user_id, tth FROM documents';
    foreach ($conn->query($sql) as $row) {
        if ($row['user_id'] == $id && $row['tth'] == $tth) {
            $isTTHInBD = true;
        }
    };
    return $isTTHInBD;
}

function addTTH($id, $tth)
{
    connectFun();
    global $conn;

    // проверка на то, есть ли он в bd
    // если нет то аддБД + запрос
    // если есть то просто запрос
    if (findTTHInBD($id, $tth)) {
        // echo 'true';
        //если есть то просто запрос
        echo requestToApi($tth);
    } else {
        // echo 'false';
        $sql = 'INSERT INTO documents (tth, user_id) VALUE ("' . $tth . '","' . $id . '")';
        $conn->query($sql);
        echo requestToApi($tth);
        // если нет то аддБД + запрос
    }

    $json = '{"param":"addTTH"}';
    $sql = 'INSERT INTO documents (tth, user_id) VALUE ("' . $tth . '","' . $id . '")';
}
// ---***--- end ---***---

// ___ novaposhta Api ___
// код скопирывал с postman*
function requestToApi($tth)
{

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.novaposhta.ua/v2.0/json/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "{\r\n    \"apiKey\": \"5923076292188b37a21ab7aba49e37dd\",\r\n    \"modelName\": \"TrackingDocument\",\r\n    \"calledMethod\": \"getStatusDocuments\",\r\n    \"methodProperties\": {\r\n        \"Documents\": [\r\n            {\r\n                \"DocumentNumber\": \"" . $tth . "\",\r\n                \"Phone\":\"\"\r\n            }\r\n        ]\r\n    }\r\n    \r\n}",
        CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "content-type: application/json",
            "postman-token: 160eb9d0-4fa0-9c09-5a77-5f0480db1404"
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);
    echo json_encode($response);
}
// ___ end ___