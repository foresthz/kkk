/*
 * Pirate Bulletin Board System Copyright (C) 1990, Edward Luke,
 * lush@Athena.EE.MsState.EDU Eagles Bulletin Board System Copyright (C)
 * 1992, Raymond Rocker, rocker@rock.b11.ingr.com Guy Vega,
 * gtvega@seabass.st.usm.edu Dominic Tynes, dbtynes@seabass.st.usm.edu
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 1, or (at your option) any later
 * version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for
 * more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc., 675
 * Mass Ave, Cambridge, MA 02139, USA.
 */

#include "bbs.h"

char cexplain[STRLEN];
char *Ctime();
static int sysoppassed = 0;

/* modified by wwj, 2001/5/7, for new md5 passwd */
void igenpass(const char *passwd, const char *userid, unsigned char md5passwd[]);

int check_systempasswd()
{
    FILE *pass;
    char passbuf[40], prepass[STRLEN];

#ifdef NINE_BUILD
    return true;
#endif

    if ((sysoppassed) && (time(NULL) - sysoppassed < 60 * 60))
        return true;
    clear();
    if ((pass = fopen("etc/systempassword", "rb")) != NULL) {
        fgets(prepass, STRLEN, pass);
        prepass[strlen(prepass) - 1] = '\0';
        if (!strcmp(prepass, "md5")) {
            fread(&prepass[16], 1, 16, pass);
        }
        fclose(pass);

        getdata(1, 0, "������ϵͳ����: ", passbuf, 39, NOECHO, NULL, true);
        if (passbuf[0] == '\0' || passbuf[0] == '\n')
            return false;


        if (!strcmp(prepass, "md5")) {
            igenpass(passbuf, "[system]", (unsigned char *) prepass);
            passbuf[0] = (char) !memcmp(prepass, &prepass[16], 16);
        } else {
            passbuf[0] = (char) checkpasswd(prepass, passbuf);
        }
        if (!passbuf[0]) {
            move(2, 0);
            prints("ϵͳ�����������...");
            securityreport("ϵͳ�����������...", NULL, NULL);
            pressanykey();
            return false;
        }
    }
    sysoppassed = time(NULL);
    return true;
}

int setsystempasswd()
{
    FILE *pass;
    char passbuf[40], prepass[40];

    modify_user_mode(ADMIN);
    if (strcmp(getCurrentUser()->userid, "SYSOP"))
        return -1;
    if (!check_systempasswd())
        return -1;
    getdata(2, 0, "�������µ�ϵͳ����: ", passbuf, 39, NOECHO, NULL, true);
    getdata(3, 0, "ȷ���µ�ϵͳ����: ", prepass, 39, NOECHO, NULL, true);
    if (strcmp(passbuf, prepass))
        return -1;
    if ((pass = fopen("etc/systempassword", "w")) == NULL) {
        move(4, 0);
        prints("ϵͳ�����޷��趨....");
        pressanykey();
        return -1;
    }
    fwrite("md5\n", 4, 1, pass);

    igenpass(passbuf, "[system]", (unsigned char *) prepass);
    fwrite(prepass, 16, 1, pass);

    fclose(pass);
    move(4, 0);
    prints("ϵͳ�����趨���....");
    pressanykey();
    return 0;
}


void deliverreport(title, str)
char *title;
char *str;
{
    FILE *se;
    char fname[STRLEN];
    int savemode;

    savemode = uinfo.mode;
	gettmpfilename( fname, "deliver" );
    //sprintf(fname, "tmp/deliver.%s.%05d", getCurrentUser()->userid, uinfo.pid);
    if ((se = fopen(fname, "w")) != NULL) {
        fprintf(se, "%s\n", str);
        fclose(se);
        post_file(getCurrentUser(), "", fname, currboard->filename, title, 0, 2, getSession());
        unlink(fname);
        modify_user_mode(savemode);
    }
}


void securityreport(char *str, struct userec *lookupuser, char fdata[7][STRLEN])
{                               /* Leeward: 1997.12.02 */
    FILE *se;
    char fname[STRLEN];
    int savemode;
    char *ptr;

    savemode = uinfo.mode;
	gettmpfilename( fname, "security" );
    //sprintf(fname, "tmp/security.%d", getpid());
    if ((se = fopen(fname, "w")) != NULL) {
        if (lookupuser) {
            if (strstr(str, "���ȷ��")) {
                struct userdata ud;

                read_userdata(lookupuser->userid, &ud);
                fprintf(se, "ϵͳ��ȫ��¼ϵͳ\n\033[32mԭ��%s\033[m\n", str);
                fprintf(se, "������ͨ���߸�������");
                /*
                 * getuinfo(se, lookupuser); 
                 */
                /*
                 * Haohmaru.99.4.15.�ѱ�ע��������еø���ϸ,ͬʱȥ��ע���ߵ����� 
                 */
                fprintf(se, "\n\n���Ĵ���     : %s\n", fdata[1]);
                fprintf(se, "�����ǳ�     : %s\n", lookupuser->username);
                fprintf(se, "��ʵ����     : %s\n", fdata[2]);
                fprintf(se, "�����ʼ����� : %s\n", ud.email);
                if (strstr(str, "�Զ��������"))
                	fprintf(se, "��ʵ E-mail  : %s$%s@SYSOP\n", fdata[3], fdata[5]);
		else	
                	fprintf(se, "��ʵ E-mail  : %s$%s@%s\n", fdata[3], fdata[5], getCurrentUser()->userid);
                fprintf(se, "����λ     : %s\n", fdata[3]);
                fprintf(se, "Ŀǰסַ     : %s\n", fdata[4]);
                fprintf(se, "����绰     : %s\n", fdata[5]);
                fprintf(se, "ע������     : %s", ctime(&lookupuser->firstlogin));
                fprintf(se, "����������� : %s", ctime(&lookupuser->lastlogin));
                fprintf(se, "������ٻ��� : %s\n", lookupuser->lasthost);
                fprintf(se, "��վ����     : %d ��\n", lookupuser->numlogins);
                fprintf(se, "������Ŀ     : %d(Board)\n", lookupuser->numposts);
                fprintf(se, "��    ��     : %s\n", fdata[6]);
                if (strstr(str,"�ܾ�"))
                	fprintf(se, "\033[1;32m�Զ��ܾ����� : %s\033[m\n", fdata[7]);
                /*
                 * fprintf(se, "\n\033[33m��������֤�߸�������\033[35m");
                 * getuinfo(se, getCurrentUser());rem by Haohmaru.99.4.16 
                 */
                fclose(se);
                if (strstr(str,"�ܾ�"))
                	post_file(getCurrentUser(), "", fname, "reject_registry", str, 0, 1, getSession());     
                else
                {
	                if (strstr(str, "�Զ��������"))
	                	post_file(getCurrentUser(), "", fname, "Registry", str, 0, 1, getSession());       
	                else
		                post_file(getCurrentUser(), "", fname, "Registry", str, 0, 2, getSession());
                }
            } else if (strstr(str, "ɾ��ʹ���ߣ�")) {
                fprintf(se, "ϵͳ��ȫ��¼ϵͳ\n\033[32mԭ��%s\033[m\n", str);
                fprintf(se, "�����Ǳ�ɾ�߸�������");
                getuinfo(se, lookupuser);
                fprintf(se, "\n������ɾ���߸�������");
                getuinfo(se, getCurrentUser());
                fclose(se);
                post_file(getCurrentUser(), "", fname, "syssecurity", str, 0, 2, getSession());
            } else if ((ptr = strstr(str, "��Ȩ��XPERM")) != NULL) {
                int oldXPERM, newXPERM;
                int num;
                char XPERM[48];

                sscanf(ptr + strlen("��Ȩ��XPERM"), "%d %d", &oldXPERM, &newXPERM);
                *(ptr + strlen("��Ȩ��")) = 0;

                fprintf(se, "ϵͳ��ȫ��¼ϵͳ\n\033[32mԭ��%s\033[m\n", str);

                strcpy(XPERM, XPERMSTR);
                for (num = 0; num < (int) strlen(XPERM); num++)
                    if (!(oldXPERM & (1 << num)))
                        XPERM[num] = ' ';
                XPERM[num] = '\0';
                fprintf(se, "�����Ǳ�����ԭ����Ȩ��\n\033[1m\033[33m%s", XPERM);

                strcpy(XPERM, XPERMSTR);
                for (num = 0; num < (int) strlen(XPERM); num++)
                    if (!(newXPERM & (1 << num)))
                        XPERM[num] = ' ';
                XPERM[num] = '\0';
                fprintf(se, "\n%s\033[m\n�����Ǳ��������ڵ�Ȩ��\n", XPERM);

                fprintf(se, "\n"
                        "\033[1m\033[33mb\033[m����Ȩ�� \033[1m\033[33mT\033[m�������� \033[1m\033[33mC\033[m�������� \033[1m\033[33mP\033[m������ \033[1m\033[33mR\033[m������ȷ \033[1m\033[33mp\033[mʵϰվ�� \033[1m\033[33m#\033[m������ \033[1m\033[33m@\033[m�ɼ�����\n"
                        "\033[1m\033[33mX\033[m�����ʺ� \033[1m\033[33mW\033[m�༭ϵͳ���� \033[1m\033[33mB\033[m���� \033[1m\033[33mA\033[m�ʺŹ��� \033[1m\033[33m$\033[m������ \033[1m\033[33mV\033[m������� \033[1m\033[33mS\033[mϵͳά��\n"
                        "\033[1m\033[33m!\033[mRead/Post���� \033[1m\033[33mD\033[m�������ܹ� \033[1m\033[33mE\033[m�������ܹ� \033[1m\033[33mM\033[m������ܹ� \033[1m\033[33m1\033[m����ZAP \033[1m\033[33m2\033[m������OP\n"
                        "\033[1m\033[33m3\033[mϵͳ�ܹ���Ա \033[1m\033[33m4\033[m�����ʺ� \033[1m\033[33m5 7\033[m ����Ȩ�� \033[1m\033[33m6\033[m�ٲ� \033[1m\033[33m8\033[m��ɱ \033[1m\033[33m9\033[m�����ʺ� \033[1m\033[33m0\033[m��ϵͳ���۰�\n"
			"\033[1m\033[33m%%\033[m���Mail"
                        "\n");

                fprintf(se, "\n�����Ǳ����߸�������");
                getuinfo(se, lookupuser);
                fprintf(se, "\n�������޸��߸�������");
                getuinfo(se, getCurrentUser());
                fclose(se);
                post_file(getCurrentUser(), "", fname, "syssecurity", str, 0, 2, getSession());
            } else {            /* Modified for change id by Bigman 2001.5.25 */

                fprintf(se, "ϵͳ��ȫ��¼ϵͳ\x1b[32mԭ��%s\x1b[m\n", str);
                fprintf(se, "�����Ǹ�������");
                getuinfo(se, lookupuser);
                fclose(se);
                post_file(getCurrentUser(), "", fname, "syssecurity", str, 0, 2, getSession());
            }
        } else {
            fprintf(se, "ϵͳ��ȫ��¼ϵͳ\n\033[32mԭ��%s\033[m\n", str);
            fprintf(se, "�����Ǹ�������");
            getuinfo(se, getCurrentUser());
            fclose(se);
            if (strstr(str, "�趨ʹ����ע������"))      /* Leeward 98.03.29 */
                post_file(getCurrentUser(), "", fname, "Registry", str, 0, 2, getSession());
            else {
                if((ptr = strchr(str, '\n')) != NULL)
                    sprintf(ptr, "...");
                post_file(getCurrentUser(), "", fname, "syssecurity", str, 0, 2, getSession());
            }
        }
        unlink(fname);
        modify_user_mode(savemode);
    }
}

void stand_title(title)
char *title;
{
    clear();
    prints("\x1b[7m%s\x1b[m", title);
}

int m_info()
{
    struct userec uinfo;
    int id;
    struct userec *lookupuser;


    modify_user_mode(ADMIN);
    if (!check_systempasswd()) {        /* Haohmaru.98.12.19 */
        return -1;
    }
    clear();
    stand_title("�޸�ʹ���ߴ���");
    move(1, 0);
    usercomplete("������ʹ���ߴ���: ", genbuf);
    if (*genbuf == '\0') {
        clear();
        return -1;
    }
    if (!(id = getuser(genbuf, &lookupuser))) {
        move(3, 0);
        prints(MSG_ERR_USERID);
        clrtoeol();
        pressreturn();
        clear();
        return -1;
    }
    uinfo = *lookupuser;

    move(1, 0);
    clrtobot();
    disply_userinfo(&uinfo, 1);
    uinfo_query(&uinfo, 1, id);
    return 0;
}

extern int cmpbnames();

const char *chgrp()
{
    int i, ch;
    char buf[STRLEN], ans[6];

    clear();
    move(2, 0);
    prints("ѡ�񾫻�����Ŀ¼\n");
    oflush();

    for (i = 0;; i++) {
        if (secname[i][0] == NULL || groups[i] == NULL)
            break;
        prints("\033[32m%2d\033[m. %-20s%-20s\n", i, secname[i][0], groups[i]);
    }
    sprintf(buf, "���������ѡ��(0~%d): ", i - 1);
    while (1) {
        getdata(i + 3, 0, buf, ans, 4, DOECHO, NULL, true);
        if (!isdigit(ans[0]))
            continue;
        ch = atoi(ans);
        if (ch < 0 || ch >= i || ans[0] == '\r' || ans[0] == '\0')
            continue;
        else
            break;
    }
    sprintf(cexplain, "%s", secname[ch][0]);

    return groups[ch];
}


