<?php
require("wapfuncs.php");
$backmenu=0;
//************************

if(loginok())
{
  waphead(0);
  if ($userid != 'guest') checkmm();
  echo "<card id=\"main\" title=\"".BBS_WAP_NAME."\">";
  echo "<p align=\"center\">���˵�</p>";
  echo "<p>";
  echo "<a href=\"".urlstr('top10')."\">ʮ����</a><br/>";
  //  echo "<a href=\"".urlstr('
  echo ($userid != 'guest')?"<a href=\"".urlstr('readmail')."\">���ʼ�</a><br/>":'';
  echo ($userid != 'guest')?"<a href=\"".urlstr('sendmsg')."\">����Ϣ</a><br/>":'';
  echo "<a href=\"".urlstr('logout')."\">�˳�</a><br/>";
  echo "</p>";
  echo "</card>";
  echo "</wml>";
}
?>
