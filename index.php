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