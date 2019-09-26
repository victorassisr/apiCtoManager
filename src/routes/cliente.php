<?php

    //clientes routes

$app->get('/clientes', function ($request, $response, $args) use ($container) {
    $sth = $container->db->prepare("SELECT p.idPessoa, p.nome, p.sobrenome, c.rua, c.numero, c.complemento, b.idBairro, b.descricao as bairro FROM pessoa AS p inner join cliente as c on c.idPessoaCliente = idPessoa inner join bairro as b on c.idBairro = b.idBairro");
    $sth->execute();
    $clientes = $sth->fetchAll();

    $tamanho = count($clientes);

    for($i=0; $i<$tamanho; $i++){
        $endereco["rua"] = $clientes[$i]["rua"];
        unset($clientes[$i]["rua"]);
        $endereco["numero"] = $clientes[$i]["numero"];
        unset($clientes[$i]["numero"]);
        $endereco["complemento"] = $clientes[$i]["complemento"];
        unset($clientes[$i]["complemento"]);
        $endereco["bairro"] = $clientes[$i]["bairro"];
        unset($clientes[$i]["bairro"]);
        $endereco["idBairro"] = $clientes[$i]["idBairro"];
        unset($clientes[$i]["idBairro"]);
        $clientes[$i]["endereco"] = $endereco;
    }


    if(!$clientes)
    {
        $error = array("error" => array("message"=>"No records have been submitted yet."));
        return $container->response->withJson($error, 404);
    }

    return $container->response->withJson($clientes, 200);
})->add(new Auth());

    // Buscar cliente pelo id
$app->get('/cliente/[{id}]', function ($request, $response, $args) use ($container) {
    
    $sth = $container->db->prepare("SELECT p.idPessoa, p.nome, p.sobrenome, c.rua, c.numero, c.complemento, b.idBairro, b.descricao FROM pessoa AS p inner join cliente as c on c.idPessoaCliente = idPessoa inner join bairro as b on c.idBairro = b.idBairro WHERE p.idPessoa=:id");
    $sth->bindParam("id", $args['id']);
    $sth->execute();
    $cliente = $sth->fetchAll();

    if(!$cliente)
    {
        $error = array("error" => array("message"=>"Not Found."));
        return $container->response->withJson($error, 404);
    }

        $endereco["rua"] = $cliente[0]["rua"];
        unset($cliente[0]["rua"]);
        $endereco["numero"] = $cliente[0]["numero"];
        unset($cliente[0]["numero"]);
        $endereco["complemento"] = $cliente[0]["complemento"];
        unset($cliente[0]["complemento"]);
        $endereco["bairro"] = $cliente[0]["descricao"];
        unset($cliente[0]["descricao"]);
        $endereco["idBairro"] = $cliente[0]["idBairro"];
        unset($cliente[0]["idBairro"]);
        $cliente[0]["endereco"] = $endereco;

    return $container->response->withJson($cliente, 200);
})->add(new Auth());

    // Add a new cliente
$app->post('/cliente', function ($request, $response) use ($container)  {
    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $usuario = $logado->descricao; //usuário logado.

    if(strtolower($usuario) != 'admin')
    {
        return $this->response->withJson(array("error"=>array("message"=>"Sorry, This feature is only allowed for administrators.")), 403);
    }
    
    $cliente = $request->getParsedBody();

    $c = (object) $cliente;

    if($c->nome == null || $c->sobrenome == null || $c->endereco["rua"] == null || $c->endereco["numero"] == null || $c->endereco["idBairro"] == null)
    {
        return $response->withJson(array("error"=>array("message"=>"The request data is invalid.")), 400);
    }

    if(!isset($c->endereco["complemento"])){
        $c->endereco["complemento"] = null;
    }

    $sql = "SELECT idBairro FROM bairro WHERE idBairro = :idBairro";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("idBairro", $c->endereco["idBairro"]);
    $sth->execute();
    $exists = $sth->fetchObject();

    if(!$exists)
    {
        return $this->response->withJson(array("error"=>array("message"=>"Specify a valid district!")), 400);
    }

    $sql = "INSERT into Pessoa(nome, sobrenome) values (:nome, :sobrenome)";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("nome", $c->nome);
    $sth->bindParam("sobrenome", $c->sobrenome);
    $sth->execute();
    $tempId = $this->db->lastInsertId();

    $c->idPessoa = $tempId;

    $sql = "INSERT INTO cliente(idPessoaCliente, rua, numero, complemento, idBairro) VALUES (:idCliente, :rua, :numero, :complemento, :idBairro)";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("idCliente", $c->idPessoa);
    $sth->bindParam("rua", $c->endereco["rua"]);
    $sth->bindParam("numero", $c->endereco["numero"]);
    $sth->bindParam("complemento", $c->endereco["complemento"]);
    $sth->bindParam("idBairro", $c->endereco["idBairro"]);   
    $sth->execute();

    $sql = "Select descricao from bairro where idBairro = :id";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("id", $c->endereco["idBairro"]);
    $sth->execute();
    $nomeBairro = $sth->fetchObject();

    $c->endereco["bairro"] = $nomeBairro->descricao;

    return $this->response->withJson($c, 201);

})->add(new Auth());

    // DELETE a cliente with given id
