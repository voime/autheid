<?php
header ("Content-Type: text/html; charset=UTF-8");
$conf = include('conf.php');
$client = $_GET['id'];

if(!isset($conf[$client]))
	die("no client");

if (parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) != $conf[$client]['from'])
	die("wrong from");

$s = $_SERVER['SSL_CLIENT_S_DN'];
$l = preg_split ('|/|', $s, -1, PREG_SPLIT_NO_EMPTY);
$valid_params=array("SN","GN","serialNumber");

    foreach ($l as $e) {
        list ($n, $v) = explode ('=', $e, 2);
        if(in_array($n,$valid_params)){
			$x=certstr2utf8($v);
			$data[$n]=$x;
			$unhash.=$x; // put param to hash
			$hidden.='<input type="hidden" name="'.$n.'" value="'.$x.'">';
		}
    }

$timestamp=time();
// hash(sha256)
// SN.GN.serialNumber.timestamp.secret
$unhash.=$timestamp.$conf[$client]['secret'];
$hash=hash("sha256",$unhash);

// convert encoding
  function certstr2utf8 ($str) {
        $str = preg_replace ("/\\\\x([0-9ABCDEF]{1,2})/e", "chr(hexdec('\\1'))", $str);
        $result="";
        $encoding=mb_detect_encoding($str,"ASCII, UCS2, UTF8");
        if ($encoding=="ASCII") {
            $result=mb_convert_encoding($str, "UTF-8", "ASCII");
        } else {
            if (substr_count($str,chr(0))>0) {
                $result=mb_convert_encoding($str, "UTF-8", "UCS2");
            } else {
                $result=$str;
            }
        }
        return $result;
    }
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
 <html>
 <head>
 <title>ID login</title>
 <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
 <script type="text/javascript" language="javascript">
 	function submitform(){ document.forms.idForm.submit(); }
 </script>
 </head>
 <body onload="submitform()">
 <p>Teie isikukood on <?PHP echo $data["serialNumber"]; ?></p>
    <form name="idForm" id="idForm" action="<?PHP echo $conf[$client]['return']; ?>" method="POST">
        <?PHP echo $hidden; ?>
        <input type="hidden" name="hash" value="<?PHP echo $hash; ?>">
        <input type="hidden" name="timestamp" value="<?PHP echo $timestamp; ?>">
        <input type="submit" name="idlogin" value="Edasi">
   </form>
 <script type="text/javascript" language="javascript">
	document.forms.idForm.submit();
 </script>
 </body>
 </html>
