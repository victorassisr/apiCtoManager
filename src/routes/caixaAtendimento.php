<?php

	//Caixa atendimentos routes

    // get all ctos
$app->get('/ctos', function ($request, $response, $args) use ($container) {
    $sth = $container->db->prepare("SELECT * FROM caixaatendimento");
    $sth->execute();
    $ctos = $sth->fetchAll();
    if(!$ctos)
    {
        $error = array("error" => array("message"=>"No records have been submitted yet."));
        return $container->response->withJson($error, 404);
    }

    $tamanho = count($ctos);

    for($i=0; $i<$tamanho; $i++){

        //Infos de bairro
        $sql = $container->db->prepare("SELECT descricao from bairro where idbairro = '".$ctos[$i]["idBairro"]."'");
        $sql->execute();
        $temp = $sql->fetchAll();
        $bairro["idBairro"] = $ctos[$i]["idBairro"];
        $bairro["descricao"] = $temp[0]["descricao"];
        unset($ctos[$i]["idBairro"]);
        $ctos[$i]["bairro"] = $bairro;

        //Infos de Spliter
        $sql = $container->db->prepare("SELECT saidas, descricao from spliter where idspliter = '".$ctos[$i]["idSpliter"]."'");
        $sql->execute();
        $temp = $sql->fetchAll();
        $spliter["idBairro"] = $ctos[$i]["idSpliter"];
        $spliter["saidas"] = $temp[0]["saidas"];
        $spliter["descricao"] = $temp[0]["descricao"];
        unset($ctos[$i]["idSpliter"]);
        $ctos[$i]["spliter"] = $spliter;
    }

    return $container->response->withJson($ctos, 200);
})->add(new Auth());

// Retrieve cto with id 
$app->get('/cto/[{id}]', function ($request, $response, $args) use ($container) {
    
    $sth = $container->db->prepare("SELECT * FROM caixaatendimento WHERE idCaixa=:id");
    $sth->bindParam("id", $args['id']);
    $sth->execute();
    $cto = $sth->fetchAll();
    if(!$cto)
    {
        $error = array("error" => array("message"=>"Not Found."));
        return $container->response->withJson($error, 404);
    }

    //Infos de bairro
    $sql = $container->db->prepare("SELECT descricao from bairro where idBairro = '".$cto[0]["idBairro"]."'");
        $sql->execute();
        $temp = $sql->fetchAll();
        $bairro["idBairro"] = $cto[0]["idBairro"];
        $bairro["descricao"] = $temp[0]["descricao"];
        unset($cto[0]["idBairro"]);
        $cto[0]["bairro"] = $bairro;

        //Infos de Spliter
        $sql = $container->db->prepare("SELECT saidas, descricao from spliter where idSpliter = '".$cto[0]["idSpliter"]."'");
        $sql->execute();
        $temp = $sql->fetchAll();
        $spliter["idSpliter"] = $cto[0]["idSpliter"];
        $spliter["saidas"] = $temp[0]["saidas"];
        $spliter["descricao"] = $temp[0]["descricao"];
        unset($cto[0]["idSpliter"]);
        $cto[0]["spliter"] = $spliter;

    return $container->response->withJson($cto, 200);
})->add(new Auth());

    // Add a new cto
$app->post('/cto', function ($request, $response) use ($container)  {
    
    
    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $tipoUsuario = $logado->descricao; //Tipo de usuário logado.

    if(strtolower($tipoUsuario) != 'admin')
    {
        return $this->response->withJson(array("error"=>array("message"=>"Sorry, This feature is only allowed for administrators.")), 403);
    }
    
    $cto = $request->getParsedBody();

    $cto = (object) $cto;

    
    if($cto->descricao == null || $cto->latitude == null || $cto->longitude == null || $cto->spliter["idSpliter"] == null || $cto->bairro["idBairro"] == null)
    {
        return $response->withJson(array("error"=>array("message"=>"The request data is invalid.")), 400);
    }

    $sql = "SELECT descricao FROM caixaatendimento WHERE descricao = :descricao";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("descricao", $cto->descricao);
    $sth->execute();
    $exists = $sth->fetchObject();

    if($exists)
    {
        return $this->response->withJson(array("error"=>array("message"=>"A registered record with the reported data already exists.")), 400);
    }

    $sql = 'INSERT INTO caixaatendimento VALUES (null, :latitude, :longitude, :descricao, :idSpliter, :idBairro, :portasUsadas)';
    
    $init = 0;

    $sth = $this->db->prepare($sql);
    $sth->bindParam("latitude", $cto->latitude);
    $sth->bindParam("longitude", $cto->longitude);
    $sth->bindParam("descricao", $cto->descricao);
    $sth->bindParam("idSpliter", $cto->spliter["idSpliter"]);
    $sth->bindParam("idBairro", $cto->bairro["idBairro"]);
    $sth->bindParam("portasUsadas", $init);
    $sth->execute();
    $cto->idCaixa = $this->db->lastInsertId();
    unset($cto->idBairro);

    $sql = 'Select descricao from bairro where idBairro = :id';
    $sth = $this->db->prepare($sql);
    $sth->bindParam("id", $cto->bairro["idBairro"]);
    $sth->execute();
    $temp = $sth->fetchAll();
    $cto->bairro["descricao"] = $temp[0]["descricao"];

    $sql = 'Select descricao from spliter where idSpliter = :id';
    $sth = $this->db->prepare($sql);
    $sth->bindParam("id", $cto->spliter["idSpliter"]);
    $sth->execute();
    $tmp = $sth->fetchAll();
    $cto->spliter["descricao"] = $tmp[0]["descricao"];
    
    return $this->response->withJson($cto, 201);
    
})->add(new Auth());

    // DELETE a cto with given id