int m_newbrd()
{
    struct boardheader newboard;
    char ans[5];
    char vbuf[100];
    const char *group;


    modify_user_mode(ADMIN);
    if (!check_systempasswd()) {
        return -1;
    }
    clear();
    memset(&newboard, 0, sizeof(newboard));
    prints("������������:");
    while (1) {
        getdata(3, 0, "����������:   ", newboard.filename, BOARDNAMELEN, DOECHO, NULL, true);
        if (newboard.filename[0] == '\0')
            return -1;
        if (valid_brdname(newboard.filename))
            break;
        prints("���Ϸ�����...");
    }
#ifndef ZIXIA
    getdata(4, 0, "������˵��:   ", newboard.title, 60, DOECHO, NULL, true);
#else
    while(1){
        getdata(4, 0, "������˵��:   ", newboard.title, 60, DOECHO, NULL, true);
        if (newboard.title[0] != '\0')
        if(NoSpaceBdT(newboard.title))
                break;
                prints("������Ϸ�������˵��...");
                }
#endif
    strcpy(vbuf, "vote/");
    strcat(vbuf, newboard.filename);
    setbpath(genbuf, newboard.filename);
    if (getbnum(newboard.filename) > 0 || mkdir(genbuf, 0755) == -1 || mkdir(vbuf, 0755) == -1) {
        prints("\n���󣺴��������������\n");
        pressreturn();
        clear();
        return -1;
    }
    newboard.flag = 0;
    getdata(5, 0, "����������Ա: ", newboard.BM, BM_LEN - 1, DOECHO, NULL, true);
    getdata(6, 0, "�Ƿ����ƴ�ȡȨ�� (Y/N)? [N]: ", ans, 4, DOECHO, NULL, true);
    if (*ans == 'y' || *ans == 'Y') {
        getdata(6, 0, "���� Read/Post? [R]: ", ans, 4, DOECHO, NULL, true);
        if (*ans == 'P' || *ans == 'p')
            newboard.level = PERM_POSTMASK;
        else
            newboard.level = 0;
        move(1, 0);
        clrtobot();
        move(2, 0);
        prints("�趨 %s Ȩ��. ������: '%s'\n", (newboard.level & PERM_POSTMASK ? "POST" : "READ"), newboard.filename);
        newboard.level = setperms(newboard.level, 0, "Ȩ��", NUMPERMS, showperminfo, NULL);
        clear();
    } else
        newboard.level = 0;
    getdata(7, 0, "�Ƿ���������� (Y/N)? [N]: ", ans, 4, DOECHO, NULL, true);
    if (ans[0] == 'Y' || ans[0] == 'y') {
        newboard.flag |= BOARD_ANNONY;
        addtofile("etc/anonymous", newboard.filename);
    }
    getdata(8, 0, "�Ƿ񲻼�������(Y/N)? [N]: ", ans, 4, DOECHO, NULL, true);
    if (ans[0] == 'Y' || ans[0] == 'y')
        newboard.flag |= BOARD_JUNK;
    getdata(9, 0, "�Ƿ�ͳ��ʮ��(Y/N)? [N]: ", ans, 4, DOECHO, NULL, true);
    if (ans[0] == 'Y' || ans[0] == 'y')
        newboard.flag |= BOARD_POSTSTAT;
    getdata(10, 0, "�Ƿ������ת��(Y/N)? [N]: ", ans, 4, DOECHO, NULL, true);
    if (ans[0] == 'Y' || ans[0] == 'y')
        newboard.flag |= BOARD_OUTFLAG;
    getdata(11, 0, "�Ƿ񲻿�re����(Y/N)? [N]: ", ans, 4, DOECHO, NULL, true);
    if (ans[0] == 'Y' || ans[0] == 'y')
        newboard.flag |= BOARD_NOREPLY;
    build_board_structure(newboard.filename);
    group = chgrp();
    if (group != NULL) {
        if (newboard.BM[0] != '\0')
            if (strlen(newboard.BM) <= 30)
                sprintf(vbuf, "%-38.38s(BM: %s)", newboard.title + 13, newboard.BM);
            else
                snprintf(vbuf, STRLEN, "%-28.28s(BM: %s)", newboard.title + 13, newboard.BM);
        else
            sprintf(vbuf, "%-38.38s", newboard.title + 13);

        if (add_grp(group, newboard.filename, vbuf, cexplain, getSession()) == -1)
            prints("\n����������ʧ��....\n");
        else
            prints("�Ѿ����뾫����...\n");
        snprintf(newboard.ann_path,127,"%s/%s",group, newboard.filename);
        newboard.ann_path[127]=0;
    }
    if (add_board(&newboard) == -1) {
		currboard = bcache;
        move(t_lines - 1, 0);
        outs("����������ʧ��!\n");
        pressreturn();
        clear();
        return -1;
    }
	currboard = bcache;
    prints("\n������������\n");
    sprintf(genbuf, "add brd %s", newboard.filename);
    bbslog("user", "%s", genbuf);
    {
        char secu[STRLEN];

        sprintf(secu, "�����°棺%s", newboard.filename);
        securityreport(secu, NULL, NULL);
    }
    pressreturn();
    clear();
    return 0;
}

int m_editbrd()
{
    char bname[STRLEN], buf[STRLEN], oldtitle[STRLEN], vbuf[256];
    char oldpath[STRLEN], newpath[STRLEN];
    int pos, noidboard, a_mv;
    struct boardheader fh, newfh;
    int line;

    const struct boardheader* bh=NULL;
    const char* groupname="";

    modify_user_mode(ADMIN);
    if (!check_systempasswd()) {
        return -1;
    }
    clear();
    stand_title("�޸���������Ѷ");
    move(1, 0);
    make_blist(0);
    namecomplete("��������������: ", bname);
    if (*bname == '\0') {
        move(2, 0);
        prints("���������������");
        pressreturn();
        clear();
        return -1;
    }
    pos = getboardnum(bname, &fh);
    if (!pos) {
        move(2, 0);
        prints("���������������");
        pressreturn();
        clear();
        return -1;
    }
    noidboard = anonymousboard(bname);
    move(2, 0);
    memcpy(&newfh, &fh, sizeof(newfh));
    prints("����������:   %s ; ����Ա:%s\n", fh.filename, fh.BM);
    prints("������˵��:   %s\n", fh.title);

	strncpy(vbuf, fh.des, 60);
	vbuf[60]=0;
	if(strlen(fh.des) > strlen(vbuf)) strcat(vbuf, "...");
    prints("����������: %s\n", vbuf);

    prints("����������: %s  ����������: %s  ��ͳ��ʮ��: %s  �Ƿ���Ŀ¼: %s\n", 
        (noidboard) ? "Yes" : "No", (fh.flag & BOARD_JUNK) ? "Yes" : "No", (fh.flag & BOARD_POSTSTAT) ? "Yes" : "No", (fh.flag & BOARD_GROUP) ? "Yes" : "No");
    if (newfh.group) {
        bh=getboard(newfh.group);
        if (bh) groupname=bh->filename;
    }
    prints("����Ŀ¼��%s\n",bh?groupname:"��");
    prints("������ת��: %s  ��ճ������: %s ����Email����: %s ����re��: %s\n", 
			(fh.flag & BOARD_OUTFLAG) ? "Yes" : "No",
			(fh.flag & BOARD_ATTACH) ? "Yes" : "No",
			(fh.flag & BOARD_EMAILPOST) ? "Yes" : "No",
			(fh.flag & BOARD_NOREPLY) ? "Yes" : "No");
    if (fh.flag & BOARD_CLUB_READ || fh.flag & BOARD_CLUB_WRITE)
        prints("���ֲ�:   %s %s %s  ���: %d\n", fh.flag & BOARD_CLUB_READ ? "�Ķ�����" : "", fh.flag & BOARD_CLUB_WRITE ? "��������" : "", fh.flag & BOARD_CLUB_HIDE ? "����" : "", fh.clubnum);
    else
        prints("%s", "���ֲ�:   ��\n");
    strcpy(oldtitle, fh.title);
    prints("���� %s Ȩ��: %s"
#ifdef HAVE_CUSTOM_USER_TITLE
        "      ��Ҫ���û�ְ��: %s(%d)"
#endif
        ,
        (fh.level & PERM_POSTMASK) ? "POST" : "READ", 
        (fh.level & ~PERM_POSTMASK) == 0 ? "������" : "������"
#ifdef HAVE_CUSTOM_USER_TITLE
        ,fh.title_level? get_user_title(fh.title_level):"��",fh.title_level
#endif
        );
    getdata(10, 0, "�Ƿ����������Ѷ(��S�س��޸�����)? (Yes or No) [N]: ", genbuf, 4, DOECHO, NULL, true);
	if (*genbuf == 's' || *genbuf == 'S'){

		move(11,0);
		prints("�������µ�����:");
		multi_getdata(12, 0, 79, NULL, newfh.des, 195, 8, false, 0);
		if( newfh.des[0] ){
        	getdata(21, 0, "ȷ��Ҫ������? (Y/N) [N]: ", genbuf, 4, DOECHO, NULL, true);
        	if (*genbuf == 'Y' || *genbuf == 'y') {
            	set_board(pos, &newfh, &fh);
            	sprintf(genbuf, "���������� %s ������ --> %s", fh.filename, newfh.filename);
            	bbslog("user", "%s", genbuf);
			}
		}
	}else if (*genbuf == 'y' || *genbuf == 'Y') {
        move(9, 0);
        prints("ֱ�Ӱ� <Return> ���޸Ĵ�����Ѷ\n");
      enterbname:
        getdata(10, 0, "������������: ", genbuf, BOARDNAMELEN, DOECHO, NULL, true);
        if (*genbuf != 0) {
            if (getboardnum(genbuf, NULL) > 0) {
                move(3, 0);
                prints("����! ���������Ѿ�����\n");
                move(11, 0);
                clrtobot();
                goto enterbname;
            }
	    if (!valid_brdname(genbuf))
	     	{
	     	  move(3, 0);
                prints("����!�Ƿ�������������\n");
                move(11, 0);
                clrtobot();
                goto enterbname;
	     	}
            strncpy(newfh.filename, genbuf, sizeof(newfh.filename));
            strcpy(bname, genbuf);
        }
        line=11;
        getdata(line++, 0, "��������˵��: ", genbuf, 60, DOECHO, NULL, true);
        if (*genbuf != 0)
            strncpy(newfh.title, genbuf, sizeof(newfh.title));
        getdata(line++, 0, "����������Ա: ", genbuf, 60, DOECHO, NULL, true);
        if (*genbuf != 0)
            strncpy(newfh.BM, genbuf, sizeof(newfh.BM));
        if (*genbuf == ' ')
            strncpy(newfh.BM, "\0", sizeof(newfh.BM));
        /*
         * newfh.BM[ BM_LEN - 1 ]=fh.BM[ BM_LEN - 1 ]; 
         */
        sprintf(buf, "������ (Y/N)? [%c]: ", (noidboard) ? 'Y' : 'N');
        getdata(line++, 0, buf, genbuf, 4, DOECHO, NULL, true);
        if (*genbuf == 'y' || *genbuf == 'Y' || *genbuf == 'N' || *genbuf == 'n') {
            if (*genbuf == 'y' || *genbuf == 'Y')
                noidboard = 1;
            else
                noidboard = 0;
        }
        sprintf(buf, "���������� (Y/N)? [%c]: ", (newfh.flag & BOARD_JUNK) ? 'Y' : 'N');
        getdata(line++, 0, buf, genbuf, 4, DOECHO, NULL, true);
        if (*genbuf == 'y' || *genbuf == 'Y' || *genbuf == 'N' || *genbuf == 'n') {
            if (*genbuf == 'y' || *genbuf == 'Y')
                newfh.flag |= BOARD_JUNK;
            else
                newfh.flag &= ~BOARD_JUNK;
        };
        sprintf(buf, "��ͳ��ʮ�� (Y/N)? [%c]: ", (newfh.flag & BOARD_POSTSTAT) ? 'Y' : 'N');
        getdata(line++, 0, buf, genbuf, 4, DOECHO, NULL, true);
        if (*genbuf == 'y' || *genbuf == 'Y' || *genbuf == 'N' || *genbuf == 'n') {
            if (*genbuf == 'y' || *genbuf == 'Y')
                newfh.flag |= BOARD_POSTSTAT;
            else
                newfh.flag &= ~BOARD_POSTSTAT;
        };
        sprintf(buf, "������ת�� (Y/N)? [%c]: ", (newfh.flag & BOARD_OUTFLAG) ? 'Y' : 'N');
        getdata(line++, 0, buf, genbuf, 4, DOECHO, NULL, true);
        if (*genbuf == 'y' || *genbuf == 'Y' || *genbuf == 'N' || *genbuf == 'n') {
            if (*genbuf == 'y' || *genbuf == 'Y')
                newfh.flag |= BOARD_OUTFLAG;
            else
                newfh.flag &= ~BOARD_OUTFLAG;
        };
        sprintf(buf, "��ճ������ (Y/N)? [%c]: ", (newfh.flag & BOARD_ATTACH) ? 'Y' : 'N');
        getdata(line++, 0, buf, genbuf, 4, DOECHO, NULL, true);
        if (*genbuf == 'y' || *genbuf == 'Y' || *genbuf == 'N' || *genbuf == 'n') {
            if (*genbuf == 'y' || *genbuf == 'Y')
                newfh.flag |= BOARD_ATTACH;
            else
                newfh.flag &= ~BOARD_ATTACH;
        };
        sprintf(buf, "���� Email ���� (Y/N)? [%c]: ", (newfh.flag & BOARD_EMAILPOST) ? 'Y' : 'N');
        getdata(line++, 0, buf, genbuf, 4, DOECHO, NULL, true);
        if (*genbuf == 'y' || *genbuf == 'Y' || *genbuf == 'N' || *genbuf == 'n') {
            if (*genbuf == 'y' || *genbuf == 'Y')
                newfh.flag |= BOARD_EMAILPOST;
            else
                newfh.flag &= ~BOARD_EMAILPOST;
        };
        sprintf(buf, "����re�� (Y/N)? [%c]: ", (newfh.flag & BOARD_NOREPLY) ? 'Y' : 'N');
        getdata(line++, 0, buf, genbuf, 4, DOECHO, NULL, true);
        if (*genbuf == 'y' || *genbuf == 'Y' || *genbuf == 'N' || *genbuf == 'n') {
            if (*genbuf == 'y' || *genbuf == 'Y')
                newfh.flag |= BOARD_NOREPLY;
            else
                newfh.flag &= ~BOARD_NOREPLY;
        };
        getdata(line++, 0, "�Ƿ��ƶ���������λ�� (Y/N)? [N]: ", genbuf, 4, DOECHO, NULL, true);
        if (*genbuf == 'Y' || *genbuf == 'y')
            a_mv = 2;           /* ��ʾ�ƶ�������Ŀ¼ */
        else
            a_mv = 0;
        sprintf(buf, "�Ƿ�Ϊ�����ƾ��ֲ�: (Y/N)? [%c]", (newfh.flag & BOARD_CLUB_READ) ? 'Y' : 'N');
        getdata(line++, 0, buf, genbuf, 4, DOECHO, NULL, true);
        if (*genbuf == 'Y' || *genbuf == 'y')
            newfh.flag |= BOARD_CLUB_READ;
        else if (*genbuf == 'N' || *genbuf == 'n')
            newfh.flag &= ~BOARD_CLUB_READ;
        sprintf(buf, "�Ƿ�Ϊ�������ƾ��ֲ�: (Y/N)? [%c]", (newfh.flag & BOARD_CLUB_WRITE) ? 'Y' : 'N');
        getdata(line++, 0, buf, genbuf, 4, DOECHO, NULL, true);
        if (*genbuf == 'Y' || *genbuf == 'y')
            newfh.flag |= BOARD_CLUB_WRITE;
        else if (*genbuf == 'N' || *genbuf == 'n')
            newfh.flag &= ~BOARD_CLUB_WRITE;
        if (newfh.flag & BOARD_CLUB_WRITE || newfh.flag & BOARD_CLUB_READ) {
            sprintf(buf, "�Ƿ�Ϊ�������ƾ��ֲ�: (Y/N)? [%c]", (newfh.flag & BOARD_CLUB_HIDE) ? 'Y' : 'N');
            getdata(line++, 0, buf, genbuf, 4, DOECHO, NULL, true);
            if (*genbuf == 'Y' || *genbuf == 'y')
                newfh.flag |= BOARD_CLUB_HIDE;
            else if (*genbuf == 'N' || *genbuf == 'n')
                newfh.flag &= ~BOARD_CLUB_HIDE;
        } else
            newfh.flag &= ~BOARD_CLUB_HIDE;
        
        sprintf(buf, "�Ƿ�ΪĿ¼ (Y/N)? [%c]", (newfh.flag & BOARD_GROUP) ? 'Y' : 'N');
        getdata(line++, 0, buf, genbuf, 4, DOECHO, NULL, true);
        if (*genbuf == 'Y' || *genbuf == 'y')
            newfh.flag |= BOARD_GROUP;
        else if (*genbuf == 'N' || *genbuf == 'n')
            newfh.flag &= ~BOARD_GROUP;

        while(1) {
            sprintf(buf, "�趨����Ŀ¼[%s]", groupname);
            strcpy(genbuf,groupname);
            getdata(line, 0, buf, genbuf, BOARDNAMELEN, DOECHO, NULL, false);
            if (*genbuf == 0) {
                newfh.group = 0;
                break;
            }
            newfh.group=getbnum(genbuf);
            if (newfh.group) {
		if (!(getboard(newfh.group)->flag&BOARD_GROUP)) {
                    move(line+1,0);
                    prints("����Ŀ¼");
		} else break;
            }
        }
        
        line++;
        
#ifdef HAVE_CUSTOM_USER_TITLE
        getdata(line++, 0, "����ְ��: ", genbuf, 60, DOECHO, NULL, true); 
        if (*genbuf != 0)
            newfh.title_level=atoi(genbuf);
#endif

        getdata(line++, 0, "�Ƿ���Ĵ�ȡȨ�� (Y/N)? [N]: ", genbuf, 4, DOECHO, NULL, true);
        if (*genbuf == 'Y' || *genbuf == 'y') {
            char ans[5];

            sprintf(genbuf, "���� (R)�Ķ� �� (P)���� ���� [%c]: ", (newfh.level & PERM_POSTMASK ? 'P' : 'R'));
            getdata(line++, 0, genbuf, ans, 4, DOECHO, NULL, true);
            if ((newfh.level & PERM_POSTMASK) && (*ans == 'R' || *ans == 'r'))
                newfh.level &= ~PERM_POSTMASK;
            else if (!(newfh.level & PERM_POSTMASK) && (*ans == 'P' || *ans == 'p'))
                newfh.level |= PERM_POSTMASK;
            move(1, 0);
            clrtobot();
            move(2, 0);
            prints("�趨 %s '%s' ��������Ȩ��\n", newfh.level & PERM_POSTMASK ? "����" : "�Ķ�", newfh.filename);
            newfh.level = setperms(newfh.level, 0, "Ȩ��", NUMPERMS, showperminfo, NULL);
            clear();
            getdata(0, 0, "ȷ��Ҫ������? (Y/N) [N]: ", genbuf, 4, DOECHO, NULL, true);
        } else {
            getdata(line++, 0, "ȷ��Ҫ������? (Y/N) [N]: ", genbuf, 4, DOECHO, NULL, true);
        }
        if (*genbuf == 'Y' || *genbuf == 'y') {
            char secu[STRLEN];

            sprintf(secu, "�޸���������%s(%s)", fh.filename, newfh.filename);
#ifndef ZIXIA
            securityreport(secu, NULL, NULL);
#else
            board_change_report(secu, &fh, &newfh);
#endif	
            if (strcmp(fh.filename, newfh.filename)) {
                char old[256], tar[256];

                a_mv = 1;       /* ��ʾ�����ı䣬��Ҫ���¾�����·�� */
                setbpath(old, fh.filename);
                setbpath(tar, newfh.filename);
                f_mv(old, tar);
                sprintf(old, "vote/%s", fh.filename);
                sprintf(tar, "vote/%s", newfh.filename);
                f_mv(old, tar);
            }
            if (newfh.BM[0] != '\0')
                if (strlen(newfh.BM) <= 30)
                    sprintf(vbuf, "%-38.38s(BM: %s)", newfh.title + 13, newfh.BM);
                else
                    snprintf(vbuf, STRLEN, "%-28.28s(BM: %s)", newfh.title + 13, newfh.BM);
            else
                sprintf(vbuf, "%-38.38s", newfh.title + 13);
            edit_grp(fh.filename, oldtitle + 13, vbuf);
            if (a_mv >= 1) {
                const char *group;
                group = chgrp();
                /*
                 * ��ȡ�ð��Ӧ�� group 
                 */
                ann_get_path(fh.filename, newpath, sizeof(newpath));
                snprintf(oldpath, sizeof(oldpath), "0Announce/%s", newpath);
                sprintf(newpath, "0Announce/groups/%s/%s", group, newfh.filename);
                if (strcmp(oldpath, newpath) || a_mv != 2) {
                    if (group != NULL) {
                        if (newfh.BM[0] != '\0')
                            if (strlen(newfh.BM) <= 30)
                                sprintf(vbuf, "%-38.38s(BM: %s)", newfh.title + 13, newfh.BM);
                            else
                                sprintf(vbuf, "%-28.28s(BM: %s)", newfh.title + 13, newfh.BM);
                        else
                            sprintf(vbuf, "%-38.38s", newfh.title + 13);

                        if (add_grp(group, newfh.filename, vbuf, cexplain, getSession()) == -1)
                            prints("\n����������ʧ��....\n");
                        else
                            prints("�Ѿ����뾫����...\n");
                        if (dashd(oldpath)) {
                            /*
                             * sprintf(genbuf, "/bin/rm -fr %s", newpath);
                             */
                            my_f_rm(newpath);
                        }
                        f_mv(oldpath, newpath);
                        del_grp(fh.filename, fh.title + 13);
                        snprintf(newfh.ann_path,127,"%s/%s",group, newfh.filename);
                        newfh.ann_path[127]=0;
                    }
                }
            }
            if (noidboard == 1 && !anonymousboard(newfh.filename)) {
                newfh.flag |= BOARD_ANNONY;
                addtofile("etc/anonymous", newfh.filename);
            } else if (noidboard == 0) {
                newfh.flag &= ~BOARD_ANNONY;
                del_from_file("etc/anonymous", newfh.filename);
            }
            set_board(pos, &newfh, &fh);
            sprintf(genbuf, "���������� %s ������ --> %s", fh.filename, newfh.filename);
            bbslog("user", "%s", genbuf);
        }
    }
    clear();
    return 0;
}






