<?php


class ffck_connector{
    var $login;
    var $password;
    var $token;
    var $cookie_file;
    
    function ffck_connector($login, $password){
        $this->login = $login;
        $this->password = $password;
        $this->cookie_file = dirname(__FILE__).'/cookie.txt';
    }
    
    //first we need a valid token which is hidden inside the html form
    //fetch the token to use for login request
    function get_token(){
        $this->log('get_token', 'start');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://ffck-goal.multimediabs.com/login');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
        $result = curl_exec($ch);

        $pattern ='<input type="hidden" name="authenticityToken" value="(?P<token>.*?)">';
        preg_match($pattern, $result, $matches, PREG_OFFSET_CAPTURE, 3);
        echo 'got token :'.$matches['token'][0].'<br>';
        $this->log('get_token', 'done');
        $this->token= $matches['token'][0];
    }
    
    function log($function, $message){
        echo $function.' : '.$message.'<br>';
    }
    
    
    //second we get a temporary session for login purpose (token, username and password are not used by remote server)
    function get_session(){
        $this->log('get_session', 'start');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://ffck-goal.multimediabs.com/login');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Host: ffck-goal.multimediabs.com', 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'));
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // get headers too with this line
        curl_setopt($ch, CURLOPT_HEADER, 1);
        $result = curl_exec($ch);
        // get cookie
        // multi-cookie variant contributed by @Combuster in comments
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $result, $matches);
        $cookies = array();
        foreach($matches[1] as $item) {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }
        //var_dump($cookies);

        $this->log('get_session', 'done');

    }
    
    //third we run again the request with the temporary session id, login, password and token. it will return an actual session id
    function login(){
        $this->log('login', 'start');

        $this->get_token();
        $this->get_session();
        
        $this->flash='%00previousUrl%3A%2F%00%00url%3A%2F%00';
        $this->lb='was03';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://ffck-goal.multimediabs.com/login');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Host: ffck-goal.multimediabs.com', 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('authenticityToken' => $this->token, 'username' => $this->login, 'password'=> $this->password)));
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
        // get headers too with this line
        curl_setopt($ch, CURLOPT_HEADER, 1);
        $result = curl_exec($ch);
        // get cookie
        // multi-cookie variant contributed by @Combuster in comments
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $result, $matches);
        $cookies = array();
        foreach($matches[1] as $item) {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }
        
        
        $this->log('login', 'done');

    }
    
    function get_members(){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://ffck-goal.multimediabs.com/licences/afficherlistelicencies?idSaison=2015');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Host: ffck-goal.multimediabs.com', 'Accept: application/json; q=0.01'));
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
        //curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $result = curl_exec($ch);
    }
        
}

//FIXME secure
$connector = new ffck_connector($_POST['login'],$_POST['password']);

$connector->login();
$connector->get_members();