$app->delete('/cto/[{idcto}]', function ($request, $response, $args) use ($container)  {
    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $tipoUsuario = $logado->descricao; //Tipo de usuário logado.

    if(strtolower($tipoUsuario) != 'admin')
    {
        return $this->response->withJson(array("error"=>array("message"=>"Sorry, This feature is only allowed for administrators.")), 403);
    }
    
    $cto = (object) $cto;
    $cto->idCaixa = $args['idcto'];

    $sth = $this->db->prepare("SELECT idcaixa FROM caixaatendimento WHERE idcaixa=:id");
    $sth->bindParam("id", $cto->idCaixa);
    $sth->execute();
    $ret = $sth->fetchObject();

    if(!$ret)
    {
        $error = array("error" => array("message"=>"Not Found.", "status" => 404));
        return $this->response->withJson($error, 404);
    }
    try{
        $sth = $this->db->prepare("DELETE FROM caixaatendimento WHERE idcaixa=:id");
        $sth->bindParam("id", $cto->idCaixa);
        $sth->execute();
        $success = array("success" => array("message"=>"Record deleted."));
        return $this->response->withJson($success, 200);
    }catch(Exception $e){
        return $this->response->withJson(array("error"=>array("message"=>"Record present in some Instalação. Delete the instalação first so that you can later delete this record.")), 400);
    }
    
})->add(new Auth());

// Update cto with given id
$app->put('/cto/[{idcto}]', function ($request, $response, $args) use ($container) {


    $cto = $request->getParsedBody();

    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $tipoUsuario = $logado->descricao; //Tipo de usuário logado.

    if(strtolower($tipoUsuario) != 'admin')
    {
        return $this->response->withJson(array("error"=>array("message"=>"Sorry, This feature is only allowed for administrators.")), 403);
    }
    
    $cto = $request->getParsedBody();

    $cto = (object) $cto;
    $cto->idCaixa = $args["idcto"];

    if($cto->descricao == null || $cto->latitude == null || $cto->longitude == null || $cto->spliter["idSpliter"] == null || $cto->bairro["idBairro"] == null)
    {
        return $response->withJson(array("error"=>array("message"=>"The request data is invalid.")), 400);
    }

$sql = "SELECT descricao FROM caixaatendimento WHERE descricao = :descricao AND idcaixa <> ".$cto->idCaixa."";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("descricao", $cto->descricao);
    $sth->execute();
    $exists = $sth->fetchObject();

    if($exists)
    {
        return $this->response->withJson(array("error"=>array("message"=>"A registered record with the reported data already exists.")), 400);
    }

    $sql = "update caixaatendimento set latitude = :latitude, longitude = :longitude, descricao = :descricao, idSpliter = :idSpliter, idBairro = :idBairro where idCaixa = :id";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("id", $cto->idCaixa);
    $sth->bindParam("latitude", $cto->latitude);
    $sth->bindParam("longitude", $cto->longitude);
    $sth->bindParam("descricao", $cto->descricao);
    $sth->bindParam("idSpliter", $cto->spliter["idSpliter"]);
    $sth->bindParam("idBairro", $cto->bairro["idBairro"]);
    $sth->execute();
    return $this->response->withJson($cto, 200);
})->add(new Auth());

    // Search for cto with given search teram in their name
$app->get('/cto/search/[{query}]', function ($request, $response, $args) use ($container)  {
 $sth = $this->db->prepare("SELECT * FROM cto WHERE descricao LIKE :query ORDER BY saidas");
 $query = "%".$args['query']."%";
 $sth->bindParam("query", $query);
 $sth->execute();
 $ctos = $sth->fetchAll();
 return $this->response->withJson($ctos, 200);
})->add(new Auth());
?>