/*etnlegend,2005.07.01,�޸�����������*/
#define KEY_CANCEL '~'
#define EDITBRD_WAIT while(igetkey()!=13);
extern int in_do_sendmsg;
static int lastkey=0;
/*����Ȩ���ַ���*/
char* gen_permstr(unsigned int level,char* buf){
    int i;
    /*����bufӦ�þ����㹻�Ĵ�С*/
    sprintf(buf,"%s","bTCPRp#@XWBA$VS!DEM1234567890%");
    for(i=0;i<30;i++)
        if(!(level&(1<<i)))
            buf[i]='-';
    return buf;
}
/*�ϸ��龫����·���ĺϷ���*/
unsigned int check_ann(struct boardheader* bh){
    char buf[256],*ptr;
    unsigned int ret,i;
    ret=0;
    sprintf(buf,"%s",bh->ann_path);
    ptr=strrchr(buf,'/');
    *ptr++=0;
    /*������·�������������Ʋ���*/
    if(strcmp(bh->filename,ptr))
        ret|=0x010000;
    /*��������������*/
    for(i=0;groups[i];i++)
        if(!strcmp(groups[i],buf))
            break;
    if(!groups[i])
        ret|=0x020000;
    else
        ret|=(i&0xFFFF);
    /*������Ŀ¼������*/
    sprintf(buf,"0Announce/groups/%s",bh->ann_path);
    if(!dashd(buf))
        ret|=0x040000;
    return ret;
}
/*����list_select_loop����ṹ*/
struct _simple_select_arg{
    const struct _select_item* items;
    int flag;
};
static int editbrd_on_select(struct _select_def* conf){
    return SHOW_SELECT;
}
static int editbrd_show(struct _select_def* conf,int i){
    struct _simple_select_arg* arg=(struct _simple_select_arg*)conf->arg;
    outs((char*)((arg->items[i-1]).data));
    return SHOW_CONTINUE;
}
static int editbrd_key(struct _select_def* conf,int key){
    struct _simple_select_arg *arg=(struct _simple_select_arg*)conf->arg;
    int i;
    lastkey=key;
    if(key==KEY_ESC)
        return SHOW_QUIT;
    if(key==KEY_CANCEL)
        return SHOW_QUIT;
    for(i=0;i<conf->item_count;i++)
        if(toupper(key)==toupper(arg->items[i].hotkey)){
            conf->new_pos=i+1;
            return SHOW_SELCHANGE;
        }
    return SHOW_CONTINUE;
}
/*ѡ�������������򾫻�������*/
int select_group(int pos){
    /*ʹ����SECNUM��*/
    struct _select_item sel[SECNUM+1];
    struct _select_def conf;
    struct _simple_select_arg arg;
    POINT pts[SECNUM];
    char menustr[SECNUM][64];
    int i;
    /*����˵���ʾ*/
    for(i=0;i<SECNUM;i++){
        sel[i].x=4;
        sel[i].y=i+4;
        sel[i].hotkey=((i<10)?('0'+i):('A'+i-10));
        sel[i].type=SIT_SELECT;
        sel[i].data=menustr[i];
        sprintf(menustr[i],"[%c] %-24s%-24s",sel[i].hotkey,secname[i][0],groups[i]);
        pts[i].x=sel[i].x;
        pts[i].y=sel[i].y;
    }
    sel[i].x=-1;sel[i].y=-1;sel[i].hotkey=-1;sel[i].type=0;sel[i].data=NULL;
    /*������ʾ��ǰ����*/
    if(!(pos<0)&&pos<SECNUM)
        sprintf(menustr[pos],"\033[1;36m[%c] %-24s%-24s\033[m",sel[pos].hotkey,secname[pos][0],groups[pos]);
    /*����select�ṹ*/
    arg.items=sel;
    arg.flag=SIF_SINGLE;
    bzero(&conf,sizeof(struct _select_def));
    conf.item_count=SECNUM;
    conf.item_per_page=SECNUM;
    conf.flag=LF_LOOP;
    conf.prompt="��";
    conf.item_pos=pts;
    conf.arg=&arg;
    conf.title_pos.x=-1;
    conf.title_pos.y=-1;
    /*��ʼλ��*/
    conf.pos=(!(pos<0)&&pos<SECNUM)?(pos+1):0;
    conf.on_select=editbrd_on_select;
    conf.show_data=editbrd_show;
    conf.key_command=editbrd_key;
    /*ѡ�����*/
    move(1,0);clrtobot();
    move(2,4);prints("\033[1;33m��ѡ�񾫻������ڷ���\033[m");
    list_select_loop(&conf);
    return conf.pos-1;
}
/*�޸�����������ά��������*/
int new_m_editbrd(void){
    struct _select_item sel[24];
    struct _select_def conf;
    struct _simple_select_arg arg;
    POINT pts[23];
    struct boardheader bh,newbh;
    char buf[256],src[256],dst[256],menustr[23][256],orig[23][256],*ptr;
    int i,pos,loop,section,currpos,ret;
    unsigned int annstat,change,error;
    const struct boardheader *bhptr=NULL;
    const char menuldr[23][16]={
        "[1]����������:","[2]����������:","[3]������˵��:","[4]����������:","[5]����������:",
        "[6]ת�ű�ǩ  :","[7]����������:","[8]����������:","[9]ͳ��������:","[A]ͳ��ʮ��  :",
        "[B]Ŀ¼������:","[C]����Ŀ¼  :","[D]����ת��  :","[E]�ϴ�����  :","[F]E-mail����:",
        "[G]���ɻظ�  :","[H]������Club:","[I]д����Club:","[J]����Club  :","[K]������λ��:",
        "[L]Ȩ������  :","[M]�������  :","[Q][�˳�]    :"
    };
    pos=0;change=0;loop=1;
    /*���ϵͳ���벢�޸�״̬*/
    if(!check_systempasswd())
        return -1;
    modify_user_mode(ADMIN);
    /*ѡ��������*/
    clear();
    move(0,0);prints("\033[1;32m�޸�������˵�����趨\033[m");
    move(1,0);clrtobot();
    make_blist(0);
    in_do_sendmsg=1;
    i = namecomplete("����������������: ",buf);
    in_do_sendmsg=0;
    if(i=='#'){
        if(!HAS_PERM(getCurrentUser(),PERM_ADMIN)){
            move(2,0);prints("ʹ�þ�Ԯģʽ�޸�������������ҪADMINȨ��...");
            EDITBRD_WAIT;clear();
            return -1;
        }
        /*��Ԯģʽ*/
        getdata(2,0,"������������˳���(��������ֱ�ӻس�): ",buf,8,DOECHO,NULL,true);
        pos=atoi(buf);
        if(!pos){
            getdata(3,0,"����������������������: ",buf,128,DOECHO,NULL,true);
            if(!*buf){
                move(4,0);prints("ȡ��...");
                EDITBRD_WAIT;clear();
                return -1;
            }
            pos=getboardnum(buf,&bh);
            if(!pos){
                move(4,0);prints("���������������!");
                EDITBRD_WAIT;clear();
                return -1;
            }
        }
        else{
            bhptr=getboard(pos);
            if(!(bhptr&&bhptr->filename[0])){
                move(3,0);prints("�����������˳���!");
                EDITBRD_WAIT;clear();
                return -1;
            }
            memcpy(&bh,bhptr,sizeof(struct boardheader));
        }
    }
    else{
        /*����ģʽ*/
        if(!*buf){
            move(2,0);prints("ȡ��...");
            EDITBRD_WAIT;clear();
            return -1;
        }
        pos=getboardnum(buf,&bh);
        if(!pos){
            move(2,0);prints("���������������!");
            EDITBRD_WAIT;clear();
            return -1;
        }
    }
    sprintf(buf,"\033[1;33mbid=%4.4d clubnum=%3.3d\033[m",pos,bh.clubnum);
    move(0,40);prints(buf);
    /*��ȡ���������ݲ�����˵���ʽ*/
    memcpy(&newbh,&bh,sizeof(struct boardheader));
    /*�˵���λ*/
    for(i=0;i<23;i++){
        if(i<13){
            sel[i].x=2;
            sel[i].y=i+2;
        }
        else if(i<19){
            sel[i].x=42;
            sel[i].y=i-4;
        }
        else{
            sel[i].x=2;
            sel[i].y=i-4;
        }
        sel[i].type=SIT_SELECT;
        sel[i].data=menustr[i];
        pts[i].x=sel[i].x;
        pts[i].y=sel[i].y;
    }
    /*�˵�����*/
    /*����������*/
    sel[0].hotkey='1';
    sprintf(menustr[0],"%-15s%s",menuldr[0],bh.filename);
    /*����������*/
    sel[1].hotkey='2';
    sprintf(menustr[1],"%-15s%s",menuldr[1],bh.BM);
    /*������˵��*/
    sel[2].hotkey='3';
    sprintf(menustr[2],"%-15s%s",menuldr[2],&bh.title[13]);
    /*����������*/
    sel[3].hotkey='4';
    sprintf(menustr[3],"%-15s<%c>",menuldr[3],bh.title[0]);
    /*����������*/
    sel[4].hotkey='5';
    sprintf(menustr[4],"%-15s<%-6.6s>",menuldr[4],&bh.title[1]);
    /*ת�ű�ǩ*/
    sel[5].hotkey='6';
    sprintf(menustr[5],"%-15s<%-6.6s>",menuldr[5],&bh.title[7]);
    /*����������*/
    sel[6].hotkey='7';
    sprintf(buf,"%s",bh.des);
    for(ptr=&buf[0];*ptr;ptr++)
        if(*ptr==10)
            *ptr=32;
    sprintf(menustr[6],"%-15s%s",menuldr[6],buf[0]?buf:"<��>");
    if(strlen(menustr[6])>76)
        sprintf(&menustr[6][73],"...");
    /*����������*/
    sel[7].hotkey='8';
    sprintf(menustr[7],"%-15s%s",menuldr[7],(bh.flag&BOARD_ANNONY)?"��":"��");
    /*ͳ��������*/
    sel[8].hotkey='9';
    sprintf(menustr[8],"%-15s%s",menuldr[8],(bh.flag&BOARD_JUNK)?"��":"��");
    /*ͳ��ʮ��*/
    sel[9].hotkey='A';
    sprintf(menustr[9],"%-15s%s",menuldr[9],(bh.flag&BOARD_POSTSTAT)?"��":"��");
    /*Ŀ¼������*/
    sel[10].hotkey='B';
    sprintf(menustr[10],"%-15s%s",menuldr[10],(bh.flag&BOARD_GROUP)?"��":"��");
    /*����Ŀ¼*/
    sel[11].hotkey='C';
    sprintf(menustr[11],"%-15s%s",menuldr[11],
        bh.group?(!(bhptr=getboard(bh.group))?"�쳣":bhptr->filename):"��");
    /*����ת��*/
    sel[12].hotkey='D';
    sprintf(menustr[12],"%-15s%s",menuldr[12],(bh.flag&BOARD_OUTFLAG)?"��":"��");
    /*�ϴ�����*/
    sel[13].hotkey='E';
    sprintf(menustr[13],"%-15s%s",menuldr[13],(bh.flag&BOARD_ATTACH)?"��":"��");
    /*E-mail����*/
    sel[14].hotkey='F';
    sprintf(menustr[14],"%-15s%s",menuldr[14],(bh.flag&BOARD_EMAILPOST)?"��":"��");
    /*���ɻظ�*/
    sel[15].hotkey='G';
    sprintf(menustr[15],"%-15s%s",menuldr[15],(bh.flag&BOARD_NOREPLY)?"��":"��");
    /*������Club*/
    sel[16].hotkey='H';
    sprintf(menustr[16],"%-15s%s",menuldr[16],(bh.flag&BOARD_CLUB_READ)?"��":"��");
    /*д����Club*/
    sel[17].hotkey='I';
    sprintf(menustr[17],"%-15s%s",menuldr[17],(bh.flag&BOARD_CLUB_WRITE)?"��":"��");
    /*����Club*/
    sel[18].hotkey='J';
    sprintf(menustr[18],"%-15s%s",menuldr[18],
        (bh.flag&(BOARD_CLUB_READ|BOARD_CLUB_WRITE))?((bh.flag&BOARD_CLUB_HIDE)?"��":"��"):"��Чѡ��");
    /*������λ��*/
    sel[19].hotkey='K';
    annstat=check_ann(&bh);
    section=(annstat&0x020000)?-1:(annstat&0xFFFF);
    sprintf(menustr[19],"%-15s%s <%s>",menuldr[19],
        !(annstat&~0xFFFF)?"��Ч":(annstat&0x040000?"��Ч":"�쳣"),bh.ann_path);
    if(strlen(menustr[19])>76)
        sprintf(&menustr[19][72],"...>");
    /*Ȩ������*/
    sel[20].hotkey='L';
    sprintf(menustr[20],"%-15s%s <%s>",menuldr[20],
        (bh.level&~PERM_POSTMASK)?((bh.level&PERM_POSTMASK)?"��������":"��ȡ����"):"������",
        gen_permstr(bh.level,buf));
    /*�������*/
    sel[21].hotkey='M';
    sprintf(menustr[21],"%-15s%s <%d>",menuldr[21],
#ifdef HAVE_CUSTOM_USER_TITLE
        bh.title_level?get_user_title(bh.title_level):"������",
#else
        "��Чѡ��",
#endif
        bh.title_level);
    /*�˳�*/
    sel[22].hotkey='Q';
    sprintf(menustr[22],"%-15s%s",menuldr[22],change?"\033[1;31m���޸�\033[m":"δ�޸�");
    sel[23].x=-1;sel[23].y=-1;sel[23].type=0;sel[23].hotkey=-1;sel[23].data=NULL;
    /*����*/
    memcpy(orig,menustr,23*256);
    currpos=23;
    /*�޸İ�������*/
    while(loop){
        move(1,0);clrtobot();
        /*����select�ṹ*/
        arg.items=sel;
        arg.flag=SIF_SINGLE;
        bzero(&conf,sizeof(struct _select_def));
        conf.item_count=23;
        conf.item_per_page=23;
        conf.flag=LF_LOOP;
        conf.prompt="��";
        conf.item_pos=pts;
        conf.arg=&arg;
        conf.title_pos.x=-1;
        conf.title_pos.y=-1;
        /*��ǰλ��*/
        conf.pos=currpos;
        conf.on_select=editbrd_on_select;
        conf.show_data=editbrd_show;
        conf.key_command=editbrd_key;
        /*ѡ��*/
        sprintf(menustr[22],"%-15s%s",menuldr[22],change?"\033[1;31m���޸�\033[m":"δ�޸�");
        ret=list_select_loop(&conf);
        currpos=conf.pos;
        /*����SHOW_QUITʱ*/
        if(ret==SHOW_QUIT){
            /*ȡ�������޸�*/
            if(lastkey==KEY_CANCEL&&((change&(1<<(currpos-1)))||currpos==23)){
                switch(currpos-1){
                    /*���������ƻ򾫻���λ��*/
                    case 0:
                    case 19:
                        sprintf(newbh.filename,"%s",bh.filename);
                        sprintf(newbh.ann_path,"%s",bh.ann_path);
                        sprintf(menustr[0],"%s",orig[0]);
                        sprintf(menustr[19],"%s",orig[19]);
                        section=(annstat&0x020000)?-1:(annstat&0xFFFF);
                        change&=~(1<<0);
                        change&=~(1<<19);
                        break;
                    /*����������*/
                    case 1:
                        sprintf(newbh.BM,"%s",bh.BM);
                        sprintf(menustr[1],"%s",orig[1]);
                        change&=~(1<<1);
                        break;
                    /*������˵��*/
                    case 2:
                        sprintf(&newbh.title[13],"%s",&bh.title[13]);
                        sprintf(menustr[2],"%s",orig[2]);
                        change&=~(1<<2);
                        break;
                    /*����������*/
                    case 3:
                        newbh.title[0]=bh.title[0];
                        sprintf(menustr[3],"%s",orig[3]);
                        change&=~(1<<3);
                        break;
                    /*����������*/
                    case 4:
                        memcpy(&newbh.title[1],&bh.title[1],6);
                        sprintf(menustr[4],"%s",orig[4]);
                        change&=~(1<<4);
                        break;
                    /*ת�ű�ǩ*/
                    case 5:
                        memcpy(&newbh.title[7],&bh.title[7],6);
                        sprintf(menustr[5],"%s",orig[5]);
                        change&=~(1<<5);
                        break;
                    /*����������*/
                    case 6:
                        sprintf(newbh.des,"%s",bh.des);
                        sprintf(menustr[6],"%s",orig[6]);
                        change&=~(1<<6);
                        break;
                    /*����Ŀ¼*/
                    case 11:
                        newbh.group=bh.group;
                        sprintf(menustr[11],"%s",orig[11]);
                        change&=~(1<<11);
                        break;
                    /*Ȩ������*/
                    case 20:
                        newbh.level=bh.level;
                        sprintf(menustr[20],"%s",orig[20]);
                        change&=~(1<<20);
                        break;
                    /*�������*/
                    case 21:
#ifdef HAVE_CUSTOM_USER_TITLE
                        newbh.title_level=bh.title_level;
                        sprintf(menustr[21],"%s",orig[21]);
                        change&=~(1<<21);
#endif
                        break;
                    /*ȫ������*/
                    case 22:
                        memcpy(&newbh,&bh,sizeof(struct boardheader));
                        memcpy(menustr,orig,23*256);
                        section=(annstat&0x020000)?-1:(annstat&0xFFFF);
                        change=0;
                        break;
                    default:
                        break;
                }
            }
            /*�����޸��˳�*/
            if(lastkey==KEY_ESC){
                if(change){
                    move(20,0);clrtoeol();
                    getdata(20,2,"\033[1;31m�����޸��˳�? [N]: \033[m",buf,2,DOECHO,NULL,true);
                    if(buf[0]!='y'&&buf[0]!='Y')
                        continue;
                }
                return -1;
            }
            continue;
        }
        /*����SHOW_SELECTʱ*/
        switch(currpos-1){
            /*����������*/
            case 0:
                move(2,0);clrtoeol();getdata(2,2,"����������������: ",buf,BOARDNAMELEN,DOECHO,NULL,true);
                /*ȡ���޸�*/
                if(!*buf||!strcmp(buf,newbh.filename))
                    break;
                /*Ŀ���������Ѿ�����*/
                if(strcasecmp(buf,bh.filename)&&getboardnum(buf,NULL)>0){
                    move(2,0);clrtoeol();getdata(2,2,"\033[1;31m����: ���������Ѿ�����!\033[m",buf,1,NOECHO,NULL,true);
                    break;
                }
                /*�������������ƺ��зǷ��ַ�*/
                if(strchr(buf,'/')||strchr(buf,' ')){
                    move(2,0);clrtoeol();getdata(2,2,"\033[1;31m����: �����������к��зǷ��ַ�!\033[m",buf,1,NOECHO,NULL,true);
                    break;
                }
                sprintf(newbh.filename,"%s",buf);
                /*����޸�״̬*/
                if(strcmp(bh.filename,newbh.filename)){
                    sprintf(menustr[0],"%-15s\033[1;32m%s\033[m",menuldr[0],newbh.filename);
                    change|=(1<<0);
                }
                else{
                    sprintf(menustr[0],"%s",orig[0]);
                    change&=~(1<<0);
                }
                /*�޸ľ�����λ��*/
                if((annstat&0x020000)&&!strcmp(bh.ann_path,newbh.ann_path))
                    section=select_group(section);
                sprintf(newbh.ann_path,"%s/%s",groups[section],newbh.filename);
                newbh.ann_path[127]='\0';
                /*����޸�״̬*/
                if(strcmp(bh.ann_path,newbh.ann_path)){
                    sprintf(menustr[19],"%-15s\033[1;32m%s\033[m <%s>",menuldr[19],
                        (annstat&0x020000)?"����":"����",newbh.ann_path);
                    if(strlen(menustr[19])>86)
                        sprintf(&menustr[19][82],"...>");
                    change|=(1<<19);
                }
                else{
                    sprintf(menustr[19],"%s",orig[19]);
                    change&=~(1<<19);
                }
                break;
            /*����������*/
            case 1:
                move(3,0);clrtoeol();getdata(3,2,"���������Ա�б�: ",buf,BM_LEN,DOECHO,NULL,true);
                /*ȡ���޸�*/
                if(!*buf)
                    break;
                if(*buf==' ')
                    newbh.BM[0]='\0';
                else
                    sprintf(newbh.BM,"%s",buf);
                /*����޸�״̬*/
                if(strcmp(bh.BM,newbh.BM)){
                    sprintf(menustr[1],"%-15s\033[1;32m%s\033[m",menuldr[1],newbh.BM);
                    change|=(1<<1);
                }
                else{
                    sprintf(menustr[1],"%s",orig[1]);
                    change&=~(1<<1);
                }
                break;
            /*������˵��*/
            case 2:
                move(4,0);clrtoeol();getdata(4,2,"������������˵��: ",buf,48,DOECHO,NULL,true);
                /*ȡ���޸�*/
                if(!*buf)
                    break;
                if(*buf==' ')
                    newbh.title[13]='\0';
                else
                    sprintf(&newbh.title[13],"%s",buf);
                /*����޸�״̬*/
                if(strcmp(&bh.title[13],&newbh.title[13])){
                    sprintf(menustr[2],"%-15s\033[1;32m%s\033[m",menuldr[2],&newbh.title[13]);
                    change|=(1<<2);
                }
                else{
                    sprintf(menustr[2],"%s",orig[2]);
                    change&=~(1<<2);
                }
                break;
            /*����������*/
            case 3:
                move(5,0);clrtoeol();getdata(5,2,"����������������(1�ַ�����): ",buf,2,DOECHO,NULL,true);
                /*ȡ���޸�*/
                if(!*buf)
                    break;
                newbh.title[0]=buf[0];
                /*����޸�״̬*/
                if(bh.title[0]!=newbh.title[0]){
                    sprintf(menustr[3],"%-15s\033[1;32m<%c>\033[m",menuldr[3],newbh.title[0]);
                    change|=(1<<3);
                }
                else{
                    sprintf(menustr[3],"%s",orig[3]);
                    change&=~(1<<3);
                }
                break;
            /*����������*/
            case 4:
                move(6,0);clrtoeol();getdata(6,2,"����������������(4�ַ�����): ",buf,5,DOECHO,NULL,true);
                /*ȡ���޸�*/
                if(!*buf)
                    break;
                /*���Ȳ���ʱ����*/
                if((i=strlen(buf))<4)
                    while(i!=4)
                        buf[i++]=' ';
                newbh.title[1]='[';newbh.title[6]=']';memcpy(&newbh.title[2],buf,4);
                /*����޸�״̬*/
                if(strncmp(&bh.title[1],&newbh.title[1],6)){
                    sprintf(menustr[4],"%-15s\033[1;32m<%-6.6s>\033[m",menuldr[4],&newbh.title[1]);
                    change|=(1<<4);
                }
                else{
                    sprintf(menustr[4],"%s",orig[4]);
                    change&=~(1<<4);
                }
                break;
            /*ת�ű�ǩ*/
            case 5:
                move(7,0);clrtoeol();getdata(7,2,"������ת�ű�ǩ(6�ַ�����;<#1>˫��ת��,<#2>����ת��): ",buf,7,DOECHO,NULL,true);
                /*ȡ���޸�*/
                if(!*buf)
                    break;
                /*Ԥ����ת�ű��*/
                if(buf[0]=='#'){
                    switch(buf[1]){
                        /*˫��ת�ű��*/
                        case '1':
                            sprintf(buf," ��   ");
                            break;
                        /*����ת�ű��*/
                        case '2':
                            sprintf(buf," ��   ");
                            break;
                        /*��ת�ű��*/
                        default:
                            sprintf(buf,"      ");
                            break;
                    }
                }
                /*���Ȳ���ʱ����*/
                if((i=strlen(buf))<6)
                    while(i!=6)
                        buf[i++]=' ';
                memcpy(&newbh.title[7],buf,6);
                /*����޸�״̬*/
                if(strncmp(&bh.title[7],&newbh.title[7],6)){
                    sprintf(menustr[5],"%-15s\033[1;32m<%-6.6s>\033[m",menuldr[5],&newbh.title[7]);
                    change|=(1<<5);
                }
                else{
                    sprintf(menustr[5],"%s",orig[5]);
                    change&=~(1<<5);
                }
                break;
            /*����������*/
            case 6:
                move(1,0);clrtobot();sprintf(buf,"%s",newbh.des);
                /*��������*/
                multi_getdata(8,0,72,"����������������: \n",buf,195,8,false,0);
                /*ȡ���޸�*/
                if(!*buf)
                    break;
                if(*buf==' ')
                    buf[0]=0;
                else
                    for(ptr=&buf[0];*ptr;ptr++)
                        if(*ptr==10)
                            *ptr=32;
                sprintf(newbh.des,"%s",buf);
                /*����޸�״̬*/
                if(strcmp(bh.des,newbh.des)){
                    sprintf(menustr[6],"%-15s\033[1;32m%s\033[m",menuldr[6],newbh.des[0]?newbh.des:"<��>");
                    if(strlen(menustr[6])>86)
                        sprintf(&menustr[6][80],"...\033[m");
                    change|=(1<<6);
                }
                else{
                    sprintf(menustr[6],"%s",orig[6]);
                    change&=~(1<<6);
                }
                break;
            /*����������*/
            case 7:
                newbh.flag^=BOARD_ANNONY;
                /*����޸�״̬*/
                if((bh.flag&BOARD_ANNONY)^(newbh.flag&BOARD_ANNONY)){
                    sprintf(menustr[7],"%-15s\033[1;32m%s\033[m",menuldr[7],(newbh.flag&BOARD_ANNONY)?"��":"��");
                    change|=(1<<7);
                }
                else{
                    sprintf(menustr[7],"%s",orig[7]);
                    change&=~(1<<7);
                }
                break;
            /*ͳ��������*/
            case 8:
                newbh.flag^=BOARD_JUNK;
                /*����޸�״̬*/
                if((bh.flag&BOARD_JUNK)^(newbh.flag&BOARD_JUNK)){
                    sprintf(menustr[8],"%-15s\033[1;32m%s\033[m",menuldr[8],(newbh.flag&BOARD_JUNK)?"��":"��");
                    change|=(1<<8);
                }
                else{
                    sprintf(menustr[8],"%s",orig[8]);
                    change&=~(1<<8);
                }
                break;
            /*ͳ��ʮ��*/
            case 9:
                newbh.flag^=BOARD_POSTSTAT;
                /*����޸�״̬*/
                if((bh.flag&BOARD_POSTSTAT)^(newbh.flag&BOARD_POSTSTAT)){
                    sprintf(menustr[9],"%-15s\033[1;32m%s\033[m",menuldr[9],(newbh.flag&BOARD_POSTSTAT)?"��":"��");
                    change|=(1<<9);
                }
                else{
                    sprintf(menustr[9],"%s",orig[9]);
                    change&=~(1<<9);
                }
                break;
            /*Ŀ¼������*/
            case 10:
                newbh.flag^=BOARD_GROUP;
                /*����޸�״̬*/
                if((bh.flag&BOARD_GROUP)^(newbh.flag&BOARD_GROUP)){
                    sprintf(menustr[10],"%-15s\033[1;32m%s\033[m",menuldr[10],(newbh.flag&BOARD_GROUP)?"��":"��");
                    change|=(1<<10);
                }
                else{
                    sprintf(menustr[10],"%s",orig[10]);
                    change&=~(1<<10);
                }
                break;
            /*����Ŀ¼*/
            case 11:
                move(13,0);clrtoeol();getdata(13,2,"����������Ŀ¼: ",buf,BOARDNAMELEN,DOECHO,NULL,true);
                /*ȡ���޸�*/
                if(!*buf)
                    break;
                if(*buf==' ')
                    newbh.group=0;
                else{
                    i=getbnum(buf);
                    if(!i){
                        move(13,0);clrtoeol();getdata(13,2,"\033[1;31m����: �����������������!\033[m",buf,1,NOECHO,NULL,true);
                        break;
                    }
                    else if(!(getboard(i)->flag&BOARD_GROUP)){
                        move(13,0);clrtoeol();getdata(13,2,"\033[1;31m����: ���������������Ŀ¼!\033[m",buf,1,NOECHO,NULL,true);
                        break;
                    }
                    else
                        newbh.group=i;
                }
                /*����޸�״̬*/
                if(bh.group!=newbh.group){
                    sprintf(menustr[11],"%-15s\033[1;32m%s\033[m",menuldr[11],
                        newbh.group?(!(bhptr=getboard(newbh.group))?"�쳣":bhptr->filename):"��");
                    change|=(1<<11);
                }
                else{
                    sprintf(menustr[11],"%s",orig[11]);
                    change&=~(1<<11);
                }
                break;
            /*����ת��*/
            case 12:
                newbh.flag^=BOARD_OUTFLAG;
                /*����޸�״̬*/
                if((bh.flag&BOARD_OUTFLAG)^(newbh.flag&BOARD_OUTFLAG)){
                    sprintf(menustr[12],"%-15s\033[1;32m%s\033[m",menuldr[12],(newbh.flag&BOARD_OUTFLAG)?"��":"��");
                    change|=(1<<12);
                }
                else{
                    sprintf(menustr[12],"%s",orig[12]);
                    change&=~(1<<12);
                }
                break;
            /*�ϴ�����*/
            case 13:
                newbh.flag^=BOARD_ATTACH;
                /*����޸�״̬*/
                if((bh.flag&BOARD_ATTACH)^(newbh.flag&BOARD_ATTACH)){
                    sprintf(menustr[13],"%-15s\033[1;32m%s\033[m",menuldr[13],(newbh.flag&BOARD_ATTACH)?"��":"��");
                    change|=(1<<13);
                }
                else{
                    sprintf(menustr[13],"%s",orig[13]);
                    change&=~(1<<13);
                }
                break;
            /*E-mail����*/
            case 14:
                newbh.flag^=BOARD_EMAILPOST;
                /*����޸�״̬*/
                if((bh.flag&BOARD_EMAILPOST)^(newbh.flag&BOARD_EMAILPOST)){
                    sprintf(menustr[14],"%-15s\033[1;32m%s\033[m",menuldr[14],(newbh.flag&BOARD_EMAILPOST)?"��":"��");
                    change|=(1<<14);
                }
                else{
                    sprintf(menustr[14],"%s",orig[14]);
                    change&=~(1<<14);
                }
                break;
            /*���ɻظ�*/
            case 15:
                newbh.flag^=BOARD_NOREPLY;
                /*����޸�״̬*/
                if((bh.flag&BOARD_NOREPLY)^(newbh.flag&BOARD_NOREPLY)){
                    sprintf(menustr[15],"%-15s\033[1;32m%s\033[m",menuldr[15],(newbh.flag&BOARD_NOREPLY)?"��":"��");
                    change|=(1<<15);
                }
                else{
                    sprintf(menustr[15],"%s",orig[15]);
                    change&=~(1<<15);
                }
                break;
            /*������Club*/
            case 16:
                newbh.flag^=BOARD_CLUB_READ;
                /*����޸�״̬*/
                if((bh.flag&BOARD_CLUB_READ)^(newbh.flag&BOARD_CLUB_READ)){
                    sprintf(menustr[16],"%-15s\033[1;32m%s\033[m",menuldr[16],(newbh.flag&BOARD_CLUB_READ)?"��":"��");
                    change|=(1<<16);
                }
                else{
                    sprintf(menustr[16],"%s",orig[16]);
                    change&=~(1<<16);
                }
                /*�Ǿ��ֲ�ʱȡ�����ؾ��ֲ���ǩ*/
                if(!(newbh.flag&(BOARD_CLUB_READ|BOARD_CLUB_WRITE)))
                    newbh.flag&=~BOARD_CLUB_HIDE;
                /*����޸�״̬*/
                if((bh.flag&BOARD_CLUB_HIDE)^(newbh.flag&BOARD_CLUB_HIDE)){
                    sprintf(menustr[18],"%-15s\033[1;32m%s\033[m",menuldr[18],
                        (newbh.flag&(BOARD_CLUB_READ|BOARD_CLUB_WRITE))?((newbh.flag&BOARD_CLUB_HIDE)?"��":"��"):"��Чѡ��");
                    change|=(1<<18);
                }
                else{
                    sprintf(menustr[18],"%-15s%s",menuldr[18],
                        (newbh.flag&(BOARD_CLUB_READ|BOARD_CLUB_WRITE))?((newbh.flag&BOARD_CLUB_HIDE)?"��":"��"):"��Чѡ��");
                    change&=~(1<<18);
                }
                break;
            /*д����Club*/
            case 17:
                newbh.flag^=BOARD_CLUB_WRITE;
                /*����޸�״̬*/
                if((bh.flag&BOARD_CLUB_WRITE)^(newbh.flag&BOARD_CLUB_WRITE)){
                    sprintf(menustr[17],"%-15s\033[1;32m%s\033[m",menuldr[17],(newbh.flag&BOARD_CLUB_WRITE)?"��":"��");
                    change|=(1<<17);
                }
                else{
                    sprintf(menustr[17],"%s",orig[17]);
                    change&=~(1<<17);
                }
                /*�Ǿ��ֲ�ʱȡ�����ؾ��ֲ���ǩ*/
                if(!(newbh.flag&(BOARD_CLUB_READ|BOARD_CLUB_WRITE)))
                    newbh.flag&=~BOARD_CLUB_HIDE;
                /*����޸�״̬*/
                if((bh.flag&BOARD_CLUB_HIDE)^(newbh.flag&BOARD_CLUB_HIDE)){
                    sprintf(menustr[18],"%-15s\033[1;32m%s\033[m",menuldr[18],
                        (newbh.flag&(BOARD_CLUB_READ|BOARD_CLUB_WRITE))?((newbh.flag&BOARD_CLUB_HIDE)?"��":"��"):"��Чѡ��");
                    change|=(1<<18);
                }
                else{
                    sprintf(menustr[18],"%-15s%s",menuldr[18],
                        (newbh.flag&(BOARD_CLUB_READ|BOARD_CLUB_WRITE))?((newbh.flag&BOARD_CLUB_HIDE)?"��":"��"):"��Чѡ��");
                    change&=~(1<<18);
                }
                break;
            /*����Club*/
            case 18:
                /*�Ǿ��ֲ�*/
                if(!(newbh.flag&(BOARD_CLUB_READ|BOARD_CLUB_WRITE)))
                    break;
                newbh.flag^=BOARD_CLUB_HIDE;
                /*����޸�״̬*/
                if((bh.flag&BOARD_CLUB_HIDE)^(newbh.flag&BOARD_CLUB_HIDE)){
                    sprintf(menustr[18],"%-15s\033[1;32m%s\033[m",menuldr[18],(newbh.flag&BOARD_CLUB_HIDE)?"��":"��");
                    change|=(1<<18);
                }
                else{
                    sprintf(menustr[18],"%-15s%s",menuldr[18],
                        (newbh.flag&(BOARD_CLUB_READ|BOARD_CLUB_WRITE))?((newbh.flag&BOARD_CLUB_HIDE)?"��":"��"):"��Чѡ��");
                    change&=~(1<<18);
                }
                break;
            /*������λ��*/
            case 19:
                section=select_group(section);
                sprintf(newbh.ann_path,"%s/%s",groups[section],newbh.filename);
                newbh.ann_path[127]='\0';
                /*����޸�״̬*/
                if(strcmp(bh.ann_path,newbh.ann_path)){
                    sprintf(menustr[19],"%-15s\033[1;32m%s\033[m <%s>",menuldr[19],
                        (annstat&0x020000)?"����":"����",newbh.ann_path);
                    if(strlen(menustr[19])>86)
                        sprintf(&menustr[19][82],"...>");
                    change|=(1<<19);
                }
                else{
                    sprintf(menustr[19],"%s",orig[19]);
                    change&=~(1<<19);
                }
                break;
            /*Ȩ������*/
            case 20:
                move(16,0);clrtoeol();getdata(16,2,"�趨{��ȡ(R)|����(P)}Ȩ�����ƻ�����趨(C): ",buf,2,DOECHO,NULL,true);
                i=0;
                switch(buf[0]){
                    case 'r':
                    case 'R':
                        newbh.level&=~PERM_POSTMASK;
                        break;
                    case 'p':
                    case 'P':
                        newbh.level|=PERM_POSTMASK;
                        break;
                    case 0:
                        break;
                    default:
                        i=1;
                        break;
                }
                /*ȡ���޸�*/
                if(i)
                    break;
                move(1,0);clrtobot();
                move(2,0);prints("�趨%sȨ��",newbh.level&PERM_POSTMASK?"����":"��ȡ");
                newbh.level=setperms(newbh.level,0,"Ȩ��",NUMPERMS,showperminfo,NULL);
                /*����޸�״̬*/
                if(bh.level!=newbh.level){
                    sprintf(menustr[20],"%-15s\033[1;32m%s\033[m <%s>",menuldr[20],
                        (newbh.level&~PERM_POSTMASK)?((newbh.level&PERM_POSTMASK)?"��������":"��ȡ����"):"������",
                        gen_permstr(newbh.level,buf));
                    change|=(1<<20);
                }
                else{
                    sprintf(menustr[20],"%s",orig[20]);
                    change&=~(1<<20);
                }
                break;
            /*�������*/
            case 21:
#ifdef HAVE_CUSTOM_USER_TITLE
                move(17,0);clrtoeol();getdata(17,2,"�趨�������{(���)|(#ְ��)}: ",buf,USER_TITLE_LEN+2,DOECHO,NULL,true);
                /*ȡ���޸�*/
                if(!*buf)
                    break;
                if(buf[0]!='#'){
                    i=atoi(buf);
                    sprintf(&buf[128],"%d",i);
                    if(i>255||strcmp(buf,&buf[128])){
                        move(17,0);clrtoeol();getdata(17,2,"\033[1;31m����: �������Խ���Ƿ�!\033[m",buf,1,NOECHO,NULL,true);
                        break;
                    }
                    if(i&&!*get_user_title(i)){
                        move(17,0);clrtoeol();
                        getdata(17,2,"\033[1;33m��ʾ: Ŀǰ�����������Ӧ���û���ݲ�����,{ȷ��(Y)|ȡ��(N)}? [N]: \033[m",
                            buf,2,DOECHO,NULL,true);
                        if(buf[0]!='y'&&buf[0]!='Y')
                            break;
                    }
                }
                else{
                    if(!buf[1])
                        i=0;
                    else{
                        for(i=0;i<255;i++)
                            if(!strcmp(get_user_title(i+1),&buf[1]))
                                break;
                        if(i==255){
                            move(17,0);clrtoeol();getdata(17,2,"\033[1;31m����: Ŀǰ��δ���ƴ��û����!\033[m",
                                buf,1,NOECHO,NULL,true);
                            break;
                        }
                        i++;
                    }
                }
                newbh.title_level=i;
                /*����޸�״̬*/
                if(bh.title_level!=newbh.title_level){
                    sprintf(menustr[21],"%-15s\033[1;32m%s\033[m <%d>",menuldr[21],
                        newbh.title_level?get_user_title(newbh.title_level):"������",newbh.title_level);
                    change|=(1<<21);
                }
                else{
                    sprintf(menustr[21],"%s",orig[21]);
                    change&=~(1<<21);
                }
#endif
                break;
            /*�˳�*/
            case 22:
                if(change){
                    /*��ͻ��⼰ȷ��*/
                    if(change&(1<<0)){
                        sprintf(src,"boards/%s",bh.filename);
                        sprintf(dst,"boards/%s",newbh.filename);
                        if(!dashd(src)){
                            move(20,0);clrtoeol();
                            getdata(20,2,"\033[1;36mȷ��: Դ������Ŀ¼������,�Ƿ񴴽�? [Y]: \033[m",buf,2,DOECHO,NULL,true);
                            if(buf[0]=='n'||buf[0]=='N')
                                break;
                        }
                        if(dashd(dst)){
                            move(20,0);clrtoeol();
                            getdata(20,2,"\033[1;36mȷ��: Ŀ��������Ŀ¼�Ѵ���,�Ƿ񸲸�? [Y]: \033[m",buf,2,DOECHO,NULL,true);
                            if(buf[0]=='n'||buf[0]=='N')
                                break;
                        }
                    }
                    if(change&(1<<19)){
                        sprintf(dst,"0Announce/groups/%s",newbh.ann_path);
                        if(dashd(dst)){
                            move(20,0);clrtoeol();
                            getdata(20,2,"\033[1;36mȷ��: Ŀ�ľ�����Ŀ¼�Ѵ���,�Ƿ񸲸�? [Y]: \033[m",buf,2,DOECHO,NULL,true);
                            if(buf[0]=='n'||buf[0]=='N')
                                break;
                        }
                    }
                    move(20,0);clrtoeol();
                    getdata(20,2,"\033[1;31mȷ���޸�����������? [N]: \033[m",buf,2,DOECHO,NULL,true);
                    if(buf[0]!='y'&&buf[0]!='Y')
                        break;
                    loop=0;
                }
                else{
                    clear();
                    return -1;
                }
                break;
            default:
                break;
        }
    }
    /*ִ���޸Ĳ���*/
    error=0;
    if(change&(1<<0)){
        sprintf(src,"boards/%s",bh.filename);
        sprintf(dst,"boards/%s",newbh.filename);
        if(dashd(dst))
            error|=my_f_rm(dst);
        if(dashd(src))
            error|=rename(src,dst);
        else{
            error|=mkdir(dst,0755);
            build_board_structure(newbh.filename);
        }
        sprintf(src,"vote/%s",bh.filename);
        sprintf(dst,"vote/%s",newbh.filename);
        if(dashd(dst))
            my_f_rm(dst);
        if(dashd(src))
            rename(src,dst);
    }
    error|=edit_group(&bh,&newbh);
    set_board(pos,&newbh,&bh);
    /*���ɰ�ȫ��˺���־*/
    sprintf(buf,"�޸�������: <%4.4d,%#6.6x> %s%c-> %s",pos,change,bh.filename,change&(1<<0)?32:0,newbh.filename);
    securityreport(buf,NULL,NULL);
    bbslog("user","%s",buf);
    move(20,0);clrtoeol();
    move(20,2);prints(error?"\033[1;33m�������,�븴��ȷ�ϲ������!\033m":"\033[1;32m�����ɹ�!\033[m");
    EDITBRD_WAIT;clear();
    return 0;
}
/*END - etnlegend,2005.07.01,�޸�����������*/


