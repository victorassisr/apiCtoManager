<?php

	//Spliters routes

    // get all spliters
$app->get('/spliters', function ($request, $response, $args) use ($container) {
    $sth = $container->db->prepare("SELECT * FROM spliter ORDER BY quantidadePortas");
    $sth->execute();
    $spliters = $sth->fetchAll();
    return $container->response->withJson($spliters, 200);
})->add(new Auth());

    // Retrieve spliter with id 
$app->get('/spliters/[{id}]', function ($request, $response, $args) use ($container) {
    $sth = $container->db->prepare("SELECT * FROM spliter WHERE idSpliter=:id");
    $sth->bindParam("id", $args['id']);
    $sth->execute();
    $spliter = $sth->fetchObject();
    if(!$spliter)
    {
        $error = array("error" => array("message"=>"Not Found.", "status" => 404));
        return $container->response->withJson($error, 404);
    }
    return $container->response->withJson($spliter, 200);
})->add(new Auth());

    // Add a new spliter
$app->post('/spliters', function ($request, $response) use ($container)  {
    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $tipoUsuario = $logado->tipoUser->descricao; //Tipo de usuário logado.

    if($tipoUsuario != 'Admin')
    {
        return $this->response->withJson(array("error"=>array("message"=>"Sorry, This feature is only allowed for administrators.")), 403);
    }
    
    $spliter = $request->getParsedBody();

    if($spliter != null)
    {
        $spliter = (object) $spliter;
    }
    else
    {
        $spliter = array("quantidadePortas"=>null);
        $spliter = (object) $spliter;
    }

    if($spliter->quantidadePortas == null)
    {
        return $response->withJson(array("error"=>array("message"=>"The request data is invalid.")), 400);
    }

    $sql = "INSERT INTO spliter (quantidadePortas) VALUES (:quantidadePortas)";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("quantidadePortas", $spliter['quantidadePortas']);
    $sth->execute();
    $spliter['id'] = $this->db->lastInsertId();
    return $this->response->withJson($spliter, 201);
})->add(new Auth());

    // DELETE a spliter with given id
$app->delete('/spliters/[{id}]', function ($request, $response, $args) use ($container)  {
    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $tipoUsuario = $logado->tipoUser->descricao; //Tipo de usuário logado.

    if($tipoUsuario != 'Admin')
    {
        return $this->response->withJson(array("error"=>array("message"=>"Sorry, This feature is only allowed for administrators.")), 403);
    }
    
    $spliter = $args['idSpliter'];

    if($spliter != null)
    {
        $spliter = (object) $spliter;
    }
    else
    {
        $spliter = array("idSpliter"=>null);
        $spliter = (object) $spliter;
    }

    if($spliter->idSpliter == null)
    {
        return $response->withJson(array("error"=>array("message"=>"The request data is invalid.")), 400);
    }

    $sth = $this->db->prepare("SELECT idSpliter FROM spliter WHERE idSpliter=:id");
    $sth->bindParam("id", $spliter->idSpliter);
    $sth->execute();
    $ret = $sth->fetchObject();

    if(!$ret)
    {
        $error = array("error" => array("message"=>"Not Found.", "status" => 404));
        return $this->response->withJson($error, 404);
    }

    $sth = $this->db->prepare("DELETE FROM spliter WHERE idSpliter=:id");
    $sth->bindParam("id", $spliter->idSpliter);
    $sth->execute();
    $success = array("success" => array("message"=>"Record deleted.", "status" => 200));
    return $this->response->withJson($success, 200);
})->add(new Auth());

    // Update spliter with given id
$app->put('/spliters/[{id}]', function ($request, $response, $args) use ($container) {

    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $tipoUsuario = $logado->tipoUser->descricao; //Tipo de usuário logado.

    if($tipoUsuario != 'Admin')
    {
        return $this->response->withJson(array("error"=>array("message"=>"Sorry, This feature is only allowed for administrators.")), 403);
    }
    
    $spliter = $request->getParsedBody();

    if($spliter != null)
    {
        $spliter = (object) $spliter;
    }
    else
    {
        $spliter = array(
            "idSpliter"=>null,
            "quantidadePortas"=>null
        );
        $spliter = (object) $spliter;
    }

    if($spliter->idSpliter == null || $spliter->quantidadePortas == null)
    {
        return $response->withJson(array("error"=>array("message"=>"The request data is invalid.")), 400);
    }

    $sql = "UPDATE spliter SET quantidadePortas=:quantidadePortas WHERE idSpliter=:id";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("id", $spliter->idSpliter);
    $sth->bindParam("quantidadePortas", $spliter->quantidadePortas);
    $sth->execute();
    return $this->response->withJson($spliter, 200);
})->add(new Auth());

    // Search for spliter with given search teram in their name
$app->get('/spliters/search/[{query}]', function ($request, $response, $args) use ($container)  {
 $sth = $this->db->prepare("SELECT * FROM spliter WHERE quantidadePortas LIKE :query ORDER BY quantidadePortas");
 $query = "%".$args['query']."%";
 $sth->bindParam("query", $query);
 $sth->execute();
 $spliters = $sth->fetchAll();
 return $this->response->withJson($spliters, 200);
})->add(new Auth());
?>