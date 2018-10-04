<?php

namespace classes;

class Auth {
	
	static $salt = "GGrrTToo55CC!!XX";
	
	public static $type;
	
	public static function isAdmin() {
		return (isset($_SESSION['AUTH']) && $_SESSION['AUTH'] && in_array('admin', $_SESSION['GROUPS']));
	}
	
	public static function isLogin() {
		return (isset($_SESSION['AUTH']) && $_SESSION['AUTH']);
	}

	public static function makeHash($password) {
		return password_hash(self::$salt.$password, PASSWORD_DEFAULT, ["cost" => 10]);
	}
	
	public static function updateCfgUser($data) {
		if (!isset($data['id']) || !isset($data['login']) || !isset($data['email'])) 
			throw new \classes\BaseException('PARAMS_ERROR');
		
		$data['id'] = (int)$data['id'];
		if (!$data['id']) throw new \classes\BaseException('FIELD_WRONG% id');
		$data['login'] = trim(htmlspecialchars($data['login']));
		if ($data['login']=='') throw new \classes\BaseException('FIELD_WRONG% login');
		$arUser = self::getCfgUser($data['login']);
		// проверим что такой логин либо свой либо еще не существует
		if (!(!$arUser || $arUser['id'] == $data['id']))
			throw new \classes\BaseException('FIELD_WRONG% login');
		$data['email'] = trim(htmlspecialchars($data['email']));
		if ($data['email']=='') throw new \classes\BaseException('FIELD_WRONG% email');
		if (isset($data['password'])) {
			$data['password'] = trim($data['password']);
			if (strlen($data['password']) < 6) throw new \classes\BaseException('FIELD_WRONG% password');
			if (!isset($data['repeat']) || $data['repeat'] != $data['password']) throw new \classes\BaseException('FIELD_WRONG% repeat');
			$data['password'] = self::makeHash($data['password']);
			unset($data['repeat']);
		}
		return $data;
	}
	
	public static function setCfgUser($data) {
		if (isset($data['id']) || !isset($data['login']) || !isset($data['password']) || !isset($data['repeat']) || !isset($data['email'])) 
			throw new \classes\BaseException('PARAMS_ERROR');
		
		$data['login'] = trim(htmlspecialchars($data['login']));
		if ($data['login']=='') throw new \classes\BaseException('FIELD_WRONG% login');
		$data['password'] = trim($data['password']);
		if (strlen($data['password']) < 6) throw new \classes\BaseException('FIELD_WRONG% password');
		if ($data['repeat'] != $data['password']) throw new \classes\BaseException('FIELD_WRONG% repeat');
		$data['email'] = trim(htmlspecialchars($data['email']));
		if ($data['email']=='') throw new \classes\BaseException('FIELD_WRONG% email');
		return $data;
	}

	public static function checkLogin($login, $password) {
		$method = 'get'.self::$type.'User';
		$arUser = self::$method($login);
		if (!$arUser) return false;
		$pass = $arUser['password'];
		unset($arUser['password']);
		return password_verify (self::$salt.$password , $pass )?$arUser:false;
	}
	
	public static function verifyPwd($password, $hash) {
		return password_verify (self::$salt.$password , $hash )?true:false;
	}
	
	public static function login($arUser, $bRemember=false) {
		$_SESSION['USER'] = $arUser;
		$_SESSION['AUTH'] = true;
		$method = 'get'.self::$type.'Groups';
		$_SESSION['GROUPS'] = self::$method($arUser['id']);
		$expire = $bRemember ? time()+60*60*24*30 : 0; 
		setcookie("remember", self::encrypt($arUser['login']), $expire, "/", Core::init()->router->cookie_domain, Core::init()->router->cookie_secure, Core::init()->router->cookie_httponly);
	}
	
	public static function restore() {
		if (isset($_SESSION['AUTH']) || !isset($_COOKIE['remember'])) return;
		$login = self::decrypt($_COOKIE['remember']);
		\Mpakfm\Printu::log($login,'restore $login','file');
		$arUser = self::getCfgUser($login);
		self::login($arUser, true);
		return;
	}


	public static function logout() {
		if (isset($_COOKIE['remember'])) {
			$expire = time()-3600;
			setcookie("remember", self::encrypt($_SESSION['USER']['login']), $expire, "/", Core::init()->router->cookie_domain, Core::init()->router->cookie_secure, Core::init()->router->cookie_httponly);
		}
		unset($_SESSION['USER']);
		unset($_SESSION['GROUPS']);
		$_SESSION['AUTH'] = false;
		\Mpakfm\Printu::log($_SESSION,'logout $_SESSION','file');
	}
	
	public static function encrypt($plaintext) {
		// Encrypt
		$ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
		$iv = openssl_random_pseudo_bytes($ivlen);
		$ciphertext_raw = openssl_encrypt($plaintext, $cipher, ENCRYPTION_KEY, $options=OPENSSL_RAW_DATA, $iv);
		$hmac = hash_hmac('sha256', $ciphertext_raw, ENCRYPTION_KEY, $as_binary=true);
		return base64_encode( $iv.$hmac.$ciphertext_raw );
	}
	
	public static function decrypt($ciphertext) {
		$c = base64_decode($ciphertext);
		$ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
		$iv = substr($c, 0, $ivlen);
		$hmac = substr($c, $ivlen, $sha2len=32);
		$ciphertext_raw = substr($c, $ivlen+$sha2len);
		$plaintext = openssl_decrypt($ciphertext_raw, $cipher, ENCRYPTION_KEY, $options=OPENSSL_RAW_DATA, $iv);
		$calcmac = hash_hmac('sha256', $ciphertext_raw, ENCRYPTION_KEY, $as_binary=true);
		return (hash_equals($hmac, $calcmac)) ? $plaintext : false;
	}

	public static function getDbUser($login) {
		
	}
	
	public static function getCfgUser($login) {
		$users = Core::init()->arUsers;
		foreach ($users as $user) {
			if ($user['login'] == $login) return $user;
		}
		return false;
	}
	
	public static function getDbGroups($id) {
		
	}
	
	public static function getCfgGroups($id) {
		$groups = [];
		$cfg_groups = Core::init()->arAccess['groups'];
		foreach ($cfg_groups as $group => $users) {
			if (in_array($id, $users))
				$groups[] = $group;
		}
		return $groups;
	}
}