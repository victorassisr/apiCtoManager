<?php

	//instalacoes routes

    // get all instalacoes
$app->get('/instalacoes', function ($request, $response, $args) use ($container) {
    $sth = $container->db->prepare("SELECT * FROM instalacao");
    $sth->execute();
    $instalacoes = $sth->fetchAll();
    if(!$instalacoes)
    {
        $error = array("error" => array("message"=>"No records have been submitted yet."));
        return $container->response->withJson($error, 404);
    }
    return $container->response->withJson($instalacoes, 200);
})->add(new Auth());

//get all instalacoes joins
$app->get('/instalacoes/all', function ($request, $response, $args) use ($container) {
        
        $sth = $container->db->prepare(
            "SELECT datainstalacao, i.idpessoacliente, i.idpessoafuncionario,
            pes.nome as 'nomecliente', pes.sobrenome as 'sobrenomecliente',
            p.nome as 'nomefunc', p.sobrenome as 'sobrenomefunc', 
            c.rua, c.numero, c.complemento, b.descricao, porta, idcaixa
            FROM instalacao i 
            inner join cliente c on c.idpessoacliente = i.idpessoacliente 
            inner join funcionario f on f.idpessoafuncionario = i.idpessoafuncionario
            inner join pessoa p on p.Idpessoa = f.idpessoafuncionario
            inner join pessoa pes on pes.idpessoa = c.idpessoacliente
            inner join bairro b on b.idbairro = c.idbairro
            order by datainstalacao desc"
        );
        $sth->execute();
        $instalacao = $sth->fetchAll();
        if(!$instalacao)
        {
            $error = array("error" => array("message"=>"Not Found."));
            return $container->response->withJson($error, 404);
        }
        return $container->response->withJson($instalacao, 200);
    })->add(new Auth());

    // get quant instalacoes por mes
	$app->get('/instalacoes/month', function ($request, $response, $args) use ($container) {
    $sth = $container->db->prepare("SELECT count(0) as instalacoes, MONTH(datainstalacao) as mes FROM instalacao
									WHERE YEAR(datainstalacao) = 2019
									group by mes");
    $sth->execute();
    $instalacoes = $sth->fetchAll();
    if(!$instalacoes)
    {
        $error = array("error" => array("message"=>"No records have been submitted yet."));
        return $container->response->withJson($error, 404);
    }
    return $container->response->withJson($instalacoes, 200);
	})->add(new Auth());

	// get quant instalacoes por semana
	$app->get('/instalacoes/week', function ($request, $response, $args) use ($container) {
    $sth = $container->db->prepare(
    	"SELECT count(0) as quantinstalacoes, datainstalacao, dayofweek(datainstalacao) as diadasemana 
    	 FROM instalacao	WHERE week(current_date()) = week(datainstalacao)
		 group by datainstalacao");
    $sth->execute();
    $instalacoes = $sth->fetchAll();
    if(!$instalacoes)
    {
        $error = array("error" => array("message"=>"No records have been submitted yet."));
        return $container->response->withJson($error, 404);
    }
    return $container->response->withJson($instalacoes, 200);
	})->add(new Auth());


// get instalacoes periodo
$app->get('/instalacoes/periodo/[{dataInicial}/{dataFinal}]', function ($request, $response, $args) use ($container) {
        
        $sth = $container->db->prepare(
            "SELECT dataInstalacao, pes.nome as 'NomeCliente', pes.sobrenome as 'SobrenomeCliente',
            p.nome as 'NomeFunc', p.sobrenome as 'SobrenomeFunc', c.rua, c.numero, c.complemento,
            b.descricao, Porta, idCaixa

            FROM Instalacao i 
            inner join Cliente c on c.IdPessoaCliente = i.IdPessoaCliente 
            inner join Funcionario f on f.IdPessoaFuncionario = i.IdPessoaFuncionario
            inner join Pessoa p on p.IdPessoa = f.IdPessoaFuncionario
            inner join Pessoa pes on pes.IdPessoa = c.IdPessoaCliente
            inner join Bairro b on b.idBairro = c.idBairro
            WHERE i.dataInstalacao between :dataInicial and :dataFinal
            order by dataInstalacao"
        );
        $sth->bindParam("dataInicial", $args['dataInicial']);
        $sth->bindParam("dataFinal", $args['dataFinal']);
        $sth->execute();
        $instalacao = $sth->fetchAll();
        if(!$instalacao)
        {
            $error = array("error" => array("message"=>"Not Found."));
            return $container->response->withJson($error, 404);
        }
        return $container->response->withJson($instalacao, 200);
    })->add(new Auth());

    // Retrieve instalacao by id of caixa.
    $app->get('/instalacoes/caixa/[{id}]', function ($request, $response, $args) use ($container) {
    
        $sth = $container->db->prepare("SELECT * FROM instalacao WHERE idcaixa=:id");
        $sth->bindParam("id", $args['id']);
        $sth->execute();
        $instalacao = $sth->fetchAll();
        if(!$instalacao)
        {
            $error = array("error" => array("message"=>"Not Found."));
            return $container->response->withJson($error, 404);
        }
        return $container->response->withJson($instalacao, 200);
    })->add(new Auth());

    // Retrieve instalacao by id of caixa.
    $app->get('/instalacoes/data/[{data}]', function ($request, $response, $args) use ($container) {
    
        $sth = $container->db->prepare("SELECT * FROM instalacao WHERE dataInstalacao = :data");
        $sth->bindParam("data", $args['data']);
        $sth->execute();

        $instalacao = $sth->fetchAll();
        if(!$instalacao)
        {
            $error = array("error" => array("message"=>"Not Found."));
            return $container->response->withJson($error, 404);
        }
        return $container->response->withJson($instalacao, 200);
    })->add(new Auth());

    // Add a new instalacao
$app->post('/instalacoes', function ($request, $response) use ($container)  {
    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $tipoUsuario = $logado->descricao; //Tipo de usuário logado.

    if(strtolower($tipoUsuario) != 'admin')
    {
        return $this->response->withJson(array("error"=>array("message"=>"Sorry, This feature is only allowed for administrators.")), 403);
    }
    
    $instalacao = $request->getParsedBody();

    $instalacao = (object) $instalacao;

    if($instalacao->idCaixa == null || $instalacao->porta == null || $instalacao->dataInstalacao == null)
    {
        return $response->withJson(array("error"=>array("message"=>"The request data is invalid.")), 400);
    }

    //Verifica se a caixa existe.
    $sql = "SELECT idCaixa FROM CaixaAtendimento WHERE idCaixa = :id";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("id", $instalacao->idCaixa);
    $sth->execute();
    $exists = $sth->fetchObject();

    if(!$exists)
    {
        return $this->response->withJson(array("error"=>array("message"=>"Caixa is inválid.")), 400);
    }

    //Verifica se o cliente existe.
    $sql = "SELECT idPessoaCliente FROM cliente WHERE idPessoaCliente = :id";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("id", $instalacao->idPessoaCliente);
    $sth->execute();
    $exists = $sth->fetchObject();

    if(!$exists)
    {
        return $this->response->withJson(array("error"=>array("message"=>"Cliente is inválid.")), 400);
    }

    //Verifica se o funcionario existe.
    $sql = "SELECT idPessoaFuncionario FROM funcionario WHERE idPessoaFuncionario = :id";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("id", $instalacao->idPessoaFuncionario);
    $sth->execute();
    $exists = $sth->fetchObject();

    if(!$exists)
    {
        return $this->response->withJson(array("error"=>array("message"=>"Funcionario is inválid.")), 400);
    }

    //Verifica se a porta está livre
    $sql = "select * from instalacao where porta = :porta AND idCaixa = :id AND dataLiberacaoPorta IS NULL";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("id", $instalacao->idCaixa);
    $sth->bindParam("porta", $instalacao->porta);
    $sth->execute();
    $exists = $sth->fetchObject();

    if($exists)
    {
        return $this->response->withJson(array("error"=>array("message"=>"Record already exists.")), 400);
    }

    $sql = "insert into instalacao values(:porta,:dataInstalacao,:idCaixa,:dataLiberacaoPorta,:funcionario,:cliente);";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("porta", $instalacao->porta);
    $sth->bindParam("dataInstalacao", $instalacao->dataInstalacao);
    $sth->bindParam("idCaixa", $instalacao->idCaixa);
    $sth->bindParam("dataLiberacaoPorta", $instalacao->dataLiberacaoPorta);
    $sth->bindParam("funcionario", $instalacao->idPessoaFuncionario);
    $sth->bindParam("cliente", $instalacao->idPessoaCliente);
    $sth->execute();
    $instalacao->idInstalacao = $this->db->lastInsertId();
    return $this->response->withJson($instalacao, 201);
})->add(new Auth());

    // Retrieve instalacao with idCaixa/port/date (Cliente, Funcionario, Pessoa, Bairro)
    $app->get('/instalacoes/all/[{id}/{porta}/{data}]', function ($request, $response, $args) use ($container) {
    

        $sth = $container->db->prepare("
            SELECT dataInstalacao, i.IdPessoaCliente, i.IdPessoaFuncionario,
            dataLiberacaoPorta,
            pes.nome as 'NomeCliente', pes.sobrenome as 'SobrenomeCliente',
            p.nome as 'NomeFunc', p.sobrenome as 'SobrenomeFunc', 
            c.rua, c.numero, c.complemento, b.descricao, Porta, idCaixa
            FROM Instalacao i 
            inner join Cliente c on c.IdPessoaCliente = i.IdPessoaCliente 
            inner join Funcionario f on f.IdPessoaFuncionario = i.IdPessoaFuncionario
            inner join Pessoa p on p.IdPessoa = f.IdPessoaFuncionario
            inner join Pessoa pes on pes.IdPessoa = c.IdPessoaCliente
            inner join Bairro b on b.idBairro = c.idBairro
            WHERE idcaixa=:id AND dataInstalacao = :data AND porta = :porta");
        $sth->bindParam("id", $args['id']);
        $sth->bindParam("data", $args['data']);
        $sth->bindParam("porta", $args['porta']);
        $sth->execute();
        $instalacao = $sth->fetchObject();
        
        if(!$instalacao)
        {
            $error = array("error" => array("message"=>"Not Found."));
            return $container->response->withJson($error, 404);
        }
        return $container->response->withJson($instalacao, 200);
    })->add(new Auth()); 

    // Retrieve instalacao with idCaixa/port/date
    $app->get('/instalacoes/[{id}/{porta}/{data}]', function ($request, $response, $args) use ($container) {
    

        $sth = $container->db->prepare("SELECT * FROM instalacao WHERE idcaixa=:id AND dataInstalacao = :data AND porta = :porta");
        $sth->bindParam("id", $args['id']);
        $sth->bindParam("data", $args['data']);
        $sth->bindParam("porta", $args['porta']);
        $sth->execute();
        $instalacao = $sth->fetchObject();
        
        if(!$instalacao)
        {
            $error = array("error" => array("message"=>"Not Found."));
            return $container->response->withJson($error, 404);
        }
        return $container->response->withJson($instalacao, 200);
    })->add(new Auth()); 

    // DELETE a instalacao with given id
    $app->delete('/instalacao/[{id}/{porta}/{data}]', function ($request, $response, $args) use ($container)  {
    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $tipoUsuario = $logado->descricao; //Tipo de usuário logado.

    if(strtolower($tipoUsuario) != 'admin')
    {
        return $this->response->withJson(array("error"=>array("message"=>"Sorry, This feature is only allowed for administrators.")), 403);
    }
    
    $instalacao = (object) $instalacao;
    $instalacao->idCaixa = $args['id'];
    $instalacao->porta = $args['porta'];
    $instalacao->dataInstalacao = $args['data'];

    $sth = $this->db->prepare("SELECT idCaixa FROM instalacao WHERE idCaixa=:id AND porta = :porta AND dataInstalacao = :data");
    $sth->bindParam("id", $instalacao->idCaixa);
    $sth->bindParam("porta", $instalacao->porta);
    $sth->bindParam("data", $instalacao->dataInstalacao);
    $sth->execute();
    $ret = $sth->fetchObject();

    if(!$ret)
    {
        $error = array("error" => array("message"=>"Not Found.", "status" => 404));
        return $this->response->withJson($error, 404);
    }
    try{
        $sth = $this->db->prepare("DELETE FROM instalacao WHERE idCaixa=:id AND porta = :porta AND dataInstalacao = :data");
        $sth->bindParam("id", $instalacao->idCaixa);
        $sth->bindParam("porta", $instalacao->porta);
        $sth->bindParam("data", $instalacao->dataInstalacao);
        $sth->execute();
        $success = array("success" => array("message"=>"Record deleted."));
        return $this->response->withJson($success, 200);
    }catch(Exception $e){
        return $this->response->withJson(array("error"=>array("message"=>"Error at saving record.")), 400);
    }
    
})->add(new Auth());

    // Update instalacao with given id
$app->put('/instalacao/[{id}/{porta}/{data}]', function ($request, $response, $args) use ($container) {

    $dadosJWT = $request->getAttribute('jwt');
    $logado = $dadosJWT['jwt']->data;
    $tipoUsuario = $logado->descricao; //Tipo de usuário logado.

    if(strtolower($tipoUsuario) != 'admin')
    {
        return $this->response->withJson(array("error"=>array("message"=>"Sorry, This feature is only allowed for administrators.")), 403);
    }
    
    $instalacao = $request->getParsedBody();

    $instalacao = (object) $instalacao;
    $instalacao->idCaixa = $args['id'];
    $instalacao->porta = $args['porta'];
    $instalacao->dataInstalacao = $args['data'];

    if($instalacao->idPessoaFuncionario == null || $instalacao->idPessoaCliente == null)
    {
        return $response->withJson(array("error"=>array("message"=>"The request data is invalid.")), 400);
    }
    //Verifica se existe o cliente
    $sql = "SELECT idPessoaCliente FROM cliente WHERE idPessoaCliente = :cliente";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("cliente", $instalacao->idPessoaCliente);
    $sth->execute();
    $exists = $sth->fetchObject();

    if(!$exists)
    {
        return $this->response->withJson(array("error"=>array("message"=>"Cliente is inválid.")), 400);
    }

    //Verifica se existe o funcionário
    $sql = "SELECT idPessoaFuncionario FROM funcionario WHERE idPessoaFuncionario = :funcionario";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("funcionario", $instalacao->idPessoaFuncionario);
    $sth->execute();
    $exists = $sth->fetchObject();

    if(!$exists)
    {
        return $this->response->withJson(array("error"=>array("message"=>"Funcionário is inválid.")), 400);
    }

    $sql = "UPDATE instalacao SET idPessoaCliente=:c, idPessoaFuncionario = :f, dataLiberacaoPorta = :dlp WHERE idCaixa=:id AND porta = :porta AND dataInstalacao = :di";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("id", $instalacao->idCaixa);
    $sth->bindParam("c", $instalacao->idPessoaCliente);
    $sth->bindParam("f", $instalacao->idPessoaFuncionario);
    $sth->bindParam("dlp", $instalacao->dataLiberacaoPorta);
    $sth->bindParam("porta", $instalacao->porta);
    $sth->bindParam("di", $instalacao->dataInstalacao);
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