int searchtrace()
{
    int id;
    char tmp_command[80];
    char *tmp_id;
    char buf[8192];
    struct userec *lookupuser;
	char buffile[256];

    if (check_systempasswd() == false)
        return -1;
    modify_user_mode(ADMIN);
    clear();
    stand_title("��ѯʹ���߷��ļ�¼");
    move(1, 0);
    usercomplete("������ʹ�����ʺ�:", genbuf);
    if (genbuf[0] == '\0') {
        clear();
        return -1;
    }

    if (!(id = getuser(genbuf, &lookupuser))) {
        move(3, 0);
        prints("����ȷ��ʹ���ߴ���\n");
        clrtoeol();
        pressreturn();
        clear();
        return -1;
    }
    tmp_id = lookupuser->userid;

    sprintf(buffile, "tmp/searchresult.%d", getpid());
#ifdef NEWPOSTLOG
{
	FILE *fp;
	MYSQL s;
	MYSQL_RES *res;
	MYSQL_ROW row;
	char sqlbuf[256];

	if((fp=fopen(buffile,"w"))==NULL){
        move(3, 0);
        prints("�޷�����ʱ�ļ�\n");
        clrtoeol();
        pressreturn();
        clear();
		return -1;
	}

	mysql_init (&s);

	if (! my_connect_mysql(&s) ){
        move(3, 0);
        prints("%s\n",mysql_error(&s));
        clrtoeol();
        pressreturn();
        clear();
		fclose(fp);
		return -1;
	}

	sprintf(sqlbuf,"SELECT * FROM postlog WHERE userid='%s' ORDER BY time;",lookupuser->userid);

	if( mysql_real_query( &s, sqlbuf, strlen(sqlbuf) )){
        move(3, 0);
        prints("%s\n",mysql_error(&s));
        clrtoeol();
        pressreturn();
        clear();
		mysql_close(&s);
		fclose(fp);
		return -1;
	}

	fprintf(fp,"%s  ����ķ��ļ�¼\n",lookupuser->userid);

	res = mysql_store_result(&s);
	while(1){
		row = mysql_fetch_row(res);
		if(row==NULL)
			break;

		fprintf(fp,"%s: %-20s %s\n", row[4], row[2], row[3]);
	}
	mysql_free_result(res);

	mysql_close(&s);

	fclose(fp);

}
#else
    sprintf(tmp_command, "grep -a -w %s user.log | grep posted > %s", tmp_id, buffile);
    system(tmp_command);
#endif
    sprintf(tmp_command, "%s �ķ��Ĳ�ѯ���", tmp_id);
    mail_file(getCurrentUser()->userid, buffile, getCurrentUser()->userid, tmp_command, BBSPOST_MOVE, NULL);

    sprintf(buf, "��ѯ�û� %s �ķ������", tmp_id);
    securityreport(buf, lookupuser, NULL);      /*д��syssecurity��, stephen 2000.12.21 */
    sprintf(buf, "Search the posts by %s in the trace", tmp_id);
    bbslog("user", "%s", buf);  /*д��trace, stephen 2000.12.21 */

    move(3, 0);
    prints("��ѯ����Ѿ��ĵ��������䣡 \n");
    pressreturn();
    clear();
    return 0;
}                               /* stephen 2000.12.15 let sysop search in trace */


