<?php
require("wapfuncs.php");
if(loginok())
{
  if ($userid == "guest")
    {
      authpanic("����û������");
    }
  else
    {
      waphead(0);
      checkmm();
      echo "<card id=\"main\" title=\"".BBS_WAP_NAME."\">";
      echo "<p>menus...</p>";
      echo "</card>";
      echo "</wml>";
    }
}
?>
