<?php
require("wapfuncs.php");
if(loginok())
{
  if ($userid == "guest")
    {
      authpanic("�����޷�����ѶϢ");
    }
  else
    {
      @$origmsg = $_GET['m'];
      $msg = smarticonv($origmsg);
      @$destid = $_GET['to'];
      @$destutmpstr = $_GET['pid'];

      if (!empty($destid))
	{
	  if (empty($destumpstr))
	    {
	      $destutmp=0;
	    }
	  else
	    {
	      $destutmp = decodesession($destutmpstr);
	    }
	  bbs_sendwebmsg($destid, $msg, $destutmp, $errmsg);
	  waphead(0);
	  echo "<card id=\"main\" title=\"".BBS_WAP_NAME."\">";
	  echo "<p>$errmsg</p>";
	  echo "<do type=\"prev\" label=\"����\"><prev/></do>";
	  echo "</card>";
	  echo "</wml>";
	}
      else
	{
	  waphead(0);
	  checkmm();
          echo "<card id=\"main\" title=\"".BBS_WAP_NAME."\">";
	  echo "<p>";
	  echo "��<input emptyok=\"true\" type=\"text\" name=\"to\" size=\"7\"/><br/>";
	  echo "˵<input emptyok=\"true\" type=\"text\" name=\"msg\" size=\"20\"/><br/>";
          echo "<a href=\"".urlstr('sendmsg',array('m'=>'$(msg)','to'=>'$(to)'))."\">����</a>";
	  echo "</p>";
	  echo "<p><a href=\"".urlstr('menu')."\">���˵�</a></p>";
	  echo "</card>";
	  echo "</wml>";

	}
    }
}
?>
