
function submitonce(theform){
//if IE 4+ or NS 6+
if (isIE || isW3C){
//screen thru every element in the form, and hunt down "submit" and "reset"
	for (i=0;i<theform.length;i++){
		var tempobj=theform.elements[i]
		if(tempobj.type.toLowerCase()=="submit"||tempobj.type.toLowerCase()=="reset")
//disable em
			tempobj.disabled=true
		}
	}
}
function openScript(url, width, height){
	var Win = window.open(url,"openScript",'width=' + width + ',height=' + height + ',resizable=0,scrollbars=no,menubar=no,status=no' );
}

//***********Ĭ�����ö���.*********************
tPopWait=50;//ͣ��tWait�������ʾ��ʾ��
tPopShow=5000;//��ʾtShow�����ر���ʾ
showPopStep=20;
popOpacity=99;

//***************�ڲ���������*****************
sPop=null;
curShow=null;
tFadeOut=null;
tFadeIn=null;
tFadeWaiting=null;

function showPopupText(){
	var o = event.srcElement;
	if (o == null) return;
	MouseX=event.x;
	MouseY=event.y;
	if(o.title!=null && o.title!=""){o.dypop=o.title;o.title=""};

	if(o.dypop!=sPop) {
		sPop=o.dypop;
		clearTimeout(curShow);
		clearTimeout(tFadeOut);
		clearTimeout(tFadeIn);
		clearTimeout(tFadeWaiting);	
		if(sPop==null || sPop=="") {
			dypopLayer.innerHTML="";
			dypopLayer.style.filter="Alpha()";
			dypopLayer.filters.Alpha.opacity=0;	
			}
		else {
			if(o.dyclass!=null) popStyle=o.dyclass 
				else popStyle="cPopText";
			curShow=setTimeout("showIt()",tPopWait);
		}
	}
}

function showIt(){
		dypopLayer.className=popStyle;
		dypopLayer.innerHTML=sPop;
		popWidth=dypopLayer.clientWidth;
		popHeight=dypopLayer.clientHeight;
		if(MouseX+12+popWidth>document.body.clientWidth) popLeftAdjust=-popWidth-24
			else popLeftAdjust=0;
		if(MouseY+12+popHeight>document.body.clientHeight) popTopAdjust=-popHeight-24
			else popTopAdjust=0;
		dypopLayer.style.left=MouseX+12+document.body.scrollLeft+popLeftAdjust;
		dypopLayer.style.top=MouseY+12+document.body.scrollTop+popTopAdjust;
		dypopLayer.style.filter="Alpha(Opacity=0)";
		fadeOut();
}

function fadeOut(){
	if(dypopLayer.filters.Alpha.opacity<popOpacity) {
		dypopLayer.filters.Alpha.opacity+=showPopStep;
		tFadeOut=setTimeout("fadeOut()",1);
		}
		else {
			dypopLayer.filters.Alpha.opacity=popOpacity;
			tFadeWaiting=setTimeout("fadeIn()",tPopShow);
			}
}

function fadeIn(){
	if(dypopLayer.filters.Alpha.opacity>0) {
		dypopLayer.filters.Alpha.opacity-=1;
		tFadeIn=setTimeout("fadeIn()",1);
		}
}

if (isIE) {
	document.write("<div id='dypopLayer' style='position:absolute;z-index:1000;' class='cPopText'></div>");
	document.onmouseover=showPopupText;
}

function CheckAll(form)  {
	var e;
	for (var i=0;i<form.elements.length;i++)    {
		e = form.elements[i];
		if (e.name != 'chkall')
			e.checked = form.chkall.checked; 
	}
}

//�����˵���ش���
 var h;
 var w;
 var l;
 var t;
 var topMar = 1;
 var leftMar = -2;
 var space = 1;
 var isvisible;
 var MENU_SHADOW_COLOR='#999999';//���������˵���Ӱɫ
 var global = window.document;
 
 global.fo_currentMenu = null;
 global.fo_shadows = new Array();

