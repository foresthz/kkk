<?php
require("pcfuncs.php");
require("pcstat.php");

function pc_opml_init($opmlTitle)
{
?>
<?xml version="1.0" encoding="gb2312" ?> 
<opml>
<head>
<title><?php echo htmlspecialchars($title); ?></title>
</head>
<body>
<?php	
}

function pc_opml_quit()
{
?>
</body>
</opml>
<?php	
}

function pc_opml_outline($title,$htmlUrl,$xmlUrl,$description,$text="")
{
	if(!$text) $text = $title;
?>
<outline text="<?php echo htmlspecialchars($text); ?>" title="<?php echo htmlspecialchars($title); ?>" type="rss" version="RSS" htmlUrl="<?php echo htmlspecialchars($htmlUrl); ?>" xmlUrl="<?php echo htmlspecialchars($xmlUrl); ?>" description="<?php echo htmlspecialchars($description); ?>" /> 
<?php	
}

//10min����һ��
if(pc_update_cache_header())
	return;

$type = intval( $_GET["t"]);
$link = pc_db_connect();
if($type == 2) //���û�
{
	$blogs = getNewUsers($link,50);
	$opmlTitle = $pcconfig["BBSNAME"] . "BLOG���û���";
}
elseif($type == 1) //��߷���
{
	$blogs = getMostVstUsers($link,50);
	$opmlTitle = $pcconfig["BBSNAME"] . "BLOG�����û���";
}
else //�������
{
	$blogs = getLastUpdates($link,50);
	$opmlTitle = $pcconfig["BBSNAME"] . "BLOG�����û���";
}
pc_db_close($link);
header("Content-Type: text/xml");
header("Content-Disposition: inline;filename=opml.xml");
pc_opml_init($opmlTitle);
foreach($blogs as $blog)
{
	$title = stripslashes($blog[corpusname]);
	$htmlUrl = pc_personal_domainname($blog[username]);
	$xmlUrl = "http://".$pcconfig["SITE"] . "/pc/rss.php?userid=" . $blog[username];
	$description = stripslashes($blog[description]);
	pc_opml_outline($title,$htmlUrl,$xmlUrl,$description);
}
pc_opml_quit();
?>