<?php
namespace Adhocore;

defined('COREPATH') or die('Error 403');

class Hash {
	
	private $size;
	
	public function __construct()
	{
		$this->size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
	}

	public function encrypt($data)
	{
		return base64_encode($iv.mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5(ahc()->app_config('crypt_key')), 
				$data, MCRYPT_MODE_CBC, $iv = mcrypt_create_iv($this->size, MCRYPT_RAND)));
		
	}

	public function decrypt($data)
	{
		$data = base64_decode($data);
		return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5(ahc()->app_config('crypt_key')), 
				substr($data, $this->size), MCRYPT_MODE_CBC, substr($data, 0, $this->size)), "\0");
		
	}

}