<?php
if (!defined('_BBS_FUNCS_PHP_'))
{
define('_BBS_FUNCS_PHP_', 1);

if ( (!isset($_COOKIE['iscookies'])) || ($_COOKIE['iscookies']==''))
{
	setcookie('iscookies','0',time()+3650*24*3600);
	print '<META http-equiv=Content-Type content=text/html; charset=gb2312><meta HTTP-EQUIV=REFRESH CONTENT=3>���ڵ�¼��̳����<br><br>��ϵͳҪ��ʹ��COOKIES�������������������COOKIES���������ܵ�¼��ϵͳ����';
	exit();
} 

function getmicrotime(){ 
   list($usec, $sec) = explode(" ",microtime()); 
   return ((float)$usec + (float)$sec); 
} 

global $StartTime;
$StartTime=getmicrotime();

// NOTE: If you want to statically link smth_bbs phpbbslib into php,
//       you *MUST* set enable_dl variable to Off in php.ini file.
if (BUILD_PHP_EXTENSION==0)
	@dl("libphpbbslib.so");

if (!bbs_ext_initialized())
	bbs_init_ext();

global $SQUID_ACCL;
global $BBS_PERM_POSTMASK;
global $BBS_PERM_NOZAP;
global $BBS_HOME;
global $BBS_FULL_NAME;
//$fromhost=$_SERVER["REMOTE_ADDR"];
global $fromhost;
global $fullfromhost;
global $loginok; //�Ƿ�����ʽ�û���¼��
global $guestloginok;
//global $currentuinfo;
global $currentuinfo_num;
//global $currentuser;
global $currentuuser_num;
global $cachemode;
$yank=1;

$loginok=0;
$guestloginok=0;

/* ���ļ��ڲ����� */
$needloginok = false; //��ҳ���Ƿ������ʽ�û��ſ��Է��ʣ�
$showedbanner = false; //�Ƿ��Ѿ���ʾ�� banner
$stats=''; //ҳ�����
$errMsg=''; //������Ϣ
$foundErr = false; //�Ƿ��д���
$sucmsg=''; //�ɹ���Ϣ


if (!isset($nologin)) {
	$nologin=0;
}

if (!isset($setboard)){
	$setboard=0;
}

if (!isset($needlogin)){ //��ҳ���Ƿ���Ҫ����cookie�ȵ�¼������Ĭ����Ҫ
	$needlogin=1;
}

$cachemode="";
$currentuinfo=array ();
$currentuser=array ();
$dir_modes = array(
	"NORMAL" => 0,
	"DIGEST" => 1,
	"THREAD" => 2,
	"MARK" => 3,
	"DELETED" => 4,
	"JUNK" => 5,
	"ORIGIN" => 6,
	"AUTHOR" => 7,
	"TITLE" => 8,
	"ZHIDING" => 9
);
/**
 * Constants of board flags, packed in an array.
 */
$BOARD_FLAGS = array(
	"VOTE" => 0x01,
	"NOZAP" => 0x02,
	"READONLY" => 0x04,
	"JUNK" => 0x08,
	"ANONY" => 0x10,
	"OUTGO" => 0x20,
	"CLUBREAD" => 0x40,
	"CLUBWRITE" => 0x80,
	"CLUBHIDE" => 0x100,
	"ATTACH" => 0x200
	);
$filename_trans = array(" " => "_", 
	";" => "_", 
	"|" => "_",
	"&" => "_",
	">" => "_",
	"<" => "_",
	"*" => "_",
	"\"" => "_",
	"'" => "_"
	);
require("site.php");

define("ENCODESTRING","0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ");
function decodesessionchar($ch)
{
	return strpos(ENCODESTRING,$ch);
}

$loginok=0;

@$fullfromhost=$_SERVER["HTTP_X_FORWARDED_FOR"];
  if ($fullfromhost=="") {
	  @$fullfromhost=$_SERVER["REMOTE_ADDR"];
	  $fromhost=$fullfromhost;
  }
  else {
	$str = strrchr($fullfromhost, ",");
	if ($str!=FALSE)
		$fromhost=substr($str,1);
		else
		$fromhost=$fullfromhost;
  }

//sometimes,fromhost has strang space
bbs_setfromhost(trim($fromhost),trim($fullfromhost));

$compat_telnet=0;
@$sessionid = $_GET["sid"];

//TODO: add the check of telnet compat
if (($sessionid!='')&&($_SERVER['PHP_SELF']=='/bbscon.php')) {
	$utmpnum=decodesessionchar($sessionid[0])+decodesessionchar($sessionid[1])*36+decodesessionchar($sessionid[2])*36*36;
	$utmpkey=decodesessionchar($sessionid[3])+decodesessionchar($sessionid[4])*36+decodesessionchar($sessionid[5])*36*36
		+decodesessionchar($sessionid[6])*36*36*36+decodesessionchar($sessionid[7])*36*36*36*36+decodesessionchar($sessionid[8])*36*36*36*36*36;
	$userid='';
	$compat_telnet=1;
} else {
	@$utmpkey = $_COOKIE["W_UTMPKEY"];
	@$utmpnum = $_COOKIE["W_UTMPNUM"];
	@$userid = $_COOKIE["W_UTMPUSERID"];
	@$userpassword=$_COOKIE["W_PASSWORD"];
}
if ($userid=='') {
	$userid='guest';
}

$setonlined=0;
if ($nologin==0) {

	// add by stiger, login as "guest" default.....
	if ( ($userid=='guest') && ($utmpkey == "")&&($needlogin!=0)){ 
		$error = bbs_wwwlogin(0);
		if($error == 2 || $error == 0){
			$guestloginok=1;
		}
	} else {
		if ( ($utmpkey!="") || ($userid!='guest')) {
			$ret=bbs_setonlineuser($userid,intval($utmpnum),intval($utmpkey),$currentuinfo,$compat_telnet);
			if (($ret)==0) {
				if ($userid!="guest") {
					$loginok=1;
				} else {
					$guestloginok=1;
				}
				$currentuinfo_num=bbs_getcurrentuinfo();
				$currentuser_num=bbs_getcurrentuser($currentuser);
				$setonlined=1;
			} else {
				if (($userid!='guest') && (bbs_checkpasswd($userid,$userpassword)==0)){
					$ret=bbs_wwwlogin(1);
					if ( ($ret==2) || ($ret==0) ){
						if ($userid!="guest") {
							$loginok=1;
						} else {
							$guestloginok=1;
						}

					}else if ($ret==5) {
						foundErr("����Ƶ����¼��");
					}
				} else {
					$error = bbs_wwwlogin(0);
					if($error == 2 || $error == 0){
						$guestloginok=1;
					}
				}
			}
		}
	}
}

if  ( ($loginok || $guestloginok ) && ($setonlined==0) ){
	$data=array();
	$currentuinfo_num=bbs_getcurrentuinfo($data);
	bbs_setonlineuser($userid,$currentuinfo_num,$data["utmpkey"],$currentuinfo,$compat_telnet);
	$currentuser_num=bbs_getcurrentuser($currentuser);
	$path='';
	setcookie("W_UTMPUSERID",$data["userid"],time()+360000,"",$path);
	setcookie("W_UTMPKEY",$data["utmpkey"],time()+360000,$path);
	setcookie("W_UTMPNUM",$currentuinfo_num,time()+360000,$path);
	setcookie("W_LOGINTIME",$data["logintime"],time()+360000,$path);
}


if (($needlogin!=0)&&($loginok!=1)&& ($guestloginok!=1) ){
	show_nav();
	foundErr("����δ��¼��");
	html_error_quit();
	show_footer();
	exit(0);
	return;
}

if ( ($loginok==1) || ($guestloginok==1) ) {
	$yank=bbs_is_yank() ? 0 : 1;
	if ($setboard==1) 
		bbs_set_onboard(0,0);
}

function valid_filename($fn)
{
	if ((strstr($fn,"..")!=FALSE)||(strstr($fn,"/")!=FALSE))
		return 0;
	if ( (strstr($fn,"&")!=FALSE)||(strstr($fn,";")!=FALSE)
	   ||(strstr($fn,"|")!=FALSE)||(strstr($fn,"*")!=FALSE)
	   ||(strstr($fn,"<")!=FALSE)||(strstr($fn,">")!=FALSE))
		return 0;
	return 1;
}

function bbs_get_board_filename($boardname,$filename)
{
	return "boards/" . $boardname . "/" . $filename;
}

function bbs_get_vote_filename($boardname, $filename)
{
	return "vote/" . $boardname . "/" . $filename;
}

function get_bbsfile($relative_name)
{
	return BBS_HOME . $relative_name;
}

function get_secname_index($secnum)
{
	global $section_nums;
	$arrlen = sizeof($section_nums);
	for ($i = 0; $i < $arrlen; $i++)
	{
		if (strcmp($section_nums[$i], $secnum) == 0)
			return $i;
	}
	return -1;
}

function bbs_is_owner($article, $user)
{
	if ($article["OWNER"] == $user["userid"])
		return 1;
	else
		return 0;
}

function bbs_can_delete_article($board, $article, $user)
{
	if (bbs_is_bm($board["NUM"], $user["index"]) 
			|| bbs_is_owner($article, $user))
		return 1;
	else
		return 0;
}

function bbs_can_edit_article($board, $article, $user)
{
	if (bbs_is_bm($board["NUM"], $user["index"]) 
			|| bbs_is_owner($article, $user))
		return 1;
	else
		return 0;
}

function cache_header($scope,$modifytime=0,$expiretime=300)
{
	global $cachemode;
	session_cache_limiter($scope);
	$cachemode=$scope;
	if ($scope=="no-cache")
		return FALSE;
	@$oldmodified=$_SERVER["HTTP_IF_MODIFIED_SINCE"];
	if ($oldmodified!="") {
				$oldtime=strtotime($oldmodified);
	} else $oldtime=0;
	if ($oldtime>=$modifytime) {
		header("HTTP/1.1 304 Not Modified");
			header("Cache-Control: max-age=" . "$expiretime");
		return TRUE;
	}
	header("Last-Modified: " . gmdate("D, d M Y H:i:s", $modifytime) . "GMT");
	header("Expires: " . gmdate("D, d M Y H:i:s", $modifytime+$expiretime) . "GMT");
	header("Cache-Control: max-age=" . "$expiretime");
	return FALSE;
}

function html_init($charset="",$title="",$otherheader="")
{
	global $cachemode;
	global $HTMLTitle;
	global $HTMLCharset;
	global $DEFAULTStyle;
	global $stats;

	//session_start();
	if ($charset==""){
		$charset=$HTMLCharset;
	}
	if ($title=="") {
		$title=$HTMLTitle;
	}
	if (isset($stats) ){
		$title = $title . ' -- ' . $stats;
	}
	if ($cachemode=="") {
		cache_header("no-cache");
		Header("Cache-Control: no-cache");
	}
	@$css_style = $_COOKIE["style"];
	if ($css_style==''){
		$css_style=$DEFAULTStyle;
	}
?>
<?xml version="1.0" encoding="<?php echo $charset; ?>"?>
<!DOCTYPE html
	 PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset; ?>"/>
<title><?php echo $title; ?></title>
<link rel="stylesheet" type="text/css" href="css/ansi.css"/>
<link rel="stylesheet" type="text/css" href="css/common.css"/>
<link rel="stylesheet" type="text/css" href="css/<?php echo $css_style; ?>.css"/>
<?php echo($otherheader); ?>
</head>
<?php
}


function showLogon($showBack = 0, $comeurl = "") {
	if ($comeurl == "") {
		if (isset($_SERVER['HTTP_REFERER'])) {
			$comeurl = $_SERVER['HTTP_REFERER'];
		}
	}
?>
	<table cellpadding=3 cellspacing=1 align=center class=TableBorder1 style="width: 75%;">
	<form action="logon.php" method=post> 
	<input type="hidden" name="action" value="doLogon">
	<tr>
	<th valign=middle colspan=2 align=center height=25>�����������û����������¼</td></tr>
	<tr>
	<td valign=middle class=TableBody1>�����������û���</td>
	<td valign=middle class=TableBody1><INPUT name=id type=text tabindex="1"> &nbsp; <a href="register.php">û��ע�᣿</a></td></tr>
	<tr>
	<td valign=middle class=TableBody1>��������������</font></td>
	<td valign=middle class=TableBody1><INPUT name=password type=password tabindex="2"> &nbsp; <!--<a href="foundlostpass.php">�������룿</a>--></td></tr>
	<tr>
	<td class=TableBody1 valign=top width=30% ><b>Cookie ѡ��</b><BR> ��ѡ����� Cookie ����ʱ�䣬�´η��ʿ��Է������롣</td>
	<td valign=middle class=TableBody1>
	<input type=radio name=CookieDate value=0 checked>�����棬�ر��������ʧЧ<br>
				<input type=radio name=CookieDate value=1>����һ��<br>
				<input type=radio name=CookieDate value=2>����һ��<br>
				<input type=radio name=CookieDate value=3>����һ��<br>                
	</td></tr>
	<input type=hidden name=comeurl value="<?php echo htmlspecialchars($comeurl); ?>">
	<tr>
	<td class=TableBody2 valign=middle colspan=2 align=center><input tabindex="3" type=submit name=submit value="�� ¼">
<?php
	if ($showBack) {
?>
	&nbsp;&nbsp;<input type=button name="back" value="�� ��" onclick="location.href='<?php echo htmlspecialchars($comeurl, ENT_QUOTES); ?>'">
<?php
	}
?>
	</td></tr></form></table>
<?php
}

function requireLoginok($msg = false, $directexit = true) {
	global $loginok;
	global $needloginok;
	global $showedbanner;
	$needloginok = 1;
	if ($loginok == 1) return;
	foundErr(($msg === false) ? "��ҳ��Ҫ������ʽ�û���ݵ�¼֮����ܷ��ʣ�" : $msg, $directexit, false);
}

function setSucMsg($msg){
	global $sucmsg;
	$sucmsg.='<li>'.$msg.'</li>';
}
function setStat($stat){
	GLOBAL $stats;
	$stats=$stat;
}

function foundErr($msg, $directexit = true, $showmsg = true){ //ToDo: non-standard HTML - atppp
	global $errMsg;
	global $foundErr;
	global $showedbanner;
	$errMsg.='<li>'.$msg.'</li>';
	$foundErr=true;
	if ($directexit) {
		if (!$showedbanner) show_nav();
		show_footer($showmsg, true);
		exit;
	}
}

function isErrFounded(){
	GLOBAL $foundErr;
	return $foundErr;
}

function html_error_quit()
{
	global $errMsg;
	global $needloginok;
	global $loginok;
?>
<br>
<table cellpadding=3 cellspacing=1 align=center class=TableBorder1 style="width: 75%;">
<tr align=center>
<th height=25>��̳������Ϣ</th>
</tr>
<tr>
<td class=TableBody1>
<b>��������Ŀ���ԭ��</b>
<ul>
<li>���Ƿ���ϸ�Ķ��˰����ļ�����������û�е�¼���߲�����ʹ�õ�ǰ���ܵ�Ȩ�ޡ�</li>
<?php   echo $errMsg; ?>
</ul>
</td></tr>
<?php
	if (($needloginok!=0)&&($loginok!=1)) {
		echo "</table>";
  		showLogon(1);
  	} else {
?>
	<tr>
	<td class=TableBody2 valign=middle align=center><a href="<?php echo $_SERVER['HTTP_REFERER']; ?>"> <<������һҳ </a></td></tr></table>
<?php
	}
} 

function html_success_quit($Desc='',$URL='')
{
  global $sucmsg;
?>
<br>
<table cellpadding=3 cellspacing=1 align=center class=TableBorder1 style="width: 75%;">
<tr align=center>
<th width="100%">��̳�ɹ���Ϣ</th>
</tr>
<tr>
<td width="100%" class=TableBody1>
<b>�����ɹ���</b>
<ul>
<?php   echo $sucmsg; ?>
</ul>
</td></tr>
<tr align=center><td width="100%" class=TableBody2>
<?php
	if ($Desc=='') {
?>
<a href="<?php   echo $_SERVER['HTTP_REFERER']; ?>"> << ������һҳ</a>
<?php
	} else {
?>
<a href="<?php   echo $URL; ?>"> << <?php echo $Desc; ?></a>
<?php
	}
?>
</td></tr>
</table>
<?php 
} 

function sizestring($size)
{
	if ($size<1024)
	  return "$size";
	$fsize=((double)$size)/1024;
	if ($fsize<1024) {
	  return sprintf("%01.2fk","$fsize");
	}
	$fsize=((double)$fsize)/1024;
	if ($fsize<1024) {
	  return sprintf("%01.2fM","$fsize");
	}
	$fsize=((double)$fsize)/1024;
	if ($fsize<1024) {
	  return sprintf("%01.2fG","$fsize");
	}
	$fsize=((double)$fsize)/1024;
	if ($fsize<1024) {
	  return sprintf("%01.2fT","$fsize");
	}
}

function show_nav($boardName='')
{
	global $Banner;
	global $SiteName;
	global $SiteURL;
	global $loginok;
	global $currentuser;
	global $currentuinfo;
	global $showedbanner;
	global $needloginok;
	$showedbanner = true;

  html_init();
?>
<script language="javascript">
<!--
	var siteconf_SMS_SUPPORT = <?php echo SMS_SUPPORT?"true":"false"; ?>;
	var boardsPerLine = <?php echo BOARDS_PER_ROW; ?>;
//-->
</script>
<script src="inc/funcs.js"  language="javascript"></script>
<body topmargin=0 leftmargin=0 onmouseover="HideMenu(event);">
<script src="inc/browser.js"  language="javascript"></script>
<div id=menuDiv class="navClass1"></div>
<table cellspacing=0 cellpadding=0 align=center class="navClass2">
<tr><td width=100% >
<table width=100% align=center border=0 cellspacing=0 cellpadding=0>
<tr><td class=TopDarkNav height=9></td></tr>
<tr><td height=70 class=TopLighNav2>

<TABLE border=0 width="100%" align=center>
<TR>
<TD align=left width="25%"><a href="<?php  echo  $SiteURL; ?>" target="_blank"><img border=0 src='<?php echo  $Banner; ?>'></a></TD>
<TD Align=center width="65%">
<?php echo MAINTITLE; ?>
</td>
<td align=right style="line-height: 15pt" width="10%">
<nobr><a href="#" onClick="window.external.AddFavorite('<?php   echo $SiteURL; ?>', '<?php   echo $SiteName; ?>');">�����ղ�</a></nobr><br>
<nobr><a href="">��ϵ����</a></nobr><br>
<nobr><a href="">��̳����</a></nobr>
</td>
</td></tr>
</table>

</td></tr>
<tr><td class=TopLighNav height=9></td></tr>
<?php
	if ($boardName !== false) {
?>
		<tr> 
		  <td class=TopLighNav1 height=22  valign="middle">&nbsp;&nbsp;
<?php   
	if ($loginok!=1)  {
?>
<a href="logon.php">��¼</a> <img src=pic/navspacer.gif align=absmiddle> <a href="register.php">ע��</a>
<?php  
	}  else  {
		echo '��ӭ�� <b>'.$currentuser['userid'].'</b> ';
		if ($currentuser["userlevel"] & BBS_PERM_CLOAK) {
?>
<img src=pic/navspacer.gif align=absmiddle>
<a href="changecloak.php"><?php echo $currentuinfo["invisible"]?"����":"����"; ?></a> 
<?php
		}
?>
<img src=pic/navspacer.gif align=absmiddle> <a href="logon.php">�ص�¼</a> 
<img src=pic/navspacer.gif align=absmiddle> <a href="usermanagemenu.php" onMouseOver='ShowMenu(manage,100,event)'>�û����ܲ˵�</a>
<img src=pic/navspacer.gif align=absmiddle> <a href="#" onMouseOver='ShowMenu(talk,100,event)'≯��˵�ز˵�</a>
<?php
 }
 if (AUDIO_CHAT) {
?>
<img src=pic/navspacer.gif align=absmiddle>  <a href="http://voicechat.zixia.net:10015/voicechat.htm?r=1" target=_blank>����������</a>
<?php
 }
?>
 <img src=pic/navspacer.gif align=absmiddle>  <a title="������ǰ����" href="query.php<?php echo $boardName==''?'':'?boardName='.$boardName; ?>" onMouseOver='ShowMenu(query,100,event)'>����</a> 
 <img src=pic/navspacer.gif align=absmiddle>  <a href="#" onMouseOver='ShowMenu(stylelist,100,event)'>��ѡ���</a> 
<?php 
	if ($loginok) {
?>
<img src=pic/navspacer.gif align=absmiddle> <a href="logout.php<?php if ($needloginok!=0) echo "?jumphome=1"; ?>">�˳�</a>
<?php   
	}
?>
			</td>
		</tr>
<?php
	}
?>
</table>
</td></tr>
</table>
<?php 
} 

function head_var($Title='', $URL='',$showWelcome=0)
{
  GLOBAL $SiteName;
  GLOBAL $stats;
  if ($URL=='') {
	  $URL=$_SERVER['PHP_SELF'];
  }
?>
<?php
  if ($showWelcome==1) {
?>
<br>
<table cellspacing=1 cellpadding=3 align=center border=0 width="97%">
<tr>
<td height=25>
>> ��ӭ���� <B><?php       echo $SiteName; ?></B>
</td></tr>
</table>
<?php
  } 
?>
<table cellspacing=1 cellpadding=3 align=center class=TableBorder2>
<tr><td>
<img src="pic/forum_nav.gif" align=absmiddle> <a href="index.php"><?php   echo $SiteName; ?></a> �� 
<?php 
	if ($Title!='') {
		echo  "<a href=".$URL.">".$Title."</a> �� ";
	}
	echo $stats;
?>
</td></td>
</table>
<br>
<?php 
} 

function show_footer($showmsg = true, $showerr = true)
{
  global $Version;
  global $Copyright;
  global $StartTime;
  global $FooterBan;
  global $loginok;
  global $conn;

  if ($showerr) {
  	if (isErrFounded()) {
  		html_error_quit();
  	}
  }
  $endtime=getmicrotime();
?>
<p>
<TABLE cellSpacing=0 cellPadding=0 border=0 align=center>
<tr>
	<td align=center>
		<a href="http://wforum.aka.cn/" target="_blank"><img border="0" src="images/wforum.gif"></a><br>
		<nobr><?php   echo $Version; ?></nobr>
	</td>
	<td>
		<TABLE cellSpacing=0 cellPadding=0 border=0 align=center>
		<tr>
			<td align=center>
				<?php   echo $Forum_ads[1]; ?>
			</td>
		</tr>
		<tr>
		</tr>
		<tr>
			<td align=center nowrap>
				<?php   echo $Copyright; ?>
				, ҳ��ִ��ʱ�䣺<?php  printf(number_format(($endtime-$StartTime)*1000,3)); ?>����
			</td>
		</tr>
		</table>
	</td>
</tr>
<tr>
<td colspan=2><?php echo $FooterBan ; ?></td>
</tr>
</TABLE>
<?php
	if ($loginok==1 && $showmsg) {
		getMsg();
	}
?>
<br>
<br>
</body>
</html>
<?php
	if (isset($conn) && ($conn !== false)) CloseDatabase();
} 

function getMsg(){

?>

<div id="floater" style="position:absolute; width:502px; height:152px; z-index:2; left: 200px; top: 250px; visibility: hidden; background-color: transparent; layer-background-color: #FFFFFF; "> 
</div>
<iframe width="100%" height="0" frameborder="0" scrolling=no src="getmsg.php" name="webmsg">
</iframe>
<script src="inc/floater.js"  language="javascript"></script>
<?php
}

function htmlformat($str,$multi=false) {
    $str = str_replace(' ','&nbsp;',htmlspecialchars($str));
    if ($multi)
        $str = nl2br($str);
    return $str;    
}

function jumpReferer() {
	if (!isset($_SERVER["HTTP_REFERER"]) || ( $_SERVER["HTTP_REFERER"]=="") ) {
		header("Location: index.php");
	} else {
		header("Location: ".$_SERVER["HTTP_REFERER"]);
	} 
}

} // !define ('_BBS_FUNCS_PHP_')
?>
