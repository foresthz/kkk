<?
global $SQUID_ACCL;
//$fromhost=$_SERVER["REMOTE_ADDR"];
dl("../libexec/bbs/phpbbslib.so");
global $fromhost;
global $fullfromhost;
global $loginok;
global $currentuinfo;
global $currentuinfo_num;
global $currentuser;
global $currentuuser_num;
$currentuinfo=array ();
$currentuser=array ();
$loginok=0;
if (SQUID_ACCL) {
  $fullfromhost=$_SERVER["HTTP_X_FORWARDED_FOR"];
  if ($fullfromhost=="") {
      $fullfromhost=$_SERVER["REMOTE_ADDR"];
      $fromhost=$fullfromhost;
  }
  else {
	$str = strrchr($fullfromhost, ",");
	if ($str!=FALSE)
		$fromhost=substr($str,1);
        else
		$fromhost=$fullfromhost;
  }
}
else {
  $fromhost=$_SERVER["REMOTE_ADDR"];
  $fullfromhost=$fromhost;
}

bbs_setfromhost($fromhost,$fullfromhost);

$utmpkey = $_COOKIE["UTMPKEY"];
$utmpnum = $_COOKIE["UTMPNUM"];
$userid = $_COOKIE["UTMPUSERID"];
if ($utmpkey!="") {
  if (bbs_setonlineuser($userid,$utmpnum,$utmpkey,$currentuinfo)==0) {
    $loginok=1;
    $currentuinfo_num=bbs_getcurrentuinfo();
    $currentuser_num=bbs_getcurrentuser($currentuuser);
  }
} 
?>