/*
char curruser[IDLEN + 2];
extern int delmsgs[];
extern int delcnt;

void domailclean(struct fileheader *fhdrp, char *arg)
{
    static int newcnt, savecnt, deleted, idc;
    char buf[STRLEN];

    if (fhdrp == NULL) {
        bbslog("clean", "new = %d, saved = %d, deleted = %d", newcnt, savecnt, deleted);
        newcnt = savecnt = deleted = idc = 0;
        if (delcnt) {
            setmailfile(buf, curruser, DOT_DIR);
            while (delcnt--)
                delete_record(buf, sizeof(struct fileheader), delmsgs[delcnt], NULL, NULL);
        }
        delcnt = 0;
        return;
    }
    idc++;
    if (!(fhdrp->accessed[0] & FILE_READ))
        newcnt++;
    else if (fhdrp->accessed[0] & FILE_MARKED)
        savecnt++;
    else {
        deleted++;
        setmailfile(buf, curruser, fhdrp->filename);
        unlink(buf);
        delmsgs[delcnt++] = idc;
    }
}

int cleanmail(struct userec *urec, char *arg)
{
    struct stat statb;

    if (urec->userid[0] == '\0' || !strcmp(urec->userid, "new"))
        return 0;
    setmailfile(genbuf, urec->userid, DOT_DIR);
    if (stat(genbuf, &statb) == -1) {
        bbslog("clean", "%s no mail", urec->userid);
    } else {
        if (statb.st_size == 0) {
            bbslog("clean", "%s no mail", urec->userid);
        } else {
            strcpy(curruser, urec->userid);
            delcnt = 0;
            apply_record(genbuf, (RECORD_FUNC_ARG) domailclean, sizeof(struct fileheader), 0, 1);
            domailclean(NULL, 0);
        }
    }
    return 0;
}

int m_mclean()
{
    char ans[5];

    modify_user_mode(ADMIN);
    if (!check_systempasswd()) {
        return -1;
    }
    clear();
    stand_title("���˽���ż�");
    move(1, 0);
    prints("��������Ѷ���δ mark ���ż�\n");
    getdata(2, 0, "ȷ���� (Y/N)? [N]: ", ans, 3, DOECHO, NULL, true);
    if (ans[0] != 'Y' && ans[0] != 'y') {
        clear();
        return 0;
    }
    {
        char secu[STRLEN];

        sprintf(secu, "�������ʹ�����Ѷ��ż���");
        securityreport(secu, NULL, NULL);
    }

    move(3, 0);
    prints("�����ĵȺ�.\n");
    refresh();
    apply_users(cleanmail, 0);
    move(4, 0);
    prints("������! ��鿴��־�ļ�.\n");
    bbslog("user","%s","Mail Clean");
    pressreturn();
    clear();
    return 0;
}
*/

