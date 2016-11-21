<?php

class HttpRequest
{
	/**
     * @var string cookieFile Cookie file
     */
    private $cookieFile = null;
	
	/**
     * @var string cookieFile Cookie file
     */
    private $logFile = null;
    
    /**
     * @var string userAgent User-Agent string
     */
    private $userAgent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.117 Safari/537.36';

	/**
     * @var string method
     */
	private $method = 'GET';
	
	/**
     * @var array headers
     */
	private $headers = array();
	
	/**
     * @var curl handle
     */
	private $curl = null;
	
	/**
     * @var url string
     */
	private $url = null;
	
	/**
     * @var referer string
     */
	private $referer = null;
	
	/**
     * @var body string
     */
	private $body = null;
	
	/**
     * @var filename to upload string
     */
	private $fileToUpload = null;

	/**
	*@var proxy string proxy_server
	**/
	private $proxy_server = null;

	/**
	*@var proxy_user string proxy_server
	**/
	private $proxy_server_user = null;

	/**
	*@var proxy_pass string proxy_server
	**/
	private $proxy_server_pass = null;

	private $last_error = null;
	
	/**
     * constructor
	 * @param string url
	 * @param string 
     */
	public function __construct($url = null){
		$this->url = $url;
		$this->curl = curl_init();
        if ( $this->curl === false )
			throw new Exception('Failed CURL init');
		if (is_string($this->url))
			curl_setopt( $this->curl, CURLOPT_URL, $this->url);
	}
	
	/**
     * destructor
     */
	public function __dectruct(){
		if ($this->curl)
			curl_close( $this->curl );
	}
	
	/**
     * reset the curl object options
     */
	private function _resetOptions(){
		curl_setopt( $this->curl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $this->curl, CURLOPT_FOLLOWLOCATION, 1 );
        
		if (is_null($this->url))
			throw new Exception('Url not defined!');
		curl_setopt( $this->curl, CURLOPT_URL, $this->url);
		
		if (!is_null($this->cookieFile)){
			curl_setopt($this->curl, CURLOPT_COOKIEJAR, trim($this->cookieFile));
			curl_setopt($this->curl, CURLOPT_COOKIEFILE, trim($this->cookieFile));
		}
		
        if (count($this->headers)) 
			curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->headers);
        
		if (!is_null($this->userAgent)){
			curl_setopt($this->curl, CURLOPT_USERAGENT, $this->userAgent);
		}
		
		if (substr($this->url,0,5) == 'https'){
			curl_setopt( $this->curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt( $this->curl, CURLOPT_SSL_VERIFYHOST, 0);
		}
		
        if (!is_null($this->referer))
            curl_setopt( $this->curl, CURLOPT_REFERER, $this->referer );
        
		if ($this->method == 'POST'){			
			curl_setopt( $this->curl, CURLOPT_POST, 1 );
            if (!is_null($this->body))
				curl_setopt( $this->curl, CURLOPT_POSTFIELDS, $this->body );
        }else{
			curl_setopt( $this->curl, CURLOPT_POST, 0 );
		}		
	}
		
	/**
	* sendind request to server
	* @return string response from server 
	**/
	public function send(){
		$logFile = null;
		$this->_resetOptions();
		if ( !is_null($this->logFile)){
            $logFile = @fopen( $this->logFile, 'a+' );
			curl_setopt( $this->curl, CURLOPT_STDERR, $logFile );
        }
		
	if ($this->proxy_server != null){
		curl_setopt($this->curl, CURLOPT_PROXY, $this->proxy_server);
		if ($this->proxy_server_user != null && $this->proxy_server_pass != null){
			curl_setopt($this->curl, CURLOPT_PROXYUSERPWD, "{$this->proxy_server_user}:{$this->proxy_server_pass}");
		}
	}       
	$content = curl_exec( $this->curl );
		$error = curl_errno( $this->curl );
		$this->last_error = curl_strerror($error);
		if ( $logFile )
			fclose( $logFile );
		return (!$error)? $content : false;
	}
	