function HideMenu(evt) 
{
 var mX;
 var mY;
 var vDiv;
 var mDiv;
	if (isvisible == true) {
		evt = (evt) ? evt : ((window.event) ? event : null);
		if (evt) {
			evt.cancelBubble = true;
			vSrc = (evt.target) ? evt.target :   ((evt.srcElement) ? evt.srcElement : null);

			if (vSrc.nodeType == 3) {
			   vSrc = vSrc.parentNode;
			}
			oDiv=getRawObject("menuDiv");

			mX = evt.clientX + document.body.scrollLeft;
			mY = evt.clientY + document.body.scrollTop;

			if ((mX < parseInt(oDiv.style.left)) || (mX > parseInt(oDiv.style.left)+getObjectWidth(oDiv)) || (mY < parseInt(oDiv.style.top)-h) || (mY > parseInt(oDiv.style.top)+getObjectHeight(oDiv))){
				hide(oDiv);
				isvisible = false;
			}
		}
	}
}

function ShowMenu_Internal(vMnuCode,tWidth,evt,isTd) {
	evt = (evt) ? evt : ((window.event) ? event : null);
	if (evt) {
		evt.cancelBubble = true;
	   vSrc = (evt.target) ? evt.target :   ((evt.srcElement) ? evt.srcElement : null);
	   
	   if (vSrc.nodeType == 3) {
		   vSrc = vSrc.parentNode;
	   }
		if (isTd) vMnuCode = "<table id='submenu' cellspacing=1 cellpadding=3 style='width:"+tWidth+"' class=TableBorder1 onmouseout='HideMenu()'><tr height=23><td nowrap class=TableBody1 align=center>" + vMnuCode + "</td></tr></table>";
		oDiv=getRawObject("menuDiv");

		h = vSrc.offsetHeight;
		w = vSrc.offsetWidth;
		l = vSrc.offsetLeft + leftMar+4;
		t = vSrc.offsetTop + topMar + h + space-2;

		vParent = vSrc.offsetParent;
		while (vParent.tagName.toUpperCase() != "BODY")
		{
			l += vParent.offsetLeft;
			t += vParent.offsetTop;
			vParent = vParent.offsetParent;
		}
		oDiv.innerHTML = vMnuCode;
		shiftTo(oDiv,l,t)
		show(oDiv);
		isvisible = true;
		if( isIE ){
			makeRectangularDropShadow(submenu, MENU_SHADOW_COLOR, 4)
		}
	}
	return false;
}
function ShowMenu(a,b,c) {
	ShowMenu_Internal(a,b,c,true);
}

function makeRectangularDropShadow(el, color, size)
{
	var i;
	for (i=size; i>0; i--)
	{
		var rect = document.createElement('div');
		var rs = rect.style
		rs.position = 'absolute';
		rs.left = (el.style.posLeft + i) + 'px';
		rs.top = (el.style.posTop + i) + 'px';
		rs.width = el.offsetWidth + 'px';
		rs.height = el.offsetHeight + 'px';
		rs.zIndex = el.style.zIndex - i;
		rs.backgroundColor = color;
		var opacity = 1 - i / (i + 1);
		rs.filter = 'alpha(opacity=' + (100 * opacity) + ')';
		el.insertAdjacentElement('afterEnd', rect);
		global.fo_shadows[global.fo_shadows.length] = rect;
	}
}


function createCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}
function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
	var c = ca[i];
	while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

var stylelist, manage, talk, query;

var stylesheets = new Array(
	new Array("defaultstyle", "Ĭ��ģ��"),
	new Array("crystalpurplestyle", "ˮ����ɫ"),
	new Array("dotgreenstyle", "���С��"),
	new Array("emotiongraystyle", "�����ɫ"),
	new Array("autumnstyle", "���ⰻȻ"),
	new Array("bluegrandstyle", "��ɫׯ��"),
	new Array("greenqieutlyelegantstyle", "��ɫ����"),
	new Array("bluegreenelegantstyle", "������"),
	new Array("purpleqieutlyelegantstyle", "��ɫ����"),
	new Array("lightpurplestyle", "����ɫ"),
	new Array("orangeredstyle", "���Ӻ���"),
	new Array("rednightstyle", "���ҹ˼"),
	new Array("pinkmemorystyle", "��ɫ����"),
	new Array("greengrassstyle", "����Ӳ�"),
	new Array("thickgreenstyle", "ŨŨ����"),
	new Array("brownredstyle", "�غ�Ԥ��"),
	new Array("littlecoffeestyle", "������"),
	new Array("seaskystyle", "�̺�����"),
	new Array("bluecrystalstyle", "��ɫˮ��"),
	new Array("snowstyle", "ѩ��ƮƮ")
);