$app->delete('/cliente/[{id}]', function ($request, $response, $args) use ($container)  {
    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $clienteUsuario = $logado->descricao; //cliente de usuário logado.

    if(strtolower($clienteUsuario) != 'admin')
    {
        return $this->response->withJson(array("error"=>array("message"=>"Sorry, This feature is only allowed for administrators.")), 403);
    }
    
    $cliente = (object) $cliente;
    $cliente->idPessoa = $args['id'];

    $sth = $this->db->prepare("SELECT idPessoa FROM pessoa WHERE idPessoa=:id");
    $sth->bindParam("id", $cliente->idPessoa);
    $sth->execute();
    $ret = $sth->fetchObject();

    if(!$ret)
    {
        $error = array("error" => array("message"=>"Not Found.", "status" => 404));
        return $this->response->withJson($error, 404);
    }
    try{
        $sth = $this->db->prepare("DELETE FROM cliente WHERE idPessoaCliente=:id");
        $sth->bindParam("id", $cliente->idPessoa);
        $sth->execute();
        $sth = $this->db->prepare("DELETE FROM Pessoa WHERE idPessoa=:id");
        $sth->bindParam("id", $cliente->idPessoa);
        $sth->execute();
        $success = array("success" => array("message"=>"Record deleted."));
        return $this->response->withJson($success, 200);
    }catch(Exception $e){
        $error = array("error" => array("message"=>"Error."));
        return $this->response->withJson($error, 503);
    }
    
})->add(new Auth());

    // Update cliente with given id
$app->put('/cliente/[{id}]', function ($request, $response, $args) use ($container) {

    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $usuario = $logado->descricao; //cliente de usuário logado.

    if(strtolower($usuario) != 'admin')
    {
        return $this->response->withJson(array("error"=>array("message"=>"Sorry, This feature is only allowed for administrators.")), 403);
    }
    
    $cliente = $request->getParsedBody();

    $cliente = (object)$cliente;
    $cliente->idPessoa = $args["id"];

    if($cliente->nome == null || $cliente->sobrenome == null || $cliente->endereco["rua"] == null || $cliente->endereco["numero"] == null || $cliente->endereco["idBairro"] == null)
    {
        return $response->withJson(array("error"=>array("message"=>"The request data is invalid.")), 400);
    }

    if(!isset($cliente->endereco["complemento"])){
        $cliente->endereco["complemento"] = null;
    }

    $sql = "SELECT idBairro FROM bairro WHERE idBairro = :idBairro";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("idBairro", $cliente->endereco["idBairro"]);
    $sth->execute();
    $exists = $sth->fetchObject();

    if(!$exists)
    {
        return $this->response->withJson(array("error"=>array("message"=>"Specify a valid district!")), 400);
    }

    $sql = "update pessoa set nome=:nome, sobrenome=:snome where idPessoa = :id";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("id", $cliente->idPessoa);
    $sth->bindParam("nome", $cliente->nome);
    $sth->bindParam("snome", $cliente->sobrenome);

    $sth->execute();
    $sql = "UPDATE cliente SET rua=:rua, numero=:numero, complemento=:complemento, idBairro=:idBairro WHERE idPessoacliente=:id";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("id", $cliente->idPessoa);
    $sth->bindParam("rua", $cliente->endereco["rua"]);
    $sth->bindParam("numero", $cliente->endereco["numero"]);
    $sth->bindParam("complemento", $cliente->endereco["complemento"]);
    $sth->bindParam("idBairro", $cliente->endereco["idBairro"]);
    $sth->execute();

    $sql = "SELECT descricao from bairro where idbairro = :id";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("id", $cliente->endereco["idBairro"]);
    $sth->execute();
    $desc = $sth->fetchAll();

    $cliente->endereco["descricao"] = $desc["descricao"];
    
    return $this->response->withJson($cliente, 200); 


})->add(new Auth());

    // Search for cliente with given search teram in their name
$app->get('/cliente/search/[{query}]', function ($request, $response, $args) use ($container)  {
 $sth = $this->db->prepare("SELECT idUser, nome, usuario, clienteUser FROM user WHERE nome LIKE :query OR usuario LIKE :query ORDER BY idUser");
 $query = "%".$args['query']."%";
 $sth->bindParam("query", $query);
 $sth->execute();
 $usuarios = $sth->fetchAll();
 return $this->response->withJson($usuarios, 200);
})->add(new Auth());
?>