	/**
	* download the file from server
	* @param $filename string filename
	* @return  
	**/
	public function download($filename){
		if (is_null($this->url))
			throw new Exception('download: url not defined!');
       
		if (!is_string($filename))
			throw new Exception('download: expected string filename');
		
		if ( !($f = @fopen( $filename, 'w' )))
			throw new Exception('download: can\' open file');
			
		curl_setopt( $this->curl, CURLOPT_URL, $this->url);
        curl_setopt( $this->curl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $this->curl, CURLOPT_FOLLOWLOCATION, 1 );
		curl_setopt( $this->curl, CURLOPT_NOPROGRESS, 0 );
		curl_setopt( $this->curl, CURLOPT_FILE, $f );
        
		curl_exec( $this->curl );
		$errno = curl_errno( $this->curl );
		fclose($f);
       
        return ($errno)? false:true;
	}
	
	/**
	* upload the file to server
	* @param $filename string filename
	* @param $postfield string name the POST field
	* @return  
	**/
	public function upload($filename, $postfield){
        $logFile = null;
		if ( !file_exists( $filename ) ) 
			throw new Exception('file not found');
        
		if (!is_string($postfield))
			throw new Exception('download: expected string postfield');
        
		if (is_null($this->url))
			throw new Exception('download: url not defined!');

        if ( !is_null($this->logFile)){
            if (!($logFile = @fopen( $this->logFile, 'a+' )))
				throw new Exception('upload: can\'t open logfile');
			curl_setopt( $this->curl, CURLOPT_STDERR, $logFile );
        }
		
		$post = array();
        $post[$postfield] = '@' . $filename;
        curl_setopt( $this->curl, CURLOPT_URL, $this->url);
		curl_setopt( $this->curl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $this->curl, CURLOPT_FOLLOWLOCATION, 1 );
		
        if (!is_null($this->cookieFile)){
			curl_setopt($this->curl, CURLOPT_COOKIEJAR, trim($this->cookieFile));
			curl_setopt($this->curl, CURLOPT_COOKIEFILE, trim($this->cookieFile));
		}
        
		if (substr($this->url,0,5) == 'https'){
			curl_setopt( $this->curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt( $this->curl, CURLOPT_SSL_VERIFYHOST, 0);
		}
		
		if (count($this->headers)) 
			curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->headers);
        
		if (!is_null($this->userAgent)){
			curl_setopt($this->curl, CURLOPT_USERAGENT, $this->userAgent);
		}
        
		if (!is_null($this->referer))
            curl_setopt( $this->curl, CURLOPT_REFERER, $this->referer );

        curl_setopt( $this->curl, CURLOPT_POST, 1 );
		curl_setopt( $this->curl, CURLOPT_POSTFIELDS, $post );
        curl_exec( $this->curl );
		
		//curl_setopt( $this->curl, CURLOPT_UPLOAD, 0 );
        //curl_setopt( $this->curl, CURLOPT_PUT, 0 );
        //curl_setopt( $this->curl, CURLOPT_INFILE, $file );
        //curl_setopt( $this->curl, CURLOPT_INFILESIZE, filesize( trim( $filename ) ) );            
        
		$errno = curl_errno( $this->curl );
        if ( $logFile )
			fclose( $logFile );       
        return ( $errno )? false : true;
	}
	
	/**
	 * set name the cookie file
     * @param $file string filename
     * @return request object
     */
	public function setCookieFile($file){
		if (!is_string($file) && !is_null($file))
			throw new Exception('setCookieFile: expected string or null');
		$this->cookieFile = $file;
		return $this;
	}
	
	/**
	 * set name the log file
     * @param $file string filename
     * @return request object
     */
	public function setLogFile($file){
		if (!is_string($file) && !is_null($file))
			throw new Exception('setLogFile: expected string or null');
		$this->logFile = $file;
		return $this;
	}
	
