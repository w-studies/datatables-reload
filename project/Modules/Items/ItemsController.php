<?php

declare(strict_types=1);

namespace Modules\Items;

use Modules\Core\Controller;

class ItemsController extends Controller
{
  public function __construct()
  {
    parent::__construct();
  }

  public function index(string $id = null)
  {

    $sanitizedSearch = filter_var($_GET['search']['value'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);

    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);

    $start  = filter_input(INPUT_GET, 'start', FILTER_SANITIZE_NUMBER_INT) ?? 0;
    $limit  = filter_input(INPUT_GET, 'length', FILTER_SANITIZE_NUMBER_INT) ?? 10;
    $select = 'select * from items';
    $where  = ' where 1=1 ';
    $order  = ' order by created_at desc ';
    // busca
    if ($sanitizedSearch) {
      $escapedSearch = $this->conn->real_escape_string($sanitizedSearch);

      $where .= ' and item like "%'.$escapedSearch.'%"';
    }

    if ($id) {
      $where .= " and id=$id";
    }

    // orderna

    // monta a query de buscar total
    $query = 'select count(id) as total from items'.$where.$order;

    try {
      $result = $this->conn->query($query);
    } catch (\Exception $e) {
      // devolve json com a mensagem de erro
      exit('<p class="text-danger"><b>ERROR</b>: Falha ao contar o total de registros: '.$this->conn->error.'</p><small class="text-secondary">'.__FILE__.' at line: '.__LINE__.'<small>');
    }

    $total = $result->fetch_row()[0];

    // monta a query de busca filtrada
    $query = $select.$where.$order."limit $limit offset $start";

    try {
      $result = $this->conn->query($query);
    } catch (\Exception $e) {
      // devolve json com a mensagem de erro
      exit('<p class="text-danger"><b>ERROR</b>: '.$this->conn->error.'</p><small class="text-secondary">'.__FILE__.' at line: '.__LINE__.'<small>');
    }

    $items = [];

    if ($this->conn->error) {
      echo '<pre>$this->conn->error: ';
      print_r($this->conn->error);
      echo '</pre>';
    } elseif ($result->num_rows) {
      $items = $result->fetch_all(MYSQLI_ASSOC);

      foreach ($items as $i => $v) {
        $items[$i]   = array_values($v);

        $items[$i][] = '<a class="btn btn-primary edit m-1" href="'.$v['id'].'">Edit</a>'."<a class='btn btn-danger delete m-1' href='$v[id]'>Del</a>";
      }
    }
    // clear the old headers
    header_remove();
    // set the actual code
    http_response_code(200);

    // treat this as json
    header('Content-Type: application/json');
    exit(json_encode([
      'get'             => $_GET,
      'totalRows'       => $total,
      'recordsTotal'    => $total,  //Quantidade de registros que há no banco de dados
      'recordsFiltered' => $total, //Total de registros quando houver pesquisa
      'data'            => $items,  //Array de dados completo dos dados retornados da tabela
    ]));

  }

  public function save(string $id = null)
  {
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

    $itemToInsert = $this->conn->real_escape_string($sanitizedItem);

    $query .= "items set item = '$itemToInsert'$where";

    // verifica se houve alguma falha na execução da query
    try {
      // executa a query
      $this->conn->query($query);
    } catch (\Exception $e) {
      // devolve json com a mensagem de erro
      exit(json_encode([
        'type'    => 'danger',
        'data'    => ['item' => $e->getMessage()],
        'message' => '<p class="text-danger"><b>ERROR</b>: '.$this->conn->error.'</p><small class="text-secondary">'.__FILE__.' at line: '.__LINE__.'<small>',
      ]));
    }

    // se não houve falha
    if ($this->conn->affected_rows) {
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

  }
}