void trace_state(flag, name, size)
int flag, size;
char *name;
{
    char buf[STRLEN];

    if (flag != -1) {
        sprintf(buf, "ON (size = %d)", size);
    } else {
        strcpy(buf, "OFF");
    }
    prints("%s��¼ %s\n", name, buf);
}

int touchfile(filename)
char *filename;
{
    int fd;

    if ((fd = open(filename, O_RDWR | O_CREAT, 0600)) > 0) {
        close(fd);
    }
    return fd;
}

int m_trace()
{
    struct stat ostatb, cstatb;
    int otflag, ctflag, done = 0;
    char ans[3];
    char *msg;

    modify_user_mode(ADMIN);
    if (!check_systempasswd()) {
        return -1;
    }
    clear();
    stand_title("Set Trace Options");
    while (!done) {
        move(2, 0);
        otflag = stat("trace", &ostatb);
        ctflag = stat("trace.chatd", &cstatb);
        prints("Ŀǰ�趨:\n");
        trace_state(otflag, "һ��", ostatb.st_size);
        trace_state(ctflag, "����", cstatb.st_size);
        move(9, 0);
        prints("<1> �л�һ���¼\n");
        prints("<2> �л������¼\n");
        getdata(12, 0, "��ѡ�� (1/2/Exit) [E]: ", ans, 2, DOECHO, NULL, true);

        switch (ans[0]) {
        case '1':
            if (otflag) {
                touchfile("trace");
                msg = "һ���¼ ON";
            } else {
                f_mv("trace", "trace.old");
                msg = "һ���¼ OFF";
            }
            break;
        case '2':
            if (ctflag) {
                touchfile("trace.chatd");
                msg = "�����¼ ON";
            } else {
                f_mv("trace.chatd", "trace.chatd.old");
                msg = "�����¼ OFF";
            }
            break;
        default:
            msg = NULL;
            done = 1;
        }
        move(t_lines - 2, 0);
        if (msg) {
            prints("%s\n", msg);
            bbslog("user", "%s", msg);
        }
    }
    clear();
    return 0;
}

int valid_userid(ident)         /* check the user has registed, added by dong, 1999.4.18 */
char *ident;
{
    if (strchr(ident, '@') && valid_ident(ident))
        return 1;
    return 0;
}

int check_proxy_IP(ip, buf)
                                /*
                                 * added for rejection of register from proxy,
                                 * Bigman, 2001.11.9 
                                 */
 /*
  * ��bbsd_single�����local_check_ban_IP����һ�������Կ��ǹ��� 
  */
char *ip;
char *buf;
{                               /* Leeward 98.07.31
                                 * RETURN:
                                 * - 1: No any banned IP is defined now
                                 * 0: The checked IP is not banned
                                 * other value over 0: The checked IP is banned, the reason is put in buf
                                 */
    FILE *Ban = fopen("etc/proxyIP", "r");
    char IPBan[64];
    int IPX = -1;
    char *ptr;

    if (!Ban)
        return IPX;
    else
        IPX++;

    while (fgets(IPBan, 64, Ban)) {
        if ((ptr = strchr(IPBan, '\n')) != NULL)
            *ptr = 0;
        if ((ptr = strchr(IPBan, ' ')) != NULL) {
            *ptr++ = 0;
            strcpy(buf, ptr);
        }
        IPX = strlen(ip);
        if (!strncmp(ip, IPBan, IPX))
            break;
        IPX = 0;
    }

    fclose(Ban);
    return IPX;
}

int apply_reg(regfile, fname, pid, num)
/* added by Bigman, 2002.5.31 */
/* ����ָ������ע�ᵥ */
char *regfile, *fname;
long pid;
int num;
{
    FILE *in_fn, *out_fn, *tmp_fn;
    char fname1[STRLEN], fname2[STRLEN];
    int sum, fd;
    char *ptr;

    strcpy(fname1, "reg.ctrl");

    if ((in_fn = fopen(regfile, "r+")) == NULL) {
        move(2, 0);
        prints("ϵͳ����, �޷���ȡע�����ϵ�: %s\n", regfile);
        pressreturn();
        return -1;
    }

    fd = fileno(in_fn);
    flock(fd, LOCK_EX);

    if ((out_fn = fopen(fname, "w")) == NULL) {
        move(2, 0);
        flock(fd, LOCK_UN);
        fclose(in_fn);
        prints("ϵͳ����, �޷�д��ʱע�����ϵ�: %s\n", fname);
        pressreturn();
        return -1;
    }
    sum = 0;

    while (fgets(genbuf, STRLEN, in_fn) != NULL) {
        if ((ptr = (char *) strstr(genbuf, "----")) != NULL)
            sum++;

        fputs(genbuf, out_fn);

        if (sum >= num)
            break;
    }
    fclose(out_fn);

    if (sum >= num) {
        sum = 0;

		gettmpfilename( fname2, "reg" );
        //sprintf(fname2, "tmp/reg.%ld", pid);

        if ((tmp_fn = fopen(fname2, "w")) == NULL) {
            prints("���ܽ�����ʱ�ļ�:%s\n", fname2);
            flock(fd, LOCK_UN);
            fclose(in_fn);
            pressreturn();
            return -1;
        }

        while (fgets(genbuf, STRLEN, in_fn) != NULL) {
            if ((ptr = (char *) strstr(genbuf, "userid")) != NULL)
                sum++;
            fputs(genbuf, tmp_fn);

        }

        flock(fd, LOCK_UN);

        fclose(in_fn);
        fclose(tmp_fn);

        if (sum > 0) {
            f_rm(regfile);
            f_mv(fname2, regfile);
        } else
            f_rm(regfile);

        f_rm(fname2);

    }

    else
        f_rm(regfile);

    if ((out_fn = fopen(fname1, "a")) == NULL) {
        move(2, 0);
        prints("ϵͳ����, �޷�����ע������ļ�: %s\n", fname1);
        pressreturn();
        return -1;
    }

    fd = fileno(out_fn);

    flock(fd, LOCK_UN);
    fprintf(out_fn, "%ld\n", pid);
    flock(fd, LOCK_UN);
    fclose(out_fn);

    return (0);
}

int restore_reg(long pid)
/* added by Bigman, 2002.5.31 */
/* �ָ����ߵ�ע���ļ� */
{
    FILE *fn, *freg;
    char *regfile, buf[STRLEN];
    int fd1, fd2;

    regfile = "new_register";

    sprintf(buf, "register.%ld", pid);

    if ((fn = fopen(buf, "r")) != NULL) {
        fd1 = fileno(fn);
        flock(fd1, LOCK_EX);

        if ((freg = fopen(regfile, "a")) != NULL) {
            fd2 = fileno(freg);
            flock(fd2, LOCK_EX);
            while (fgets(genbuf, STRLEN, fn) != NULL)
                fputs(genbuf, freg);
            flock(fd2, LOCK_UN);
            fclose(freg);

        }
        flock(fd1, LOCK_UN);
        fclose(fn);

        f_rm(buf);
    }

    return (0);
}
int check_reg(mod)
int mod;

/* added by Bigman, 2002.5.31 */
/* mod=0 ���reg_control�ļ� */
/* mod=1 �����˳�ɾ�����ļ� */
{
    FILE *fn1, *fn2;
    char fname1[STRLEN];
    char fname2[STRLEN];
    long myid;
    int flag = 0, fd;

    strcpy(fname1, "reg.ctrl");

    if ((fn1 = fopen(fname1, "r")) != NULL) {

        fd = fileno(fn1);
        flock(fd, LOCK_EX);

		gettmpfilename( fname2, "reg.c");
        //sprintf(fname2, "tmp/reg.c%ld", getpid());

        if ((fn2 = fopen(fname2, "w")) == NULL) {
            prints("���ܽ�����ʱ�ļ�:%s\n", fname2);
            flock(fd, LOCK_UN);
            fclose(fn1);
            pressreturn();
            return -1;
        } else {
            while (fgets(genbuf, STRLEN, fn1) != NULL) {

                myid = atol(genbuf);

                if (mod == 0) {
/*                    	if (myid==getpid())
                    {
                	prints("��ֻ��һ�����̽��������ʺ�");
                	pressreturn();
                	return -1;
                    }
*/

                    if (kill(myid, 0) == -1) {  /*ע���м�����ˣ��ָ� */
                        flag = 1;
                        restore_reg(myid);
                    } else {
                        fprintf(fn2, "%ld\n", myid);
                    }
                } else {
                    flag = 1;
                    if (myid != getpid())
                        fprintf(fn2, "%ld\n", myid);


                }

            }
            fclose(fn2);
        }
        flock(fd, LOCK_UN);
        fclose(fn1);

        if (flag == 1) {
            f_rm(fname1);
            f_mv(fname2, fname1);
        }
        f_rm(fname2);

    }

    return (0);
}

static const char *field[] = { "usernum", "userid", "realname", "career",
    "addr", "phone", "birth", NULL
};
static const char *reason[] = {
    "��������ʵ����(�������ƴ��).", "������ѧУ��ϵ������λ.",
    "����д������סַ����.", "����������绰(���޿��ú�����Email��ַ����).",
    "��ȷʵ����ϸ����дע�������.", "����������д���뵥.",
    "������Ӵ���ע��", "ͬһ���û�ע���˹���ID",
    NULL
};

