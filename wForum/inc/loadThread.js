document.write('<iframe width=0 height=0 src="" frameborder="0" scrolling="no" id="hiddenframe" name="hiddenframe"></iframe>');

/* �����б��Լ���ϸ�б�չ�� */

/* sec == 1 means fav */
function setSecCookie(sec, flag, isShow) {
	if (isShow) cn = "ShowSecBoards";
	else cn = "HideSecBoards";
	szCookieName = cn + "=";
	var szCookie = document.cookie;
	var iPos1 = szCookie.indexOf(szCookieName), iPos2 = 0;
	if (iPos1 >= 0)
	{
		iPos1 += szCookieName.length;
		iPos2 = szCookie.indexOf(';', iPos1);
		if (iPos2 == -1)
			iPos2 = szCookie.length;
		ssb = unescape(szCookie.substring(iPos1, iPos2));
		ssb = ssb.valueOf();
	} else {
		ssb = 0;
	}
	if (flag) {
		ssb = ssb | (1 << (sec+1));
	} else {
		ssb = ssb & ~(1 << (sec+1));
	}
	var expTime = new Date();
	expTime.setTime(expTime.getTime() + 604800 * 1000);
	document.cookie = szCookieName + escape(ssb) + '; Expires=' + expTime.toGMTString();
}

function loadBoardFollow(sec, isFav, isLoading, isHide, isFold){
	targetTip = getRawObject("followTip" + sec);
	if (isLoading){
		targetTip.style.display = '';
		str = "loadsec.php?sec=" + sec;
		if (isFav) str += "&fav=1";
		if (isFold) str += "&fold=1";
		window.frames["hiddenframe"].document.location.href = str;
		/* HTML �� JS Ԫ�صı仯�ȴ� loadsec.php �ص�����������Ϊ�Ǹ�ʱ�����ݺ�״̬��������λ - atppp */
		return;
	} else {
		targetTip.style.display = 'none';
	}
	targetImg = getRawObject("followImg" + sec);
	targetDiv = getRawObject("followSpan" + sec);

	eval("boards = boards" + sec + ";");
	
	targetDiv.innerHTML = showSec(isFold, isFav, boards, sec, isHide);
	setSecCookie(isFav ? -1 : sec, isFold, true);
	setSecCookie(isFav ? -1 : sec, isHide, false);
	if (isHide || !isFold) {
		targetImg.src="pic/plus.gif";
		targetImg.title = "չ�������б�";
	} else {
		targetImg.src="pic/nofollow.gif";
		targetImg.title = "�۵������б�";
	}
	eval("curfold" + sec + " = " + (isHide ? 0 : (isFold ? 2 : 1)) + ";");
	if (!isFav) {
		targetHide = getRawObject("toogleHide" + sec);
		targetHide.innerHTML = (isHide ? "չ��" : "�ر�") + "�����б�";
	}
}

function toogleHide(sec) {
	eval("foldflag = foldflag" + sec + ";");
	eval("curfold = curfold" + sec + ";");
	if (curfold == 0) {
		loadBoardFollow(sec, false, (foldflag == 0), false, false);
	} else {
		loadBoardFollow(sec, false, false, true, false);
	}
}

function loadSecFollow(sec) {
	eval("foldflag = foldflag" + sec + ";");
	eval("curfold = curfold" + sec + ";");
	if (curfold == 2) {
		loadBoardFollow(sec, false, false, false, false);
	} else {
		loadBoardFollow(sec, false, (foldflag <= curfold), false, (curfold == 1));
	}
}

function loadFavFollow() {
	eval("foldflag = foldflag" + j_select + ";");
	eval("curfold = curfold" + j_select + ";");
	if (curfold == 2) {
		loadBoardFollow(j_select, true, false, false, false);
	} else {
		loadBoardFollow(j_select, true, (foldflag == 1), false, true);
	}
}

function Board(isBoardGroup,isFavGroup,isUnread,boardName,boardDesc,lastID,lastTitle,lastOwner,lastPosttime,
               bm,todayNum,nArticles,nThreads,npos,bid,currentusers) {
	this.isBoardGroup = isBoardGroup;
	this.isFavGroup = isFavGroup;
	this.isUnread = isUnread;
	this.boardName = boardName;
	this.boardDesc = boardDesc;
	this.lastID = lastID;
	this.lastTitle = lastTitle;
	this.lastOwner = lastOwner;
	this.lastPosttime = lastPosttime;
	this.bm = bm;
	this.todayNum = todayNum;
	this.nArticles = nArticles;
	this.nThreads = nThreads;
	this.npos = npos;
	this.bid = bid;
	this.currentusers = currentusers;
}