function defineMenus() {
	stylelist = '';
	for (i = 0; i < stylesheets.length; i++) {
		stylelist += '<a class=\"SMItem\" href=\"javascript:setActiveStyleSheet('
		             + i + ')\">' + stylesheets[i][1] + '</a><br/>';
	}

	manage = '<a class=\"MItem\" href="usermailbox.php?boxname=inbox">�ҵ�����</a><br>';
	manage += '<a class=\"MItem\" href="favboard.php">�û��ղذ���</a><br>';
	manage += '<a class=\"MItem\" href="usermanagemenu.php">�û��������</a><br>';
	manage += '<a class=\"MItem\" href="modifyuserdata.php">���������޸�</a><br>';
	manage += '<a class=\"MItem\" href="userparam.php">�û��Զ������</a><br>';
	manage += '<a class=\"MItem\" href="bbssig.php">�û�ǩ����</a><br>';
	manage += '<a class=\"MItem\" href="changepasswd.php">�޸��ǳ�����</a><br>';

	talk = '<a class=\"MItem\" href="showmsgs.php">�쿴���߶���</a><br>';
	talk += '<a class=\"MItem\" href="javascript:sendMsg()">������</a><br>';
if (siteconf_SMS_SUPPORT) {
	talk += '<a class=\"MItem\" href="javascript:sendSMSMsg()">�����ֻ�����</a><br>';
}
	talk += '<a class=\"MItem\" href="friendlist.php">�༭�����б�</a><br>';
	talk += '<a class=\"MItem\" href="showonlinefriend.php">���ߺ���</a><br>';
	talk += '<a class=\"MItem\" href="showonlineuser.php">�����û�</a><br>';
	talk += '<a class=\"MItem\" href="dispuser.php">��ѯ�û�</a>';

	query = '<a href="searchboard.php">��������</a><br>';
	query += '<form action="searchboard.php" method="get" style="margin: 0px;">';
	query += '<input class="TableBorder2" name="board" value="����ؼ���" size="12" onmouseover="this.focus()" onfocus="this.select()" /></form>';
	query += '<hr class="TableBorder2"><a class="MItem" href="query.php" title="ֱ�ӵ�����ġ��������˵�����������ǰ����">��������</a>';
}

function setActiveStyleSheet(index) {
	var i, a, main;
	title = stylesheets[index][1];
	for(i=0; (a = document.getElementsByTagName("link")[i]); i++) {
		if((a.getAttribute("rel").indexOf("style") != -1) && a.getAttribute("title")) {
			a.disabled = true;
			if(a.getAttribute("title") == title) {
				a.disabled = false;
				createCookie("style", stylesheets[index][0], 7);
			}
		}
	}
}

function writeStyleSheets() {
	currentStyle = readCookie("style");
	currentIndex = -1;
	for (i = 0; i < stylesheets.length; i++) {
		if (stylesheets[i][0] == currentStyle) {
			currentIndex = i;
			break;
		}
	}
	if (currentIndex == -1) currentIndex = 0; //default
	document.write('<link rel="stylesheet" type="text/css" href="css/' 
	               + stylesheets[currentIndex][0] + '.css" title="' + stylesheets[currentIndex][1] + '" />');
	for (i = 0; i < stylesheets.length; i++) {
		if (i != currentIndex) {
			document.write('<link rel="alternate stylesheet" type="text/css" href="css/' 
	               + stylesheets[i][0] + '.css" title="' + stylesheets[i][1] + '" />');
		}
	}	
}

var timerID=0;
function dosendmsg(){
	oMessager=getRawObjectFrom("messager",window.frames['webmsg']);
	oMessager.submit();
}

