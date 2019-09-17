<?php

    //clientes routes

$app->get('/funcionarios', function ($request, $response, $args) use ($container) {
    $sth = $container->db->prepare("SELECT p.idPessoa, p.nome, p.sobrenome, f.usuario, f.idcliente FROM pessoa as p inner join funcionario as f on p.idPessoa = f.idPessoaFuncionario");
    $sth->execute();
    $usuarios = $sth->fetchAll();
    if(!$usuarios)
    {
        $error = array("error" => array("message"=>"No records have been submitted yet."));
        return $container->response->withJson($error, 404);
    }

    $tamanho = count($usuarios);


    for($i=0; $i<$tamanho; $i++){
        $sql = $container->db->prepare("SELECT descricao from clienteUsuario where idcliente = '".$usuarios[$i]["idcliente"]."'");
        $sql->execute();
        $temp = $sql->fetchAll();
        $cliente["idcliente"] = $usuarios[$i]["idcliente"];
        $cliente["descricao"] = $temp[0]["descricao"];
        unset($usuarios[$i]["idcliente"]);
        $usuarios[$i]["clienteUsuario"] = $cliente;
    }

    return $container->response->withJson($usuarios, 200);
})->add(new Auth());

    // Buscar funcionario pelo id
$app->get('/funcionario/[{id}]', function ($request, $response, $args) use ($container) {
    
    $sth = $container->db->prepare("SELECT p.idPessoa, p.nome, p.sobrenome, f.usuario, f.idcliente FROM pessoa as p inner join funcionario as f on p.idPessoa = f.idPessoaFuncionario WHERE f.idPessoaFuncionario=:id");
    $sth->bindParam("id", $args['id']);
    $sth->execute();
    $usuario = $sth->fetchAll();
    if(!$usuario)
    {
        $error = array("error" => array("message"=>"Not Found."));
        return $container->response->withJson($error, 404);
    }

    $sql = $container->db->prepare("SELECT descricao from clienteUsuario where idcliente = '".$usuario[0]["idcliente"]."'");
        $sql->execute();
        $temp = $sql->fetchAll();
        $cliente["idcliente"] = $usuario[0]["idcliente"];
        $cliente["descricao"] = $temp[0]["descricao"];
        unset($usuario[0]["idcliente"]);
        $usuario[0]["clienteUsuario"] = $cliente;

    return $container->response->withJson($usuario, 200);
})->add(new Auth());

    // Add a new cliente
$app->post('/funcionario', function ($request, $response) use ($container)  {
    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $clienteUsuario = $logado->descricao; //cliente de usuário logado.

    if(strtolower($clienteUsuario) != 'admin')
    {
        return $this->response->withJson(array("error"=>array("message"=>"Sorry, This feature is only allowed for administrators.")), 403);
    }
    
    $usuario = $request->getParsedBody();

    $u = (object) $usuario;

    if($u->nome == null || $u->sobrenome == null || $u->usuario == null || $u->senha == null || $u->clienteUsuario["idcliente"] == null)
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

    $sql = "SELECT * FROM clienteusuario WHERE idcliente = :idcliente";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("idcliente", $u->clienteUsuario["idcliente"]);
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

    $sql = "INSERT INTO FUNCIONARIO(idPessoaFuncionario, usuario, senha, idcliente) VALUES (:idFuncionario, :usuario, :senha, :cliente)";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("idFuncionario", $u->idPessoa);
    $sth->bindParam("usuario", $u->usuario);
    $sth->bindParam("senha", sha1($u->senha));
    $sth->bindParam("cliente", $u->clienteUsuario["idcliente"]);    
    $sth->execute();

    $sql = "Select descricao from clienteUsuario where idcliente = :id";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("id", $u->clienteUsuario["idcliente"]);
    $sth->execute();
    $tp = $sth->fetchObject();

    $temp = $u;
    $user->idPessoa = $temp->idPessoa;
    $user->nome = $temp->nome;
    $user->sobrenome = $temp->sobrenome;
    $user->usuario = $temp->usuario;
    $user->clienteUsuario = array("idcliente"=>$u->clienteUsuario["idcliente"], "descricao"=>$tp->descricao);

    return $this->response->withJson($user, 201);
    
})->add(new Auth());

    // DELETE a cliente with given id