function BoardS(isBoardGroup, isFavGroup, boardName, boardDesc, todayNum, nArticles, npos, bid, currentusers) {
	return new Board(isBoardGroup, isFavGroup, 0, boardName, boardDesc, 0,0,0,0,0,todayNum, nArticles, 0, npos, bid, currentusers);
}

function showSec(isFold, isFav, boards, secNum, isHide) {
	str = '<table cellspacing=1 cellpadding=0 align=center class=TableBorder1 style="width:100%">';
	if (isHide) {
		// str += '<TR><TD class=TableBody1>&nbsp;�����б��ѹر� [<a href="#" onclick="loadSecFollow('+secNum+')" title="չ�������б�">չ��</a>]</td></tr>';
	} else if ((boards.length == 0)) {
		str += '<TR><TD class=TableBody1 align="center" height="25">���ް���</td></tr>';
	} else {
		if (isFold) {
			for (i = 0; i < boards.length; i++)	{
				str += '<TR><TD align=middle width="100%" class=TableBody1>';
				str += '<table width="100%" cellspacing=0 cellpadding=0><TR><TD align=center width=46 class=TableBody1>';
				if (boards[i].isUnread) {
					str += "<img src=pic/forum_isnews.gif title=\"��������\">";
				} else {
					str += "<img src=pic/forum_nonews.gif title=��������>";
				}
				str += '</TD><TD width=1 bgcolor=#7a437a></TD>';
				str += '<TD vAlign=top width=* class=TableBody1 style="padding-top: 2pt;">';
				str += '<TABLE cellSpacing=0 cellPadding=1 width=100% border=0><tr><td class=TableBody1 width=* style="padding-left: 15px;">';
				if (!boards[i].isFavGroup) {
					str += '<a href="board.php?name=' + boards[i].boardName + '"><font color=#000066>' + boards[i].boardDesc + '</font></a>';
				} else {
					str += '<a href="favboard.php?select=' + boards[i].bid + '"><font color=#000066>[Ŀ¼]' + boards[i].boardDesc + '</font></a>';
				}
				str += '</td><td width=40 rowspan=2 align=center class=TableBody1></td><td width=200 rowspan=2 class=TableBody1>';
				if (boards[i].isFavGroup || boards[i].isBoardGroup) {
					str += '<B>����Ϊ����Ŀ¼��</B>';
				} else if (boards[i].nArticles <= 0) {
					str += '<B>������������</B>';
				} else {
					str += '���⣺<a href="disparticle.php?boardName=' + boards[i].boardName + '&ID=' + boards[i].lastID + '">' + boards[i].lastTitle + ' &nbsp;</a><BR>�ظ���<a href="dispuser.php?id=' + boards[i].lastOwner + '" target=_blank>' + boards[i].lastOwner + ' </a><BR>���ڣ�' + boards[i].lastPosttime + '&nbsp;<a href="disparticle.php?boardName=' + boards[i].boardName + '&ID=' + boards[i].lastID + '"><IMG border=0 src="pic/lastpost.gif" title="ת����' + boards[i].lastTitle + ' "> </a>';
				}
				str += '</TD></TR><TR><TD width=*><FONT face=Arial><img src=pic/forum_readme.gif align=middle> ' + boards[i].boardName + '</FONT>';
				if (!boards[i].isBoardGroup && !boards[i].isFavGroup) {
					str += '&nbsp;&nbsp;[���ߣ�' + boards[i].currentusers + ']';
				}
				str += '</TD></TR><TR><TD class=TableBody2 height=18 width=* style="padding-left: 15px;">';
				if (!boards[i].isFavGroup && !boards[i].isBoardGroup) {
					str += '������' + (boards[i].bm == '' ? '����' : boards[i].bm);
				}
				str += ' </TD><td width=40 align=center class=TableBody2>&nbsp;</td><TD vAlign=middle class=TableBody2 width=200>';
				if (!boards[i].isFavGroup && !boards[i].isBoardGroup) {
					str += '<table width=100% border=0><tr><td width=25% vAlign=middle><img src=pic/forum_today.gif title=������ align=absmiddle>&nbsp;<font color=#FF0000>' + boards[i].todayNum + '</font></td><td width=30% vAlign=middle><img src=pic/forum_topic.gif title=���� border=0  align=absmiddle>&nbsp;' + boards[i].nThreads + '</td><td width=45% vAlign=middle><img src=pic/forum_post.gif title=���� border=0 align=absmiddle>&nbsp;' + boards[i].nArticles + '</td></tr></table>';
				}
				str += '</TD></TR></TBODY></TABLE></td>';
				str += '<TD width=1 bgcolor=#7a437a></TD><td class=TableBody1 align=center width=30>';
				if (!isFav) {
					str += '<a href="favboard.php?bname=' + boards[i].boardName + '" title="�ղر����浽�ղؼж���Ŀ¼"><img border=0 src="pic/fav.gif"></a>';
				} else if (!boards[i].isFavGroup) {
					str += '<a href="favboard.php?select=' + j_select + '&delete=' + boards[i].npos + '" title="���ղ���ɾ���ð���"><img border=0 src="pic/del.gif"></a>';
				} else {
					str += '<a href="favboard.php?select=' + j_select + '&deldir=' + boards[i].npos + '" title="���ղ���ɾ����Ŀ¼"><img border=0 src="pic/del.gif"></a>';
				}
				str += '</td></tr></table></td></tr>';
			}
		} else { //!isFold
			showed = 0;
			percent = 100 / siteconf_BOARDS_PER_ROW;
			for (i = 0; i < boards.length; i++)	{
				showed++;
				if (showed % siteconf_BOARDS_PER_ROW == 1) {
					str += "<tr>";
				}
				str += '<td class=TableBody1 width="'+percent+'%"><TABLE cellSpacing=2 cellPadding=2 width=100% border=0><tr><td width="100%" colspan=3>';

				if (!boards[i].isFavGroup) {
					str += '<a href="board.php?name=' + boards[i].boardName + '"><font color=#000066>' + boards[i].boardDesc;
					str += '&nbsp;[' + boards[i].boardName + ']</font></a>';
				} else {
					str += '<a href="favboard.php?select=' + boards[i].bid + '"><font color=#000066>[Ŀ¼]' + boards[i].boardDesc + '</font></a>';
				}
				str += '</td></tr><tr>';
				if (boards[i].isBoardGroup || boards[i].isFavGroup) {
					str += '<td> <b>����Ϊ����Ŀ¼��</b></td>';
				} else {
					str += '<td width="33%">���գ�<font color=#FF0000>' + boards[i].todayNum + '</font></td><td width="34%">������' + boards[i].nArticles + '</td><td width="33%">���ߣ�';
					if (boards[i].currentusers > 0) str += '<font color=#FF0000>' + boards[i].currentusers + '</font>';
					else str += '0';
					str += '</td>';
				}
				str += '</tr></table></td>';
				if (showed % siteconf_BOARDS_PER_ROW == 0) {
					str += "</tr>";
				}
			}
			if (showed % siteconf_BOARDS_PER_ROW != 0) {
				str += '<td class=TableBody1 colspan="' + (siteconf_BOARDS_PER_ROW - showed % siteconf_BOARDS_PER_ROW) + '" width="' + (percent*(siteconf_BOARDS_PER_ROW - showed % siteconf_BOARDS_PER_ROW)) + '%"></td></tr>';
			}
		}
	}
	return str + '</table>';
}