	/**
	 * set header Referer
     * @param $refere string referer
     * @return request object
     */
	public function setReferer($referer){
		if (!is_string($referer) && !is_null($referer))
			throw new Exception('setReferer: expected string or null');
		$this->referer = $referer;
		return $this;
	}
	
	/**
	 * set body of request (for POST)
     * @param $body string body
     * @return request object
     */
	public function setBody($body){
		if (!is_string($body) && !is_null($body))
			throw new Exception('setBody: expected string or null');
		$this->body = $body;
		return $this;
	}
	
	/**
	 * set url to request
     * @param $url string url
     * @return request object
     */
	public function setUrl($url){
		if (!is_string($url) && !is_null($url))
			throw new Exception('setUrl: expected string or null');
		$this->url = $url;
		return $this;
	}
	
	/**
	 * set filename to upload
     * @param $filename string filename
     * @return request object
     */
	public function setFileToUpload($filename){
		if (!is_string($filename) && !is_null($filename))
			throw new Exception('setFileToUpload: expected string <filename> or null');
		$this->fileToUpload = $fileToUpload;
		return $this;
	}
	
	/**
	 * set User Agent header
     * @param $ua string User-Agent
     * @return request object
     */
	public function setUserAgent($ua){
		if (!is_string($ua) && !is_null($ua))
			throw new Exception('setUserAgent: expected string or null');
		$this->userAgent = $ua;
		return $this;
	}
	
	/**
	 * set method
     * @param $method string method ('post' or 'get')
     * @return request object
     */
	public function setMethod($method){
		$method = strtoupper($method);
		if ( $method != 'GET' && $method != 'POST' )
			throw new Exception('setMethod: expected "POST" or "GET" string');
		$this->method = $method;
		return $this;
	}
	
	/**
	 * set headers of request
     * @param $headers array headers (name=>value)
     * @return request object
     */
	public function setHeaders($headers){
		if ( !is_array($headers) )
			throw new Exception('setHeaders: expected array');
		$this->headers = $headers;
		return $this;
	}

	/**
     * function ClearSession Clear cookie file
	 * @return request object
     */
    public function clearSession(){
        if( $f = @fopen($this->cookieFile, "w" ) )
            fclose( $f );
		return $this;
	}
	 
	 /**
	 * Converting array contains POST data to POST string
     * in rawurlencode format
     * @param $array array  POST param array
     * @return string  POST string param
     */
     public function makeQueryString( $array )
     {
        if ( !is_array($array) )
			throw new Exception('makeQueryString: expected array');
		$query = '';
        $len = count( $array );
        if ( is_array( $array ) && $len ){
            $count = 0;
            foreach( $array as $key  => $value ){
                $query .= trim( $key ) . '=' . rawurlencode( trim( $value ) );
                $count++;
                if( $count < $len ) $query .= '&'; 
            }
        }
        return $query;
     }
	 
	 /**
	 * Converting array contains POST data to POST string
     * in rawurlencode format and set it as request object param
     * @param $array array  POST param array (name=>value)
     * @return request object
     */
     public function setQueryString( $array )
     {
        if ( !is_array($array) )
			throw new Exception('setQueryString: expected array');
		$query = '';
        $len = count( $array );
        if ( is_array( $array ) && $len ){
            $count = 0;
            foreach( $array as $key  => $value ){
                $query .= trim( $key ) . '=' . rawurlencode( trim( $value ) );
                $count++;
                if( $count < $len ) $query .= '&'; 
            }
        }
        curl_setopt( $this->curl, CURLOPT_URL, $this->url.'?'.$query);
		$this->url .= '?'.$query;
		return $this;
     }

	/**
	* setting proxy server params
	* @param $proxy string Url:port proxy server
	* @param $user string proxy server user
	* @param $pass stirng proxy server pass
	**/
	public function setProxyServer($proxy, $user=null, $pass=null){
		$this->proxy_server = $proxy;
		$this->proxy_server_user = $user;
		$this->proxy_server_pass = $pass;
		return $this;
			
	}

	public function getLastError(){
		return $this->last_error;
	}

}