#ifdef AUTO_CHECK_REGISTER_FORM
#include "checkreg.c"
#endif
int scan_register_form(logfile, regfile)
char *logfile, *regfile;
{
    static const char *finfo[] = { "�ʺ�λ��", "�������", "��ʵ����", "����λ",
        "Ŀǰסַ", "����绰", "��    ��", NULL
    };
    struct userec uinfo;
    FILE *fn, *fout, *freg;
    char fdata[8][STRLEN];
    char fname[STRLEN], buf[STRLEN], buff;
    char sender[IDLEN + 2];
    int  useproxy;

    /*
     * ^^^^^ Added by Marco 
     */
    char ans[5], *ptr, *uid;
    int n, unum, fd;
    int count, sum, total_num;  /*Haohmaru.2000.3.9.���㻹�ж��ٵ���û���� */
    char result[256], ip[17];   /* Added for IP query by Bigman: 2002.8.20 */
    long pid;                   /* Added by Bigman: 2002.5.31 */

    uid = getCurrentUser()->userid;


    stand_title("�����趨������ע������");
/*    sprintf(fname, "%s.tmp", regfile);*/

    pid = getpid();
    sprintf(fname, "register.%ld", pid);

    move(2, 0);
    if (dashf(fname)) {
        restore_reg(pid);       /* Bigman,2002.5.31:�ָ����ļ� */
    }
/*    f_mv(regfile, fname);*/
/*����ע�ᵥ added by Bigman, 2002.5.31*/

/*ͳ���ܵ�ע�ᵥ�� Bigman, 2002.6.2 */
    if ((fn = fopen(regfile, "r")) == NULL) {
        move(2, 0);
        prints("ϵͳ����, �޷���ȡע�����ϵ�: %s\n", fname);
        pressreturn();
        return -1;
    }

    fd = fileno(fn);
    flock(fd, LOCK_EX);

    total_num = 0;
    while (fgets(genbuf, STRLEN, fn) != NULL) {
        if ((ptr = (char *) strstr(genbuf, "userid")) != NULL)
            total_num++;
    }
    flock(fd, LOCK_UN);
    fclose(fn);

    apply_reg(regfile, fname, pid, 50);

    if ((fn = fopen(fname, "r")) == NULL) {
        move(2, 0);
        prints("ϵͳ����, �޷���ȡע�����ϵ�: %s\n", fname);
        pressreturn();
        return -1;
    }
    memset(fdata, 0, sizeof(fdata));
    /*
     * Haohmaru.2000.3.9.���㹲�ж��ٵ��� 
     */
    sum = 0;
    while (fgets(genbuf, STRLEN, fn) != NULL) {
        if ((ptr = (char *) strstr(genbuf, "userid")) != NULL)
            sum++;
    }
    fseek(fn, 0, SEEK_SET);
    count = 1;
    while (fgets(genbuf, STRLEN, fn) != NULL) {
        struct userec *lookupuser;

        if ((ptr = (char *) strstr(genbuf, ": ")) != NULL) {
            *ptr = '\0';
            for (n = 0; field[n] != NULL; n++) {
                if (strcmp(genbuf, field[n]) == 0) {
                    strcpy(fdata[n], ptr + 2);
                    if ((ptr = (char *) strchr(fdata[n], '\n')) != NULL)
                        *ptr = '\0';
                }
            }
        } else if ((unum = getuser(fdata[1], &lookupuser)) == 0) {
            move(2, 0);
            clrtobot();
            prints("ϵͳ����, ���޴��ʺ�.\n\n");
            for (n = 0; field[n] != NULL; n++)
                prints("%s     : %s\n", finfo[n], fdata[n]);
            pressreturn();
            memset(fdata, 0, sizeof(fdata));
        } else {
            struct userdata ud;

            uinfo = *lookupuser;
            move(1, 0);
            prints("�ʺ�λ��     : %d   ���� %d ��ע�ᵥ����ǰΪ�� %d �ţ���ʣ %d ��\n", unum, total_num, count, sum - count + 1);    /*Haohmaru.2000.3.9.���㻹�ж��ٵ���û���� */
            count++;
#if defined(AUTO_CHECK_REGISTER_FORM) || defined(ZIXIA)
            disply_userinfo(&uinfo, 2);
#endif
			
			read_userdata(lookupuser->userid, &ud);
			useproxy = check_proxy_IP(uinfo.lasthost, buf);
#ifdef AUTO_CHECK_REGISTER_FORM
{
struct REGINFO regform;
int ret;
char errorstr[100];
bzero(&regform,sizeof(regform));
errorstr[0]=0;
strncpy(regform.userid,lookupuser->userid,99);
strncpy(regform.realname,fdata[2],99);
strncpy(regform.career,fdata[3],99);
strncpy(regform.addr,fdata[4],99);
strncpy(regform.phone,fdata[5],99);
strncpy(regform.birth,fdata[6],99);
strncpy(regform.ip, uinfo.lasthost, 20);
ret=checkreg(regform, errorstr, useproxy);
if (ret==-2) {
#endif

/* ��Ӳ�ѯIP, Bigman: 2002.8.20 */
            /*move(8, 20);*/
	     move(8,30); /* ������ŲŲ��  binxun . 2003.5.30 */
            strncpy(ip, uinfo.lasthost, 17);
            find_ip(ip, 2, result);
            prints("\033[33m%s\033[m", result);

            move(15, 0);
            printdash(NULL);
            for (n = 0; field[n] != NULL; n++) {
                /*
                 * added for rejection of register from proxy
                 */
                /*
                 * Bigman, 2001.11.9
                 */
                 clrtoeol();
#ifdef AUTO_CHECK_REGISTER_FORM
		 if (strstr(finfo[n],"��ʵ����")) continue;
#else
                if (n == 1) {
                    if (useproxy > 0)
                        prints("%s     : %s \033[33m%s\033[m\n", finfo[n], fdata[n], buf);
                    else
                        prints("%s     : %s\n", finfo[n], fdata[n]);
                } else
#endif
                    prints("%s     : %s\n", finfo[n], fdata[n]);
            }
            /*
             * if (uinfo.userlevel & PERM_LOGINOK) modified by dong, 1999.4.18 
             */
            if ((uinfo.userlevel & PERM_LOGINOK) || valid_userid(ud.realemail)) {
                move(t_lines - 1, 0);
                prints("���ʺŲ�������дע�ᵥ.\n");
                pressanykey();
                ans[0] = 'D';
            } else {
#ifdef AUTO_CHECK_REGISTER_FORM
                move(t_lines - 2, 0);
/*
		prints("%s�Զ����ע�ᵥ:%s %s\x1b[m",
	saveret==0?"\x1b[1;32m":(saveret==2?"\x1b[1;33m":"\x1b[1;31m"),
	saveret==0?"����Ϊ����ͨ��!":
	(saveret==2?"��������������":(saveret==-1?"���id��̫�ð�":"Ӧ���˻� ����:")),
	errorstr);
*/
		prints("\x1b[1;32mϵͳ����:\x1b[m%s",errorstr);
                move(t_lines - 1, 0);
#endif
                getdata(t_lines - 1, 0, "�Ƿ���ܴ����� (Y/N/Q/Del/Skip)? [S]: ", ans, 3, DOECHO, NULL, true);
            }
            move(2, 0);
            clrtobot();
#ifdef AUTO_CHECK_REGISTER_FORM
} else { //�Զ�����
	if (ret==-3) ans[0]='y';
	else ans[0]='n';
}
#endif
            switch (ans[0]) {
            case 'D':
            case 'd':
                break;
            case 'Y':
            case 'y':
			{
				struct usermemo *um;

				read_user_memo(uinfo.userid, &um);

                prints("����ʹ���������Ѿ�����:\n");
                n = strlen(fdata[5]);
                if (n + strlen(fdata[3]) > 60) {
                    if (n > 40)
                        fdata[5][n = 40] = '\0';
                    fdata[3][60 - n] = '\0';
                }
                strncpy(ud.realname, fdata[2], NAMELEN);
                strncpy(ud.address, fdata[4], NAMELEN);
#ifdef AUTO_CHECK_REGISTER_FORM
         if (ret==-2)
#endif
                sprintf(genbuf, "%s$%s@%s", fdata[3], fdata[5], uid);
#ifdef AUTO_CHECK_REGISTER_FORM
	else
		sprintf(genbuf, "%s$%s@SYSOP", fdata[3], fdata[5]);
#endif

		if(strlen(genbuf) >= STRLEN-16)
			sprintf(genbuf, "%s@%s",fdata[5],uid);
                strncpy(ud.realemail, genbuf, STRLEN - 16);
		ud.realemail[STRLEN - 16 - 1] = '\0';
                sprintf(buf, "tmp/email/%s", uinfo.userid);
                if ((fout = fopen(buf, "w")) != NULL) {
                    fprintf(fout, "%s\n", genbuf);
                    fclose(fout);
                }

                update_user(&uinfo, unum, 0);
                write_userdata(uinfo.userid, &ud);
				memcpy(&(um->ud), &ud, sizeof(ud));
				end_mmapfile(um, sizeof(struct usermemo), -1);
#ifdef AUTO_CHECK_REGISTER_FORM
         if (ret==-2)
         {
#endif
                strcpy(sender,getCurrentUser()->userid);
                sprintf(genbuf, "%s �� %s ͨ�����ȷ��.", uid, uinfo.userid);
#ifdef AUTO_CHECK_REGISTER_FORM
         }
         else
         {
                strcpy(sender,"SYSOP");
                sprintf(genbuf, "�Զ�������� �� %s ͨ�����ȷ��.", uinfo.userid);
	 }
#endif
         	mail_file(sender, "etc/s_fill", uinfo.userid, "�����㣬���Ѿ����ע�ᡣ", 0, NULL);
                securityreport(genbuf, lookupuser, fdata);
                if ((fout = fopen(logfile, "a")) != NULL) {
                    time_t now;

                    for (n = 0; field[n] != NULL; n++)
                        fprintf(fout, "%s: %s\n", field[n], fdata[n]);
                    now = time(NULL);
                    fprintf(fout, "Date: %s\n", Ctime(now));
                    fprintf(fout, "Approved: %s\n", sender);
                    fprintf(fout, "----\n");
                    fclose(fout);
                }
                /*
                 * user_display( &uinfo, 1 ); 
                 */
                /*
                 * pressreturn(); 
                 */

                /*
                 * ����ע����Ϣ��¼ 2001.11.11 Bigman 
                 */
                sethomefile(buf, uinfo.userid, "/register");
                if ((fout = fopen(buf, "w")) != NULL) {
                    for (n = 0; field[n] != NULL; n++)
                        fprintf(fout, "%s     : %s\n", finfo[n], fdata[n]);
                    fprintf(fout, "�����ǳ�     : %s\n", uinfo.username);
                    fprintf(fout, "�����ʼ����� : %s\n", ud.email);
                    fprintf(fout, "��ʵ E-mail  : %s\n", ud.realemail);
                    fprintf(fout, "ע������     : %s\n", ctime(&uinfo.firstlogin));
                    fprintf(fout, "ע��ʱ�Ļ��� : %s\n", uinfo.lasthost);
                    fprintf(fout, "Approved: %s\n", sender);
                    fclose(fout);
                }

                break;
			}
            case 'Q':
            case 'q':
                if ((freg = fopen(regfile, "a")) != NULL) {
                    fd = fileno(freg);
                    flock(fd, LOCK_EX);

                    for (n = 0; field[n] != NULL; n++)
                        fprintf(freg, "%s: %s\n", field[n], fdata[n]);
                    fprintf(freg, "----\n");
                    while (fgets(genbuf, STRLEN, fn) != NULL)
                        fputs(genbuf, freg);

                    flock(fd, LOCK_UN);
                    fclose(freg);
                }

                break;
            case 'N':
            case 'n':
                for (n = 0; field[n] != NULL; n++) {
#ifdef AUTO_CHECK_REGISTER_FORM
		 if (strstr(finfo[n],"��ʵ����")) continue;
#endif
                    prints("%s: %s\n", finfo[n], fdata[n]);
		}
                move(9, 0);
#ifdef AUTO_CHECK_REGISTER_FORM
              if (ret==-2) {
#endif
                prints("��ѡ��/�����˻������ԭ��, �� <enter> ȡ��.\n");
                for (n = 0; reason[n] != NULL; n++)
                    prints("%d) %s\n", n, reason[n]);
                getdata(10 + n, 0, "�˻�ԭ��: ", buf, STRLEN, DOECHO, NULL, true);
#ifdef AUTO_CHECK_REGISTER_FORM
              } else {
                buf[0]='!';
                strncpy(fdata[7],errorstr,STRLEN - 1);
                sprintf(genbuf, "�Զ��������ܾ� %s �����ȷ��.", uinfo.userid);
                securityreport(genbuf, lookupuser, fdata);
              }
#endif

                buff = buf[0];  /* Added by Marco */
                if (buf[0] != '\0') {
                    if (buf[0] >= '0' && buf[0] < '0' + n) {
                        strcpy(buf, reason[buf[0] - '0']);
                    }
                
#ifdef AUTO_CHECK_REGISTER_FORM
                   if (ret==-2)
                   {
#endif
                    strcpy(sender,getCurrentUser()->userid);
                    sprintf(genbuf, "<ע��ʧ��> - %s", buf);
#ifdef AUTO_CHECK_REGISTER_FORM
                   }
                   else
                   {
                    strcpy(sender,"SYSOP");
                    sprintf(genbuf, "<ע��ʧ��> - %s", errorstr);
                   }
#endif
                    strncpy(ud.address, genbuf, NAMELEN);
                    write_userdata(uinfo.userid, &ud);
                    update_user(&uinfo, unum, 0);

                    /*
                     * ------------------- Added by Marco 
                     */
                    switch (buff) {
                    case '0':
                        mail_file(sender, "etc/f_fill.realname", uinfo.userid, ud.address, BBSPOST_LINK, NULL);
                        break;
                    case '1':
                        mail_file(sender, "etc/f_fill.unit", uinfo.userid, ud.address, BBSPOST_LINK, NULL);
                        break;
                    case '2':
                        mail_file(sender, "etc/f_fill.address", uinfo.userid, ud.address, BBSPOST_LINK, NULL);
                        break;
                    case '3':
                        mail_file(sender, "etc/f_fill.telephone", uinfo.userid, ud.address, BBSPOST_LINK, NULL);
                        break;
                    case '4':
                        mail_file(sender, "etc/f_fill.real", uinfo.userid, ud.address, BBSPOST_LINK, NULL);
                        break;
                    case '5':
                        mail_file(sender, "etc/f_fill.chinese", uinfo.userid, ud.address, BBSPOST_LINK, NULL);
                        break;
                    case '6':
                        mail_file(sender, "etc/f_fill.proxy", uinfo.userid, ud.address, BBSPOST_LINK, NULL);
                        break;
                    case '7':
                        mail_file(sender, "etc/f_fill.toomany", uinfo.userid, ud.address, BBSPOST_LINK, NULL);
                        break;
                    default:
                        mail_file(sender, "etc/f_fill", uinfo.userid, ud.address, BBSPOST_LINK, NULL);
                        break;
                    }
                    /*
                     * -------------------------------------------------------
                     */
                    /*
                     * user_display( &uinfo, 1 ); 
                     */
                    /*
                     * pressreturn(); 
                     */
                    break;
                }
                move(10, 0);
                clrtobot();
                prints("ȡ���˻ش�ע�������.\n");
                /*
                 * run default -- put back to regfile 
                 */
            default:
                if ((freg = fopen(regfile, "a")) != NULL) {
                    fd = fileno(freg);
                    flock(fd, LOCK_EX);

                    for (n = 0; field[n] != NULL; n++)
                        fprintf(freg, "%s: %s\n", field[n], fdata[n]);
                    fprintf(freg, "----\n");

                    flock(fd, LOCK_UN);
                    fclose(freg);
                }
            }
            memset(fdata, 0, sizeof(fdata));
#ifdef AUTO_CHECK_REGISTER_FORM
}
#endif
        }
    }                           /* while */

    check_reg(1);               /* Bigman:2002.5.31 */

    fclose(fn);
    unlink(fname);
    return (0);
}

