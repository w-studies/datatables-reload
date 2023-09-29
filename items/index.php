<?php

require 'connect.php';

$sanitizedSearch = filter_var($_GET['search']['value'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

$start  = filter_input(INPUT_GET, 'start', FILTER_SANITIZE_NUMBER_INT) ?? 0;
$limit  = filter_input(INPUT_GET, 'length', FILTER_SANITIZE_NUMBER_INT) ?? 10;
$select = 'select * from items';
$where  = ' where 1=1 ';
$order  = ' order by created_at desc ';
// busca
if($sanitizedSearch) {
  $escapedSearch = $conn->real_escape_string($sanitizedSearch);

  $where .= ' and item like "%'.$escapedSearch.'%"';
}

if($id) {
  $where .= " and id=$id";
}

// orderna

// monta a query de buscar total
$query = 'select count(id) as total from items'.$where.$order;

try {
  $result = $conn->query($query);
} catch (Exception $e) {
  // devolve json com a mensagem de erro
  exit('<p class="text-danger"><b>ERROR</b>: Falha ao contar o total de registros: '.$sqli->error.'</p><small class="text-secondary">'.__FILE__.' at line: '.__LINE__.'<small>');
}

$total = $result->fetch_row()[0];

// monta a query de busca filtrada
$query = $select.$where.$order."limit $limit offset $start";

try {
  $result = $conn->query($query);
} catch (Exception $e) {
  // devolve json com a mensagem de erro
  exit('<p class="text-danger"><b>ERROR</b>: '.$sqli->error.'</p><small class="text-secondary">'.__FILE__.' at line: '.__LINE__.'<small>');
}

$items = [];

if($conn->error) {
  echo '<pre>$conn->error: ';
  print_r($conn->error);
  echo '</pre>';
} elseif ($result->num_rows) {
  $items = $result->fetch_all(MYSQLI_ASSOC);

  foreach($items as $i => $v) {
    $items[$i]   = array_values($v);

    $items[$i][] = '<a class="btn btn-primary edit m-1" href="'.$v['id'].'">Edit</a>'."<a class='btn btn-danger delete m-1' href='$v[id]'>Del</a>";
  }
}

exit(json_encode([
  'get'             => $query,
  'totalRows'       => $total,
  'recordsTotal'    => $total,  //Quantidade de registros que há no banco de dados
  'recordsFiltered' => $total, //Total de registros quando houver pesquisa
  'data'            => $items,  //Array de dados completo dos dados retornados da tabela
]));