$app->delete('/funcionario/[{idUser}]', function ($request, $response, $args) use ($container)  {
    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $clienteUsuario = $logado->descricao; //cliente de usuário logado.

    if(strtolower($clienteUsuario) != 'admin')
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

    // Update cliente with given id
$app->put('/usuarios/[{id}]', function ($request, $response, $args) use ($container) {

    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $clienteUsuario = $logado->descricao; //cliente de usuário logado.

    if(strtolower($clienteUsuario) != 'admin')
    {
        return $this->response->withJson(array("error"=>array("message"=>"Sorry, This feature is only allowed for administrators.")), 403);
    }
    
    $usuario = $request->getParsedBody();

    $usuario = (object)$usuario;
    $usuario->idPessoa = $args["id"];

    if($usuario->nome == null || $usuario->sobrenome == null || $usuario->usuario == null || $usuario->clienteUsuario["idcliente"] == null)
    {
        return $response->withJson(array("error"=>array("message"=>"The request data is invalid.")), 400);
    }

    $sql = "SELECT idPessoaFuncionario FROM funcionario WHERE usuario = :usuario";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("usuario", $usuario->usuario);
    $sth->execute();
    $exists = $sth->fetchObject();

    if($exists)
    {
        return $this->response->withJson(array("error"=>array("message"=>"Name of user already exists.")), 400);
    }

    $sql = "SELECT idcliente FROM clienteusuario WHERE idcliente = :idcliente";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("idcliente", $usuario->clienteUsuario["idcliente"]);
    $sth->execute();
    $exists = $sth->fetchObject();

    if(!$exists)
    {
        return $this->response->withJson(array("error"=>array("message"=>"Specify a valid user type!")), 400);
    }

    $sql = "update pessoa set nome=:nome, sobrenome=:snome where idPessoa = :id";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("id", $usuario->idPesoa);
    $sth->bindParam("nome", $usuario->nome);
    $sth->bindParam("snome", $usuario->sobrenome);

    $sth->execute();
    $sql = "UPDATE funcionario SET usuario=:usuario, idcliente=:clienteUsuario WHERE idPessoaFuncionario=:id";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("id", $usuario->idPessoa);
    $sth->bindParam("usuario", $usuario->usuario);
    $sth->bindParam("clienteUsuario", $usuario->clienteUsuario["idcliente"]);
    $sth->execute();

    $sql = "Select descricao from clienteusuario idcliente = :id";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("idcliente", $usuario->clienteUsuario["idcliente"]);
    $sth->execute();
    $desc = $sth->fetchAll();

    $temp = $usuario;
    $user->idPessoa = $temp->idPessoa;
    $user->nome = $temp->nome;
    $user->usuario = $temp->usuario;
    $user->clienteUsuario = array("idcliente"=>$temp->clienteUsuario["idcliente"], "descricao"=>$desc[0]["descricao"]);
    return $this->response->withJson($user, 200); 


})->add(new Auth());

    // Search for cliente with given search teram in their name
$app->get('/funcionario/search/[{query}]', function ($request, $response, $args) use ($container)  {
 $sth = $this->db->prepare("SELECT idUser, nome, usuario, clienteUser FROM user WHERE nome LIKE :query OR usuario LIKE :query ORDER BY idUser");
 $query = "%".$args['query']."%";
 $sth->bindParam("query", $query);
 $sth->execute();
 $usuarios = $sth->fetchAll();
 return $this->response->withJson($usuarios, 200);
})->add(new Auth());
?>