<?php

	//tipos routes

    // get all tipos
$app->get('/usuarios', function ($request, $response, $args) use ($container) {
    $sth = $container->db->prepare("SELECT idUser, nome, usuario, tipoUser FROM user ORDER BY idUser");
    $sth->execute();
    $usuarios = $sth->fetchAll();
    if(!$usuarios)
    {
        $error = array("error" => array("message"=>"No records have been submitted yet."));
        return $container->response->withJson($error, 404);
    }
    return $container->response->withJson($usuarios, 200);
})->add(new Auth());

    // Retrieve tipo with id 
$app->get('/usuarios/[{id}]', function ($request, $response, $args) use ($container) {
    
    $sth = $container->db->prepare("SELECT idUser, nome, usuario, tipoUser FROM user WHERE idUser=:id");
    $sth->bindParam("id", $args['id']);
    $sth->execute();
    $usuario = $sth->fetchObject();
    if(!$usuario)
    {
        $error = array("error" => array("message"=>"Not Found."));
        return $container->response->withJson($error, 404);
    }
    return $container->response->withJson($usuario, 200);
})->add(new Auth());

    // Add a new tipo
$app->post('/usuarios', function ($request, $response) use ($container)  {
    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $tipoUsuario = $logado->tipoUser->descricao; //Tipo de usuário logado.

    if(strtolower($tipoUsuario) != 'admin')
    {
        return $this->response->withJson(array("error"=>array("message"=>"Sorry, This feature is only allowed for administrators.")), 403);
    }
    
    $usuario = $request->getParsedBody();

    if($usuario != null)
    {
        $usuario = (object) $usuario;
    }
    else
    {
        $usuario = array(
            "idUser" => null,
            "nome" => null,
            "usuario" => null,
            "senha" => null,
            "tipoUser" => null

        );
        $usuario= (object) $usuario;
    }

    if($usuario->nome == null || $usuario->usuario == null || $usuario->senha == null || $usuario->tipoUser == null)
    {
        return $response->withJson(array("error"=>array("message"=>"The request data is invalid.")), 400);
    }

    $sql = "SELECT idUser FROM user WHERE usuario = :usuario";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("usuario", $usuario->usuario);
    $sth->execute();
    $exists = $sth->fetchObject();

    if($exists)
    {
        return $this->response->withJson(array("error"=>array("message"=>"Name of user already exists.")), 400);
    }

    $sql = "SELECT * FROM tipousuario WHERE idTipo = :idTipo";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("idTipo", $usuario->tipoUser);
    $sth->execute();
    $exists = $sth->fetchObject();

    if(!$exists)
    {
        return $this->response->withJson(array("error"=>array("message"=>"Specify a valid user type!")), 400);
    }

    $sql = "INSERT INTO user (nome, usuario, senha, tipouser) VALUES (:nome, :usuario, :senha, :tipoUser)";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("nome", $usuario->nome);
    $sth->bindParam("usuario", $usuario->usuario);
    $sth->bindParam("senha", $usuario->senha);
    $sth->bindParam("tipoUser", $usuario->tipoUser);
    $sth->execute();
    $usuario->idUser = $this->db->lastInsertId();
    $temp = $usuario;
    $user->idUser = $temp->idUser;
    $user->nome = $temp->nome;
    $user->usuario = $temp->usuario;
    $user->tipoUser = $temp->tipoUser;
    return $this->response->withJson($user, 201);
})->add(new Auth());

    // DELETE a tipo with given id
$app->delete('usuarios/[{idUser}]', function ($request, $response, $args) use ($container)  {
    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $tipoUsuario = $logado->tipoUser->descricao; //Tipo de usuário logado.

    if(strtolower($tipoUsuario) != 'admin')
    {
        return $this->response->withJson(array("error"=>array("message"=>"Sorry, This feature is only allowed for administrators.")), 403);
    }
    
    $usuario = (object) $usuario;
    $usuario->idUser = $args['idUser'];

    $sth = $this->db->prepare("SELECT idUser FROM user WHERE idUser=:id");
    $sth->bindParam("id", $usuario->idUser);
    $sth->execute();
    $ret = $sth->fetchObject();

    if(!$ret)
    {
        $error = array("error" => array("message"=>"Not Found.", "status" => 404));
        return $this->response->withJson($error, 404);
    }

    $sth = $this->db->prepare("DELETE FROM user WHERE idUser=:id");
    $sth->bindParam("id", $usuario->idUser);
    $sth->execute();
    $success = array("success" => array("message"=>"Record deleted."));
    return $this->response->withJson($success, 200);
})->add(new Auth());

    // Update tipo with given id
$app->put('/usuarios/[{id}]', function ($request, $response, $args) use ($container) {

    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $tipoUsuario = $logado->tipoUser->descricao; //Tipo de usuário logado.

    if(strtolower($tipoUsuario) != 'admin')
    {
        return $this->response->withJson(array("error"=>array("message"=>"Sorry, This feature is only allowed for administrators.")), 403);
    }
    
    $usuario = $request->getParsedBody();

    if($usuario != null)
    {
        $usuario = (object) $usuario;
        $usuario->idUser = $args['id'];
    }
    else
    {
        $usuario = array(
            "idUser" => $args['id'],
            "nome" => null,
            "usuario" => null,
            "senha" => null,
            "tipoUser" => null

        );
        $usuario= (object) $usuario;
    }

    if($usuario->nome == null || $usuario->usuario == null || $usuario->senha == null || $usuario->tipoUser == null)
    {
        return $response->withJson(array("error"=>array("message"=>"The request data is invalid.")), 400);
    }

    $sql = "SELECT idUser FROM user WHERE usuario = :usuario";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("usuario", $usuario->usuario);
    $sth->execute();
    $exists = $sth->fetchObject();

    if($exists)
    {
        return $this->response->withJson(array("error"=>array("message"=>"Name of user already exists.")), 400);
    }

    $sql = "SELECT idTipo FROM tipousuario WHERE idTipo = :idTipo";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("idTipo", $usuario->tipoUser);
    $sth->execute();
    $exists = $sth->fetchObject();

    if(!$exists)
    {
        return $this->response->withJson(array("error"=>array("message"=>"Specify a valid user type!")), 400);
    }

    $sql = "UPDATE user SET nome=:nome, usuario=:usuario, senha=:senha, tipoUser=:tipoUsuario WHERE idUser=:id";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("id", $usuario->idUser);
    $sth->bindParam("nome", $usuario->nome);
    $sth->bindParam("usuario", $usuario->usuario);
    $sth->bindParam("senha", $usuario->senha);
    $sth->bindParam("tipoUsuario", $usuario->tipoUser);
    $sth->execute();

    $temp = $usuario;
    $user->idUser = $temp->idUser;
    $user->nome = $temp->nome;
    $user->usuario = $temp->usuario;
    $user->tipoUser = $temp->tipoUser;
    return $this->response->withJson($user, 200); 
})->add(new Auth());

    // Search for tipo with given search teram in their name
$app->get('/usuarios/search/[{query}]', function ($request, $response, $args) use ($container)  {
 $sth = $this->db->prepare("SELECT idUser, nome, usuario, tipoUser FROM user WHERE nome LIKE :query OR usuario LIKE :query ORDER BY idUser");
 $query = "%".$args['query']."%";
 $sth->bindParam("query", $query);
 $sth->execute();
 $usuarios = $sth->fetchAll();
 return $this->response->withJson($usuarios, 200);
})->add(new Auth());
?>