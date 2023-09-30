<?php

// configura o php para exibir erros
// REMOVA/COMENTE ESSA LINHA QUANDO ENVIAR PARA PRODUÇÃO
error_reporting(E_ALL);

// defina as variáveis de conexão
$host     = 'localhost';
$user     = 'root';
$pass     = 'my-secret-pw';
$database = 'datatables';

// estabelecer conexão:
try {
  $sqli = new mysqli($host, $user, $pass);
} catch(Exception $e) {
  exit('<p><b>Falha na conexão com o database</b>: '.$e->getMessage().'</p>');
}

// definir o padrão de caracteres
if (!$sqli->set_charset('utf8')) {
  // se não conseguir definir o padrão de caracteres, exibe o padrão disponível
  exit("<p class='text-danger'>Seu charset não é utf8, chefe!<br>$sqli->character_set_name()</p>");
}

// selecionar/abrir o banco de dados para trabalhar
if (!$sqli->select_db($database)) {
  // se o banco de dados não for encontrado
  exit("<p class='text-danger'>Banco de dados não encontrado, chefe!</p>");
}

return $sqli;
