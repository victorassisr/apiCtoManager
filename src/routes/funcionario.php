<?php

	//tipos routes

$app->get('/funcionarios', function ($request, $response, $args) use ($container) {
    $sth = $container->db->prepare("SELECT p.idPessoa, p.nome, p.sobrenome, f.usuario, f.idTipo FROM pessoa as p inner join funcionario as f on p.idPessoa = f.idPessoaFuncionario");
    $sth->execute();
    $usuarios = $sth->fetchAll();
    if(!$usuarios)
    {
        $error = array("error" => array("message"=>"No records have been submitted yet."));
        return $container->response->withJson($error, 404);
    }

    $tamanho = count($usuarios);


    for($i=0; $i<$tamanho; $i++){
        $sql = $container->db->prepare("SELECT descricao from tipoUsuario where idTipo = '".$usuarios[$i]["idTipo"]."'");
        $sql->execute();
        $temp = $sql->fetchAll();
        $tipo["idTipo"] = $usuarios[$i]["idTipo"];
        $tipo["descricao"] = $temp[0]["descricao"];
        unset($usuarios[$i]["idTipo"]);
        $usuarios[$i]["tipoUsuario"] = $tipo;
    }

    return $container->response->withJson($usuarios, 200);
})->add(new Auth());

    // Buscar funcionario pelo id
$app->get('/funcionario/[{id}]', function ($request, $response, $args) use ($container) {
    
    $sth = $container->db->prepare("SELECT p.idPessoa, p.nome, p.sobrenome, f.usuario, f.idTipo FROM pessoa as p inner join funcionario as f on p.idPessoa = f.idPessoaFuncionario WHERE f.idPessoaFuncionario=:id");
    $sth->bindParam("id", $args['id']);
    $sth->execute();
    $usuario = $sth->fetchAll();
    if(!$usuario)
    {
        $error = array("error" => array("message"=>"Not Found."));
        return $container->response->withJson($error, 404);
    }

    $sql = $container->db->prepare("SELECT descricao from tipoUsuario where idTipo = '".$usuario[0]["idTipo"]."'");
        $sql->execute();
        $temp = $sql->fetchAll();
        $tipo["idTipo"] = $usuario[0]["idTipo"];
        $tipo["descricao"] = $temp[0]["descricao"];
        unset($usuario[0]["idTipo"]);
        $usuario[0]["tipoUsuario"] = $tipo;

    return $container->response->withJson($usuario, 200);
})->add(new Auth());

    // Add a new tipo
$app->post('/funcionario', function ($request, $response) use ($container)  {
    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $tipoUsuario = $logado->descricao; //Tipo de usuário logado.

    if(strtolower($tipoUsuario) != 'admin')
    {
        return $this->response->withJson(array("error"=>array("message"=>"Sorry, This feature is only allowed for administrators.")), 403);
    }
    
    $usuario = $request->getParsedBody();

    $u = (object) $usuario;

    if($u->nome == null || $u->sobrenome == null || $u->usuario == null || $u->senha == null || $u->tipoUsuario["idTipo"] == null)
    {
        return $response->withJson(array("error"=>array("message"=>"The request data is invalid.")), 400);
    }

    $sql = "SELECT idPessoaFuncionario FROM funcionario WHERE usuario = :usuario";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("usuario", $u->usuario);
    $sth->execute();
    $exists = $sth->fetchObject();

    if($exists)
    {
        return $this->response->withJson(array("error"=>array("message"=>"Name of user already exists.")), 400);
    }

    $sql = "SELECT * FROM tipousuario WHERE idTipo = :idTipo";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("idTipo", $u->tipoUsuario["idTipo"]);
    $sth->execute();
    $exists = $sth->fetchObject();

    if(!$exists)
    {
        return $this->response->withJson(array("error"=>array("message"=>"Specify a valid user type!")), 400);
    }

    $sql = "INSERT into Pessoa(nome, sobrenome) values (:nome, :sobrenome)";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("nome", $u->nome);
    $sth->bindParam("sobrenome", $u->sobrenome);
    $sth->execute();

    $u->idPessoa = $this->db->lastInsertId();

    echo $u->idPessoa;

    $sql = "INSERT INTO FUNCIONARIO(idPessoaFuncionario, usuario, senha, idTipo) VALUES (:idFuncionario, :usuario, :senha, :tipo)";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("idFuncionario", $u->idPessoa);
    $sth->bindParam("usuario", $u->usuario);
    $sth->bindParam("senha", sha1($u->senha));
    $sth->bindParam("tipo", $u->tipoUsuario["idTipo"]);    
    $sth->execute();

    $sql = "Select descricao from tipoUsuario where idTipo = :id";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("id", $u->tipoUsuario["idTipo"]);
    $sth->execute();
    $tp = $sth->fetchObject();

    $temp = $u;
    $user->idPessoa = $temp->idPessoa;
    $user->nome = $temp->nome;
    $user->sobrenome = $temp->sobrenome;
    $user->usuario = $temp->usuario;
    $user->tipoUsuario = array("idTipo"=>$u->tipoUsuario["idTipo"], "descricao"=>$tp->descricao);

    return $this->response->withJson($user, 201);
    
})->add(new Auth());

    // DELETE a tipo with given id
