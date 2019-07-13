<?php
class Auth{
	public function __invoke($request, $response, $next)
	{

		require('src/JWT/JWTWrapper.php');
	$token = $request->getHeaderLine('X-Access-Token');
        if($token) {
            try {
                $request = $request->withAttribute("jwt",array("jwt"=>JWTWrapper::decode($token)));
            } catch(Exception $ex) {
                // nao foi possivel decodificar o token jwt
                return $response->withJson(array("error"=>array("login"=>"false","message"=>"Login is required. Check if your token is válid.")), 401);
            }
 
        } else {
            // nao foi possivel extrair token do header
            return $response->withJson(array("error"=>array("login"=>"false","message"=>"Token not provided.")), 401);
        }

		$response = $next($request, $response);
		return $response;
	}
}
?>