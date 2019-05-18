<?php
	error_reporting(E_ALL ^ E_NOTICE);
	ob_end_clean();
	header("content-Type: text/html; charset=utf-8");
	date_default_timezone_set("Asia/Shanghai");
	function curl_get($url, $cookie="cookie", $use=false, $save=false, $referer=null, $post_data=null){
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.131 Safari/537.36");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if ($use) {
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
		}
		if ($save) {
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
		}
		if (isset($referer)) {
			curl_setopt($ch, CURLOPT_REFERER, $referer);
		}
		if (is_array($post_data)) {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		}
		$content = curl_exec($ch);
		curl_close($ch);
		return $content;
	}
	function get_formhash($res){
		if (preg_match("/name=\"formhash\" value=\"(.*?)\"/i", $res, $matches)) {
			return $matches[1];
		} else {
			return "none";
		}
	}
	function getSubStr($str, $leftStr, $rightStr) {
		$left = strpos($str, $leftStr);
		$right = strpos($str, $rightStr,$left);
		if ($left < 0 or $right < $left) {
			return "none";
		}
		return substr($str, $left + strlen($leftStr), $right - $left - strlen($leftStr));
	}
	function unicode_decode($name) {
		$pattern = "/([\w]+)|(\\\u([\w]{4}))/i";
		preg_match_all($pattern, $name, $matches);
		if (!empty($matches)) {
			$name = "";
			for ($j = 0; $j < count($matches[0]); $j++) {
				$str = $matches[0][$j];
				if (strpos($str, "\\u") === 0) {
					$code = base_convert(substr($str, 2, 2), 16, 10);
					$code2 = base_convert(substr($str, 4), 16, 10);
					$c = chr($code) . chr($code2);
					$c = iconv("UCS-2", "UTF-8", $c);
					$name .= $c;
				} else {
					$name .= $str;
				}
			}
		}
		return $name;
	}
	function printFile($file, $msg) {
		$msg = "[".date("H:i:s")."] " . $msg; 
		fwrite($file, $msg);
		echo $msg;
	}
	require (__DIR__) . "/" . "config.php";
	$cookie = (__DIR__) . "/" . $cookie;
	$version = "1.3.1";
	$link_base = "http://www.oneplusbbs.com/";
	$link_sumbit = $link_base . "plugin.php?id=dsu_paulsign:sign&operation=qiandao&infloat=1&inajax=1";
	$link_hash = $link_base . "plugin.php?id=dsu_paulsign:sign";
	$link_draw = $link_base . "plugin.php?id=choujiang&do=draw";
	$logFile = fopen((__DIR__) . "/log/" . time() . ".log", "w") or die("Unable to open file!");
	$msg = "[STARTUP] [INFO] MlgmXyysd Automatic Signer Bot v" . $version . "\n";
	printFile($logFile, $msg);
	$mode = "ALL";
	if ($argc>=2) {
		if ($argv[1] == "draw") {
			$mode = "DRAW";
		} else if ($argv[1] == "sign") {
			$mode = "SIGN";
		}
	} else if ($_GET["mode"] == "draw" || $_POST["mode"] == "draw") {
		$mode = "DRAW";
	} else if ($_GET["mode"] == "sign" || $_POST["mode"] == "sign") {
		$mode = "SIGN";
	}
	if (file_exists($cookie)) {
		$msg = "[INITAL] [INFO] Cookies: ".$cookie.".\n";
		printFile($logFile, $msg);
		switch ($mode) {
			case "SIGN":
				for (;;) {
					$formhash = get_formhash(curl_get($link_hash, $cookie, true, true));
					if ($formhash!="none") {
						$form = array("qdmode" => 1,
									  "formhash" => $formhash,
									  "qdxq" => "kx",
									  "fastreply" => 0,
									  "todaysay" => "MlgmXyysd Automatic Signer Bot v".$version.": ".time());
						$refer = curl_get($link_sumbit, $cookie, true, true, null, $form);
						$return = getSubStr($refer, "<div class=\"c\">\r\n", "</div>");
						if (strpos($return, "成功") !== false) {
							$msg = "[SIGN] [INFO] ".$return."\n";
							printFile($logFile, $msg);
							break;
						}
						if (strpos($return, "您所在的用户组未被加入允许签到的行列") !== false) {
							$msg = "[SIGN] [WARN] Cookies has expired.\n";
							printFile($logFile, $msg);
							break;
						}
					} else {
						$msg = "[SIGN] [WARN] Form hash is null.\n";
						printFile($logFile, $msg);
					}
				}
				break;
			case "DRAW":
				for (;;) {
					$refer  = curl_get($link_draw, $cookie, true, true, null, null);
					$return = unicode_decode(getSubStr($refer, "\"msg\":\"", "\",\"data\""));
					if (strpos($return, "抽奖速度太快了") === false && strpos($return, "未登录") === false) {
						$msg = "[DRAW] [INFO] " . $return . "\n";
						printFile($logFile, $msg);
					}
					if (strpos($return, "你今天抽奖次数已用完") !== false) {
						break;
					}
					if (strpos($return, "未登录") !== false) {
						$msg = "[DRAW] [WARN] Cookies has expired.\n";
						printFile($logFile, $msg);
						break;
					}
				}
				break;
			case "ALL":
				for (;;) {
					$formhash = get_formhash(curl_get($link_hash, $cookie, true, true));
					if ($formhash!="none") {
						$form = array("qdmode" => 1,
									  "formhash" => $formhash,
									  "qdxq" => "kx",
									  "fastreply" => 0,
									  "todaysay" => "MlgmXyysd Automatic Signer Bot v".$version.": ".time());
						$refer = curl_get($link_sumbit, $cookie, true, true, null, $form);
						$return = getSubStr($refer, "<div class=\"c\">\r\n", "</div>");
						if (strpos($return, "成功") !== false) {
							$msg = "[SIGN] [INFO] ".$return."\n";
							printFile($logFile, $msg);
							break;
						}
						if (strpos($return, "您所在的用户组未被加入允许签到的行列") !== false) {
							$msg = "[SIGN] [WARN] Cookies has expired.\n";
							printFile($logFile, $msg);
							break;
						}
					} else {
						$msg = "[SIGN] [WARN] Form hash is null.\n";
						printFile($logFile, $msg);
					}
				}
				for (;;) {
					$refer = curl_get($link_draw, $cookie, true, true, null, null);
					$return = unicode_decode(getSubStr($refer, "\"msg\":\"", "\",\"data\""));
					if (strpos($return, "抽奖速度太快了") === false && strpos($return, "未登录") === false) {
						$msg = "[DRAW] [INFO] " . $return . "\n";
						printFile($logFile, $msg);
					}
					if (strpos($return, "你今天抽奖次数已用完") !== false) {
						break;
					}
					if (strpos($return, "未登录") !== false) {
						$msg = "[DRAW] [WARN] Cookies has expired.\n";
						printFile($logFile, $msg);
						break;
					}
				}
				break;
		}
	} else {
		$msg = "[INITIAL] [ERROR] Cookies file not found: " . $cookie ."\n";
		printFile($logFile, $msg);
	}
	$msg = "[THREAD] [INFO] All done.\n";
	printFile($logFile, $msg);
	exit(0);
?>