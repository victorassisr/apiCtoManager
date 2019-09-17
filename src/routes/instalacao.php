<?php

	//instalacaos routes

    // get all instalacaos
$app->get('/instalacoes', function ($request, $response, $args) use ($container) {
    $sth = $container->db->prepare("SELECT * FROM instalacao");
    $sth->execute();
    $instalacaos = $sth->fetchAll();
    if(!$instalacaos)
    {
        $error = array("error" => array("message"=>"No records have been submitted yet."));
        return $container->response->withJson($error, 404);
    }
    return $container->response->withJson($instalacaos, 200);
})->add(new Auth());

    // Retrieve instalacao with id 
$app->get('/instalacao/[{id}]', function ($request, $response, $args) use ($container) {
    
    $sth = $container->db->prepare("SELECT * FROM instalacao WHERE idinstalacao=:id");
    $sth->bindParam("id", $args['id']);
    $sth->execute();
    $instalacao = $sth->fetchObject();
    if(!$instalacao)
    {
        $error = array("error" => array("message"=>"Not Found."));
        return $container->response->withJson($error, 404);
    }
    return $container->response->withJson($instalacao, 200);
})->add(new Auth());

    // Add a new instalacao
$app->post('/instalacao', function ($request, $response) use ($container)  {
    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $tipoUsuario = $logado->descricao; //Tipo de usuário logado.

    if(strtolower($tipoUsuario) != 'admin')
    {
        return $this->response->withJson(array("error"=>array("message"=>"Sorry, This feature is only allowed for administrators.")), 403);
    }
    
    $instalacao = $request->getParsedBody();

    $instalacao = (object) $instalacao;

    if($instalacao->descricao == null)
    {
        return $response->withJson(array("error"=>array("message"=>"The request data is invalid.")), 400);
    }

    $sql = "SELECT descricao FROM instalacao WHERE descricao = :descricao";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("descricao", $instalacao->descricao);
    $sth->execute();
    $exists = $sth->fetchObject();

    if($exists)
    {
        return $this->response->withJson(array("error"=>array("message"=>"A registered record with the reported data already exists.")), 400);
    }

    $sql = "INSERT INTO instalacao (descricao) VALUES (:descricao)";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("descricao", $instalacao->descricao);
    $sth->execute();
    $instalacao->idinstalacao = $this->db->lastInsertId();
    return $this->response->withJson($instalacao, 201);
})->add(new Auth());

    // DELETE a instalacao with given id
$app->delete('/instalacao/[{idinstalacao}]', function ($request, $response, $args) use ($container)  {
    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $tipoUsuario = $logado->descricao; //Tipo de usuário logado.

    if(strtolower($tipoUsuario) != 'admin')
    {
        return $this->response->withJson(array("error"=>array("message"=>"Sorry, This feature is only allowed for administrators.")), 403);
    }
    
    $instalacao = (object) $instalacao;
    $instalacao->idinstalacao = $args['idinstalacao'];

    $sth = $this->db->prepare("SELECT idinstalacao FROM instalacao WHERE idinstalacao=:id");
    $sth->bindParam("id", $instalacao->idinstalacao);
    $sth->execute();
    $ret = $sth->fetchObject();

    if(!$ret)
    {
        $error = array("error" => array("message"=>"Not Found.", "status" => 404));
        return $this->response->withJson($error, 404);
    }
    try{
        $sth = $this->db->prepare("DELETE FROM instalacao WHERE idinstalacao=:id");
        $sth->bindParam("id", $instalacao->idinstalacao);
        $sth->execute();
        $success = array("success" => array("message"=>"Record deleted."));
        return $this->response->withJson($success, 200);
    }catch(Exception $e){
        return $this->response->withJson(array("error"=>array("message"=>"Record present in some caixa de atendimento or cliente. Delete the caixa de atendimento first, or cliente, so that you can later delete this record.")), 400);
    }
    
})->add(new Auth());

    // Update instalacao with given id
$app->put('/instalacao/[{idinstalacao}]', function ($request, $response, $args) use ($container) {

    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $tipoUsuario = $logado->descricao; //Tipo de usuário logado.

    if(strtolower($tipoUsuario) != 'admin')
    {
        return $this->response->withJson(array("error"=>array("message"=>"Sorry, This feature is only allowed for administrators.")), 403);
    }
    
    $instalacao = $request->getParsedBody();

    $instalacao = (object) $instalacao;
    $instalacao->idinstalacao = $args["idinstalacao"];

    if($instalacao->descricao == null)
    {
        return $response->withJson(array("error"=>array("message"=>"The request data is invalid.")), 400);
    }

    $sql = "SELECT descricao FROM instalacao WHERE descricao = :descricao";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("descricao", $instalacao->descricao);
    $sth->execute();
    $exists = $sth->fetchObject();

    if($exists)
    {
        return $this->response->withJson(array("error"=>array("message"=>"A registered record with the reported data already exists.")), 400);
    }

    $sql = "UPDATE instalacao SET descricao=:descricao WHERE idinstalacao=:id";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("id", $instalacao->idinstalacao);
    $sth->bindParam("descricao", $instalacao->descricao);
    $sth->execute();
    return $this->response->withJson($instalacao, 200); 
})->add(new Auth());

    // Search for instalacao with given search teram in their name
$app->get('/instalacao/search/[{query}]', function ($request, $response, $args) use ($container)  {
 $sth = $this->db->prepare("SELECT * FROM instalacao WHERE descricao LIKE :query ORDER BY saidas");
 $query = "%".$args['query']."%";
 $sth->bindParam("query", $query);
 $sth->execute();
 $instalacaos = $sth->fetchAll();
 return $this->response->withJson($instalacaos, 200);
})->add(new Auth());
?>