/* �����б��Լ�����չ�� */

function loadThreadFollow(t_id,b_name){
	var targetImg=getRawObject("followImg"+t_id);
	var targetDiv =getRawObject("follow" + t_id);

	if ("object"==typeof(targetImg)){
		if (targetDiv.style.display=='none'){
//			targetDiv.style.display="block";
			targetDiv.style.display="";

			targetImg.src="pic/nofollow.gif";
			window.frames["hiddenframe"].document.location.href="loadtree.php?bname="+b_name+"&ID="+t_id;
		}else{
			targetDiv.style.display="none";
			targetImg.src="pic/plus.gif";
		}
	}
}



function Post(id, owner, posttime, flags) {
	this.ID = id;
	this.OWNER = owner;
	this.POSTTIME = posttime;
	this.FLAGS = flags;
}

function writepost(pos, html_title, threadNum, origin, lastreply, origin_exists, has_attach) {
	/* note: when origin post does not exists, origin is actually the same as lastreply except
	 * groupID is different. See also www_generateOriginIndex() */
	//if (!origin_exists) return;
	document.write("<TR align=middle><TD class=TableBody2 width=32 height=27 align=\"center\">");
	if (article_is_zhiding(origin.FLAGS)) {
		document.write("<img src=\"pic/istop.gif\" title=\"�̶�������\">");
	} else if( threadNum > 10 ) {
		document.write("<img src=\"pic/blue/hotfolder.gif\" title=\"�ظ�����10��\">");
	} else if(article_is_noreply(origin.FLAGS)) {
		document.write("<img src=\"pic/blue/lockfolder.gif\" title=\"����������\">");
	} else if(article_is_digest(origin.FLAGS)) {
		document.write("<img src=\"pic/isbest.gif\" title=\"������\">");
	} else {
		document.write("<img src=\"pic/blue/folder.gif\" title=\"��������\">");
	}
	document.write("</TD><TD align=left class=TableBody1 width=* >");
	if (threadNum==0) {
		document.write('<img src="pic/nofollow.gif" id="followImg' + pos + '">');
	} else {
		document.write('<img loaded="no" src="pic/plus.gif" id="followImg' + origin.ID + '" style="cursor:hand;" onclick="loadThreadFollow(\'' + origin.ID +"','" + boardName + "')\" title=\"չ�������б�\">");
	}
/*	if (has_attach) {
		document.write('<img src="pic/havefolder.gif" align=absmiddle title="�и���">');
	} ͼ�겻�ÿ�����ʱ�� enable - atppp */
	if (origin_exists) {
		if (isIE) {
			href_title = html_title + ' <br>���ߣ�' + origin.OWNER + '<br>������' + origin.POSTTIME;
		} else {
			href_title = '������' + origin.POSTTIME;
		}
	} else {
		href_title = "ԭ����ɾ��";
	}
	document.write('<a href="disparticle.php?boardName=' + boardName + '&ID=' + origin.ID + '&pos=' + pos + '" title="' + href_title + '">' + html_title + ' </a>');
	threadPages = Math.ceil((threadNum+1)/siteconf_THREADSPERPAGE);
	if (threadPages>1) {
		document.write("<b>[<img src=\"pic/multipage.gif\"> ");
		for (t=1; (t<7) && (t<=threadPages); t++) {
			document.write("<a href=\"disparticle.php?boardName=" + boardName + "&ID=" + origin.ID + "&pos=" + pos + "&page=" + t + "\">" + t + "</a> ");
		}
		if (threadPages>=7) {
			if (threadPages>=8) {
				document.write("...");
			}
			document.write("<a href=\"disparticle.php?boardName=" + boardName + "&ID=" + origin.ID + "&pos=" + pos + "&page=" + t + "\">" + threadPages + "</a> ");
		}
		document.write(" ]</b>");
	}
	if (article_is_unread(lastreply.FLAGS)) {
		 //���ظ�δ������� thread ��δ��
		document.write("<img src=\"pic/topnew2.gif\" title=\"δ��\">");
	}
	document.write("</TD>");
	document.write('<TD class=TableBody2 width=80 align="center">');
	if (origin_exists) {
		document.write('<a href="dispuser.php?id=' + origin.OWNER + '" target=_blank>' + origin.OWNER + '</a>');
	} else {
		document.write('ԭ����ɾ��');
	}
	document.write('</TD>');
	document.write('<TD class=TableBody1 width=64 align="center">' + ((origin_exists?0:1)+threadNum) + '</TD>');
	document.write('<TD align=left class=TableBody2 width=210><nobr>&nbsp;<a href="disparticle.php?boardName=' + boardName + '&ID=' + origin.ID + '&pos=' + pos + '&page=' + Math.ceil((threadNum+1)/siteconf_THREADSPERPAGE) + '#a' + threadNum + '">');
	document.write(lastreply.POSTTIME + '</a>&nbsp;<font color=#FF0000>|</font>&nbsp;<a href=dispuser.php?id=' + lastreply.OWNER + ' target=_blank>');
	document.write(lastreply.OWNER + '</nobr></a></TD></TR>');
	if (threadNum>0) {
		document.write('<tr style="display:none" id="follow' + origin.ID + '"><td colspan=5 id="followTd' + origin.ID + '" style="padding:0px"><div style="width:240px;margin-left:18px;border:1px solid black;background-color:lightyellow;color:black;padding:2px" onclick="loadThreadFollow(\'' + origin.ID + '\', \'' + boardName + '\')">���ڶ�ȡ���ڱ�����ĸ��������Ժ��</div></td></tr>');
	}
}