$app->delete('/funcionario/[{idUser}]', function ($request, $response, $args) use ($container)  {
    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $tipoUsuario = $logado->descricao; //Tipo de usuário logado.

    if(strtolower($tipoUsuario) != 'admin')
    {
        return $this->response->withJson(array("error"=>array("message"=>"Sorry, This feature is only allowed for administrators.")), 403);
    }
    
    $usuario = (object) $usuario;
    $usuario->idUser = $args['idUser'];

    $sth = $this->db->prepare("SELECT idPessoa FROM pessoa WHERE idPessoa=:id");
    $sth->bindParam("id", $usuario->idUser);
    $sth->execute();
    $ret = $sth->fetchObject();

    if(!$ret)
    {
        $error = array("error" => array("message"=>"Not Found.", "status" => 404));
        return $this->response->withJson($error, 404);
    }
    try{
        $sth = $this->db->prepare("DELETE FROM funcionario WHERE idPessoaFuncionario=:id");
        $sth->bindParam("id", $usuario->idUser);
        $sth->execute();
        $sth = $this->db->prepare("DELETE FROM Pessoa WHERE idPessoa=:id");
        $sth->bindParam("id", $usuario->idUser);
        $sth->execute();
        $success = array("success" => array("message"=>"Record deleted."));
        return $this->response->withJson($success, 200);
    }catch(Exception $e){
        $error = array("error" => array("message"=>"Error."));
        return $this->response->withJson($error, 503);
    }
    
})->add(new Auth());

    // Update tipo with given id
$app->put('/usuarios/[{id}]', function ($request, $response, $args) use ($container) {

    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $tipoUsuario = $logado->descricao; //Tipo de usuário logado.

    if(strtolower($tipoUsuario) != 'admin')
    {
        return $this->response->withJson(array("error"=>array("message"=>"Sorry, This feature is only allowed for administrators.")), 403);
    }
    
    $usuario = $request->getParsedBody();

    $usuario = (object)$usuario;
    $usuario->idPessoa = $args["id"];

    if($usuario->nome == null || $usuario->sobrenome == null || $usuario->usuario == null || $usuario->tipoUsuario["idTipo"] == null || $usuario->senha == null)
    {
        return $response->withJson(array("error"=>array("message"=>"The request data is invalid.")), 400);
    }

    $sql = "SELECT idPessoaFuncionario FROM funcionario WHERE usuario = :usuario AND idPessoaFuncionario <> :id";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("usuario", $usuario->usuario);
    $sth->bindParam("id", $usuario->idPessoa);
    $sth->execute();
    $exists = $sth->fetchObject();

    if($exists)
    {
        return $this->response->withJson(array("error"=>array("message"=>"Name of user already exists.")), 400);
    }

    $sql = "SELECT idTipo FROM tipousuario WHERE idTipo = :idTipo";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("idTipo", $usuario->tipoUsuario["idTipo"]);
    $sth->execute();
    $exists = $sth->fetchObject();

    if(!$exists)
    {
        return $this->response->withJson(array("error"=>array("message"=>"Specify a valid user type!")), 400);
    }

    $sql = "update pessoa set nome=:nome, sobrenome=:snome where idPessoa = :id";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("id", $usuario->idPessoa);
    $sth->bindParam("nome", $usuario->nome);
    $sth->bindParam("snome", $usuario->sobrenome);

    $sth->execute();
    $sql = "UPDATE funcionario SET usuario=:usuario, idTipo=:tipoUsuario, senha = :senha WHERE idPessoaFuncionario=:id";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("id", $usuario->idPessoa);
    $sth->bindParam("usuario", $usuario->usuario);
    $sth->bindParam("senha", sha1($usuario->senha));
    $sth->bindParam("tipoUsuario", $usuario->tipoUsuario["idTipo"]);
    $sth->execute();

    $sql = "Select descricao from tipousuario where idTipo = :idTipo";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("idTipo", $usuario->tipoUsuario["idTipo"]);
    $sth->execute();
    $desc = $sth->fetchAll();

    $temp = $usuario;
    $user->idPessoa = $temp->idPessoa;
    $user->nome = $temp->nome;
    $user->usuario = $temp->usuario;
    $user->tipoUsuario = array("idTipo"=>$temp->tipoUsuario["idTipo"], "descricao"=>$desc[0]["descricao"]);
    return $this->response->withJson($user, 200); 


})->add(new Auth());

    // Search for tipo with given search teram in their name
$app->get('/funcionario/search/[{query}]', function ($request, $response, $args) use ($container)  {
 $sth = $this->db->prepare("SELECT idUser, nome, usuario, tipoUser FROM user WHERE nome LIKE :query OR usuario LIKE :query ORDER BY idUser");
 $query = "%".$args['query']."%";
 $sth->bindParam("query", $query);
 $sth->execute();
 $usuarios = $sth->fetchAll();
 return $this->response->withJson($usuarios, 200);
})->add(new Auth());
?>