int m_register()
{
    FILE *fn;
    char ans[3], *fname;
    int x, y, wid, len;

    modify_user_mode(ADMIN);
    if (!check_systempasswd()) {
        return -1;
    }
    clear();

    if (check_reg(0) != 0)
        return (-1);            /* added by Bigman, 2002.5.31 */

    stand_title("�趨ʹ����ע������");
    move(2, 0);

    fname = "new_register";

    if ((fn = fopen(fname, "r")) == NULL) {
        prints("Ŀǰ������ע������.");
        pressreturn();
    } else {
        y = 2, x = wid = 0;
        while (fgets(genbuf, STRLEN, fn) != NULL && x < 65) {
            if (strncmp(genbuf, "userid: ", 8) == 0) {
                move(y++, x);
                prints(genbuf + 8);
                len = strlen(genbuf + 8);
                if (len > wid)
                    wid = len;
                if (y >= t_lines - 2) {
                    y = 2;
                    x += wid + 2;
                }
            }
        }
        fclose(fn);
        getdata(t_lines - 1, 0, "�趨������ (Y/N)? [N]: ", ans, 2, DOECHO, NULL, true);
        if (ans[0] == 'Y' || ans[0] == 'y') {
            {
                char secu[STRLEN];

                sprintf(secu, "�趨ʹ����ע������");
                securityreport(secu, NULL, NULL);
            }
            scan_register_form("register.list", fname);
        }
    }
    clear();
    return 0;
}

int m_stoplogin()
{
    char ans[4];

    modify_user_mode(ADMIN);
    if (!check_systempasswd()) {
        return -1;
    }
    if (!HAS_PERM(getCurrentUser(), PERM_ADMIN))
        return -1;
    getdata(t_lines - 1, 0, "��ֹ��½�� (Y/N)? [N]: ", ans, 2, DOECHO, NULL, true);
    if (ans[0] == 'Y' || ans[0] == 'y') {
        if (vedit("NOLOGIN", false, NULL, NULL, 0) == -1)
            unlink("NOLOGIN");
    }
    return 0;
}

/* czz added 2002.01.15 */
int inn_start()
{
    char ans[4], tmp_command[80];

    getdata(t_lines - 1, 0, "����ת���� (Y/N)? [N]: ", ans, 2, DOECHO, NULL, true);
    if (ans[0] == 'Y' || ans[0] == 'y') {
        sprintf(tmp_command, BBSHOME "/innd/innbbsd");
        system(tmp_command);
    }
    return 0;
}

int inn_reload()
{
    char ans[4], tmp_command[80];

    getdata(t_lines - 1, 0, "�ض������� (Y/N)? [N]: ", ans, 2, DOECHO, NULL, true);
    if (ans[0] == 'Y' || ans[0] == 'y') {
        sprintf(tmp_command, BBSHOME "/innd/ctlinnbbsd reload");
        system(tmp_command);
    }
    return 0;
}

int inn_stop()
{
    char ans[4], tmp_command[80];

    getdata(t_lines - 1, 0, "ֹͣת���� (Y/N)? [N]: ", ans, 2, DOECHO, NULL, true);
    if (ans[0] == 'Y' || ans[0] == 'y') {
        sprintf(tmp_command, BBSHOME "/innd/ctlinnbbsd shutdown");
        system(tmp_command);
    }
    return 0;
}

/* added end */
/* ���Ȩ�޹���*/
int x_deny()
{
    int sel;
    char userid[IDLEN + 2];
    struct userec *lookupuser;
    const int level[] = {
        PERM_BASIC,
        PERM_POST,
        PERM_DENYMAIL,
        PERM_CHAT,
        PERM_PAGE,
        PERM_DENYRELAX,
        -1
    };
    const int normal_level[] = {
        PERM_BASIC,
        PERM_POST,
        0,
        PERM_CHAT,
        PERM_PAGE,
        0,
        -1
    };

    const struct _select_item level_conf[] = {
        {3, 6, -1, SIT_SELECT, (void *) "1)��¼Ȩ��"},
        {3, 7, -1, SIT_SELECT, (void *) "2)��������Ȩ��"},
        {3, 8, -1, SIT_SELECT, (void *) "3)����Ȩ��"},
        {3, 9, -1, SIT_SELECT, (void *) "4)����������Ȩ��"},
        {3, 10, -1, SIT_SELECT, (void *) "5)��������Ȩ��"},
        {3, 11, -1, SIT_SELECT, (void *) "6)��������Ȩ��"},
        {3, 12, -1, SIT_SELECT, (void *) "7)��һ��ID"},
        {3, 13, -1, SIT_SELECT, (void *) "8)�˳�"},
        {-1, -1, -1, 0, NULL}
    };

    modify_user_mode(ADMIN);
    if (!check_systempasswd()) {
        return -1;
    }
    move(0, 0);
    clear();

    while (1) {
        int i;
        int basicperm;
        int s[10][2];
        int lcount;

        move(1, 0);

        usercomplete("������ʹ�����ʺ�:", genbuf);
        strncpy(userid, genbuf, IDLEN + 1);
        if (userid[0] == '\0') {
            clear();
            return 0;
        }

        if (!(getuser(userid, &lookupuser))) {
            move(3, 0);
            prints("����ȷ��ʹ���ߴ���\n");
            clrtoeol();
            pressreturn();
            clear();
            continue;
        }
        lcount = get_giveupinfo(lookupuser->userid, &basicperm, s);
        move(3, 0);
        clrtobot();

        for (i = 0; level[i] != -1; i++)
            if ((lookupuser->userlevel & level[i]) != normal_level[i]) {
                move(6 + i, 40);
                if (level[i] & basicperm)
                    prints("������");
                else
                    prints("�����");
            }
        sel = simple_select_loop(level_conf, SIF_NUMBERKEY | SIF_SINGLE, 0, 6, NULL);
        if (sel == i + 2)
            break;
        if (sel > 0 && sel <= i) {
            /*char buf[40];  commented by binxun*/
            /*---------*/
            char buf[STRLEN]; /*buf is too small...changed by binxun .2003/05/11 */
            /*---------*/
            char reportbuf[120];

            move(40, 0);
            if ((lookupuser->userlevel & level[sel - 1]) == normal_level[sel - 1]) {
                sprintf(buf, "���Ҫ���%s��%s", lookupuser->userid, (char *) level_conf[sel - 1].data + 2);
                if (askyn(buf, 0) != 0) {
                    sprintf(reportbuf, "���%s��%s ", lookupuser->userid, (char *) level_conf[sel - 1].data + 2);
                    lookupuser->userlevel ^= level[sel - 1];
                    securityreport(reportbuf, lookupuser, NULL);
                    break;
                }
            } else {
                if (!(basicperm & level[sel - 1])) {
                    sprintf(buf, "���Ҫ�⿪%s��%s ���", lookupuser->userid, (char *) level_conf[sel - 1].data + 2);
                    sprintf(reportbuf, "�⿪%s��%s ���", lookupuser->userid, (char *) level_conf[sel - 1].data + 2);
                } else {
                    sprintf(buf, "���Ҫ�⿪%s��%s ����", lookupuser->userid, (char *) level_conf[sel - 1].data + 2);
                    sprintf(reportbuf, "�⿪%s��%s ����", lookupuser->userid, (char *) level_conf[sel - 1].data + 2);
                }
                if (askyn(buf, 0) != 0) {
                    lookupuser->userlevel ^= level[sel - 1];
                    securityreport(reportbuf, lookupuser, NULL);
                    save_giveupinfo(lookupuser, lcount, s);
                    break;
                }
            }
        }
    }
    return 0;
}

int set_BM(void){
//etnlegend ��д, 2005.05.26 �ύ
    char bname[STRLEN],vbuf[256],*p;
    char genbuf[1024];
    int pos,flag=0,id,n,brd_num;
    unsigned int newlevel;
    struct boardheader fh,newfh;
    struct userec *lookupuser,uinfo;
    struct boardheader *bptr;

#if defined(FREE) || defined(ZIXIA)
    if(!HAS_PERM(getCurrentUser(),PERM_ADMIN)&&!HAS_PERM(getCurrentUser(),PERM_SYSOP)&&!HAS_PERM(getCurrentUser(),PERM_OBOARDS))
#else
    if(!HAS_PERM(getCurrentUser(),PERM_ADMIN)||!HAS_PERM(getCurrentUser(),PERM_SYSOP))
#endif
    {
        move(3,0);clrtobot();
        prints("��Ǹ,ֻ��ADMINȨ�޵Ĺ���Ա�����޸������û�Ȩ��");
        pressreturn();
        return 0;
    }
    modify_user_mode(ADMIN);
    if(!check_systempasswd()){
        return -1;
    }
    clear();
    stand_title("�������");
    move(1,0);
    make_blist(0);
    namecomplete("��������������: ",bname);
    if(!*bname){
        move(2,0);
        prints("ȡ��...");
        pressreturn();
        clear();
        return -1;
    }
    pos=getboardnum(bname,&fh);
    memcpy(&newfh,&fh,sizeof(newfh));
    if(!pos){
        move(2,0);
        prints("���������������");
        pressreturn();
        clear();
        return -1;
    }
    while(true){
        clear();
        stand_title("�������");
        move(1,0);
        prints("����������  : %s\n",fh.filename);
        prints("������˵��  : %s\n",fh.title);
        prints("����������Ա: %s\n",fh.BM);
        getdata(6,0,"(A)���Ӱ��� (D)ɾ������ (Q)�˳�?: [Q]",genbuf,2,DOECHO,NULL,true);
        if(*genbuf=='a'||*genbuf=='A')
            flag=1;
        else if(*genbuf=='d'||*genbuf=='D')
            flag=2;
        else{
            clear();
            return 0;
        }
        if(flag>0){
            if(flag==1)
                getdata(7,0,"������"NAME_USER_SHORT"ID: ",genbuf,IDLEN+1,DOECHO,NULL,true);
            else if(flag==2)
                getdata(7,0,"������"NAME_BM"ID�����: ",genbuf,IDLEN+1,DOECHO,NULL,true);
            /*Ϊ�Ժ�����flag==3֮�����׼����,ʡ�û��ø�...*/
            if(genbuf[0]=='\0'){
                clear();
                flag=0;
            }
            else if(flag==2&&((genbuf[0]>'0')&&!(genbuf[0]>'9'))){
                /*9����Ź��˰�?Ҫ���г���10�������İ��������ʵʵ���ֶ���id��...*/
                n=genbuf[0]-'0';
                p=newfh.BM;
                if(!*p)
                    flag=0;
                if(n>1&&flag)
                    for(n--;n;n--,p++){
                        p=strchr(p,' ');
                        if(!p){
                            flag=0;
                            break;
                        }
                    }
                if(flag){
                    sscanf(p,"%s",genbuf);
                    if(!(id=getuser(genbuf,&lookupuser))){
                        prints("\n\033[1;31m��Ӧ��ŵİ���id�Ƿ�!\033[m");
                        if(askyn("�Ƿ�����",false)){
                            sprintf(vbuf,"���� %s ��Ƿ����� %s",newfh.filename,genbuf);
                            securityreport(vbuf,NULL,NULL);
                            if(strlen(p)==strlen(genbuf))
                                (p==newfh.BM)?(newfh.BM[0]=NULL):(*--p=NULL);
                            else
                                memmove(p,p+strlen(genbuf)+1,strlen(p)-strlen(genbuf));
                            edit_group(&fh,&newfh);
                            set_board(pos,&newfh,NULL);
                            sprintf(genbuf,"���������� %s ������ --> %s",fh.filename,newfh.filename);
                            bbslog("user", "%s", genbuf);
                            strncpy(fh.BM,newfh.BM,BM_LEN-1);
                        }
                        else{
                            clrtoeol();pressreturn();clear();
                        }
                        flag=0;
                    }
                }
                else{
                    prints("\n\033[1;31mδ�ҵ���Ӧ��ŵİ���!\033[m");
                    clrtoeol();pressreturn();clear();
                }
            }
            else if(!(id=getuser(genbuf,&lookupuser))){
                prints("\n\033[1;31m�Ƿ�ID!\033[m");
                clrtoeol();
                pressreturn();
                clear();
                flag = 0;
            }
            else if(flag==1&&chk_BM_instr(newfh.BM,lookupuser->userid)){
                prints("\033[1;31m����:\033[m\n%s �Ѿ��Ǹð����,�޷�����!",lookupuser->userid);
                clrtoeol();pressreturn();clear();
                flag=0;
            }
            else if(flag==2&&!chk_BM_instr(newfh.BM,lookupuser->userid)){
                prints("\033[1;31m����:\033[m\n%s ���Ǹð����,�޷�ɾ��!",lookupuser->userid);
                clrtoeol();pressreturn();clear();
                flag=0;
            }
            if(flag>0){
                uinfo=*lookupuser;
                disply_userinfo(&uinfo,1);
                brd_num=0;
                if(!(lookupuser->userlevel&PERM_BOARDS)){
                     move(22,0);clrtoeol();//���찡,���Ӧ������"����ע������Ѿ����"�����һ�е�λ��,��ô�Ḳ����...
                     prints("\033[1;33m�û� \033[1;32m%s\033[1;33m ���ǰ���!\033[m",lookupuser->userid);
                }
                else{
                    for(n=0;n<get_boardcount();n++){
                        bptr=(struct boardheader*)getboard(n + 1);
                        if(chk_BM_instr(bptr->BM,lookupuser->userid)){
                            move(++brd_num,56);
                            prints("* %-32s",bptr->filename);
                        }
                    }
                    move(22,0);clrtoeol();
                    prints("\033[1;33m�û� \033[1;32m%s\033[1;33m Ϊ�Ҳ� \033[1;32m%d\033[1;33m ������İ���:\033[m",
                        lookupuser->userid,brd_num);
                }
                getdata(t_lines-1,0,"ȷ��������û�(Y/N)?: [N]",genbuf,2,DOECHO,NULL,true);
                if(*genbuf=='y'||*genbuf=='Y'){
                    newlevel=lookupuser->userlevel;
                    if(flag==1){
                        sprintf(vbuf,"%s %s",newfh.BM,lookupuser->userid);
                        if(strlen(vbuf)<BM_LEN){
                            sprintf(newfh.BM,"%s",vbuf+((vbuf[0]==' ')?1:0));
                            newlevel|=PERM_BOARDS;
                            mail_file(getCurrentUser()->userid,"etc/forbm",lookupuser->userid,"����" NAME_BM "�ض�",BBSPOST_LINK,NULL);
#if HAVE_MYSQL_SMTH == 1
#ifdef BMSLOG
                            if(normal_board(newfh.filename))
                                bms_add(lookupuser->userid, newfh.filename, time(0), 2 , NULL );
#endif
#endif
                        }
                        else{
                            clear();move(3,0);
                            prints("\033[1;31m����:\033[m\n�޷����� %s ,�����ַ������!",lookupuser->userid);
                            pressreturn();clear();
                            continue;
                        }
                    }
                    else if(flag==2){
                        sprintf(vbuf," %s ",lookupuser->userid);
                        do{
                            if(!(p=strstr(newfh.BM,vbuf))&&!((p=strstr(newfh.BM,vbuf+1))==newfh.BM)){
                                !(p=strrchr(newfh.BM,' '))?(newfh.BM[0]=NULL):(*p=NULL);
                                continue;
                            }
                            memmove(p,p+strlen(lookupuser->userid)+1,strlen(p)-strlen(lookupuser->userid));
                        }while(chk_BM_instr(newfh.BM,lookupuser->userid));
                        if(!--brd_num)
                            newlevel&=~(PERM_BOARDS|PERM_CLOAK);
#if HAVE_MYSQL_SMTH == 1
#ifdef BMSLOG
                        bms_del(lookupuser->userid,newfh.filename);
#endif
#endif
                    }
                    if(flag==1)
                        sprintf(genbuf,"���� %s �İ��� %s ",newfh.filename,lookupuser->userid);
                    else if(flag==2)
                        sprintf(genbuf,"��ȥ %s �İ��� %s ",newfh.filename,lookupuser->userid);
                    securityreport(genbuf,lookupuser,NULL);
                    lookupuser->userlevel=newlevel;
                    edit_group(&fh,&newfh);
                    set_board(pos,&newfh,NULL);
                    sprintf(genbuf,"���������� %s ������ --> %s",fh.filename,newfh.filename);
                    bbslog("user","%s",genbuf);
                    strncpy(fh.BM,newfh.BM,BM_LEN-1);
                }
            }
        }
    }
}
