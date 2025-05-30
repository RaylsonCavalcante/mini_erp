<?php


    $host = 'localhost';
    $usuario = 'root';
    $senha = ''; 
    $banco = 'mini_erp';

    $connect = new mysqli($host, $usuario, $senha, $banco);

    // Verifica se ocorreu algum erro
    if ($connect->connect_error) {
        die("Falha na conexão: " . $conn->connect_error);
    }
