<?php
	//tipos routes
    // get all tipos
$app->get('/tipos', function ($request, $response, $args) use ($container) {
    $sth = $container->db->prepare("SELECT * FROM tipousuario ORDER BY idTipo");
    $sth->execute();
    $tipos = $sth->fetchAll();
    if(!$tipos)
    {
        $error = array("error" => array("message"=>"No records have been submitted yet."));
        return $container->response->withJson($error, 404);
    }
    return $container->response->withJson($tipos, 200);
})->add(new Auth());

    // Retrieve tipo with id 
$app->get('/tipo/[{id}]', function ($request, $response, $args) use ($container) {
    
    $sth = $container->db->prepare("SELECT * FROM tipousuario WHERE idTipo=:id");
    $sth->bindParam("id", $args['id']);
    $sth->execute();
    $tipo = $sth->fetchObject();
    if(!$tipo)
    {
        $error = array("error" => array("message"=>"Not Found."));
        return $container->response->withJson($error, 404);
    }
    return $container->response->withJson($tipo, 200);
})->add(new Auth());

    // Add a new tipo
$app->post('/tipo', function ($request, $response) use ($container)  {
    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $tipoUsuario = $logado->descricao; //Tipo de usuário logado.

    if(strtolower($tipoUsuario) != 'admin')
    {
        return $this->response->withJson(array("error"=>array("message"=>"Sorry, This feature is only allowed for administrators.")), 403);
    }
    
    $tipo = $request->getParsedBody();

    $tipo = (object) $tipo;

    if($tipo->descricao == null)
    {
        return $response->withJson(array("error"=>array("message"=>"The request data is invalid.")), 400);
    }

    //Verifica se no banco existe um registro com a mesma descrição.
    $sql = "SELECT descricao FROM tipousuario WHERE descricao = :descricao";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("descricao", $tipo->descricao);
    $sth->execute();
    $exists = $sth->fetchObject();

    //Se existir faça:
    if($exists)
    {
        return $this->response->withJson(array("error"=>array("message"=>"A registered record with the reported data already exists.")), 400);
    }

    //Se não existir faça:

    $sql = "INSERT INTO tipousuario (descricao) VALUES (:descricao)";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("descricao", $tipo->descricao);
    $sth->execute();
    $tipo->idTipo = $this->db->lastInsertId(); //Pega o id da inserção
    return $this->response->withJson($tipo, 201); //Retorna o obj ao cliente
})->add(new Auth());

    // DELETE a tipo with given id
$app->delete('/tipo/[{idTipo}]', function ($request, $response, $args) use ($container)  {
    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $tipoUsuario = $logado->descricao; //Tipo de usuário logado.

    if(strtolower($tipoUsuario) != 'admin')
    {
        return $this->response->withJson(array("error"=>array("message"=>"Sorry, This feature is only allowed for administrators.")), 403);
    }
    
    $tipo = (object) array();
    $tipo->idTipo = $args['idTipo'];

    $sth = $this->db->prepare("SELECT idTipo FROM tipousuario WHERE idTipo=:id");
    $sth->bindParam("id", $tipo->idTipo);
    $sth->execute();
    $ret = $sth->fetchObject();

    if(!$ret)
    {
        $error = array("error" => array("message"=>"Not Found."));
        return $this->response->withJson($error, 404);
    }

    $sth = $this->db->prepare("SELECT idPessoaFuncionario FROM funcionario WHERE idTipo=:id");
    $sth->bindParam("id", $tipo->idTipo);
    $sth->execute();
    $ret = $sth->fetchObject();

    if($ret)
    {
        return $this->response->withJson(array("error"=>array("message"=>"Record present in some user. Delete the user first so that you can later delete this record.")), 400);
    }

    $sth = $this->db->prepare("DELETE FROM tipousuario WHERE idTipo=:id");
    $sth->bindParam("id", $tipo->idTipo);
    $sth->execute();
    $success = array("success" => array("message"=>"Record deleted."));
    return $this->response->withJson($success, 200);
})->add(new Auth());

    // Update tipo with given id
$app->put('/tipo/[{idTipo}]', function ($request, $response, $args) use ($container) {

    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $tipoUsuario = $logado->descricao; //Tipo de usuário logado.

    if(strtolower($tipoUsuario) != 'admin')
    {
        return $this->response->withJson(array("error"=>array("message"=>"Sorry, This feature is only allowed for administrators.")), 403);
    }
    
    $tipo = $request->getParsedBody();

    $tipo = (object) $tipo;
    $tipo->idTipo = $args["idTipo"];

    if($tipo->descricao == null)
    {
        return $response->withJson(array("error"=>array("message"=>"The request data is invalid.")), 400);
    }

    $sql = "SELECT idTipo FROM tipousuario WHERE idTipo = :id";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("id", $tipo->idTipo);
    $sth->execute();
    $exists = $sth->fetchObject();

    if(!$exists)
    {
        return $this->response->withJson(array("error"=>array("message"=>"Not found.")), 404);
    }

    $sql = "SELECT descricao FROM tipousuario WHERE descricao = :descricao";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("descricao", $tipo->descricao);
    $sth->execute();
    $exists = $sth->fetchObject();

    if($exists)
    {
        return $this->response->withJson(array("error"=>array("message"=>"A registered record with the reported data already exists.")), 400);
    }

    $sql = "UPDATE tipousuario SET descricao=:descricao WHERE idTipo=:id";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("id", $tipo->idTipo);
    $sth->bindParam("descricao", $tipo->descricao);
    $sth->execute();
    return $this->response->withJson($tipo, 200); 
})->add(new Auth());

    // Search for tipo with given search teram in their name
$app->get('/tipo/busca/[{query}]', function ($request, $response, $args) use ($container)  {
 $sth = $this->db->prepare("SELECT * FROM tipousuario WHERE descricao LIKE :query ORDER BY idTipo");
 $query = "%".$args['query']."%";
 $sth->bindParam("query", $query);
 $sth->execute();
 $tipos = $sth->fetchAll();
 return $this->response->withJson($tipos, 200);
})->add(new Auth());

?>