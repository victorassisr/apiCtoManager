<?php

	//Spliters routes

    // get all spliters
$app->get('/spliters', function ($request, $response, $args) use ($container) {
    $sth = $container->db->prepare("SELECT * FROM spliter ORDER BY saidas");
    $sth->execute();
    $spliters = $sth->fetchAll();
    if(!$spliters)
    {
        $error = array("error" => array("message"=>"No records have been submitted yet."));
        return $container->response->withJson($error, 404);
    }
    return $container->response->withJson($spliters, 200);
})->add(new Auth());

    // Retrieve spliter with id 
$app->get('/spliter/[{id}]', function ($request, $response, $args) use ($container) {
    
    $sth = $container->db->prepare("SELECT * FROM spliter WHERE idSpliter=:id");
    $sth->bindParam("id", $args['id']);
    $sth->execute();
    $spliter = $sth->fetchObject();
    if(!$spliter)
    {
        $error = array("error" => array("message"=>"Not Found."));
        return $container->response->withJson($error, 404);
    }
    return $container->response->withJson($spliter, 200);
})->add(new Auth());

    // Add a new spliter
$app->post('/spliter', function ($request, $response) use ($container)  {
    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $tipoUsuario = $logado->descricao; //Tipo de usuário logado.

    if(strtolower($tipoUsuario) != 'admin')
    {
        return $this->response->withJson(array("error"=>array("message"=>"Sorry, This feature is only allowed for administrators.")), 403);
    }
    
    $spliter = $request->getParsedBody();

    $spliter = (object) $spliter;

    if($spliter->saidas == null || $spliter->descricao == null)
    {
        return $response->withJson(array("error"=>array("message"=>"The request data is invalid.")), 400);
    }

    $sql = "SELECT saidas, descricao FROM spliter WHERE saidas = :saidas OR descricao = :descricao";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("saidas", $spliter->saidas);
    $sth->bindParam("descricao", $spliter->descricao);
    $sth->execute();
    $exists = $sth->fetchObject();

    if($exists)
    {
        return $this->response->withJson(array("error"=>array("message"=>"A registered record with the reported data already exists.")), 400);
    }

    $sql = "INSERT INTO spliter (saidas, descricao) VALUES (:saidas, :descricao)";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("saidas", $spliter->saidas);
    $sth->bindParam("descricao", $spliter->descricao);
    $sth->execute();
    $spliter->idSpliter = $this->db->lastInsertId();
    return $this->response->withJson($spliter, 201);
})->add(new Auth());

    // DELETE a spliter with given id
$app->delete('/spliter/[{idSpliter}]', function ($request, $response, $args) use ($container)  {
    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $tipoUsuario = $logado->descricao; //Tipo de usuário logado.

    if(strtolower($tipoUsuario) != 'admin')
    {
        return $this->response->withJson(array("error"=>array("message"=>"Sorry, This feature is only allowed for administrators.")), 403);
    }
    
    $spliter = (object) $spliter;
    $spliter->idSpliter = $args['idSpliter'];

    $sth = $this->db->prepare("SELECT idSpliter FROM spliter WHERE idSpliter=:id");
    $sth->bindParam("id", $spliter->idSpliter);
    $sth->execute();
    $ret = $sth->fetchObject();

    if(!$ret)
    {
        $error = array("error" => array("message"=>"Not Found.", "status" => 404));
        return $this->response->withJson($error, 404);
    }
    try{
        $sth = $this->db->prepare("DELETE FROM spliter WHERE idSpliter=:id");
        $sth->bindParam("id", $spliter->idSpliter);
        $sth->execute();
        $success = array("success" => array("message"=>"Record deleted."));
        return $this->response->withJson($success, 200);
    }catch(Exception $e){
        return $this->response->withJson(array("error"=>array("message"=>"Record present in some caixa de atendimento. Delete the caixa de atendimento first so that you can later delete this record.")), 400);
    }
    
})->add(new Auth());

    // Update spliter with given id
$app->put('/spliter/[{idSpliter}]', function ($request, $response, $args) use ($container) {

    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $tipoUsuario = $logado->descricao; //Tipo de usuário logado.

    if(strtolower($tipoUsuario) != 'admin')
    {
        return $this->response->withJson(array("error"=>array("message"=>"Sorry, This feature is only allowed for administrators.")), 403);
    }
    
    $spliter = $request->getParsedBody();

    $spliter = (object) $spliter;
    $spliter->idSpliter = $args["idSpliter"];

    if($spliter->saidas == null || $spliter->descricao == null)
    {
        return $response->withJson(array("error"=>array("message"=>"The request data is invalid.")), 400);
    }

    $sql = "SELECT saidas, descricao FROM spliter WHERE (saidas = :saidas OR descricao = :descricao) AND idSpliter <> :idSpliter";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("saidas", $spliter->saidas);
    $sth->bindParam("descricao", $spliter->descricao);
    $sth->bindParam("idSpliter", $spliter->idSpliter);
    $sth->execute();
    $exists = $sth->fetchObject();

    if($exists)
    {
        //return $this->response->withJson($exists);
        return $this->response->withJson(array("error"=>array("message"=>"A registered record with the reported data already exists.")), 400);
    }

    $sql = "UPDATE spliter SET saidas=:saidas, descricao=:descricao WHERE idSpliter=:id";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("id", $spliter->idSpliter);
    $sth->bindParam("saidas", $spliter->saidas);
    $sth->bindParam("descricao", $spliter->descricao);
    $sth->execute();
    return $this->response->withJson($spliter, 200); 
})->add(new Auth());

    // Search for spliter with given search teram in their name
$app->get('/spliter/search/[{query}]', function ($request, $response, $args) use ($container)  {
 $sth = $this->db->prepare("SELECT * FROM spliter WHERE descricao LIKE :query ORDER BY saidas");
 $query = "%".$args['query']."%";
 $sth->bindParam("query", $query);
 $sth->execute();
 $spliters = $sth->fetchAll();
 return $this->response->withJson($spliters, 200);
})->add(new Auth());
?>