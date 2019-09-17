<?php

	//Bairros routes

    // get all bairros
$app->get('/bairros', function ($request, $response, $args) use ($container) {
    $sth = $container->db->prepare("SELECT * FROM bairro");
    $sth->execute();
    $bairros = $sth->fetchAll();
    if(!$bairros)
    {
        $error = array("error" => array("message"=>"No records have been submitted yet."));
        return $container->response->withJson($error, 404);
    }
    return $container->response->withJson($bairros, 200);
})->add(new Auth());

    // Retrieve bairro with id 
$app->get('/bairro/[{id}]', function ($request, $response, $args) use ($container) {
    
    $sth = $container->db->prepare("SELECT * FROM bairro WHERE idbairro=:id");
    $sth->bindParam("id", $args['id']);
    $sth->execute();
    $bairro = $sth->fetchObject();
    if(!$bairro)
    {
        $error = array("error" => array("message"=>"Not Found."));
        return $container->response->withJson($error, 404);
    }
    return $container->response->withJson($bairro, 200);
})->add(new Auth());

    // Add a new bairro
$app->post('/bairro', function ($request, $response) use ($container)  {
    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $tipoUsuario = $logado->descricao; //Tipo de usuário logado.

    if(strtolower($tipoUsuario) != 'admin')
    {
        return $this->response->withJson(array("error"=>array("message"=>"Sorry, This feature is only allowed for administrators.")), 403);
    }
    
    $bairro = $request->getParsedBody();

    $bairro = (object) $bairro;

    if($bairro->descricao == null)
    {
        return $response->withJson(array("error"=>array("message"=>"The request data is invalid.")), 400);
    }

    $sql = "SELECT descricao FROM bairro WHERE descricao = :descricao";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("descricao", $bairro->descricao);
    $sth->execute();
    $exists = $sth->fetchObject();

    if($exists)
    {
        return $this->response->withJson(array("error"=>array("message"=>"A registered record with the reported data already exists.")), 400);
    }

    $sql = "INSERT INTO bairro (descricao) VALUES (:descricao)";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("descricao", $bairro->descricao);
    $sth->execute();
    $bairro->idbairro = $this->db->lastInsertId();
    return $this->response->withJson($bairro, 201);
})->add(new Auth());

    // DELETE a bairro with given id
$app->delete('/bairro/[{idbairro}]', function ($request, $response, $args) use ($container)  {
    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $tipoUsuario = $logado->descricao; //Tipo de usuário logado.

    if(strtolower($tipoUsuario) != 'admin')
    {
        return $this->response->withJson(array("error"=>array("message"=>"Sorry, This feature is only allowed for administrators.")), 403);
    }
    
    $bairro = (object) $bairro;
    $bairro->idbairro = $args['idbairro'];

    $sth = $this->db->prepare("SELECT idbairro FROM bairro WHERE idbairro=:id");
    $sth->bindParam("id", $bairro->idbairro);
    $sth->execute();
    $ret = $sth->fetchObject();

    if(!$ret)
    {
        $error = array("error" => array("message"=>"Not Found.", "status" => 404));
        return $this->response->withJson($error, 404);
    }
    try{
        $sth = $this->db->prepare("DELETE FROM bairro WHERE idbairro=:id");
        $sth->bindParam("id", $bairro->idbairro);
        $sth->execute();
        $success = array("success" => array("message"=>"Record deleted."));
        return $this->response->withJson($success, 200);
    }catch(Exception $e){
        return $this->response->withJson(array("error"=>array("message"=>"Record present in some caixa de atendimento or cliente. Delete the caixa de atendimento first, or cliente, so that you can later delete this record.")), 400);
    }
    
})->add(new Auth());

    // Update bairro with given id
$app->put('/bairro/[{idbairro}]', function ($request, $response, $args) use ($container) {

    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $tipoUsuario = $logado->descricao; //Tipo de usuário logado.

    if(strtolower($tipoUsuario) != 'admin')
    {
        return $this->response->withJson(array("error"=>array("message"=>"Sorry, This feature is only allowed for administrators.")), 403);
    }
    
    $bairro = $request->getParsedBody();

    $bairro = (object) $bairro;
    $bairro->idbairro = $args["idbairro"];

    if($bairro->descricao == null)
    {
        return $response->withJson(array("error"=>array("message"=>"The request data is invalid.")), 400);
    }

    $sql = "SELECT descricao FROM bairro WHERE descricao = :descricao";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("descricao", $bairro->descricao);
    $sth->execute();
    $exists = $sth->fetchObject();

    if($exists)
    {
        return $this->response->withJson(array("error"=>array("message"=>"A registered record with the reported data already exists.")), 400);
    }

    $sql = "UPDATE bairro SET descricao=:descricao WHERE idbairro=:id";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("id", $bairro->idbairro);
    $sth->bindParam("descricao", $bairro->descricao);
    $sth->execute();
    return $this->response->withJson($bairro, 200); 
})->add(new Auth());

    // Search for bairro with given search teram in their name
$app->get('/bairro/search/[{query}]', function ($request, $response, $args) use ($container)  {
 $sth = $this->db->prepare("SELECT * FROM bairro WHERE descricao LIKE :query ORDER BY saidas");
 $query = "%".$args['query']."%";
 $sth->bindParam("query", $query);
 $sth->execute();
 $bairros = $sth->fetchAll();
 return $this->response->withJson($bairros, 200);
})->add(new Auth());
?>