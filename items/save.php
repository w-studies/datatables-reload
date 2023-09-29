<?php

require 'connect.php';

$sanitizedItem = filter_input(INPUT_POST, 'item', FILTER_SANITIZE_SPECIAL_CHARS);
$id            = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

$query  = 'insert into ';
$where  = '';
$action = 'inserido';

if ($id) {
  $query  = 'update ';
  $where  = ' where id = '.(int) $id;
  $action = 'atualizado';
}

$itemToInsert = $conn->real_escape_string($sanitizedItem);

$query .= "items set item = '$itemToInsert'$where";

// verifica se houve alguma falha na execução da query
try {
  // executa a query
  $sqli->query($query);
} catch (Exception $e) {
  // devolve json com a mensagem de erro
  exit(json_encode([
    'type'    => 'danger',
    'data'    => ['item' => $e->getMessage()],
    'message' => '<p class="text-danger"><b>ERROR</b>: '.$sqli->error.'</p><small class="text-secondary">'.__FILE__.' at line: '.__LINE__.'<small>',
  ]));
}

// se não houve falha
if ($sqli->affected_rows) {
  // devolve mensagem de sucesso
  exit(json_encode([
    'type'    => 'success',
    'message' => 'Registro '.$action.' com sucesso, chefe!',
  ]));
}
// devolve json com a mensagem de erro
exit(json_encode([
  'type' => 'info',
  'data' => ['item' => 'Nada foi alterado, chefe!'],
]));