function msg_textchange(str){
	oMsg=getRawObjectFrom("oMsgText",window.frames['webmsg']);
	oMsg.value=str;
}

function msg_typechanged(checked){
	oIsSMS=getRawObjectFrom("isSMS",window.frames['webmsg']);
	oIsSMS.checked=checked;
}

function msg_idchange(str){
	oID=getRawObjectFrom("odestid",window.frames['webmsg']);
	oID.value=str;
}
function closeWindow(){
	hide("floater");
//	document.all.floater.style.visibility='hidden';
	window.frames['webmsg'].document.location.href='getmsg.php';
}
function refreshWindow(){
	timerID=window.setTimeout("window.frames['webmsg'].document.location.href='getmsg.php';", 60000);
}
function pauseMsg(){
	window.clearTimeout(timerID);
}
function replyMsg(id){
	window.frames['webmsg'].document.location.href='sendmsg.php?destid='+id;
}
function sendMsg(){
	window.frames['webmsg'].document.location.href='sendmsg.php';
}

function sendSMSMsg(){
	window.frames['webmsg'].document.location.href='sendmsg.php?type=sms';
}


/* article flag functions. reference: libBBS/article.c get_article_flag(). - atppp */
function article_is_digest(flag) {
	upperflag = flag.toUpperCase();
	return ( 'M' == upperflag || 'B' == upperflag || 'G' == upperflag);
}

function article_is_zhiding(flag) {
	return(flag == "d" || flag == "D");
}

function article_is_noreply(flag) {
	return ( 'O' == flag || 'U' == flag || '8' == flag || ';' == flag);
}

function article_is_unread(flag) {
	if (!siteconf_SHOW_POST_UNREAD) return false;
	return ( 'D' == flag || 'G' == flag || 'M' == flag  || 'B' == flag || 'N' == flag || '*' == flag);
}


/* ������ʱ�� */
var serverDiff;
var lastClockStr = "";
function initTime(st) {
	serverDiff = st * 1000 - new Date().getTime();
	showTime();
	setInterval("showTime()", 1000);
}
function prefixZero(d) {
	if (d > 9) return d;
	else return "0" + d;
}
function showTime() {
	if ((KCN = getRawObject("serverTime")) == null) return;
	var Timer = new Date(serverDiff + new Date().getTime());
	str = Timer.getUTCFullYear() + "-" + prefixZero(Timer.getUTCMonth() + 1) + "-" + prefixZero(Timer.getUTCDate()) + " " + prefixZero(Timer.getUTCHours()) + ":" + prefixZero(Timer.getUTCMinutes()); // + ":" + prefixZero(Timer.getUTCSeconds());
	if (lastClockStr != str) {
		lastClockStr = str;
		KCN.innerHTML = "������ʱ��: " + str;
	}
}


var bbsconImg = new Array();
function resizeImg(obj) {
	bbsconImg[bbsconImg.length] = obj;
	obj.o_width = obj.width;
	maxWidth = document.body.clientWidth - 250;
	if (obj.width > maxWidth) obj.width = maxWidth;
}
function adjustImg() {
	var maxWidth = document.body.clientWidth - 250;
	for (var i in bbsconImg) {
		obj = bbsconImg[i];
		o_width = obj.o_width;
		obj.width = (o_width > maxWidth) ? maxWidth : o_width;
	}
}
window.onresize = adjustImg;

function refreshLeft() {
	if (top != self) {
		top.menu.doShowLeft();
		top.menu.location.href = "bbsleft.php";
	}
}



var onLoadListeners = new Array();
var focusElement = null;
function addOnLoadListener(listener) {
	onLoadListeners[onLoadListeners.length] = listener;
}
function documentOnLoad() {
	for (i = 0; i < onLoadListeners.length; i++) {
		eval(onLoadListeners[i]);
	}
	if (focusElement == null) return;
	e = getRawObject(focusElement);
	if (e == null) return;
	e.focus();
}
function registerFocusOnLoad(ele) {
	focusElement = ele;
}
window.onload = documentOnLoad;

