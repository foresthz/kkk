<?php
require("wapfuncs.php");
$havetoget = 0; 
$backmenu = 0; //index.php is special

//***********************************
//begin dealing cookies

@$cuid = $_COOKIE['UID'];
@$cupwd = $_COOKIE['PWD'];
@$clear = $_GET['c'];

if($clear == 1)
{
  //  setcookie("PWD",'',time()-360,urlstr(index));
  //  setcookie("UID",'',time()-360,urlstr(index));
  setcookie("PWD",'',time()-360,"/");
  setcookie("UID",'',time()-360,"/");

}
waphead(0);

echo "<card id=\"main\" title=\"".BBS_WAP_NAME."\">";
echo "<p align=\"center\">";
echo BBS_FULL_NAME;
echo "</p>";
echo "<p align=\"left\">";

if($cuid != '' && empty($clear))
{
  echo "����:$cuid<br/>";
  echo "����:";
  $len = strlen($cupwd);
  for($i = 0; $i < $len; ++$i)
    echo "*";
  echo "</p>";
  echo "<p align=\"center\">";
  echo "<do type=\"accept\" label=\"�ɰ�\">";
  echo "<go href=\"".urlstr('index')."?c=1\"></go>";
  echo "</do>";
  echo "<do type=\"button\" label=\"��½\">";
  echo "<go href=\"".urlstr('login',array('id'=>$cuid,'pwd'=>$cupwd))."\">";
  echo "</go>";
  echo "</do>";
  echo "</p>";
}
else
{
  echo "����:<input name=\"id\" size=\"7\" /><br/>";
  echo "����:<input emptyok=\"true\" type=\"password\" name=\"pwd\" size=\"7\" />";
  echo "</p>";
  echo "<p align=\"center\">";
  echo "<do type=\"accept\" label=\"�ɰ�\">";
  echo "<go href=\"".urlstr('index')."?c=1\"></go>";
  echo "</do>";
  echo "<do type=\"button\" label=\"��½\">";
  echo "<go href=\"".urlstr('login',array('id'=>'$(id)','pwd'=>'$(pwd)'))."\"></go>";
  echo "</do>";
  echo "</p>";
}
?>
</card>
</wml>
