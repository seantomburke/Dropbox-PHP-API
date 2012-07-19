<?php
session_start();

/**
 * Dropbox API
 *
 * This is a simpler API for the Dropbox PHP API
 * 
 * @author Sean Thomas Burke	http://www.seantburke.com/
 */

class Dropbox
{

	private static $APP_KEY 		= '71y17gy5y517jbi';
	private static $APP_SECRET		= '56u3lyumzaeo2ed';
	private static $CALLBACK_URL 	= 'http://www.seantburke.com/Dropbox/example.php';
	
	public $request_token_url;
	public $oauth_token_secret;
	public $oauth_request_token;
	public $oauth_access_token;
	public $oauth_signature;
	public $uid;

	public function __construct()
	{	
		$this->oauth_token_secret 	= $_SESSION['oauth_token_secret'];
		$this->oauth_request_token 	= $_SESSION['oauth_request_token'];
		$this->oauth_access_token 	= $_SESSION['oauth_access_token'];
		$this->oauth_signature 		= $_SESSION['oauth_signature'];
		$this->uid 					= $_SESSION['uid'];		
			  
		if(!isset($this->oauth_signature) || !isset($this->oauth_access_token))
		{
			if(isset($_GET['uid']) && isset($_GET['oauth_token']) && isset($this->oauth_token_secret) && isset($this->oauth_request_token))
			{
				$this->processCallBack();
			}
			else
			{
				$this->request();
			}
		}
	}
	
	public function request()
	{
		// initiate a cURL; if you don't know what curl is, look it up at http://curl.haxx.se/
		$ch = curl_init(); 
		//Dropbox uses plaintext OAuth 1.0; make the header for this request
		$headers = array('Authorization: OAuth oauth_version="1.0", oauth_signature_method="PLAINTEXT", oauth_consumer_key="'.self::$APP_KEY.'", oauth_signature="'.self::$APP_SECRET.'&"');  
		// set cURL options and execute
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
		curl_setopt($ch, CURLOPT_URL, "https://api.dropbox.com/1/oauth/request_token");  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);  
		$request_token_response = curl_exec($ch);
		
		//	parse the returned data which has the format:
		// "oauth_token=<access-token>&oauth_token_secret=<access-token-secret>"
		parse_str($request_token_response, $parsed_request_token);
		$json_access = json_decode($request_token_response);
		if($json_access->error)
		{
			echo '<br><br>FATAL ERROR: '.$json_access->error.'<br><br>';
		}
		
		//set these variables in a $_SESSION variable
		$_SESSION['oauth_token_secret'] 	= $parsed_request_token['oauth_token_secret'];
		$_SESSION['oauth_request_token'] 	= $parsed_request_token['oauth_token'];
		//also store them in the object
		$this->oauth_token_secret 			= $parsed_request_token['oauth_token_secret'];
		$this->oauth_request_token 			= $parsed_request_token['oauth_token'];
		
		//get the request URL; this is where you send the user to authorize your request. Be sure to set the CALLBACK_URL before doing this.
		$this->request_token_url = 'https://www.dropbox.com/1/oauth/authorize?oauth_token='.$parsed_request_token['oauth_token'].'&oauth_callback='.self::$CALLBACK_URL;	
		
	}
	
	function processCallBack()
	{
	
		//Now we must process the request 
		//same steps as before, but now the header is modified to include the response variables that were stored in the session
		//notice the signature is a concatenation of the app_secret and the token_secret
		$ch = curl_init();  
		$headers = array('Authorization: OAuth oauth_version="1.0", oauth_signature_method="PLAINTEXT", oauth_consumer_key="'.self::$APP_KEY.'", oauth_token="'.$this->oauth_request_token.'", oauth_signature="'.self::$APP_SECRET.'&'.$this->oauth_token_secret.'"');  
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
		curl_setopt($ch, CURLOPT_URL, "https://api.dropbox.com/1/oauth/access_token");  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);  
		$access_token_response = curl_exec($ch);  
		//execute and parse
		parse_str($access_token_response, $parsed_access_token);
		$json_access = json_decode($access_token_response);
		if($json_access->error)
		{
			echo '<br><br>FATAL ERROR: '.$json_access->error.'<br><br>';
		}
		//store responses in $_SESSION
		//these 2 variables are what you need to make API requests
		
		session_unset();
		$_SESSION['oauth_access_token'] 	= $parsed_access_token['oauth_token'];
		$_SESSION['oauth_signature'] 		= self::$APP_SECRET.'&'.$parsed_access_token['oauth_token_secret'];	
		$_SESSION['uid'] 					= $_GET['uid'];
		//also store in the object
		$this->oauth_access_token 			= $parsed_access_token['oauth_token'];
		$this->oauth_signature 				= self::$APP_SECRET.'&'.$parsed_access_token['oauth_token_secret'];	
		$this->uid		 					= $_GET['uid'];
		
		return true;
	}
	
	function call($url)
	{
		$ch = curl_init(); 
		$headers = array('Authorization: OAuth oauth_version="1.0", oauth_signature_method="PLAINTEXT", oauth_consumer_key="'.self::$APP_KEY.'", oauth_token="'.$this->oauth_access_token.'", oauth_signature="'.$this->oauth_signature.'"');  
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
		curl_setopt($ch, CURLOPT_URL, $url);  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);  
		$api_response = curl_exec($ch);
		return json_decode($api_response);
	}
	
	function getAccessURL()
	{
		//get the Request URL
		return $this->request_token_url;
	}
	
	function hasAccess()
	{
		return ($this->oauth_access_token && $this->oauth_signature && $this->uid);
	}
	
	function filesGet($root,$path)
	{
		return $this->call('https://api-content.dropbox.com/1/files/'.$root.'/'.$path);
	}


}
