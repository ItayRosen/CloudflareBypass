<?php
class cfBypass {
	
	public $url;
	private $action;
	private $jschl_vc;
	private $pass;
	private $jschl_answer;
	private $cookies;
	
	public function bypass() {
		//get first data
		$data = $this -> request($this -> url);
		//check if CF protection is on
		if (!$this -> isCloudflare($data)) {
			return $data;
		}
		//set get solution variables
		$this -> action = $this -> getStringBetween($data,'action="','"'); //set solutin url
		$this -> jschl_vc =  $this -> getStringBetween($data,'jschl_vc" value="','"'); //set solutin hash
		$this -> pass =  $this -> getStringBetween($data,'pass" value="','"'); //set solutin pass
		$this -> jschl_answer = $this -> jschl_answer($data);
		//build solution url
		$solutionUrl = $this -> buildUrl();
		//send request solution url
		$this -> request($solutionUrl);
		//get bypassed content
		$content = $this -> request($this -> url);
		return $content;
	}
	
	private function buildUrl() {
		$url = parse_url($this -> url, PHP_URL_SCHEME);
		$url .= '://';
		$url .= parse_url($this -> url, PHP_URL_HOST);
		$url .= $this -> action;
		$url .= '?jschl_vc='.$this -> jschl_vc;
		$url .= '&pass='.$this -> pass;
		$url .= '&jschl_answer='.$this -> jschl_answer;
		
		return $url;
	}
	
	private function jschl_answer($data) {
		$content = $this -> getStringBetween($data,'s,t,o,p,b,r,e,a,k,i,n,g,f,','</script>'); //get only relevant content
		preg_match_all('/:[\/!\[\]+()]+|[-*+\/]?=[\/!\[\]+()]+/', $content, $mathObjects); //get math objects to array
		$php_code = "";
		//loop through math objects and add them to php code
		foreach ($mathObjects[0] as $js_code) {
			$js_code = str_replace(array(")+(",  "![]","!+[]", "[]"), array(").(", "(!1)", "(!0)", "(0)"), $js_code);
			$php_code .= '$math' . ($js_code[0] == ':' ? '=' . substr($js_code, 1) : $js_code) . ';';
		}
		eval($php_code);
		$solution = round($math, 10); //add to solution
		$solution += strlen(parse_url($this -> url, PHP_URL_HOST)); //add domain length to solution
		return $solution;
	}
	
	private function isCloudflare($data) {
		if (strpos($data, "DDoS protection by Cloudflare") !== FALSE) {
			return true;
		}
		else {
			return false;
		}
	}
	
	private function request($url) {
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_COOKIE, $this -> cookies );
		curl_setopt($ch, CURLOPT_REFERER, $this -> url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36');
        $output = curl_exec($ch); 
		echo htmlspecialchars($output).'<br>';
		curl_close($ch);
		preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $output, $matches);
		$this -> cookies .= @implode('; ',$matches[1]);
		return $output;
	}
	
	private function getStringBetween($string, $start, $end){
		$string = ' ' . $string;
		$ini = strpos($string, $start);
		if ($ini == 0) return '';
		$ini += strlen($start);
		$len = strpos($string, $end, $ini) - $ini;
		return substr($string, $ini, $len);
	}
}
