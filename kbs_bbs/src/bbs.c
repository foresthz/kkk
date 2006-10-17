/*
    Pirate Bulletin Board System
    Copyright (C) 1990, Edward Luke, lush@Athena.EE.MsState.EDU
    Eagles Bulletin Board System
    Copyright (C) 1992, Raymond Rocker, rocker@rock.b11.ingr.com
                        Guy Vega, gtvega@seabass.st.usm.edu
                        Dominic Tynes, dbtynes@seabass.st.usm.edu

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 1, or (at your option)
    any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
*/
/* ���� ��ע�� �� Alex&Sissi ��� �� alex@mars.net.edu.cn */

#include "bbs.h"
#include "read.h"
#include <time.h>
#include <dlfcn.h>
#include "md5.h"

/*#include "../SMTH2000/cache/cache.h"*/

int scrint = 0;
int local_article = 0;  //0:ת�Ű���Ĭ��ת�ţ�1:��ת�Ű��棬2:ת�Ű���Ĭ�ϲ�ת�� - atppp
int readpost;
int helpmode = 0;
struct boardheader* currboard=NULL;
int currboardent;
char currBM[BM_LEN - 1];
int selboard = 0;
int check_upload = 0; //��������ʱ�Ƿ�Ҫ�����Ӹ���

int Anony;
char genbuf[1024];
unsigned int tmpuser = 0;
char quote_title[120], quote_board[120];
char quote_user[120];

#ifndef NOREPLY
char replytitle[STRLEN];
#endif

char *filemargin();

/* bad 2002.8.1 */

int deny_user();

int m_template();

/*int     b_jury_edit();  stephen 2001.11.1*/
int add_author_friend();
int m_read();                   /*Haohmaru.2000.2.25 */
int Goodbye();

void RemoveAppendedSpace();     /* Leeward 98.02.13 */
extern time_t login_start_time;

extern struct screenline *big_picture;
extern struct userec *user_data;
int post_reply(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg);
extern int b_vote(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg);
extern int b_vote_maintain(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg);
extern int b_jury_edit(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg);
extern int mainreadhelp(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg);

int check_readonly(char *checked)
{                               /* Leeward 98.03.28 */
    if (checkreadonly(checked)) {       /* Checking if DIR access mode is "555" */
        if (!strcmp(currboard->filename,checked)) {
            move(0, 0);
            clrtobot();
            move(8, 0);
            prints("                                        "); /* 40 spaces */
            move(8, (80 - (24 + strlen(checked))) / 2); /* Set text in center */
            prints("\033[1m\033[33m�ܱ�Ǹ��\033[31m%s ��Ŀǰ��ֻ��ģʽ\033[33m\n\n                          �������ڸð淢������޸�����\033[m\n", checked);
            pressreturn();
            clear();
        }
        return true;
    } else
        return false;
}

/* Undel an article   pig2532 2005.12.18 */

/* etnlegend, 2006.10.06, �����û�������ɾ���¹���... */
int UndeleteArticle(struct _select_def *conf,struct fileheader *fh, void *varg){
    struct read_arg *arg=(struct read_arg*)conf->arg;
    /* �����ڴ˴�������Ȩ��, ���� DIR_MODE �ĵط������������... */
    if(!fh||!(arg->mode==DIR_MODE_DELETED||arg->mode==DIR_MODE_JUNK
#ifndef DENY_SELF_UNDELETE
        /* �Ƿ������û��ָ���ɾ������ DENY_SELF_UNDELETE �����... */
        ||arg->mode==DIR_MODE_SELF
#endif /* DENY_SELF_UNDELETE */
        ))
        return DONOTHING;
    switch(do_undel_post(currboard->filename,arg->direct,conf->pos,fh,NULL,getSession())){
        case -1:
            move(t_lines-1,0);
            clrtoeol();
            prints("\033[1;31;47m\t%s\033[K\033[m","�������ѱ��ָ����б����, �� <Enter> ������...");
            WAIT_RETURN;
            break;
        case 1:
            move(t_lines-1,0);
            clrtoeol();
            prints("\033[1;34;47m\t%s\033[K\033[m","�ָ��ɹ�, �� <Enter> ������!");
            WAIT_RETURN;
            break;
        default:
            return DONOTHING;
    }
    return FULLUPDATE;
}

int check_stuffmode()
{
    if (uinfo.mode == RMAIL)
        return true;
    else
        return false;
}

/*Add by SmallPig*/
int shownotepad()
{                               /* ��ʾ notepad */
    modify_user_mode(NOTEPAD);
    ansimore("etc/notepad", true);
    clear();
    return 1;
}

void printutitle()
{                               /* ��Ļ��ʾ �û��б� title */
    /*---	modified by period	2000-11-02	hide posts/logins	---*/

    move(2, 0);
    clrtoeol();
    prints(
              "\033[44m �� ��  ʹ���ߴ���     %-19s  #��վ #���� %4s    �����������   \033[m\n",
#if defined(ACTS_REALNAMES)
              "��ʵ����",
#else
              "ʹ�����ǳ�",
#endif
              "���");
}


int g_board_names(struct boardheader *fhdrp,void* arg)
{
    if (check_read_perm(getCurrentUser(), fhdrp)) {
        AddNameList(fhdrp->filename);
    }
    return 0;
}

void make_blist(int addfav)
{                               /* ���а� ���� �б� */
    CreateNameList();
    apply_boards((int (*)()) g_board_names,NULL);
	if(addfav){
		int i;
		for( i=0; i<bdirshm->allbrd_list_t; i++){
			if( bdirshm->allbrd_list[i].ename[0] && HAS_PERM(getCurrentUser(), bdirshm->allbrd_list[i].level) )
				AddNameList( bdirshm->allbrd_list[i].ename );
		}
	}
}

int ReadBoard(void){
    int returnmode;
    while(1){
        returnmode=Read();
        if((returnmode==-2)||(returnmode==CHANGEMODE)){ //is directory or select another board
            if(currboard->flag&BOARD_GROUP){
                choose_board(0,NULL,currboardent,0);
                break;
            }
        }
        else
            break;
    }
    return 0;
}

/* etnlegend, 2006.04.07, ��ѡ���Ķ�������... */
int Select(void){
    return (do_select(NULL,NULL,NULL)==CHANGEMODE?ReadBoard():0);
}

int Post()
{                               /* ���˵��ڵ� �ڵ�ǰ�� POST ���� */
    if (!selboard) {
        prints("\n\n���� (S)elect ȥѡ��һ����������\n");
        pressreturn();          /* �ȴ���return�� */
        clear();
        return 0;
    }
#ifndef NOREPLY
    *replytitle = '\0';
#endif
    do_post(NULL,NULL,NULL);
    return 0;
}

int get_a_boardname(char *bname, char *prompt)
{                               /* ����һ������ */
    /*
     * struct boardheader fh; 
     */

    make_blist(0);
    namecomplete(prompt, bname);        /* �����Զ����� */
    if (*bname == '\0') {
        return 0;
    }
    /*---	Modified by period	2000-10-29	---*/
    if (getbnum_safe(bname,getSession()) <= 0)
        /*---	---*/
    {
        move(1, 0);
        prints("���������������\n");
        pressreturn();
        move(1, 0);
        return 0;
    }
    return 1;
}

int top_move(struct _select_def* conf,struct fileheader *fileinfo)
{
    struct read_arg* arg=(struct read_arg*)conf->arg;
    char ans[3];
	int d;
	int alltop;
	int ret;

	alltop = arg->boardstatus->toptitle;
	if (alltop <= 0)
		return DONOTHING;
	getdata(t_lines - 1, 0, "�ƶ�����λ��: ", ans, 3, DOECHO, NULL, true);
	if (ans[0] == '\0' || ans[0] == '\n' || ans[0] == '\r')
		return SHOW_REFRESH;
	d = atoi(ans);
	if (d < 1)
		d = 1;
	else if (d > alltop)
		d = alltop;
	if (d == conf->pos - arg->filecount)
		return SHOW_REFRESH;

	ret = move_record(arg->dingdirect, sizeof(struct fileheader), conf->pos-arg->filecount, d,
					(RECORD_FUNC_ARG) cmpname, fileinfo->filename);
	if (ret==0) {
		ret=DIRCHANGED;
		board_update_toptitle(arg->bid, true);
	} else {
		char buf[STRLEN];
		a_prompt(-1, "����ʧ��, �밴 Enter ���� << ", buf);
		ret=SHOW_REFRESH;
		board_update_toptitle(arg->bid, true);
	}
	return ret;
}

int set_article_flag(struct _select_def* conf,struct fileheader *fileinfo,long flag)
{
    struct read_arg* arg=(struct read_arg*)conf->arg;
    bool    isbm=chk_currBM(currboard->BM, getCurrentUser());
    struct write_dir_arg dirarg;
    struct fileheader data;
    int ret;
    struct actionlist{
        int action;
        int pos;
        long flag;
        char* name;
    };
    const struct actionlist *ptr;
    static const struct actionlist flaglist[]= {
            {FILE_MARK_FLAG,0,FILE_MARKED,"���m"},
            {FILE_NOREPLY_FLAG,1,FILE_READ,"����Re"},
            {FILE_SIGN_FLAG,0,FILE_SIGN,"���#"},
            {FILE_PERCENT_FLAG,0,FILE_PERCENT,"���%"},
            {FILE_DELETE_FLAG,1,FILE_DEL,"���X"},
            {FILE_DIGEST_FLAG,0,FILE_DIGEST,"��ժ"},
            {FILE_TITLE_FLAG,0,0,"ԭ��"},
            {FILE_IMPORT_FLAG,0,FILE_IMPORTED,"���뾫����"},
#ifdef FILTER
            {FILE_CENSOR_FLAG,0,0,"���ͨ��"},
#endif
#ifdef COMMEND_ARTICLE
            {FILE_COMMEND_FLAG,1,FILE_COMMEND,"���ͨ��"},
#endif
            {0,0,0,NULL}
    };
    if (fileinfo==NULL)
        return DONOTHING;
    
#ifdef FILTER
#ifdef SMTH
    if (!strcmp(currboard->filename,"NewsClub")&&haspostperm(getCurrentUser(), currboard->filename)) 
            isbm=true;
#endif
#endif

    if (!isbm
#ifdef OPEN_NOREPLY
		&& (flag!=FILE_NOREPLY_FLAG || strcmp(fileinfo->owner,getCurrentUser()->userid) )
#endif
					)
        return DONOTHING;
    if (conf->pos > arg->filecount && flag == FILE_MARK_FLAG)
        return top_move(conf, fileinfo);
    data=*fileinfo;
    init_write_dir_arg(&dirarg);
    dirarg.fd=arg->fd;
    dirarg.ent = conf->pos;
    ptr=flaglist;
    while (ptr->action!=0) {
        if (ptr->action==flag) {
            data.accessed[ptr->pos] = (fileinfo->accessed[ptr->pos] & ptr->flag)?0 : ptr->flag;
            break;
        }
        ptr++;
    }
    if (ptr->action!=0) {
        ret=change_post_flag(&dirarg, arg->mode, currboard,  
            fileinfo,flag, &data,isbm,getSession());
        if (ret==0) {
//prompt...
            ret=DIRCHANGED;
        } else {
            char buf[STRLEN];
            a_prompt(-1, "����ʧ��, �밴 Enter ���� << ", buf);
            ret=FULLUPDATE;
        }
    } else
        ret=DONOTHING;
    free_write_dir_arg(&dirarg);
    return ret;
}

#ifdef COMMEND_ARTICLE
/* stiger, �Ƽ����� */
int do_commend(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
{

    char ispost[3];
    struct read_arg* arg=conf->arg;

    if (fileinfo==NULL)
        return DONOTHING;
    if (arg->mode!=DIR_MODE_NORMAL) {
        return DONOTHING;
    }
    if ((fileinfo->accessed[1] & FILE_COMMEND) && !HAS_PERM(getCurrentUser(), PERM_SYSOP)) {
        clear();
        move(1, 0);
        prints("�������Ѿ��Ƽ�������л���������Ƽ�");
        move(2, 0);
        pressreturn();
        return FULLUPDATE;
    }
    if( ! normal_board(currboard->filename) ){
        clear();
        move(1, 0);
        prints("�Բ��������Ƽ��ڲ���������");
        move(2, 0);
        pressreturn();
        return FULLUPDATE;
    }
    if ( deny_me(getCurrentUser()->userid, COMMEND_ARTICLE) ) {
        clear();
        move(1, 0);
        prints("�Բ�������ֹͣ���Ƽ���Ȩ��");
        move(2, 0);
        pressreturn();
        return FULLUPDATE;
    }

    clear();
    move(0, 0);
    prints("�Ƽ� ' %s '", fileinfo->title);
    clrtoeol();
    move(1, 0);
    getdata(1, 0, "ȷ��? [y/N]: ", ispost, 2, DOECHO, NULL, true);
    if (ispost[0] == 'y' || ispost[0] == 'Y') {
        if (post_commend(getCurrentUser(), currboard->filename, fileinfo ) == -1) {
        	prints("�Ƽ�ʧ�ܣ�ϵͳ���� \n");
            pressreturn();
            move(2, 0);
            return FULLUPDATE;
        }
        move(2, 0);
        prints("�Ƽ��ɹ� \n");
	set_article_flag(conf,fileinfo,FILE_COMMEND_FLAG);
	/*
        fileinfo->accessed[1] |= FILE_COMMEND;
        substitute_record(direct, fileinfo, sizeof(*fileinfo), ent);
	*/
    } else {
        prints("ȡ��");
    }
    move(2, 0);
    pressreturn();
    return FULLUPDATE;
}
#endif	/* COMMEND_ARTICLE */

/* Add by SmallPig */
int do_cross(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
{                               /* ת�� һƪ ���� */
    char bname[STRLEN];
    char ispost[10];
    char q_file[STRLEN];
    struct read_arg* arg=(struct read_arg*)conf->arg;
    int outgo_board = true, proceed = false;
    const struct boardheader* bh;

    if (fileinfo==NULL)
        return DONOTHING;
    if (!HAS_PERM(getCurrentUser(), PERM_POST)) {    /* �ж��Ƿ���POSTȨ */
        return DONOTHING;
    }
#if 0 //atppp 20051230
    if ((fileinfo->accessed[0] & FILE_FORWARDED) && !HAS_PERM(getCurrentUser(), PERM_SYSOP)) {
        clear();
        move(1, 0);
        prints("�������Ѿ�ת����һ�Σ��޷��ٴ�ת��");
        move(2, 0);
        pressreturn();
        return FULLUPDATE;
    }
#endif //atppp 20051230
    if (uinfo.mode != RMAIL)
        sprintf(q_file, "boards/%s/%s", currboard->filename, fileinfo->filename);
    else
        setmailfile(q_file, getCurrentUser()->userid, fileinfo->filename);
    strcpy(quote_title, fileinfo->title);

    clear();

    move(4, 0);                 /* Leeward 98.02.25 */
    prints
        ("\033[1m\033[33m��ע�⣺\033[31m��վվ��涨��ͬ�����ݵ������Ͻ��� 5 (��) ���������������ظ�������\n\nΥ����\033[33m���������»ᱻɾ��֮�⣬������\033[31m��������������µ�Ȩ����\033[33m��ϸ�涨����գ�\n\n    Announce ���վ�棺������ת�����������µĹ涨����\n\n���ҹ�ͬά�� BBS �Ļ�������ʡϵͳ��Դ��лл������\n\n\033[m");

    move(1, 0);
    if (!get_a_boardname(bname, "������Ҫת��������������: ")) {
        return FULLUPDATE;
    }

    if (!strcmp(bname, currboard->filename) && (uinfo.mode != RMAIL)) {
        move(3, 0);
        clrtobot();
        prints("\n\n                          ��������²���Ҫת��������!");
        pressreturn();
        clear();
        return FULLUPDATE;
    }

    if (!haspostperm(getCurrentUser(), bname)) {
        move(3, 0);
        clrtobot();
        prints("������Ȩ���� %s ��������.\n", bname);
        prints("�������δע�ᣬ���ڸ��˹���������ϸע�����\n");
        prints("δͨ�����ע����֤���û���û�з������µ�Ȩ�ޡ�\n");
        prints("лл������ :-) \n");
        pressreturn();
        clear();
        return FULLUPDATE;
    }

    if(!(bh=getbcache(bname)))
        return FULLUPDATE;

#ifdef NEWSMTH
    if(!check_score_level(getCurrentUser(),bh)){
        move(3,0);
        clrtobot();
        prints("\n\n    \033[1;33m%s\033[0;33m<Enter>\033[m",
            "���Ļ��ֲ����ϵ�ǰ���������趨, ��ʱ�޷��ڵ�ǰ��������������...");
        WAIT_RETURN;
        return FULLUPDATE;
    }
#endif

        /* Leeward 98.01.13 ���ת����������ת���İ����Ƿ񱻽�ֹ�� POST Ȩ */


        if (normal_board(bname) && !normal_board(currboard->filename) && (uinfo.mode != RMAIL)) {
            char ans[4];
            move(2,0);clrtobot();
            move(4,4);prints("\033[1;31mע��:\033[m ����ͼ���ڲ�����(%s)������ת�ص��������棬",currboard->filename);
            getdata(5,4,"��������ͨ����\033[1;33m������\033[m�Ļ�\033[1;33m������\033[m��, ��ȷ��ת�ز���? [y/N]: ",ans,2,DOECHO,NULL,true);
            if(!(ans[0]=='y'||ans[0]=='Y'))
                return FULLUPDATE;
        }
        if ((fileinfo->attachment!=0)&&!(bh->flag&BOARD_ATTACH)) {
            char ans[4];
            move(3, 0);
            clrtobot();
            prints("\n\n  ��Ҫת�������´��и�������%s�治��ճ��������", bh->filename);
            getdata(8, 2, "�Ƿ�����������ת�� [y/N]: ", ans, 2, DOECHO, NULL, true);
            if(!(ans[0]=='y'||ans[0]=='Y'))
                return FULLUPDATE;
        }
        if (deny_me(getCurrentUser()->userid, bname) ) {    /* ������ֹPOST ��� */
			if( HAS_PERM(getCurrentUser(), PERM_SYSOP) ){
				char buf[3];

				clear();
                getdata(3,0,"���ѱ�ȡ����Ŀ�İ���ķ���Ȩ��, �Ƿ�ǿ��ת��? [y/N]: ",buf,2,DOECHO,NULL,true);
				if( buf[0]!='y' && buf[0]!='Y' ){
					clear();
					return FULLUPDATE;
				}
			}else{
            	move(3, 0);
            	clrtobot();
                prints("\n\n\t\t���ѱ�������Աȡ����Ŀ�İ���ķ���Ȩ��...\n");
            	pressreturn();
            	clear();
            	return FULLUPDATE;
			}
        }
		if (true == check_readonly(bname)) { /* Leeward 98.03.28 */
            return FULLUPDATE;
        }
        outgo_board = (bh->flag & BOARD_OUTFLAG);


    move(0, 0);
    prints("ת�� ' %s ' �� %s �� ", quote_title, bname);
    clrtoeol();
    move(1, 0);
    if (outgo_board) {
        getdata(1, 0, "(S)ת�� (L)��վ (A)ȡ��? [A]: ", ispost, 9, DOECHO, NULL, true);
        proceed = (ispost[0] == 's' || ispost[0] == 'S' || ispost[0] == 'L' || ispost[0] == 'l');
    } else {
        getdata(1, 0, "(L)��վ (A)ȡ��? [A]: ", ispost, 9, DOECHO, NULL, true);
        proceed = (ispost[0] == 's' || ispost[0] == 'S' || ispost[0] == 'L' || ispost[0] == 'l');
    }
    if (proceed) {
		if(!outgo_board)
			ispost[0]='l';
        /*add by stiger
        if(conf->pos<=arg->filecount) {
            int i=0;
            for (i = conf->pos-arg->filecount; i > 0; i--) {
                if (0 == strcmp(arg->boardstatus->topfh[i].filename, fileinfo->filename)) {
                    conf->new_pos=i;
                    break;
                }
           }
	    if (i==0){
                move(2, 0);
	        prints("����б����仯��ȡ��");
		move(2,0);
		pressreturn();
		return FULLUPDATE;
	    }
        } else conf->new_pos=conf->pos; */
        /*add old*/
        if (post_cross(getCurrentUser(), bh, currboard->filename, 
            quote_title, q_file, Anony, 
            arg->mode==DIR_MODE_MAIL?1:0, 
            ispost[0], 0, getSession()) == -1) { /* ת�� */
            pressreturn();
            move(2, 0);
            return FULLUPDATE;
        }
        move(2, 0);
        prints("' %s ' ��ת���� %s �� \n", quote_title, bname);
#if 0 //atppp 20051230
        fileinfo->accessed[0] |= FILE_FORWARDED;        /*added by alex, 96.10.3 */
        if(conf->pos<=arg->filecount)
            substitute_record(arg->direct, fileinfo, sizeof(*fileinfo), conf->pos);
    /* �ö������Զ��ת��Ӧ��û�������...
        else
            substitute_record(arg->dingdirect, fileinfo, sizeof(*fileinfo), conf->new_pos);
    */
#endif //atppp 20051230
        conf->new_pos=0;
    } else {
        prints("ȡ��");
    }
    move(2, 0);
    pressreturn();
    return FULLUPDATE;
}

void readtitle(struct _select_def* conf)
{                               /* ���� ��ʾ�����б� �� title */
    const struct boardheader *bp;
    struct BoardStatus * bs;
    char header[STRLEN*2], title[STRLEN];
    const char *readmode;
    int chkmailflag = 0;
    int bnum;
    struct read_arg* arg=(struct read_arg*)conf->arg;

    bnum = getbid(currboard->filename,&bp);
    bs = getbstatus(bnum);
    memcpy(currBM, bp->BM, BM_LEN - 1);
    if (currBM[0] == '\0' || currBM[0] == ' ') {
        strcpy(header, "����������");
    } else {
        //if (HAS_PERM(getCurrentUser(), PERM_OBOARDS)) {
#ifdef OPEN_BMONLINE
		if (1)
#else
        if(check_board_delete_read_perm(getCurrentUser(),currboard,0))
#endif
        {
            char *p1, *p2;

            strcpy(header, "����: ");
            p1 = currBM;
            p2 = p1;
            while (1) {
                if ((*p2 == ' ') || (*p2 == 0)) {
                    int end;

                    end = 0;
                    if (p1 == p2) {
                        if (*p2 == 0)
                            break;
                        p1++;
                        p2++;
                        continue;
                    }
                    if (*p2 == 0)
                        end = 1;
                    *p2 = 0;
                    if (apply_utmp(NULL, 1, p1, NULL)) {
                        sprintf(genbuf, "\x1b[32m%s\x1b[33m ", p1);
                        strcat(header, genbuf);
                    } else {
                        strcat(header, p1);
                        strcat(header, " ");
                    }
                    if (end)
                        break;
                    p1 = p2 + 1;
                    *p2 = ' ';
                }
                p2++;
            }
        } else {
            sprintf(header, "����: %s", currBM);
        }
    }
    chkmailflag = chkmail();
    if (chkmailflag == 2)       /*Haohmaru.99.4.4.������Ҳ������ */
        strcpy(title, "[�������䳬������,����������!]");
    else if (chkmailflag)       /* �ż���� */
        strcpy(title, "[�����ż�]");
    else if ((bp->flag & BOARD_VOTEFLAG))       /* ͶƱ��� */
        sprintf(title, "ͶƱ�У��� V ����ͶƱ");
    else
        strcpy(title, bp->title + 13);

    showtitle(header, title);   /* ��ʾ ��һ�� */
    update_endline();
    move(1, 0);
    clrtoeol();
    if (DEFINE(getCurrentUser(), DEF_HIGHCOLOR))
        prints
            ("�뿪[\x1b[1;32m��\x1b[m,\x1b[1;32me\x1b[m] ѡ��[\x1b[1;32m��\x1b[m,\x1b[1;32m��\x1b[m] �Ķ�[\x1b[1;32m��\x1b[m,\x1b[1;32mr\x1b[m] ��������[\x1b[1;32mCtrl-P\x1b[m] ����[\x1b[1;32md\x1b[m] ����¼[\x1b[1;32mTAB\x1b[m] ����[\x1b[1;32mh\x1b[m]\033[m");
    else
        prints("�뿪[��,e] ѡ��[��,��] �Ķ�[��,r] ��������[Ctrl-P] ����[d] ����¼[TAB] ����[h]\x1b[m");

    switch(arg->mode){
        case DIR_MODE_NORMAL:
            readmode="һ��";
            break;
        case DIR_MODE_DIGEST:
            readmode="��ժ";
            break;
        case DIR_MODE_THREAD:
            readmode="����";
            break;
        case DIR_MODE_MARK:
            readmode="����";
            break;
        case DIR_MODE_DELETED:
            readmode="����";
            break;
        case DIR_MODE_JUNK:
            readmode="ֽ¦";
            break;
        case DIR_MODE_ORIGIN:
            readmode="ԭ��";
            break;
        case DIR_MODE_AUTHOR:
            readmode="����";
            break;
        case DIR_MODE_TITLE:
            readmode="����";
            break;
        case DIR_MODE_SUPERFITER:
            readmode="����";
            break;
        case DIR_MODE_SELF:
            readmode="��ɾ";
            break;
        default:
            readmode="δ֪";
            break;
    }

    move(2, 0);
    setfcolor(WHITE, DEFINE(getCurrentUser(), DEF_HIGHCOLOR));
    setbcolor(BLUE);
    clrtoeol();
    prints("  ���   %-12s %6s %s", "�� �� ��", "��  ��", " ���±���");
    sprintf(title, "����:%4d [%4sģʽ]", bs->currentusers, readmode);
    move(2, -strlen(title)-1);
    prints("%s", title);
    resetcolor();
}

#if defined(COLOR_ONLINE) || defined(LOWCOLOR_ONLINE)

int count_login(struct user_info *uentp, int *arg, int pos)
{
     if (uentp->invisible == 1) {
         return COUNT;
     }
     (*arg)++;
     UNUSED_ARG(pos);
     return COUNT;
}

int isonline(char *s){

	int tuid;
	int logincount=0,seecount=0;
    struct userec *lookupuser;

	if( ! (tuid=getuser(s,&lookupuser)) ){
		return 7;
	}
	logincount = apply_utmp((APPLY_UTMP_FUNC) count_login, 0, lookupuser->userid, &seecount);
	if( logincount == 0 )
		return 7;
	if( seecount != 0 )
		return 2;
    if (HAS_PERM(getCurrentUser(), PERM_SEECLOAK))
		return 6;
	return 7;
}
#endif

char *readdoent(char *buf, int num, struct fileheader *ent,struct fileheader* readfh,struct _select_def* conf)
{                               /* �������б��� ��ʾ һƪ���±��� */
    time_t filetime;
    char date[20];
    char TITLE[ARTICLE_TITLE_LEN+30];
    int titlelen;
    int type;
    int manager;
    char typeprefix[20];
    char *typesufix;
    char attachch[20];
    struct read_arg * arg=(struct read_arg*)conf->arg;
	int isreply=0;
	int isthread=0;
	char highstr[10];
	char threadprefix[20];
	char threadprefix1[20];
	char threadsufix[20];

   /* typesufix = typeprefix = "";*/
   typesufix = ""; 
   typeprefix[0]='\0';

    manager = chk_currBM(currBM, getCurrentUser());

    type = get_article_flag(ent, getCurrentUser(), currboard->filename, manager, NULL, getSession());
    if (manager && (ent->accessed[0] & FILE_IMPORTED)) {        /* �ļ��Ѿ������뾫���� */
        if (type == ' ') {
            strcpy(typeprefix ,"\x1b[42m");
            typesufix = "\x1b[m";
        } else {
            strcpy(typeprefix ,"\x1b[32m");
            typesufix = "\x1b[m";
        }
    }
    filetime = get_posttime(ent);
    if (filetime > 740000000) {
#ifdef HAVE_COLOR_DATE
        struct tm *mytm;
        char *datestr = ctime(&filetime) + 4;

        mytm = localtime(&filetime);
        sprintf(date, "\033[1;%dm%6.6s\033[m", mytm->tm_wday + 31, datestr);
#else
        strncpy(date, ctime(&filetime) + 4, 6);
        date[6] = 0;
#endif
    }
    /*
     * date = ctime( &filetime ) + 4;   ʱ�� -> Ӣ�� 
     */
    else
        /*
         * date = ""; char *���ͱ���, ���ܴ���, modified by dong, 1998.9.19 
         */
        /*
         * { date = ctime( &filetime ) + 4; date = ""; } 
         */
        date[0] = 0;

    /*
     * Re-Write By Excellent 
     */
#if defined(OPEN_NOREPLY) && defined(LOWCOLOR_ONLINE)
    if ( ent->accessed[1] & FILE_READ ) {
		if(ent->attachment!=0)
			strcpy(attachch,"\033[0;1;4;33m@\033[m");
		else
			strcpy(attachch,"\033[0;1;4;33mx\033[m");
	}else{
		if(ent->attachment!=0)
			strcpy(attachch,"\033[0;1;33m@\033[m");
		else
			strcpy(attachch," ");
	}
#else
    if (ent->attachment!=0)
        attachch[0]='@';
    else
        attachch[0]=' ';
	attachch[1]='\0';
#endif
    titlelen = scr_cols > 80 ? scr_cols - 80 + 45 : 45;
    if (titlelen > ARTICLE_TITLE_LEN) {
        titlelen = ARTICLE_TITLE_LEN - 1;
    }
    if (! DEFINE(getCurrentUser(), DEF_SHOWSIZE) && arg->mode != DIR_MODE_DELETED && arg->mode != DIR_MODE_JUNK){
		char sizebuf[30];
		strnzhcpy(TITLE, ent->title, titlelen - 7);
		if(ent->eff_size < 1000)
			sprintf(sizebuf,"(%d)", ent->eff_size);
		else if(ent->eff_size < 1000000){
			sprintf(sizebuf,"\033[1;33m(%dk)\033[m", ent->eff_size/1000);
		}else{
			sprintf(sizebuf, "\033[1;31m(%dm)\033[m", (ent->eff_size/1000000)%1000 );
		}
		strcat(TITLE, sizebuf);
	}else {
		strnzhcpy(TITLE, ent->title, titlelen);
	}
//    TITLE = ent->title;         /*���±���TITLE */
//	sprintf(TITLE,"%s(%d)",ent->title,ent->eff_size);
    if ((type=='d')||(type=='D')) { //�ö�����
        sprintf(buf, " \x1b[1;33m[��ʾ]\x1b[m %-12.12s %s %s" FIRSTARTICLE_SIGN " %s ", ent->owner, date, attachch, TITLE);
        return buf;
    }

    if(toupper(type)=='Y'){
        sprintf(buf," \033[1;33m[����]\033[m %-12.12s %s %s" FIRSTARTICLE_SIGN " %s ",ent->owner,date,attachch,TITLE);
        return buf;
    }

    if (uinfo.mode != RMAIL && arg->mode != DIR_MODE_DIGEST && arg->mode != DIR_MODE_DELETED && arg->mode != DIR_MODE_JUNK
        && strcmp(currboard->filename, "sysmail")) { /* �·����Ƚ�*/
        if ((ent->groupid != ent->id)&&(arg->mode==DIR_MODE_THREAD||!strncasecmp(TITLE,"Re:",3)||!strncmp(TITLE,"�ظ�:",5))) {
			isreply=1;
		}
        if ((readfh&&readfh->groupid == ent->groupid))
			isthread=1;
	}else {
        if (!strncmp("Re:", ent->title, 3)) {
			isreply=1;
            if (readfh&&isThreadTitle(readfh->title, ent->title))
				isthread=1;
		}else
            if ((readfh!=NULL)&&!strcmp(readfh->title, ent->title))
				isthread=1;
	}

    if (DEFINE(getCurrentUser(), DEF_HIGHCOLOR))
		strcpy(highstr,"1;");
	else
		highstr[0]='\0';

	if(isthread){
		if(isreply){
			sprintf(threadprefix,"\033[%s36m", highstr);
			sprintf(threadprefix1,"\033[%s36m.", highstr);
			strcpy(threadsufix,"\033[m");
		}else{
			sprintf(threadprefix,"\033[%s33m", highstr);
			sprintf(threadprefix1,"\033[%s33m.", highstr);
			strcpy(threadsufix,"\033[m");
		}
	}else{
		threadprefix[0]='\0';
		strcpy(threadprefix1," ");
		threadsufix[0]='\0';
	}

#if defined(COLOR_ONLINE)
                        sprintf(buf, " %s%4d%s %s%c%s \033[1;3%dm%-12.12s\033[m %s%s%c%s%s%s ", threadprefix, num, threadsufix, typeprefix, type, typesufix, isonline(ent->owner), ent->owner, date, threadprefix1, attachch[0], isreply?"":FIRSTARTICLE_SIGN" ", TITLE, threadsufix);
#elif defined(LOWCOLOR_ONLINE)
                        sprintf(buf, " %s%4d%s %s%c%s \033[3%dm%-12.12s\033[m %s%s%c%s%s%s ", threadprefix, num, threadsufix, typeprefix, type, typesufix, isonline(ent->owner), ent->owner, date, threadprefix1, attachch[0], isreply?"":FIRSTARTICLE_SIGN" ", TITLE, threadsufix);
#else
                        sprintf(buf, " %s%4d%s %s%c%s %-12.12s %s%s%c%s%s%s ", threadprefix, num, threadsufix, typeprefix, type, typesufix, ent->owner, date, threadprefix1, attachch[0], isreply?"":FIRSTARTICLE_SIGN" ", TITLE, threadsufix);
#endif

    return buf;
}

#define SESSIONLEN 9
void get_telnet_sessionid(char* buf,int unum)
{
    static const char encode[] = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    struct user_info* pui=get_utmpent(unum);
    int utmpkey=pui->utmpkey;
    buf[0]=encode[unum%36];
    unum/=36;
    buf[1]=encode[unum%36];
    unum/=36;
    buf[2]=encode[unum%36];

    buf[3]=encode[utmpkey%36];
    utmpkey/=36;
    buf[4]=encode[utmpkey%36];
    utmpkey/=36;
    buf[5]=encode[utmpkey%36];
    utmpkey/=36;
    buf[6]=encode[utmpkey%36];
    utmpkey/=36;
    buf[7]=encode[utmpkey%36];
    utmpkey/=36;
    buf[8]=encode[utmpkey%36];
    utmpkey/=36;

    buf[9]=0;
}

char *get_my_webdomain(int force)
{
	static char myurl[80];
	static int urlinit=0;

	if(force) urlinit=0;

	if(!urlinit){
		FILE *fp;
		char buf[256];
		char *c;

		sethomefile(buf, getCurrentUser()->userid, "myurl");
		if((fp=fopen(buf,"r"))!=NULL){
			if(fgets(myurl,80,fp)){
				if((c=strchr(myurl,'\n'))!=NULL) *c='\0';
				if((c=strchr(myurl,'\r'))!=NULL) *c='\0';
				if(strlen(myurl)>5)
					urlinit=1;
			}
			fclose(fp);
		}
		if(!urlinit){
			const char *c=sysconf_str("BBS_WEBDOMAIN");
			if(c!=NULL){
				urlinit=1;
				strncpy(myurl, c, 80);
				myurl[79]='\0';
			}
		}
		if(!urlinit){
			strncpy(myurl, sysconf_str("BBSDOMAIN"), 80);
			myurl[79]='\0';
			urlinit=1;
		}
	}

	return myurl;

}

static void  board_attach_link(char* buf,int buf_len,char *ext,int len,long attachpos,void* arg)
{
    struct fileheader* fh=(struct fileheader*)arg;
    char ftype[12];
    int zd = (POSTFILE_BASENAME(fh->filename)[0] == 'Z');
    ftype[0] = '\0';
    if (attachpos!=-1) {
        char ktype = 's';
        if (!public_board(currboard)) {
#ifdef ATPPP_YMSW_YTJH
            MD5_CTX md5;
            char info[9+19], base64_info[9+25];
            char *ptr = info;
            uint32_t ii; uint16_t is;
            char md5ret[17];
            get_telnet_sessionid(info, getSession()->utmpent);
            ptr = info + 9;
            is = (uint16_t)currboardent; memcpy(ptr, &is, 2), ptr += 2;
            ii = (fh->id);          memcpy(ptr, &ii, 4); ptr += 4;
            ii = (attachpos);       memcpy(ptr, &ii, 4); ptr += 4;
            ii = ((int)time(NULL)); memcpy(ptr, &ii, 4); ptr += 4;
 
            MD5Init(&md5);
            MD5Update(&md5, (unsigned char *) info, 23);
            MD5Final((unsigned char*)md5ret, &md5);

            memcpy(ptr, md5ret, 4);
            memcpy(base64_info, info, 9);
            to64frombits((unsigned char*)base64_info+9, (unsigned char*)info+9, 18);
            snprintf(buf,buf_len,"http://%s/att.php?n.%s%s",
                get_my_webdomain(0),base64_info,ext);
            return;
#else
            ktype = 'n';
#endif
        } else {
            if (len > 51200) ktype = 'p';
        }

        if (zd) sprintf(ftype, ".%d.0", DIR_MODE_ZHIDING);
        
        snprintf(buf,buf_len,"http://%s/att.php?%c.%d.%d%s.%ld%s",
            get_my_webdomain(0),ktype,currboardent,fh->id,ftype,attachpos,ext);
    } else {
        if (zd) sprintf(ftype, "&ftype=%d", DIR_MODE_ZHIDING);
        
        snprintf(buf,buf_len,"http://%s/bbscon.php?bid=%d&id=%d%s",
            get_my_webdomain(0),currboardent,fh->id, ftype);
    }
}

int zsend_attach(int ent, struct fileheader *fileinfo, char *direct)
{
    char *t;
    char buf1[512];
    char *ptr, *p;
    off_t size;
    long left;

    if(fileinfo->attachment==0) return -1;
    strcpy(buf1, direct);
    if ((t = strrchr(buf1, '/')) != NULL)
        *t = '\0';
    snprintf(genbuf, 512, "%s/%s", buf1, fileinfo->filename);
    BBS_TRY {
        if (safe_mmapfile(genbuf, O_RDONLY, PROT_READ, MAP_SHARED, &ptr, &size, NULL) == 0) {
            BBS_RETURN(-1);
        }
        for (p=ptr,left=size;left>0;p++,left--) {
            long attach_len;
            char* file,*attach;
            FILE* fp;
            char name[100];
            if (NULL !=(file = checkattach(p, left, &attach_len, &attach))) {
                left-=(attach-p)+attach_len-1;
                p=attach+attach_len-1;
                setcachehomefile(name, getCurrentUser()->userid,getSession()->utmpent, "attach.tmp");
                fp=fopen(name, "wb");
                fwrite(attach, 1, attach_len, fp);
                fclose(fp);
                if (bbs_zsendfile(name, file)==-1) {
                    unlink(name);
                    break;
                }
                unlink(name);
                continue;
            }
        }
    }
    BBS_CATCH {
    }
    BBS_END;
    end_mmapfile((void *) ptr, size, -1);
    return 0;
}

int showinfo(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
{
    char slink[256];
    bool isbm;
    char unread_mark;
    if (fileinfo==NULL) return DONOTHING;

    clear();

    if (!deny_modify_article(currboard, fileinfo, DIR_MODE_NORMAL, getSession())
        && ((currboard->flag & BOARD_ATTACH) || HAS_PERM(getCurrentUser(), PERM_SYSOP) || fileinfo->attachment)) {
        char ses[20];
        move(0,0);
        get_telnet_sessionid(ses, getSession()->utmpent);
        snprintf(slink, sizeof(slink), "�����༭��ַ: (���𽫴����ӷ��͸�����)\n\033[4mhttp://%s/bbseditatt.php?bid=%d&id=%d&sid=%s\033[m\n",
            get_my_webdomain(0), currboardent, fileinfo->id, ses);
        prints("%s", slink);
    }

    move(3,0);
    board_attach_link(slink,255,NULL,-1,-1,fileinfo);
    prints("ȫ�����ӣ�\n\033[4m%s\033[m\n",slink);

    isbm=chk_currBM(currboard->BM, getCurrentUser());
    unread_mark = (DEFINE(getCurrentUser(), DEF_UNREADMARK) ? UNREAD_SIGN : 'N');
    move(6,0);
    prints("���±��: %c%c%c%c%c%c%c%s%c",
        (strcmp(getCurrentUser()->userid, "guest") && brc_unread(fileinfo->id, getSession())) ? unread_mark : ' ',
        (fileinfo->accessed[0] & FILE_DIGEST) ? 'g' : ' ',
        (fileinfo->accessed[0] & FILE_MARKED) ? 'm' : ' ',
        (fileinfo->accessed[1] & FILE_READ) ? ';' : ' ',
        (isbm && (fileinfo->accessed[0] & FILE_SIGN)) ? '#' : ' ',
        (isbm && (fileinfo->accessed[0] & FILE_PERCENT)) ? '%' : ' ',
        (isbm && (fileinfo->accessed[1] & FILE_DEL)) ? 'X' : ' ',
        (isbm && (fileinfo->accessed[0] & FILE_IMPORTED)) ? "\x1b[42m \x1b[0m" : " ",
        (isbm && (fileinfo->accessed[1] & FILE_CENSOR)) ? '@' : ' '
        );
    pressanykey();
    return FULLUPDATE;
}

int jumpReID(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
{
    int now; // 1-based
    char *data;
    struct fileheader *pFh, *pFh1;
    off_t size;
    struct read_arg *arg = (struct read_arg *) conf->arg;

    if (arg->mode != DIR_MODE_NORMAL) return DONOTHING;
    if (fileinfo->reid == fileinfo->id) return DONOTHING;

    BBS_TRY {
        if (safe_mmapfile_handle(arg->fd, PROT_READ, MAP_SHARED, &data, &size) == 0)
            BBS_RETURN(0);
        pFh = (struct fileheader*)data;
        now = arg->filecount = size/sizeof(struct fileheader);
        if (now >= conf->pos) now = conf->pos - 1;
        for(;now>=1;now--) { /* ˳�����أ����� yuhuan mm �� Search_Bin ��... */
            pFh1 = pFh + now - 1;
            if (fileinfo->reid == pFh1->id) break;
            if (fileinfo->reid > pFh1->id) {
                now = -1;
                break;
            }
        }
    }
    BBS_CATCH {
        now = -1;
    }
    BBS_END;
    end_mmapfile(data, size, -1);
    if(now > 0) {
        conf->new_pos = now;
        return SELCHANGE;
    }
    return DONOTHING;
}

/* etnlegend, 2006.09.14, ��ת��ͬ�������޸ı���ĵ�һƪ���� */
static int jump_changed_title(struct _select_def *conf,struct fileheader *fh,void *varg){
    int res,okay,last;
    char *data;
    off_t size;
    struct fileheader *map;
    struct read_arg *arg=(struct read_arg*)conf->arg;
    if(arg->mode!=DIR_MODE_NORMAL)
        return DONOTHING;
    if((fh->id==fh->groupid)||strncmp(fh->title,"Re: ",4))  /* ��ʼ�ж� */
        return DONOTHING;
    BBS_TRY{
        if(!safe_mmapfile_handle(arg->fd,PROT_READ,MAP_SHARED,&data,&size))
            BBS_RETURN(0);
        map=(struct fileheader*)data;
        if((arg->filecount=size/sizeof(struct fileheader))<conf->pos)
            res=-1;
        else{
            res=conf->pos-1;
            if(map[res].id!=fh->id)
                res=-1;
            else{
                do{         /* ������������������� */
                    for(last=res;!(res<0)&&(map[res].id>map[last].reid);res--)
                        continue;
                    if(!(okay=(!(res<0)&&(map[res].id==map[last].reid))))
                        break;
                    if((res==last)||
                        !((!strncmp(map[res].title,"Re: ",4)
                        &&!strncmp(&map[res].title[4],&fh->title[4],54))
                        ||!strncmp(map[res].title,&fh->title[4],54)))
                        break;
                }
                while(1);
                if(!okay){  /* ������������������ */
                    for(res=last;!(res<0)&&!(map[res].id<fh->groupid);res--)
                        if((map[res].groupid==fh->groupid)
                            &&((!strncmp(map[res].title,"Re: ",4)
                            &&!strncmp(&map[res].title[4],&fh->title[4],54))
                            ||!strncmp(map[res].title,&fh->title[4],54)))
                            last=res;
                }
                res=last;
            }
        }
    }
    BBS_CATCH{
        res=-1;
    }
    BBS_END;
    end_mmapfile(data,size,-1);
    if(res==-1)
        return DONOTHING;
    conf->new_pos=(res+1);
    return SELCHANGE;
}

int read_post(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
{
    char *t;
    char buf[512];
    int ch;
    int ent=conf->pos;
    struct read_arg* arg=conf->arg;
    int ret=FULLUPDATE;

    if (fileinfo==NULL)
        return DONOTHING;
    /* czz 2003.3.4 forbid reading cancelled post in board */
    if ((fileinfo->owner[0] == '-') 
        && (arg->readmode != DIR_MODE_DELETED) 
        && (arg->readmode != DIR_MODE_JUNK) )
	    return ret;

    clear();
    strcpy(buf, read_getcurrdirect(conf));
    if ((t = strrchr(buf, '/')) != NULL)
        *t = '\0';
    sprintf(genbuf, "%s/%s", buf, fileinfo->filename);
/*
    strcpy(quote_file, genbuf);
*/
    strcpy(quote_board, currboard->filename);
    strncpy(quote_title, fileinfo->title, ARTICLE_TITLE_LEN);
/*    quote_file[119] = fileinfo->filename[STRLEN - 2];
*/
    strncpy(quote_user, fileinfo->owner, OWNER_LEN);
    quote_user[OWNER_LEN - 1] = 0;

    register_attach_link(board_attach_link, fileinfo);
#ifndef NOREPLY
    ch = ansimore_withzmodem(genbuf, false, fileinfo->title);   /* ��ʾ�������� */
#else
    ch = ansimore_withzmodem(genbuf, true, fileinfo->title);    /* ��ʾ�������� */
#endif
    register_attach_link(NULL,NULL);
#ifdef HAVE_BRC_CONTROL
    brc_add_read(fileinfo->id,currboardent,getSession());
#endif
#ifndef NOREPLY
    move(t_lines - 1, 0);
    switch (arg->readmode) {
    case READ_THREAD:
        if (DEFINE(getCurrentUser(), DEF_HIGHCOLOR))
	        prints("\x1b[44m\x1b[1;31m[�����Ķ�] \x1b[33m ���� R �� ���� Q,�� ����һ�� ������һ�� <Space>,��");
        else
	        prints("\x1b[44m\x1b[31m[�����Ķ�] \x1b[33m ���� R �� ���� Q,�� ����һ�� ������һ�� <Space>,��");
        break;
    case READ_AUTHOR:
        if (DEFINE(getCurrentUser(), DEF_HIGHCOLOR))
	        prints("\x1b[44m\x1b[1;31m[ͬ�����Ķ�] \x1b[33m ���� R �� ���� Q,�� ����һ�� ������һ�� <Space>,��");
        else
	        prints("\x1b[44m\x1b[31m[ͬ�����Ķ�] \x1b[33m ���� R �� ���� Q,�� ����һ�� ������һ�� <Space>,��");
        break;
    default:
        if (DEFINE(getCurrentUser(), DEF_HIGHCOLOR))
            prints("\033[44m\033[1;31m[�Ķ�����] \033[33m ���� R �� ���� Q,�� ����һ�� ������һ�� <Space>,���������Ķ� ^X��p ");
        else
            prints("\033[44m\033[31m[�Ķ�����] \033[33m ���� R �� ���� Q,�� ����һ�� ������һ�� <Space>,���������Ķ� ^X��p ");
    }

    clrtoeol();                 /* ��������β */
    resetcolor();

    setreadpost(conf, fileinfo);

    if (!(ch == KEY_RIGHT || ch == KEY_UP || ch == KEY_PGUP
        || ch == KEY_DOWN) && (ch <= 0 || strchr("RrEexp", ch) == NULL))
reget:
        ch = igetkey();

    switch (ch) {
    case Ctrl('Z'):
        r_lastmsg();            /* Leeward 98.07.30 support msgX */
        break;
    case Ctrl('Y'):
        read_zsend(conf, fileinfo, NULL);
        break;
    case Ctrl('D'):
        zsend_attach(ent, fileinfo, read_getcurrdirect(conf));
        break;
    case 'Q':
    case 'q':
    case KEY_LEFT:
        break;
    case KEY_REFRESH:
        break;
    case ' ':
    case 'j':
    case 'n':
    case KEY_DOWN:
    case KEY_PGDN:
        return READ_NEXT;
    case KEY_UP:
    case KEY_PGUP:
    case 'k':
    case 'l':
        return READ_PREV;
    case 'Y':
    case 'R':
    case 'y':
    case 'r':
        if ( currboard->flag & BOARD_NOREPLY ) 
        {
            clear();
            move(3, 0);
            clrtobot();
            prints("\n\n                    �ܱ�Ǹ���ð���ܷ�������,���ܻ�����...\n");
            pressreturn();
            break;              /*Haohmaru.98.12.19,���ܻ����µİ� */
        }
        if (fileinfo->accessed[1] & FILE_READ) {        /*Haohmaru.99.01.01.���²���re */
            clear();
            move(3, 0);
            prints("\n\n            �ܱ�Ǹ�������Ѿ�����Ϊ����reģʽ,�벻Ҫ��ͼ���۱���...\n");
            pressreturn();
            break;
        }
        do_reply(conf,fileinfo);
        ret=DIRCHANGED;
        break;
    case Ctrl('R'):
        post_reply(conf, fileinfo, extraarg);      /* ������ */
        break;
    case 'g':
        ret=set_article_flag(conf , fileinfo, FILE_DIGEST_FLAG);       /* Leeward 99.03.02 */
        break;
    case 'M':
        ret=set_article_flag(conf , fileinfo, FILE_MARK_FLAG);       /* Leeward 99.03.02 */
        break;
    case Ctrl('U'):
        if (arg->readmode==READ_NORMAL) {
            move(t_lines - 1, 0);
            if (DEFINE(getCurrentUser(), DEF_HIGHCOLOR))
                prints("\x1b[44m\x1b[1;31m[ͬ�����Ķ�] \x1b[33m ���� R �� ���� Q,�� ����һ�� ������һ�� <Space>,��");
            else
	        prints("\x1b[44m\x1b[31m[ͬ�����Ķ�] \x1b[33m ���� R �� ���� Q,�� ����һ�� ������һ�� <Space>,��");
            clrtoeol();
            arg->readmode=READ_AUTHOR;
            arg->oldpos=0;
            goto reget;
        } else return READ_NEXT;
    case Ctrl('H'):
        if (arg->readmode==READ_NORMAL) {
            move(t_lines - 1, 0);
            if (DEFINE(getCurrentUser(), DEF_HIGHCOLOR))
                prints("\x1b[44m\x1b[1;31m[ͬ�����Ķ�] \x1b[33m ���� R �� ���� Q,�� ����һ�� ������һ�� <Space>,��");
            else
	        prints("\x1b[44m\x1b[31m[ͬ�����Ķ�] \x1b[33m ���� R �� ���� Q,�� ����һ�� ������һ�� <Space>,��");
            clrtoeol();
            arg->readmode=READ_AUTHOR;
            arg->oldpos=conf->pos;
            goto reget;
        } else return READ_NEXT;
    case Ctrl('N'):
        list_select_add_key(conf,Ctrl('N'));
        break;
    case Ctrl('S'):
    case 'p':                  /*Add by SmallPig */
        if (arg->readmode==READ_NORMAL) {
           move(t_lines - 1, 0);
           if (DEFINE(getCurrentUser(), DEF_HIGHCOLOR))
	        prints("\x1b[44m\x1b[1;31m[�����Ķ�] \x1b[33m ���� R �� ���� Q,�� ����һ�� ������һ�� <Space>,��");
           else
	        prints("\x1b[44m\x1b[31m[�����Ķ�] \x1b[33m ���� R �� ���� Q,�� ����һ�� ������һ�� <Space>,��");
	   clrtoeol();
            arg->readmode=READ_THREAD;
            arg->oldpos=0;
            goto reget;
        } else return READ_NEXT;
    case Ctrl('X'):            /* Leeward 98.10.03 */
    case KEY_RIGHT:
        if (arg->readmode==READ_NORMAL) {
           move(t_lines - 1, 0);
           if (DEFINE(getCurrentUser(), DEF_HIGHCOLOR))
	        prints("\x1b[44m\x1b[1;31m[�����Ķ�] \x1b[33m ���� R �� ���� Q,�� ����һ�� ������һ�� <Space>,��");
           else
	        prints("\x1b[44m\x1b[31m[�����Ķ�] \x1b[33m ���� R �� ���� Q,�� ����һ�� ������һ�� <Space>,��");
	   clrtoeol();
            arg->readmode=READ_THREAD;
            arg->oldpos=conf->pos;
            goto reget;
        } else return READ_NEXT;
    case '~':            /*Haohmaru.98.12.05,ϵͳ����Աֱ�Ӳ��������� */
        ret=read_authorinfo(conf,fileinfo,NULL);
        break;
    case Ctrl('W'):            /*cityhunter 00.10.18�쿴������Ϣ */
        ret=read_showauthorBM(conf, fileinfo, NULL);
        break;
    case Ctrl('O'):
        clear();
        read_addauthorfriend(conf, fileinfo, NULL);
        return READ_NEXT;
    case 'Z':
    case 'z':
        if (!HAS_PERM(getCurrentUser(), PERM_PAGE))
            break;
        read_sendmsgtoauthor(conf, fileinfo, NULL);
        return READ_NEXT;
    case Ctrl('A'):            /*Add by SmallPig */
        clear();
        read_showauthor(conf, fileinfo, NULL);
        return READ_NEXT;
    case 'L':
        if (uinfo.mode != LOOKMSGS) {
            show_allmsgs();
            break;
        }
        return DONOTHING;
    case '!':                  /*Haohmaru 98.09.24 */
        Goodbye();
        break;
    case 'H':                  /* Luzi 1997.11.1 */
        r_lastmsg();
        break;
    case 'w':                  /* Luzi 1997.11.1 */
        if (!HAS_PERM(getCurrentUser(), PERM_PAGE))
            break;
        s_msg();
        break;
    case 'O':
    case 'o':                  /* Luzi 1997.11.1 */
        if (!HAS_PERM(getCurrentUser(), PERM_BASIC))
            break;
        t_friends();
        break;
    case 'u':                  /* Haohmaru 1999.11.28 */
        clear();
        modify_user_mode(QUERY);
        t_query(NULL);
        break;
	case 'U':		/* pig2532 2005.12.10 */
		return(board_query());
    }
#endif
    if ((ret==FULLUPDATE)&&(arg->oldpos!=0)) {
        conf->new_pos=arg->oldpos;
	arg->oldpos=0;
	ret=SELCHANGE;
	list_select_add_key(conf,KEY_REFRESH);
        arg->readmode=READ_NORMAL;
    }
    return ret;
}

int skip_post(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
{
#ifdef HAVE_BRC_CONTROL
    brc_add_read(fileinfo->id,currboardent,getSession());
#endif
    return GOTO_NEXT;
}

/************
  ��������ѡ��add by stiger
  *****/
#define MAXBOARDSEARCH 100
int super_board_count;
int super_board_now=0;

static int sb_show(struct _select_def *conf, int i)
{
	const struct boardheader *bp;
	int *result = (int *) (conf->arg);
    int dir = 0;
	struct BoardStatus *bptr;

	bp=getboard(result[i-1]);
	if(bp==NULL){
		prints(" ERROR ");
		return SHOW_CONTINUE;
	}
    bptr = getbstatus(result[i-1]);

//	prints(" %2d %-20s %-40s", i, bp->filename, bp->title);
    if (bp->flag&BOARD_GROUP) {
        dir = 1;
    }
    prints(" %4d%s", dir?bp->board_data.group_total:bptr->total, !dir&&bptr->total>9999?"":" ");
    prints("%2s %-16s %s%-36s", dir?"��":"  ", bp->filename, (bp->flag & BOARD_VOTEFLAG) ? "\033[31;1mV\033[m" : " ",  bp->title+1); 

	return SHOW_CONTINUE;
}

static int sb_prekey(struct _select_def *conf, int *key)
{
    switch (*key) {
    case 'q':
	case 'e':
        *key = KEY_LEFT;
        break;
    case 'p':
    case 'k':
        *key = KEY_UP;
        break;
    case 'N':
        *key = KEY_PGDN;
        break;
    case 'n':
    case 'j':
        *key = KEY_DOWN;
        break;
    }
    return SHOW_CONTINUE;
}

static int sb_key(struct _select_def *conf, int key)
{
	switch(key){
	default:
		break;
	}

	return SHOW_CONTINUE;
}

static int sb_refresh(struct _select_def *conf)
{
    clear();
    docmdtitle("[��������ѡ��]",
               "�˳�[\x1b[1;32m��\x1b[0;37m] ѡ��[\x1b[1;32m��\x1b[0;37m,\x1b[1;32m��\x1b[0;37m] �������[\x1b[1;32mENTER\x1b[0;37m]");
    move(2, 0);
    setfcolor(WHITE, DEFINE(getCurrentUser(), DEF_HIGHCOLOR));
    setbcolor(BLUE);
    clrtoeol();
    prints("   %s   ����������       V ��� ת��  %-24s            ", "ȫ�� " , "��  ��  ��  ��");
    resetcolor();
    update_endline();
    return SHOW_CONTINUE;
}

static int sb_getdata(struct _select_def *conf, int pos, int len)
{
    conf->item_count = super_board_count;
    return SHOW_CONTINUE;
}

static int sb_select(struct _select_def *conf)
{
	super_board_now = conf->pos;
	return SHOW_QUIT;
}

static int do_super_board_select(int result[])
{
	POINT *pts;
    struct _select_def grouplist_conf;
    int i;

    pts = (POINT *) malloc(sizeof(POINT) * BBS_PAGESIZE);
    for (i = 0; i < BBS_PAGESIZE; i++) {
        pts[i].x = 2;
        pts[i].y = i + 3;
    }
    bzero(&grouplist_conf, sizeof(struct _select_def));

	grouplist_conf.arg = result;
    grouplist_conf.item_count = super_board_count;
    grouplist_conf.item_per_page = BBS_PAGESIZE;
    grouplist_conf.flag = LF_VSCROLL | LF_BELL | LF_LOOP | LF_MULTIPAGE;
    grouplist_conf.prompt = "��";
    grouplist_conf.item_pos = pts;
    grouplist_conf.title_pos.x = 0;
    grouplist_conf.title_pos.y = 0;
    grouplist_conf.pos = 1;
    grouplist_conf.page_pos = 1;

    grouplist_conf.show_data = sb_show;
    grouplist_conf.pre_key_command = sb_prekey;
    grouplist_conf.key_command = sb_key;
    grouplist_conf.show_title = sb_refresh;
    grouplist_conf.get_data = sb_getdata;
	grouplist_conf.on_select = sb_select;

    list_select_loop(&grouplist_conf);

	free(pts);

	return 0;
}

int super_select_board(char *bname)
{
	int result[MAXBOARDSEARCH];
	char searchname[STRLEN];

	clear();

	bname[0]='\0';
	searchname[0]='\0';

//	while(bname[0]=='\0'){
    	getdata(1, 0, "��������ؼ���: ", searchname, STRLEN-1, DOECHO, NULL, false);
		if (searchname[0] == '\0' || searchname[0]=='\n')
			return 0;
		if( ( super_board_count = fill_super_board (getCurrentUser(),searchname, result, MAXBOARDSEARCH) ) <= 0 ){
			move(5,0);
			prints("û���ҵ��κ���ذ���\n");
			pressanykey();
			return 0;
		}
		super_board_now=0;
		do_super_board_select(result);
		if (super_board_now != 0){
			const struct boardheader *bp;
			bp=getboard(result[super_board_now-1]);
			if (bp==NULL){
				bname[0]='\0';
				return 0;
			}else
				strcpy(bname, bp->filename);
		}
//	}

	return 0;
}

extern int in_do_sendmsg;

int do_select(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
        /*
         * ������������ ѡ�������� 
         */
{
    char bname[STRLEN], bpath[STRLEN];
    int bid;
    struct read_arg* arg=NULL;
	int addfav=0;
    int ret;
    
    if (conf!=NULL) arg=conf->arg;
	if( extraarg ) addfav = * ( (int *) extraarg ) ;

    /*
    move(0, 0);
    prints("ѡ��һ�������� (Ӣ����ĸ��Сд�Կ�, ��\033[1;32m#\033[0m����\033[1;31m�ؼ��ֻ��������\033[0m����)");
    clrtoeol();
    prints("\n������������ (���հ׼���Tab���Զ�����): ");
    clrtoeol();
    */

    /* etnlegend, 2006.04.07, ��ط��ĸ�... */
    move(0,0);clrtoeol();
    prints("%s","ѡ�������� [ \033[1;32m#\033[m - \033[1;31m��������/�ؼ�������\033[m, "
        "\033[1;32mSPACE/TAB\033[m - �Զ���ȫ, \033[1;32mESC\033[m - �˳� ]");
    move(1,0);clrtoeol();
    if(uinfo.mode!=MMENU)
        prints("����������������: ");
    else
        prints("���������������� [\033[1;32m%s\033[m]: ",currboard->filename);

    make_blist(addfav);               /* ��������Board�� �б� */
	in_do_sendmsg=true;
    if((ret=namecomplete(NULL,bname))=='#'){ /* ��ʾ���� board �� */
		super_select_board(bname);
	}
	in_do_sendmsg=0;

    if(!*bname)
        return ((uinfo.mode!=MMENU||ret==KEY_ESC)?FULLUPDATE:CHANGEMODE);

	if(addfav){
		bid = EnameInFav(bname,getSession());

		if(bid){
			*((int *)extraarg) = bid;
    		board_setcurrentuser(uinfo.currentboard, -1);
    		uinfo.currentboard = 0;
    		UPDATE_UTMP(currentboard,uinfo);
    		return CHANGEMODE;
		}else
			*((int *)extraarg) = 0;
	}

    setbpath(bpath,bname);
    if(!dashd(bpath)||!(bid=getbnum_safe(bname,getSession()))){
        move(2,0);clrtoeol();
        prints("\033[1;37m%s\033[0;33m<ENTER>\033[m","���������������...");
        WAIT_RETURN;
        return FULLUPDATE;
    }

    board_setcurrentuser(uinfo.currentboard, -1);
    uinfo.currentboard = bid;
    UPDATE_UTMP(currentboard,uinfo);
    board_setcurrentuser(uinfo.currentboard, 1);
    
    selboard = 1;

    currboardent=bid;
    currboard=(struct boardheader*)getboard(bid);

#ifdef HAVE_BRC_CONTROL
    brc_initial(getCurrentUser()->userid, bname,getSession());
#endif

    move(0, 0);
    clrtoeol();
    move(1, 0);
    clrtoeol();
    if (arg!=NULL) {
    arg->mode=DIR_MODE_NORMAL;
    setbdir(arg->mode, arg->direct, currboard->filename);     /* direct �趨 Ϊ ��ǰboardĿ¼ */
    }
    return CHANGEMODE;
}

/* ��ѯ������Ϣ by pig2532 on 2005-12-10 */
int board_query()
{
	char bname[STRLEN];
	int bid;
    int oldmode;
    oldmode = uinfo.mode;
    modify_user_mode(QUERYBOARD);
	clear();
	move(2,0);
	clrtobot();
	prints("��������Ҫ��ѯ�İ���Ӣ�����ƣ����ո����Tab�����롣");
	move(1,0);
	prints("�����ѯ��");
	make_blist(0);
	namecomplete(NULL, bname);
	if(*bname!='\0') {
        bid = getbnum_safe(bname,getSession());
        if (bid == 0)
    	{
            move(2, 0);
            prints("����ȷ��������.");
            clrtoeol();
            pressreturn();
        } else {
            show_boardinfo(bname);
        }
	}
    modify_user_mode(oldmode);
	return FULLUPDATE;
}

int digest_mode(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
{                               /* ��ժģʽ �л� */
    struct read_arg* arg=(struct read_arg*)conf->arg;
    if (arg->mode == DIR_MODE_DIGEST) {
        arg->newmode = DIR_MODE_NORMAL;
        setbdir(arg->newmode, arg->direct, currboard->filename);
    } else {
        arg->newmode = DIR_MODE_DIGEST;
        setbdir(arg->newmode, arg->direct, currboard->filename);
        if (!dashf(arg->direct)) {
            setbdir(arg->mode, arg->direct, currboard->filename);
            return FULLUPDATE;
        }
    }
    return NEWDIRECT;
}

int deleted_mode(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
{
    struct read_arg* arg=(struct read_arg*)conf->arg;

    if(!check_board_delete_read_perm(getCurrentUser(),currboard,1)){
        return DONOTHING;
    }

    if (arg->mode == DIR_MODE_DELETED) {
        arg->newmode = DIR_MODE_NORMAL;
        setbdir(arg->newmode, arg->direct, currboard->filename);
    } else {
        arg->newmode = DIR_MODE_DELETED;
        setbdir(arg->newmode, arg->direct, currboard->filename);
        if (!dashf(arg->direct)) {
            arg->mode = DIR_MODE_NORMAL;
            setbdir(arg->mode, arg->direct, currboard->filename);
            return DONOTHING;
        }
    }
    return NEWDIRECT;
}

int marked_mode(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
{
    struct read_arg* arg=NULL;
    if (conf!=NULL)
        arg=(struct read_arg*)conf->arg;
    if (arg==NULL||arg->mode!= DIR_MODE_MARK) {
        if (setboardmark(currboard->filename, -1)) {
            if (board_regenspecial(currboard->filename, DIR_MODE_MARK, NULL) == -1) {
                return FULLUPDATE;
            }
        }
        arg->newmode= DIR_MODE_MARK;
        setbdir(arg->newmode, arg->direct, currboard->filename);
        if (!dashf(arg->direct)) {
            arg->mode=DIR_MODE_NORMAL;
            setbdir(DIR_MODE_NORMAL, arg->direct, currboard->filename);
            return FULLUPDATE;
        }
        return NEWDIRECT;
    }
    return DONOTHING;
}

int title_mode(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
{
    struct stat st;

    struct read_arg* arg=NULL;
    if (conf!=NULL)
        arg=(struct read_arg*)conf->arg;
    if (!stat("heavyload", &st)) {
        move(t_lines - 1, 0);
        clrtoeol();
        prints("ϵͳ�������أ���ʱ������Ӧ�����Ķ�������...");
        pressanykey();
        return FULLUPDATE;
    }

    arg->newmode = DIR_MODE_THREAD;
    if (setboardtitle(currboard->filename, -1)) {
    	setbdir(arg->newmode, arg->direct, currboard->filename);
        if (gen_title(currboard->filename) == -1) {
            arg->mode=DIR_MODE_NORMAL;
            setbdir(DIR_MODE_NORMAL, arg->direct, currboard->filename);
            return FULLUPDATE;
        }
    }
    setbdir(arg->newmode, arg->direct, currboard->filename);
    if (!dashf(arg->direct)) {
        arg->mode=DIR_MODE_NORMAL;
        setbdir(DIR_MODE_NORMAL, arg->direct, currboard->filename);
        return FULLUPDATE;
    }
    return NEWDIRECT;
}

int self_mode(struct _select_def *conf,struct fileheader *fh,void *varg){
#define SM_QUIT(msg)                                            \
    do{                                                         \
        if(cptr!=MAP_FAILED){                                   \
            if(filedes!=-1){                                    \
                flock(filedes,LOCK_UN);                         \
                close(filedes);                                 \
            }                                                   \
            end_mmapfile(cptr,size,-1);                         \
        }                                                       \
        if(msg){                                                \
            move(t_lines-2,0);                                  \
            clrtoeol();                                         \
            move(t_lines-1,0);                                  \
            clrtoeol();                                         \
            prints("\033[1;34;47m\t%s\033[K\033[m",(msg));      \
            WAIT_RETURN;                                        \
            BBS_RETURN(FULLUPDATE);                             \
        }                                                       \
    }                                                           \
    while(0)
    struct read_arg *arg=(struct read_arg*)conf->arg;
    struct fileheader info;
    const struct fileheader *ptr;
    char dir[STRLEN],*cptr;
    int filedes,count,selected,i;
    const void *data;
    ssize_t length,writen;
    off_t size;
    if(arg->mode==DIR_MODE_SELF){
        arg->newmode=DIR_MODE_NORMAL;
        setbdir(DIR_MODE_NORMAL,arg->direct,currboard->filename);
        return NEWDIRECT;
    }
    BBS_TRY{
        cptr=MAP_FAILED;
        filedes=-1;
        if(!HAS_PERM(getCurrentUser(),PERM_LOGINOK))
            SM_QUIT("��ǰ�û������д�����ɾ���µ�Ȩ��...");
        setbdir(DIR_MODE_JUNK,dir,currboard->filename);
        if(!safe_mmapfile(dir,O_RDONLY,PROT_READ,MAP_SHARED,&cptr,&size,NULL))
            SM_QUIT("Ŀǰû����ɾ����, �� <Enter> ������...");
        setbdir(DIR_MODE_SELF,dir,currboard->filename);
        if((filedes=open(dir,O_WRONLY|O_CREAT|O_TRUNC,0644))==-1||flock(filedes,LOCK_EX)==-1)
            SM_QUIT("���ļ�ʱ��������, �� <Enter> ������...");
        ptr=(const struct fileheader*)cptr;
        count=size/sizeof(struct fileheader);
        for(selected=0,i=0;i<count;i++){
            if(getCurrentUser()->firstlogin
                &&(get_posttime(&ptr[i])>getCurrentUser()->firstlogin)
                &&!strcmp(ptr[i].owner,getCurrentUser()->userid)){
                info=ptr[i];
                strnzhcpy(info.title,ptr[i].title,34);
                for(data=&info,length=sizeof(struct fileheader),writen=0;
                    writen!=-1&&length>0;vpm(data,writen),length-=writen){
                    writen=write(filedes,data,length);
                }
                selected++;
            }
        }
        SM_QUIT((!selected?"Ŀǰû����ɾ����, �� <Enter> ������...":NULL));
    }
    BBS_CATCH{
    }
    BBS_END;
    arg->newmode=DIR_MODE_SELF;
    strcpy(arg->direct,dir);
    return NEWDIRECT;
#undef SM_QUIT
}

int junk_mode(struct _select_def *conf,struct fileheader *fh,void *varg){
    struct read_arg *arg=(struct read_arg*)conf->arg;
    if(!HAS_PERM(getCurrentUser(),PERM_SYSOP))
        return self_mode(conf,fh,varg);
    if(arg->mode==DIR_MODE_JUNK){
        arg->newmode=DIR_MODE_NORMAL;
        setbdir(arg->newmode,arg->direct,currboard->filename);
    }
    else{
        arg->newmode=DIR_MODE_JUNK;
        setbdir(DIR_MODE_JUNK,arg->direct,currboard->filename);
        if(!dashf(arg->direct)){
            arg->newmode=DIR_MODE_NORMAL;
            setbdir(arg->mode,arg->direct,currboard->filename);
            return DONOTHING;
        }
    }
    return NEWDIRECT;
}

static char search_data[STRLEN];

int search_mode(struct _select_def* conf,struct fileheader *fileinfo,int mode, char *index)
/* added by bad 2002.8.8 search mode*/
{
    int count = 0;
    struct read_arg* arg=(struct read_arg*)conf->arg;

    strncpy(search_data, index, STRLEN);
    arg->newmode=mode;
    setbdir(mode,arg->direct, currboard->filename);
    if (mode == DIR_MODE_ORIGIN && !setboardorigin(currboard->filename, -1)) {
        return NEWDIRECT;
    }

    count = board_regenspecial(currboard->filename, mode, index);

    if (count<0)
	return FULLUPDATE;

    if (count==0) {
        clear();
        move(t_lines-2,0);
        prints("û����������������");
		pressanykey();
		unlink(arg->direct);
		arg->newmode=DIR_MODE_NORMAL;
        setbdir(DIR_MODE_NORMAL, arg->direct, currboard->filename);
    }
    return NEWDIRECT;
}

int search_x(char * b, char * s)
{
    void *hdll;
	typedef int (*iquery_board_func)(char *b, char *s);
	iquery_board_func iquery_board;
    int oldmode;

    oldmode = uinfo.mode;
    modify_user_mode(SERVICES);
    hdll=dlopen("service/libiquery.so",RTLD_NOW);
    if(hdll)
    {
        const char* error;
        if((iquery_board = (iquery_board_func)dlsym(hdll,"iquery_board"))!=NULL)
            iquery_board(b, s);
        else
        if ((error = dlerror()) != NULL)  {
            clear();
            prints ("%s\n", error);
            pressanykey();
        }
        dlclose(hdll);
    }
    modify_user_mode(oldmode);
    return 0;
}

/* etnlegend, 2006.10.06, �����û�������ɾ���¹���... */
int change_mode(struct _select_def *conf,struct fileheader *fh,int mode){
    static char title[32];
    struct read_arg *arg=(struct read_arg*)conf->arg;
    char buf[STRLEN],echo[STRLEN],ans[4];
    if(!mode){
        move(t_lines-2,0);
        clrtoeol();
        prints("%s","�л�ģʽ��: 0)ȡ�� 1)��ժ�� 2)ͬ���� 3)������ 4)ԭ�� 5)ͬ���� 6)����ؼ���");
        move(t_lines-1,0);
        clrtoeol();
        getdata(t_lines-1,12,"7)��������ѡ��"
#ifdef NEWSMTH
            " 8)���澫��������"
#endif /* NEWSMTH */
            " 9)��ɾ���� [1]: ",ans,2,DOECHO,NULL,true);
        switch(ans[0]){
            case '0':
                return FULLUPDATE;
            case '1':
                mode=DIR_MODE_DIGEST;
                break;
            case '2':
                mode=DIR_MODE_THREAD;
                break;
            case '3':
                mode=DIR_MODE_MARK;
                break;
            case '4':
                mode=DIR_MODE_ORIGIN;
                buf[0]=0;
                break;
            case '5':
                mode=DIR_MODE_AUTHOR;
                move(t_lines-2,0);
                clrtoeol();
                move(t_lines-1,0);
                clrtoeol();
                sprintf(echo,"��ϣ��������λ�û�������[%s]: ",fh->owner);
                getdata(t_lines-1,0,echo,buf,IDLEN+2,DOECHO,NULL,true);
                if(!buf[0])
                    strcpy(buf,fh->owner);
                if(!buf[0])
                    return FULLUPDATE;
                break;
            case '6':
                mode=DIR_MODE_TITLE;
                move(t_lines-2,0);
                clrtoeol();
                move(t_lines-1,0);
                clrtoeol();
                snprintf(echo,STRLEN,"��ϣ�����ҵı���ؼ���[%s]: ",title);
                getdata(t_lines-1,0,echo,buf,32,DOECHO,NULL,true);
                if(buf[0])
                    strcpy(title,buf);
                if(!title[0])
                    return FULLUPDATE;
                strcpy(buf,title);
                break;
            case '7':
                mode=DIR_MODE_SUPERFITER;
                break;
#ifdef NEWSMTH
            case '8':
                move(t_lines-2,0);
                clrtoeol();
                move(t_lines-1,0);
                clrtoeol();
                snprintf(echo,STRLEN,"��ϣ�����ҵ�ȫ�Ĺؼ���[%s]: ",title);
                getdata(t_lines-1,0,echo,buf,64,DOECHO,NULL,true);
                if(buf[0])
                    strcpy(title,buf);
                if(title[0]){
                    strcpy(buf,title);
                    search_x(currboard->filename,buf);
                }
                return FULLUPDATE;
#endif /* NEWSMTH */
            case '9':
                mode=DIR_MODE_SELF;
                break;
            default:
                mode=DIR_MODE_NORMAL;
                break;
        }
        if(mode!=DIR_MODE_SUPERFITER&&(arg->mode==DIR_MODE_AUTHOR||arg->mode==DIR_MODE_TITLE||arg->mode==DIR_MODE_SELF))
            unlink(arg->direct);
    }
    switch(mode){
        case DIR_MODE_NORMAL:
        case DIR_MODE_DIGEST:
            return digest_mode(conf,fh,NULL);
        case DIR_MODE_THREAD:
            return title_mode(conf,fh,NULL);
        case DIR_MODE_MARK:
            return marked_mode(conf,fh,NULL);
        case DIR_MODE_ORIGIN:
        case DIR_MODE_AUTHOR:
        case DIR_MODE_TITLE:
            return search_mode(conf,fh,mode,buf);
        case DIR_MODE_SUPERFITER:
            return super_filter(conf,fh,NULL);
        case DIR_MODE_SELF:
            return self_mode(conf,fh,NULL);
        case DIR_MODE_DELETED:
            return deleted_mode(conf,fh,NULL);
        case DIR_MODE_JUNK:
            return junk_mode(conf,fh,NULL);
        default:
            break;
    }
    return DIRCHANGED;
}

#ifdef NEWSMTH
int set_board_rule(struct boardheader *bh, int flag)
{
	int pos;
	struct boardheader newbh;

	pos = getbid(bh->filename, NULL);
	if(!pos) return -1;

    memcpy(&newbh,bh,sizeof(struct boardheader));
	if(flag) newbh.flag|=BOARD_RULES;
	else newbh.flag&=~BOARD_RULES;

    set_board(pos,&newbh,bh);

	if(flag){
		char buf[256];
		char buf2[256];

		setvfile(buf, bh->filename, "rules");
		sprintf(buf2,"%sͨ��%s�ΰ淽��", getCurrentUser()->userid, bh->filename);
		post_file(getCurrentUser(), "", buf, "BoardRules", buf2, 0, 2, getSession());
		post_file(getCurrentUser(), "", buf, "BoardManager", buf2, 0, 2, getSession());
		sprintf(buf2,"���ͨ��%s���ΰ淽��", bh->filename);
		post_file(getCurrentUser(), "", buf, bh->filename, buf2, 0, 2, getSession());
	}
	return 0;
}
#endif

#ifndef USE_PRIMORDIAL_TOP10
static int select_top(void);
#endif /* USE_PRIMORDIAL_TOP10 */

int read_hot_info()
{
    char ans[4];
	char prompt[STRLEN];
    move(t_lines - 1, 0);
    clrtoeol();
    snprintf(prompt,STRLEN,"ѡ��: 1)ʮ���� "
#ifdef HAPPY_BBS
        "2)ϵ���ȵ� "
#else
        "2)ʮ��ף�� "
#endif
        "3)�����ȵ� 4)ϵͳ�ȵ� 5)�����ռ� %s[1]: ",
#ifdef NEWSMTH
        ((uinfo.mode==READING)?"6)�ΰ淽�� ":"")
#else
        ""
#endif
        );
    getdata(t_lines - 1, 0, prompt, ans, 3, DOECHO, NULL, true);
    switch (ans[0])
	{
    case '2':
        show_help("etc/posts/bless");
        break;
    case '3':
		show_help("0Announce/hotinfo");
        break;
    case '4':
		showsysinfo("0Announce/systeminfo");
        pressanykey();
        break;
    case '5':
            if (getCurrentUser()&&!HAS_PERM(getCurrentUser(),PERM_DENYRELAX))
            exec_mbem("@mod:service/libcalendar.so#calendar_main");
        break;
#ifdef NEWSMTH
	case '6':
	{
		char fpath[256];
		struct stat st;
		if(uinfo.mode!=READING)
			break;
		if(!(currboard->flag & BOARD_RULES) && !HAS_PERM(getCurrentUser(),PERM_SYSOP) && (getCurrentUser()->title==0) && !chk_currBM(currBM, getCurrentUser()) ){
			clear();
			move(3,0);
			prints("%s����δ��ͨ����˵��ΰ淽��\n", currboard->filename);
			pressanykey();
			break;
		}
		setvfile(fpath, currboard->filename, "rules");
    	if(stat(fpath, &st)==-1){
			clear();
			move(3,0);
			prints("������δ�ύ�ΰ淽��\n");
			pressanykey();
       		break;
		}
		if(!(currboard->flag & BOARD_RULES)){
			clear();
			move(3,0);
			prints("%s���ΰ淽����δͨ�����,����:%d\n", currboard->filename, st.st_mtime);
			pressanykey();
		}
		show_help(fpath);
		if(!(currboard->flag & BOARD_RULES) && HAS_PERM(getCurrentUser(), PERM_SYSOP) ){
			char ans[4];
			clear();
			move(3,0);
			prints("%s���ΰ淽����δͨ�����,����:%d\n", currboard->filename, st.st_mtime);
        	getdata(t_lines - 1, 0, "��Ҫͨ���ð���ΰ淽���� (Y/N)? [N] ", ans, 3, DOECHO, NULL, true);
			if(ans[0]=='y' || ans[0]=='Y'){
				int ret;
				ret = set_board_rule(currboard, 1);
				move(6,0);
				prints("ͨ��%s:%d\n",(ret==0)?"�ɹ�":"ʧ��",ret);
				pressreturn();
			}
		}
		break;
	}
#endif
	case '1':
	default:

/* etnlegend, 2006.05.30, �Ķ�ʮ�� ... */

#ifndef USE_PRIMORDIAL_TOP10
        select_top();
#else /* USE_PRIMORDIAL_TOP10 */
        show_help("etc/posts/day");
#endif /* USE_PRIMORDIAL_TOP10 */

    }
    return FULLUPDATE;
}

#ifndef NOREPLY
int do_reply(struct _select_def* conf,struct fileheader *fileinfo)
/* reply POST */
{
    char buf[255];

    if (fileinfo==NULL)
        return DONOTHING;
    if (fileinfo->accessed[1] & FILE_READ) {    /*Haohmaru.99.01.01.���²���re */
        clear();
        move(3, 0);
        prints("\n\n            �ܱ�Ǹ�������Ѿ�����Ϊ����reģʽ,�벻Ҫ��ͼ���۱���...\n");
        pressreturn();
        return FULLUPDATE;
    }
    setbfile(buf, currboard->filename, fileinfo->filename);
    strcpy(replytitle, fileinfo->title);
    post_article(conf,buf, fileinfo);
    replytitle[0] = '\0';
    return FULLUPDATE;
}
#endif

int garbage_line(const char *str)
{                               /* �жϱ����Ƿ��� ���õ� */
    int qlevel = 0;

    while (*str == ':' || *str == '>') {
        str++;
        if (*str == ' ')
            str++;
        else
            break;
        if (qlevel++ >= QUOTELEV)
            return 1;
    }
    while (*str == ' ' || *str == '\t')
        str++;
    if (qlevel >= QUOTELEV)
        if (strstr(str, "�ᵽ:\n") || strstr(str, ": ��\n") || strncmp(str, "==>", 3) == 0 || strstr(str, "������ ˵"))
            return 1;
    return (*str == '\n');
}

/* When there is an old article that can be included -jjyang */
void do_quote(char *filepath, char quote_mode, char *q_file, char *q_user)
{                               /* �������£� ȫ�ֱ���quote_file,quote_user, */
    FILE *inf, *outf;
    char *qfile, *quser;
    char buf[256], *ptr;
    char op;
    int bflag;
    int line_count = 0;         /* ��Ӽ���ģʽ���� Bigman: 2000.7.2 */

    qfile = q_file;
    quser = q_user;
    bflag = strncmp(qfile, "mail", 4);  /* �ж����õ������»����� */
    outf = fopen(filepath, "a");
    if (outf==NULL) {
    	bbslog("user","do_quote() fopen(%s):%s",filepath,strerror(errno));
    	return;
    }
    if (*qfile != '\0' && (inf = fopen(qfile, "rb")) != NULL) {  /* �򿪱������ļ� */
        op = quote_mode;
		if (op != 'N') {        /* ����ģʽΪ N ��ʾ ������ */
            skip_attach_fgets(buf, 256, inf);
            /* ȡ����һ���� ���������µ� ������Ϣ */
            if ((ptr = strrchr(buf, ')')) != NULL) {    /* ��һ��':'�����һ�� ')' �е��ַ��� */
                ptr[1] = '\0';
                if ((ptr = strchr(buf, ':')) != NULL) {
                    quser = ptr + 1;
                    while (*quser == ' ')
                        quser++;
                }
            }
            /*---	period	2000-10-21	add '\n' at beginning of Re-article	---*/
            if (bflag)
                fprintf(outf, "\n�� �� %s �Ĵ������ᵽ: ��\n", quser);
            else
                fprintf(outf, "\n�� �� %s ���������ᵽ: ��\n", quser);

            if (op == 'A') {    /* ����һ���⣬ȫ������ */
				int enterflag=1;
                while (skip_attach_fgets(buf, 256, inf) != 0) {
					if(enterflag)
                    	fprintf(outf, ": %s", buf);
					else
                    	fprintf(outf, "%s", buf);
                    if(buf[strlen(buf)-1]!='\n')
						enterflag=0;
					else
						enterflag=1;
                }
            } else if (op == 'R') {
                while (skip_attach_fgets(buf, 256, inf) != 0)
                    if (buf[0] == '\n')
                        break;
                while (skip_attach_fgets(buf, 256, inf) != 0) {
                    if (Origin2(buf))   /* �ж��Ƿ� ������� */
                        continue;
                    fprintf(outf, "%s", buf);

                }
            } else {
                while (skip_attach_fgets(buf, 256, inf) != 0)
                    if (buf[0] == '\n')
                        break;
                while (skip_attach_fgets(buf, 256, inf) != 0) {
                    if (strcmp(buf, "--\n") == 0)       /* ���� ��ǩ����Ϊֹ */
                        break;
                    if(buf[strlen(buf)-1]!='\n') {
                        char ch;
                        while((ch=fgetc(inf))!=EOF)
                            if(ch=='\n') break;
                    }
                    if (buf[250] != '\0')
                        strcpy(buf + 250, "\n");
                    if (!garbage_line(buf)) {   /* �ж��Ƿ��������� */
                        fprintf(outf, ": %s", buf);
#if defined(QUOTED_LINES) && QUOTED_LINES >= 3
                        if (op == 'S') {        /* ����ģʽ,ֻ����ǰ���� Bigman:2000.7.2 */
                            line_count++;
                            if (line_count >= QUOTED_LINES) {
                                fprintf(outf, ": ...................");
                                break;
                            }
                        }
#endif
                    }
                }
            }
        }

        fprintf(outf, "\n");
        fclose(inf);
    }
    /*
     * *q_file = '\0';
     * *q_user = '\0';
     */

    if ((getSession()->currentmemo->ud.signum > 0) && !(getCurrentUser()->signature == 0 || Anony == 1)) {       /* ǩ����Ϊ0����� */
        if (getCurrentUser()->signature < 0)
            addsignature(outf, getCurrentUser(), (rand() % getSession()->currentmemo->ud.signum) + 1);
        else
            addsignature(outf, getCurrentUser(), getCurrentUser()->signature);
    }
    fclose(outf);
}

int do_post(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
{                               /* �û�post */
#ifndef NOREPLY
    *replytitle = '\0';
#endif
    *quote_user = '\0';
    return post_article(conf,"", NULL);
}

int post_reply(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
        /*
         * ���Ÿ�POST���� 
         */
{
    char uid[STRLEN];
    char title[STRLEN];
    char *t;
    FILE *fp;
    char q_file[STRLEN];


    if (fileinfo==NULL)
        return DONOTHING;
    if (!HAS_PERM(getCurrentUser(), PERM_LOGINOK) || !strcmp(getCurrentUser()->userid, "guest"))  /* guest ��Ȩ */
        return 0;
    /*
     * ̫���˰�,����post�Ͳ��û�����
     * if (!HAS_PERM(getCurrentUser(),PERM_POST)) return; Haohmaru.99.1.18 
     */

    /*
     * ���Mail Bigman:2000.8.22 
     */
    if (HAS_PERM(getCurrentUser(), PERM_DENYMAIL)) {
        clear();
        move(3, 10);
        prints("�ܱ�Ǹ,��Ŀǰû��MailȨ��!");
        pressreturn();
        return FULLUPDATE;
    }

    modify_user_mode(SMAIL);

    /*
     * indicate the quote file/user 
     */
    setbfile(q_file, currboard->filename, fileinfo->filename);
    strncpy(quote_user, fileinfo->owner, OWNER_LEN);
    quote_user[OWNER_LEN - 1] = 0;

    /*
     * find the author 
     */
    if (strchr(quote_user, '.')) {
        genbuf[0] = '\0';
        fp = fopen(q_file, "rb");
        if (fp != NULL) {
            skip_attach_fgets(genbuf, 255, fp);
            fclose(fp);
        }

        t = strtok(genbuf, ":");
        if (strncmp(t, "������", 6) == 0 || strncmp(t, "Posted By", 9) == 0 || strncmp(t, "��  ��", 6) == 0) {
            t = (char *) strtok(NULL, " \r\t\n");
            strcpy(uid, t);
        } else {
            prints("Error: Cannot find Author ... \n");
            pressreturn();
        }
    } else
        strcpy(uid, quote_user);

    /*
     * make the title 
     */
    if (toupper(fileinfo->title[0]) != 'R' || fileinfo->title[1] != 'e' || fileinfo->title[2] != ':')
        strcpy(title, "Re: ");
    else
        title[0] = '\0';
    strncat(title, fileinfo->title, ARTICLE_TITLE_LEN - 5);

    clear();

    switch (do_send(uid, title, q_file)) {
    case -1:
        prints("ϵͳ�޷�����\n");
        break;
    case -2:
        prints("���Ŷ����Ѿ���ֹ\n");
        break;
    case -3:
        prints("ʹ���� '%s' �޷�����\n", uid);
        break;
    case -4:
        prints("�Է������������޷�����\n");
        break;
    default:
        prints("�ż��ѳɹ��ؼĸ�ԭ���� %s\n", uid);
    }
	/* �ָ� in_mail ����ԭ����ֵ.
	 * do_send() �������, �ͼ��������, by flyriver, 2003.5.9 */
    pressreturn();
    return FULLUPDATE;
}

static int show_board_notes(char bname[30], int all)
{                               /* ��ʾ�����Ļ� */
    char buf[256];

    sprintf(buf, "vote/%s/notes", bname);       /* ��ʾ����İ����Ļ� vote/����/notes */
    if (dashf(buf)) {
		if(all)
        	ansimore2(buf, false, 0, 0);
		else
        ansimore2(buf, false, 0, 23 /*19 */ );
        return 1;
    } else if (dashf("vote/notes")) {   /* ��ʾϵͳ�Ļ� vote/notes */
        ansimore2("vote/notes", false, 0, 23 /*19 */ );
        return 1;
    }
    return -1;
}

int add_attach(char* file1, char* file2, char* filename)
{
    FILE* fp,*fp2;
    struct stat st;
    uint32_t size;
    char o[8]={0,0,0,0,0,0,0,0};
    char buf[1024*16];
    int i;
    if(stat(file2, &st)==-1)
        return 0;
    if(st.st_size>=MAXATTACHMENTSIZE&&!HAS_PERM(getCurrentUser(), PERM_SYSOP)) {
        unlink(file2);
        return 0;
    }
    size=htonl(st.st_size);
    fp2=fopen(file2, "rb");
    if(!fp2) return 0;
    fp=fopen(file1, "ab");
    fwrite(o,1,8,fp);
    filename = filter_upload_filename(filename);
    fwrite(filename, 1, strlen(filename)+1, fp);
    fwrite(&size,1,4,fp);
    while((i=fread(buf,1,1024*16,fp2))) {
        fwrite(buf,1,i,fp);
    }
    
    fclose(fp2);
    fclose(fp);
    unlink(file2);
    return st.st_size;
}

int process_upload(int nUpload, int maxShow, char *ans, struct ea_attach_info* ai)
{
    char buf[256], ses[20];
    char *upload = NULL;
    int i, totalsize = 0;

    if (ans[0] == 'U' && nUpload<MAXATTACHMENTCOUNT) {
        chdir("tmp");
        upload = bbs_zrecvfile();
        chdir("..");
        if (upload != NULL && *upload) {
            char uploaded_file[PATHLEN];
            snprintf(uploaded_file, PATHLEN, "tmp/%s", upload);
            upload_add_file(uploaded_file, upload, getSession());
        }
    } else if (ans[0] == 'u' && ans[1]) {
        int att = atoi(ans+1);
        if (att > 0 && att <= nUpload) {
            upload_del_file(ai[att-1].name, getSession());
        }
    }

    get_telnet_sessionid(ses, getSession()->utmpent);
    snprintf(buf, sizeof(buf), "�����ϴ���ַ: (���𽫴����ӷ��͸�����)\n\033[4mhttp://%s/bbsupload.php?sid=%s\033[m\n", get_my_webdomain(0), ses);
    clear();
    prints(buf);
    nUpload = upload_read_fileinfo(ai, getSession());
    prints("%s", "���ϴ������б�: (�� \033[1;32mu\033[m ˢ��, \033[1;32mu<���>\033[m ɾ����Ӧ��Ÿ���"
#ifdef SSHBBS
        ", \033[1;32mU\033[m zmodem �ϴ�"
#endif
        ")\n");
    for(i=0;i<nUpload;i++) {
        if (i>=nUpload-maxShow) {
            snprintf(buf, sizeof(buf), "[%02d] %-60.60s (%7d �ֽ�)\n", i+1, ai[i].name, ai[i].size);
            prints("%s", buf);
        }
        totalsize += ai[i].size;
    }
    if (nUpload > maxShow) {
        snprintf(buf, sizeof(buf), "������������ %d ����ǰ��Ĳ���ʾ�ˣ�\n", maxShow);
        prints("%s", buf);
    }
    snprintf(buf, sizeof(buf), "\n\033[1;36m�� \033[1;37m%d\033[1;36m ����/�� \033[1;37m%d\033[1;36m �ֽ�\033[m"
             " \033[1;36m(���� \033[1;37m%d\033[1;36m ����/ \033[1;37m%d\033[1;36m �ֽ�)\033[m\n",
             nUpload, totalsize, MAXATTACHMENTCOUNT, MAXATTACHMENTSIZE);
    prints("%s", buf);
    check_upload = 1;
    return nUpload;
}

int post_article(struct _select_def* conf,char *q_file, struct fileheader *re_file)
{                               /*�û� POST ���� */
    struct fileheader post_file;
    char filepath[STRLEN];
    char buf[256], buf2[256], buf3[STRLEN], buf4[STRLEN];
//	char tmplate[STRLEN];
	int use_tmpl=0;
    int aborted, anonyboard;
    int replymode = 1;          /* Post New UI */
    char ans[8], ooo, include_mode = 'S';
    struct boardheader *bp;
    long eff_size;/*����ͳ�����µ���Ч����*/
    int nUpload = 0;
    struct ea_attach_info ai[MAXATTACHMENTCOUNT];
    int mailback = 0;		/* stiger,�ظ������� */
	int ret = DIRCHANGED;

    char direct[PATHLEN];
    int cmdmode;
#ifdef FILTER
    int returnvalue;
#endif

    check_upload = 0;

    if (conf!=NULL)  {
        struct read_arg* arg;
        arg=(struct read_arg*)conf->arg;
        cmdmode=arg->mode;
    }
    else {
	cmdmode=DIR_MODE_NORMAL;
    }

    if (true == check_readonly(currboard->filename))      /* Leeward 98.03.28 */
        return FULLUPDATE;

#ifdef AIX_CANCELLED_BY_LEEWARD
    if (true == check_RAM_lack())       /* Leeward 98.06.16 */
        return FULLUPDATE;
#endif

#ifdef HAPPY_BBS
    anonyboard = anonymousboard(currboard->filename);
    if (!anonyboard)
#endif
    modify_user_mode(POSTING);
    setbdir(DIR_MODE_NORMAL, direct, currboard->filename);
    if(!((cmdmode==DIR_MODE_MARK)||(cmdmode==DIR_MODE_THREAD)||(cmdmode==DIR_MODE_NORMAL)||(cmdmode==DIR_MODE_TOP10)))
    {
        move(3, 0);
        clrtobot();
        prints("\n\n     Ŀǰ����ժ������ģʽ, ���Բ��ܷ�������.(���س��뿪)\n");
        pressreturn();
        clear();
        return FULLUPDATE;
    }
    if (!haspostperm(getCurrentUser(), currboard->filename)) { /* POSTȨ�޼�� */
        move(3, 0);
        clrtobot();
            prints("\n\n        ����������Ψ����, ����������Ȩ���ڴ˷�������.\n");
            prints("        �������δע�ᣬ���ڸ��˹���������ϸע�����\n");
            prints("        δͨ�����ע����֤���û���û�з������µ�Ȩ�ޡ�\n");
            prints("        лл������ :-) \n");
        pressreturn();
        clear();
        return FULLUPDATE;
#ifdef NEWSMTH
    }
    else if(!check_score_level(getCurrentUser(),currboard)){
        move(3,0);
        clrtobot();
        prints("\n\n    \033[1;33m%s\033[0;33m<Enter>\033[m",
            "���Ļ��ֲ����ϵ�ǰ���������趨, ��ʱ�޷��ڵ�ǰ��������������...");
        WAIT_RETURN;
        return FULLUPDATE;
#endif /* NEWSMTH */
    } else if (deny_me(getCurrentUser()->userid, currboard->filename)) { /* ������ֹPOST ��� */
		if( !HAS_PERM(getCurrentUser(), PERM_SYSOP) ){
        	move(3, 0);
        	clrtobot();
            prints("\n\n\t\t���ѱ�������Աȡ���ڵ�ǰ����ķ���Ȩ��...\n");
        	pressreturn();
        	clear();
        	return FULLUPDATE;
		}else{
			clear();
            getdata(3,0,"���ѱ�ȡ���ڵ�ǰ����ķ���Ȩ��, �Ƿ�ǿ�Ʒ���? [y/N]: ",buf,2,DOECHO,NULL,true);
			if( buf[0]!='y' && buf[0]!='Y' ){
				clear();
				return FULLUPDATE;
			}
		}
    }

    memset(&post_file, 0, sizeof(post_file));
//	tmplate[0]='\0';
    clear();
    show_board_notes(currboard->filename, 0);        /* �����Ļ� */
#ifndef NOREPLY                 /* title�Ƿ���Re: */
    if (replytitle[0] != '\0') {
        buf4[0] = ' ';
        /*
         * if( strncasecmp( replytitle, "Re:", 3 ) == 0 ) Change By KCN:
         * why use strncasecmp? 
         */
        if (strncmp(replytitle, "Re:", 3) == 0)
            strcpy(buf, replytitle);
        else if (strncmp(replytitle, "�� ", 3) == 0)
            sprintf(buf, "Re: %s", replytitle + 3);
        else if (strncmp(replytitle, "�� ", 3) == 0)
            sprintf(buf, "Re: %s", replytitle + 3);
        else
            sprintf(buf, "Re: %s", replytitle);
        buf[78] = '\0';
    } else
#endif
    {
        buf[0] = '\0';
        buf4[0] = '\0';
        replymode = 0;
    }
    if (getCurrentUser()->signature > getSession()->currentmemo->ud.signum)      /*ǩ����No.��� */
        getCurrentUser()->signature = (getSession()->currentmemo->ud.signum == 0) ? 0 : 1;
    anonyboard = anonymousboard(currboard->filename);     /* �Ƿ�Ϊ������ */
    if (!strcmp(currboard->filename, "Announce"))
        Anony = 1;
    else if (anonyboard)
        Anony = ANONYMOUS_DEFAULT;
    else
        Anony = 0;

#ifdef FREE
#define RAND_SIG_KEY 'X'
#define RAND_SIG_KEYS "X"
#define ANONY_KEY 'L'
#define ANONY_KEYS "L"
#else
#define RAND_SIG_KEY 'L'
#define RAND_SIG_KEYS "L"
#define ANONY_KEY 'M'
#define ANONY_KEYS "M"
#endif

    while (1) {                 /* ����ǰ�޸Ĳ����� ���Կ������'��ʾǩ����' */
        sprintf(buf3, "����ģʽ [%c]", include_mode);
        move(t_lines - 4, 0);
        clrtoeol();
        prints("\033[m���������� %s ������  %s %s %s\n", currboard->filename, (anonyboard) ? (Anony == 1 ? "\033[1mҪ\033[mʹ������" : "\033[1m��\033[mʹ������") : "", mailback? "�ظ�������":"",use_tmpl?"ʹ��ģ��":"");
        clrtoeol();
        prints("ʹ�ñ���: %s\n", (buf[0] == '\0') ? "[�����趨����]" : buf);

        if (buf4[0] == '\0' || buf4[0] == '\n') {
        	clrtoeol();
        	if (getCurrentUser()->signature < 0)
            	prints("ʹ�����ǩ����     %s", (replymode) ? buf3 : " ");
        	else
            	prints("ʹ�õ� %d ��ǩ����     %s", getCurrentUser()->signature, (replymode) ? buf3 : " ");

            move(t_lines - 1, 0);
            clrtoeol();
            strcpy(buf4, buf);
            getdata(t_lines - 1, 0, "����: ", buf4, 79, DOECHO, NULL, false);
            if ((buf4[0] == '\0' || buf4[0] == '\n')) {
                if (buf[0] != '\0') {
                    buf4[0] = ' ';
                    continue;
                } else
                    return FULLUPDATE;
            }
            strcpy(buf, buf4);
            continue;
        }

       	clrtoeol();
       	if (getCurrentUser()->signature < 0)
			strcpy(buf2, "ʹ�����ǩ����");
       	else
           	sprintf(buf2, "ʹ�õ� %d ��ǩ����", getCurrentUser()->signature);
		prints("%s  %s ��\033[1;32m0\033[m~\033[1;32m%d/V/" RAND_SIG_KEYS "\033[mѡ/��/���ǩ����", buf2, (replymode) ? buf3 : " ", getSession()->currentmemo->ud.signum);

        move(t_lines - 1, 0);
        clrtoeol();
        /*
         * Leeward 98.09.24 add: viewing signature(s) while setting post head 
         */
        sprintf(buf2, "%s��\033[1;32mb\033[m�ظ������䣬\033[1;32mT\033[m�ı��⣬%s%s%s\033[1;32mEnter\033[m����: ", 
                (replymode) ? "\033[1;32mS/Y/N/R/A\033[m ������ģʽ" : "\033[1;32mP\033[mʹ��ģ��", (anonyboard) ? "\033[1;32m" ANONY_KEYS "\033[m������" : "",
				(currboard->flag&BOARD_ATTACH)?"\033[1;32mu\033[m������, ":"", "\033[1;32mQ\033[m����, ");
        getdata(t_lines - 1, 0, buf2, ans, 4, DOECHO, NULL, true);
        ooo = toupper(ans[0]);       /* Leeward 98.09.24 add; delete below toupper */
        if ((ooo - '0') >= 0 && ooo - '0' <= 9) {
            int ii = atoi(ans);
            if (ii <= getSession()->currentmemo->ud.signum)
                getCurrentUser()->signature = ii;
        } else if ((ooo == 'S' || ooo == 'Y' || ooo == 'N' || ooo == 'A' || ooo == 'R') && replymode) {
            include_mode = ooo;
        } else if (ooo == 'T') {
            buf4[0] = '\0';
		} else if (ooo == 'P') {
			if( use_tmpl >= 0)
				use_tmpl = use_tmpl ? 0 : 1;
				/*
			if( replymode == 0 ){
				choose_tmpl( tmplate );
				clear();
				if( tmplate[0] ){
					setbfile( buf2, currboard->filename, tmplate );
					ansimore2(buf2, false, 0, 18);
				}
			}*/
		} else if (ooo == 'B') {
			/* if( replymode == 0 ) */
				mailback = mailback ? 0 : 1;
        } else if (ooo == ANONY_KEY) {
            Anony = (Anony == 1) ? 0 : 1;
        } else if (ooo == RAND_SIG_KEY) {
            getCurrentUser()->signature = -1;
		} else if (ooo == 'Q') {
        	return FULLUPDATE;
        } else if (ooo == 'V') {     /* Leeward 98.09.24 add: viewing signature(s) while setting post head */
            sethomefile(buf2, getCurrentUser()->userid, "signatures");
            move(t_lines - 1, 0);
            if (askyn("Ԥ����ʾǰ����ǩ����, Ҫ��ʾȫ����", false) == true)
                ansimore(buf2, 0);
            else {
                clear();
                ansimore2(buf2, false, 0, 18);
            }
        } else if (ooo == 'U') {
            if(currboard->flag&BOARD_ATTACH || HAS_PERM(getCurrentUser(),PERM_SYSOP)) {
                nUpload = process_upload(nUpload, 10, ans, ai);
            }
        } else {
            /*
             * Changed by KCN,disable color title 
             */
            process_control_chars(buf);
            strnzhcpy(post_file.title, buf, ARTICLE_TITLE_LEN);
            strcpy(save_title, post_file.title);
            if (save_title[0] == '\0') {
                return FULLUPDATE;
            }
            break;
        }
    }                           /* ������� */

    setbfile(filepath, currboard->filename, "");
    if ((aborted = GET_POSTFILENAME(post_file.filename, filepath)) != 0) {
        move(3, 0);
        clrtobot();
        prints("\n\n�޷������ļ�:%d...\n", aborted);
        pressreturn();
        clear();
        return FULLUPDATE;
    }

    in_mail = false;

    /*
     * strncpy(post_file.owner,(anonyboard&&Anony)?
     * "Anonymous":getCurrentUser()->userid,STRLEN) ;
     */
    strncpy(post_file.owner, (anonyboard && Anony) ? currboard->filename : getCurrentUser()->userid, OWNER_LEN);
    post_file.owner[OWNER_LEN - 1] = 0;

	/* �ظ������䣬stiger */
	if(mailback)
		post_file.accessed[1] |= FILE_MAILBACK;

    /*
     * if ((!strcmp(currboard,"Announce"))&&(!strcmp(post_file.owner,"Anonymous")))
     * strcpy(post_file.owner,"SYSOP");
     */

    if ((!strcmp(currboard->filename, "Announce")) && (!strcmp(post_file.owner, currboard->filename)))
        strcpy(post_file.owner, "SYSOP");

    setbfile(filepath, currboard->filename, post_file.filename);

    bp=currboard;
    if (bp->flag & BOARD_OUTFLAG) {
        local_article = 0;
        /* �ظ� local_save ������Ĭ�� local_save - atppp */
        if (re_file) local_article = (re_file->innflag[0]=='L' && re_file->innflag[1]=='L')?2:0;
    } else
        local_article = 1;
    if (!strcmp(post_file.title, buf) && q_file[0] != '\0')
        if (q_file[119] == 'L') /* FIXME */
            local_article = 1; //����ط�̫�����ˣ���ȫ����������֪���� local_save ��ʲôӰ�졣

#ifdef HAPPY_BBS
    if (!anonyboard)
#endif
    modify_user_mode(POSTING);

	if( use_tmpl > 0 ){
		FILE *fp,*fp1;
		char filepath1[STRLEN];
		char buff[256];
		char title_prefix[STRLEN];

		if( ! strncmp(post_file.title, "Re: ",4) )
			strncpy(title_prefix, post_file.title+4, ARTICLE_TITLE_LEN);
		else
			strncpy(title_prefix, post_file.title, ARTICLE_TITLE_LEN);

		title_prefix[STRLEN-1]='\0';

		sprintf(filepath1,"%s.mbak",filepath);
		aborted =  choose_tmpl( title_prefix , filepath ) ;
		if(aborted != -1){
			if( (fp=fopen( filepath1,"w"))!=NULL){
				if((fp1=fopen(filepath,"r"))==NULL){
					aborted = -1;
				}else{
					if( title_prefix[0] ){
						if( ! strncmp(post_file.title, "Re: ",4) )
							snprintf(save_title, ARTICLE_TITLE_LEN, "Re: %s", title_prefix );
						else
							snprintf(save_title, ARTICLE_TITLE_LEN , "%s", title_prefix );

						save_title[ARTICLE_TITLE_LEN-1]='\0';
            			strnzhcpy(post_file.title, save_title, ARTICLE_TITLE_LEN);
					}

					write_header(fp, getCurrentUser(), 0, currboard->filename, post_file.title, Anony, 0,getSession());
					while(fgets(buff,255,fp1))
						fprintf(fp,"%s",buff);

					fclose(fp);
					fclose(fp1);

					unlink(filepath);
					rename(filepath1,filepath);

				}
			}else{
				aborted = -1;
			}
		}
	}
				

	do_quote(filepath, include_mode, q_file, quote_user);       /*����ԭ���� */

    strnzhcpy(quote_title, save_title, sizeof(quote_title));
    strcpy(quote_board, currboard->filename);

    if( use_tmpl <= 0 )
        aborted = vedit(filepath, true, &eff_size, NULL, 1);    /* ����༭״̬ */
    else{
        add_loginfo(filepath, getCurrentUser(), currboard->filename, Anony,getSession());
        eff_size = get_effsize(filepath);
    }

    post_file.eff_size = eff_size;

    strnzhcpy(post_file.title, save_title, ARTICLE_TITLE_LEN);
    if (aborted == 1 || !(bp->flag & BOARD_OUTFLAG)) {  /* local save */
        post_file.innflag[1] = 'L';
        post_file.innflag[0] = 'L';
    } else if (aborted == 0) {
        post_file.innflag[1] = 'S';
        post_file.innflag[0] = 'S';
        outgo_post(&post_file, currboard->filename, save_title,getSession());
    }

    if (aborted == -1) {        /* ȡ��POST */
        my_unlink(filepath);
        clear();
        return FULLUPDATE;
    }
    /*
     * ��boards����������Զ�������±�� Bigman:2000.8.12 
     */
    if (!strcmp(currboard->filename, "BM_Apply") && !HAS_PERM(getCurrentUser(), PERM_OBOARDS) && HAS_PERM(getCurrentUser(), PERM_BOARDS)) {
        post_file.accessed[0] |= FILE_SIGN;
    }
    if(check_upload) {
        FILE *fp;
        if ((fp = fopen(filepath, "ab")) != NULL) {
            upload_post_append(fp, &post_file, getSession());
        }
        fclose(fp);
    }
#ifdef FILTER
    returnvalue =
#endif
        after_post(getCurrentUser(), &post_file, currboard->filename, re_file, !(Anony && anonyboard),getSession());
    Anony = 0;                  /*Inital For ShowOut Signature */
    
    if (!junkboard(currboard->filename)) {
        getCurrentUser()->numposts++;
    }
#ifdef FILTER
    if (returnvalue == -2) {
        clear();
        move(3, 0);
        prints("\n\n        �ܱ�Ǹ�����Ŀ��ܺ��в������ݣ��辭��˷��ɷ���\n\n"
                   "        ���ݡ��ʺŹ���취������ϵͳ���˵�������ͬ�������������ĵȴ�\n"
                   "    վ����Ա����ˣ���Ҫ��γ��Է�������¡�\n\n"
                   "        �������ʣ������� SYSOP ��ѯ��");
        pressreturn();
        return FULLUPDATE;
    }
#endif
    switch (cmdmode) {
    case 2:
        ret=title_mode(conf,NULL,NULL);
        break;
    case 3:
        ret=marked_mode(conf,NULL,NULL);
        break;
    }
	if (ret==NEWDIRECT)
		return ret;
    return DIRCHANGED;
}

int edit_post(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
        /*
         * POST �༭
         */
{
    char buf[512];
    char *t;
    long eff_size;
    long attachpos;
    bool dobmlog=false;
    struct read_arg* arg=(struct read_arg*) conf->arg;
    int ret;
    
    ret = deny_modify_article(currboard, fileinfo, arg->mode, getSession());
    if (ret) {
        switch(ret) {
        case -2:
            move(3, 0);
            clrtobot();
            prints("\n\n\t\t���ѱ�������Աȡ���ڵ�ǰ����ķ���Ȩ��...\n");
            pressreturn();
            clear();
            return FULLUPDATE;
            break;
        case -5:
            check_readonly(currboard->filename);
            return FULLUPDATE;
            break;
        default:
            return DONOTHING;
            break;
        }
    }

#ifdef AIX_CANCELLED_BY_LEEWARD
    if (true == check_RAM_lack())       /* Leeward 98.06.16 */
        return FULLUPDATE;
#endif

    modify_user_mode(EDIT);

    if (!HAS_PERM(getCurrentUser(), PERM_SYSOP) && chk_currBM(currBM, getCurrentUser()))
        dobmlog=true;

    clear();
    strcpy(buf, arg->direct);
    if ((t = strrchr(buf, '/')) != NULL)
        *t = '\0';
#if 0 /* #ifndef LEEWARD_X_FILTER removed by atppp */
    sprintf(genbuf, "/bin/cp -f %s/%s tmp/%d.editpost.bak", buf, fileinfo->filename, getpid()); /* Leeward 98.03.29 */
    system(genbuf);
#endif

    sprintf(genbuf, "%s/%s", buf, fileinfo->filename);
	strncpy(buf, genbuf, 512);
	buf[511]=0;
	attachpos = fileinfo->attachment;
    if (vedit_post(buf, false, &eff_size,&attachpos) != -1) {
		int changemark=0;
		if (attachpos != fileinfo->attachment){
            fileinfo->attachment=attachpos;
			changemark |= FILE_ATTACHPOS_FLAG;
		}
        if (eff_size!=fileinfo->eff_size) {
        	fileinfo->eff_size = eff_size;
			changemark |= FILE_EFFSIZE_FLAG;
		}
		if (changemark){
            struct write_dir_arg dirarg;
            init_write_dir_arg(&dirarg);
            dirarg.fd=arg->fd;
            dirarg.ent = conf->pos;
            change_post_flag(&dirarg, arg->mode, currboard,  
                fileinfo,changemark, fileinfo,dobmlog,getSession());
            free_write_dir_arg(&dirarg);
        }
        if (ADD_EDITMARK)
            add_edit_mark(buf, 0, /*NULL*/ fileinfo->title,getSession());
    }
    newbbslog(BBSLOG_USER, "edited post '%s' on %s", fileinfo->title, currboard->filename);
    return FULLUPDATE;
}

int edit_title(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
        /*
         * �༭���±��� 
         */
{
    char buf[STRLEN];
    struct read_arg* arg=(struct read_arg*) conf->arg;
    int ent=conf->pos;

    /*
     * Leeward 99.07.12 added below 2 variables 
     */
    long i=0;
    struct fileheader xfh;
    int fd, ret;

    ret = deny_modify_article(currboard, fileinfo, arg->mode, getSession());
    if (ret) {
        switch(ret) {
        case -2:
            move(3, 0);
            clrtobot();
            prints("\n\n\t\t���ѱ�������Աȡ���ڵ�ǰ����ķ���Ȩ��...\n");
            pressreturn();
            clear();
            return FULLUPDATE;
            break;
        case -5:
            check_readonly(currboard->filename);
            return FULLUPDATE;
            break;
        default:
            return DONOTHING;
            break;
        }
    }

    strcpy(buf, fileinfo->title);
    getdata(t_lines - 1, 0, "�����±���: ", buf, 78, DOECHO, NULL, false);      /*������� */
    if (buf[0] != '\0'&&strcmp(buf,fileinfo->title)) {
        char tmp[STRLEN * 2], *t;
        char tmp2[STRLEN];      /* Leeward 98.03.29 */

#ifdef FILTER
        if (check_badword_str(buf, strlen(buf), getSession())) {
            clear();
            move(3, 0);
            outs("     �ܱ�Ǹ���ñ�����ܺ��в�ǡ�������ݣ�����ϸ��黻�����⡣");
            pressreturn();
            return PARTUPDATE;
        }
#endif
        strcpy(tmp2, fileinfo->title);  /* Do a backup */
        process_control_chars(buf);
        strnzhcpy(fileinfo->title, buf, ARTICLE_TITLE_LEN);

        strcpy(tmp, arg->direct);
        if ((t = strrchr(tmp, '/')) != NULL)
            *t = '\0';
        sprintf(genbuf, "%s/%s", tmp, fileinfo->filename);

        if(strcmp(tmp2,buf)){
			add_edit_mark(genbuf, 2, buf,getSession());
			newbbslog(BBSLOG_USER,"edit_title %s %s %s",currboard->filename, tmp2 , buf);
		}
        /*
         * Leeward 99.07.12 added below to fix a big bug
         */
		/* add by stiger */
	if(conf->pos>arg->filecount) {
            ent = get_num_records(arg->dingdirect,sizeof(struct fileheader));
            fd = open(arg->dingdirect, O_RDONLY, 0);
	} else fd=arg->fd;
	/* add end */
	if (fd!=-1) {
            for (i = ent; i > 0; i--) {//todo: dingdirect needn't read,should read from boardstatus
                if (0 == get_record_handle(fd, &xfh, sizeof(xfh), i)) {
                    if (0 == strcmp(xfh.filename, fileinfo->filename)) {
                        ent = i;
                        break;
                    }
                }
            }
	    if(conf->pos>arg->filecount) {
                close(fd);
                if (i!=0) 
                    substitute_record(arg->dingdirect, fileinfo, sizeof(*fileinfo), ent);
                board_update_toptitle(arg->bid, true);
            } else
                if (i!=0) 
                    substitute_record(arg->direct, fileinfo, sizeof(*fileinfo), ent);
        }
        if (0 == i)
            return PARTUPDATE;
        /*
         * Leeward 99.07.12 added above to fix a big bug
         */

		if(arg->mode != DIR_MODE_ORIGIN && fileinfo->id == fileinfo->groupid){
			if( setboardorigin(currboard->filename, -1) ){
				board_regenspecial(currboard->filename,DIR_MODE_ORIGIN,NULL);
			}else{
				char olddirect[PATHLEN];
	    		setbdir(DIR_MODE_ORIGIN, olddirect, currboard->filename);
				if ((fd = open(olddirect, O_RDWR, 0644)) >= 0){
					struct fileheader tmpfh;
					if (get_records_from_id(fd, fileinfo->id, &tmpfh, 1, &ent) == 0){
						close(fd);
					}else{
						close(fd);
   	                	substitute_record(olddirect, fileinfo, sizeof(*fileinfo), ent);
					}
				}
   	     //setboardorigin(currboard->filename, 1);
			}
		}
        setboardtitle(currboard->filename, 1);
    }
    return PARTUPDATE;
}

/* etnlegend, 2006.10.12, �����õ�����... */
int del_ding(struct _select_def *conf,struct fileheader *info,void *varg){
    struct read_arg *arg=(struct read_arg*)conf->arg;
    char ans[4];
    if(arg->mode!=DIR_MODE_NORMAL||!chk_currBM(currBM,getCurrentUser()))
        return DONOTHING;
    getdata((t_lines-1),0,"ɾ���õ�����, ȷ�ϲ��� (Y/N) [N]: ",ans,2,DOECHO,NULL,true);
    if(toupper(ans[0])!='Y')
        return FULLUPDATE;
    if(do_del_ding(currboard->filename,arg->bid,(conf->pos-arg->filecount),info,getSession())==-1){
        move(t_lines-1,0);
        clrtoeol();
        prints("\033[m%s\033[0;33m<Enter>\033[m","���������з�������...");
        WAIT_RETURN;
        return FULLUPDATE;
    }
    return DIRCHANGED;
}

/* stiger, �ö� */
int zhiding_post(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
{
    struct read_arg* arg=(struct read_arg*)conf->arg;
    if (fileinfo==NULL)
        return DONOTHING;
    if(conf->pos>arg->filecount)
        return del_ding(conf,fileinfo,extraarg);
    if (add_top(fileinfo, currboard->filename, 0)!=0)
        return DONOTHING;
    return DIRCHANGED;
}

int noreply_post(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
{
	/* add by stiger ,20030414, �ö�ѡ��*/
    char ans[4];
    int mode=0; /* 0x1����, 0x2:�Ƽ������, 0x4:owner */
	int can=0; /*0x1:noreply  0x2:ding 0x4:commend */
    int ret=FULLUPDATE;
	char buf[100];

#ifdef COMMEND_ARTICLE
    int bnum;
    const struct boardheader *bp;

    bnum = getbid(COMMEND_ARTICLE,&bp);
    if( bnum && chk_currBM(bp->BM, getCurrentUser()) )
		mode |= 0x2 ;
#endif
    if (fileinfo==NULL)
        return DONOTHING;
	if(chk_currBM(currBM, getCurrentUser())) mode |= 0x1;
#if defined(OPEN_NOREPLY) || defined(COMMEND_ARTICLE)
	if(!strcmp(getCurrentUser()->userid, fileinfo->owner)) mode |= 0x4;
#endif


	if( (mode&0x1) 
#ifdef OPEN_NOREPLY
		|| (mode & 0x4)
#endif
	  ) can |= 0x1;

	if(mode & 0x1) can |= 0x2;

#ifdef COMMEND_ARTICLE
	if ( (mode & 0x1) || (mode & 0x2) || (mode & 0x4) ) can |= 0x4;
#endif

	if(can==0) return DONOTHING;

	sprintf(buf,"�л�: 0)ȡ�� %s%s%s[%d]", (can&0x1)?"1)����re��� ":"", (can&0x2)?"2)�ö���� ":"", (can&0x4)?"3)�Ƽ� ":"", (can&0x1)?1:0);

    move(t_lines - 1, 0);
    clrtoeol();
    getdata(t_lines - 1, 0, buf, ans, 3, DOECHO, NULL, true);

    if (ans[0] == ' ') {
        ans[0] = ans[1];
        ans[1] = 0;
    }
    if (ans[0]=='0') return FULLUPDATE;
	else if(ans[0]=='2'){
		if( !(can & 0x2) )
			return FULLUPDATE;
		return zhiding_post(conf,fileinfo,extraarg);
	}
#ifdef COMMEND_ARTICLE
	else if(ans[0]=='3'){
		if(!(can & 0x4))
			return FULLUPDATE;
		return do_commend(conf,fileinfo,extraarg);
	}
#endif
	else{
		if( !(can & 0x1) )
			return FULLUPDATE;
		ret=set_article_flag(conf,fileinfo, FILE_NOREPLY_FLAG);
	}
	return ret;
}

#ifdef DENYANONY
int deny_anony(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
{
	char anonybuf[256];
	struct fileheader tmpfh;
	int ret=0;
	int fd;

    if(!anonymousboard(currboard->filename) || strcmp(currboard->filename, fileinfo->owner))
		return DONOTHING;
	if(!HAS_PERM(getCurrentUser(), PERM_SYSOP))
		return DONOTHING;
	setbfile(anonybuf, currboard->filename, ".ANONYDIR");
	if ((fd = open(anonybuf, O_RDWR, 0644)) >= 0){
		ret = get_records_from_id(fd, fileinfo->id, &tmpfh, 1, NULL);
		close(fd);

		if(ret != 0){
			ret = giveup_addpost(tmpfh.owner);
			if(ret){
				char title[80];
				char buff[256];
				FILE *fp;
				sprintf(buff,"tmp/%s.%d.ad", getCurrentUser()->userid, (int)getpid());
				if((fp=fopen(buff,"w"))==NULL){
					ret = 0;
				}else{
					fprintf(fp,"%s :\n\n",tmpfh.owner);
					fprintf(fp,"��������\033[1;31m%s\033[m�����������\033[1;31m%s\033[m,%s����׷��ȡ������ȫվpostȨ��1��\n",currboard->filename,fileinfo->title,getCurrentUser()->userid);
					fprintf(fp,"\n����������������������Ǳ�ǿ�ƽ䷢��Ȩ��1�죬�������ģ�û����֪����������˾������㣬վ���Ͱ���Ҳ����֪��\n");
					fclose(fp);

					sprintf(title,"%sȡ��%s����������ԭid����Ȩ��1��", getCurrentUser()->userid, currboard->filename);
					mail_file("SYSOP", buff, tmpfh.owner, title, BBSPOST_COPY, NULL);
					unlink(buff);
					securityreport(title, NULL,NULL, getSession());
				}
			}
		}
	}

	clear();
	if(ret)
		prints("�ɹ�\n");
	else
		prints("ʧ��\n");
	pressanykey();

	return FULLUPDATE;
}
#endif

int del_post(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
{
    char direct[MAXPATH];
    struct read_arg* arg=conf->arg;
    int ent,ret;
    int flag=(POINTDIFF)extraarg;
    struct write_dir_arg delarg;

    if (fileinfo==NULL)
        return DONOTHING;
    ent=conf->pos;
    /* add by stiger */
    if (ent>arg->filecount)
        return del_ding(conf,fileinfo,extraarg);

    if (arg->mode== DIR_MODE_DELETED|| arg->mode== DIR_MODE_JUNK)
        return DONOTHING;

    if (deny_del_article(currboard, fileinfo, getSession())) {
        return DONOTHING;
    }

    if (!(flag&ARG_NOPROMPT_FLAG)) {
        char buf[STRLEN];
        a_prompt(-1, "ɾ�����£�ȷ����(Y/N) [N] ", buf);
        if (buf[0] != 'Y' && buf[0] != 'y') {     /* if not yes quit */
            return FULLUPDATE;
        }
    }

    if (arg->writearg==NULL) {
        init_write_dir_arg(&delarg);
        if ((arg->mode!=DIR_MODE_NORMAL)&& arg->mode != DIR_MODE_DIGEST) {
            setbdir(DIR_MODE_NORMAL, direct, currboard->filename);
            delarg.filename=direct;
        } else {
            delarg.fd=arg->fd;
            delarg.ent=conf->pos;
        }
        ret=do_del_post(getCurrentUser(),&delarg,fileinfo,currboard->filename,DIR_MODE_NORMAL,flag,getSession());
        free_write_dir_arg(&delarg);
    } else
        ret=do_del_post(getCurrentUser(),arg->writearg,fileinfo,currboard->filename,DIR_MODE_NORMAL,flag,getSession());
    if (ret != 0) {
        if (!(flag&ARG_NOPROMPT_FLAG)) {
            char buf[STRLEN];
            a_prompt(-1, "ɾ��ʧ��, �밴 Enter ���� << ", buf);
            return FULLUPDATE;
        }
    }
	ret=DIRCHANGED;
    if (!(flag&ARG_BMFUNC_FLAG)&&arg->mode) {
        switch (arg->mode) {
        case DIR_MODE_THREAD:
            ret=title_mode(conf,fileinfo,extraarg);
            break;
        case DIR_MODE_MARK:
            ret=marked_mode(conf,fileinfo,extraarg);
            break;
        case DIR_MODE_ORIGIN:
        case DIR_MODE_AUTHOR:
        case DIR_MODE_TITLE:
            ret=search_mode(conf,fileinfo,arg->mode, search_data);
            break;
        default:
            break;
        }
    }
	if (ret==NEWDIRECT)
		return NEWDIRECT;
    return DIRCHANGED;
}

/* Added by netty to handle post saving into (0)Announce */
int Save_post(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
{
    int ret;
    char filepath[PATHLEN], ans[STRLEN], buf[STRLEN];
    bool append;

    struct read_arg* arg=(struct read_arg*)conf->arg;
    if (!HAS_PERM(getCurrentUser(), PERM_SYSOP))
        if (!chk_currBM(currBM, getCurrentUser()))
            return DONOTHING;
    sprintf(filepath, "tmp/bm.%s", getCurrentUser()->userid);
    append = false;
    if (dashf(filepath)) {
        sprintf(buf, "Ҫ�����ھ��ݴ浵֮����?(Y/N/C) [Y]: ");
        a_prompt(-1, buf, ans);
        if ((ans[0] == 'N' || ans[0] == 'n')) {
            append = false;
        } else if (ans[0] == 'C' || ans[0] == 'c')
            return DONOTHING;
        else {
            append = true;
        }
    }
    ret=a_Save(NULL, currboard->filename, fileinfo, append, arg->direct, conf->pos, getCurrentUser()->userid);
    if (ret) {
        struct write_dir_arg dirarg;
        struct fileheader data;
        init_write_dir_arg(&dirarg);
        dirarg.fd=arg->fd;
        dirarg.ent = conf->pos;
        data.accessed[0]=FILE_IMPORTED;
        change_post_flag(&dirarg, 
            arg->mode,
            currboard, 
            fileinfo, 
            FILE_IMPORT_FLAG, &data, true,getSession());
        free_write_dir_arg(&dirarg);
        sprintf(buf, " �ѽ������´����ݴ浵, �밴 ENTER ���Լ��� << ");
        a_prompt(-1, buf, ans);
	return DIRCHANGED;
    }
    return DONOTHING;
}

/* Semi_save ���������´浽�ݴ浵��ͬʱɾ�����µ�ͷβ Life 1997.4.6 */
int Semi_save(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
{
    int ret;
    char filepath[PATHLEN], ans[STRLEN], buf[STRLEN];
    bool append;
    struct read_arg* arg=(struct read_arg*)conf->arg;
    if (!HAS_PERM(getCurrentUser(), PERM_SYSOP))
        if (!chk_currBM(currBM, getCurrentUser()))
            return DONOTHING;
    sprintf(filepath, "tmp/bm.%s", getCurrentUser()->userid);
    append = false;
    if (dashf(filepath)) {
        sprintf(buf, "Ҫ�����ھ��ݴ浵֮����?(Y/N/C) [Y]: ");
        a_prompt(-1, buf, ans);
        if ((ans[0] == 'N' || ans[0] == 'n')) {
            append = false;
        } else if (ans[0] == 'C' || ans[0] == 'c')
            return DONOTHING;
        else {
            append = true;
        }
    }
    ret=a_SeSave("0Announce", currboard->filename, fileinfo, append,arg->direct,conf->pos,1, getCurrentUser()->userid);
    if (ret) {
        struct write_dir_arg dirarg;
        struct fileheader data;
        init_write_dir_arg(&dirarg);
        dirarg.fd=arg->fd;
        dirarg.ent = conf->pos;
        data.accessed[0]=FILE_IMPORTED;
        change_post_flag(&dirarg, 
            arg->mode,
            currboard, 
            fileinfo, 
            FILE_IMPORT_FLAG, &data, true,getSession());
        free_write_dir_arg(&dirarg);
        sprintf(buf, " �ѽ������´����ݴ浵, �밴 ENTER ���Լ��� << ");
        a_prompt(-1, buf, ans);
	return DIRCHANGED;
   }
    return DONOTHING;
}

/* Added by netty to handle post saving into (0)Announce */
int Import_post(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
{
    char szBuf[STRLEN],*p;
    struct read_arg* arg=(struct read_arg*)conf->arg;
    int ret=FULLUPDATE;

    if (!HAS_PERM(getCurrentUser(), PERM_SYSOP))
        if (!chk_currBM(currBM, getCurrentUser())
#ifdef FB2KPC
			&& !haspc(getCurrentUser()->userid)
#endif
						)
            return DONOTHING;

    if (fileinfo->accessed[0] & FILE_IMPORTED) {        /* Leeward 98.04.15 */
        a_prompt(-1, "������������¼����������. �����ٴ���¼��? (Y/N) [N]: ", szBuf);
        if (szBuf[0] != 'y' && szBuf[0] != 'Y')
            return FULLUPDATE;
    }

    /* etnlegend, 2006.03.31, ��ط���ôŪ�ǲ� nice ��... */
    p=NULL;
    if((arg->mode==DIR_MODE_DELETED||arg->mode==DIR_MODE_JUNK)&&(p=strrchr(fileinfo->title,'-')))
        *p=0;

    /*
     * Leeward 98.04.15 
     */
    if (a_Import(NULL, currboard->filename, fileinfo, false, arg->direct, conf->pos)==0) {
        if (!(fileinfo->accessed[0] & FILE_IMPORTED)) {
            /* etnlegend, 2006.03.31, ������Խ��Խ���ź���... */
            if(!((arg->mode==DIR_MODE_DELETED||arg->mode==DIR_MODE_JUNK)
                ||(arg->mode==DIR_MODE_NORMAL&&(conf->pos>arg->filecount)))){
                ret=set_article_flag(conf, fileinfo, FILE_IMPORT_FLAG);
            }
        }
    }
    if((arg->mode==DIR_MODE_DELETED||arg->mode==DIR_MODE_JUNK)&&p)
        *p='-';
    return ret;
}

int show_b_note(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
{
    clear();
    if (show_board_notes(currboard->filename, 1) == -1) {
        move(3, 30);
        prints("�����������ޡ�����¼����");
    }
    pressanykey();
    return FULLUPDATE;
}

#if 0
int show_sec_board_notes(char bname[30])
{                               /* ��ʾ�����Ļ� */
    char buf[256];

    sprintf(buf, "vote/%s/secnotes", bname);    /* ��ʾ����İ����Ļ� vote/����/notes */
    if (dashf(buf)) {
        ansimore2(buf, false, 0, 23 /*19 */ );
        return 1;
    } else if (dashf("vote/secnotes")) {        /* ��ʾϵͳ�Ļ� vote/notes */
        ansimore2("vote/secnotes", false, 0, 23 /*19 */ );
        return 1;
    }
    return -1;
}

int show_sec_b_note(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
{
    clear();
    if (show_sec_board_notes(currboard->filename) == -1) {
        move(3, 30);
        prints("�����������ޡ����ܱ���¼����");
    }
    pressanykey();
    return FULLUPDATE;
}
#endif

int into_announce(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
{
    if (a_menusearch("0Announce", currboard->filename, (HAS_PERM(getCurrentUser(), PERM_ANNOUNCE) || HAS_PERM(getCurrentUser(), PERM_SYSOP) || HAS_PERM(getCurrentUser(), PERM_OBOARDS)) ? PERM_BOARDS : 0))
        return FULLUPDATE;
    return DONOTHING;
}

#ifdef FB2KPC
void a_menu();

int haspc(char *userid)
{
	char buf[256];
	sprintf(buf,"%s/%c/%s", FB2KPC, toupper(userid[0]),userid);

	if(dashd(buf))
		return 1;
	return 0;
}

int Personal(char *userid)
{
   char    found[256], lookid[IDLEN];
   char buf[STRLEN];
   struct userec *lookupuser;
   
   if(!userid || userid[0]=='\0') {
      clear();
      move(1, 0);
      usercomplete( "���뿴˭�ĸ����ļ�: " , lookid);
      if (lookid[0] == '\0') {
         clear();
         return 1;
      }
   }else 
	  strcpy(lookid, userid);

   if(lookid[0] == '*'){
      sprintf(buf,"/%c/%s", toupper(getCurrentUser()->userid[0]),getCurrentUser()->userid);
   } else {
      if (! getuser(lookid,&lookupuser) ){
         lookid[1] = toupper(lookid[0]);
	     if(lookid[1] < 'A' || lookid[1] > 'Z'){
			buf[0]='\0';
		 }else {
			sprintf(buf,"/%c",lookid[1]);
	     }
      } else {
         sprintf(buf, "/%c/%s", toupper(lookupuser->userid[0]),lookupuser->userid);
      }
   }
   if(buf[0]=='/')
	  sprintf(found,FB2KPC "%s", buf);
   else
	  sprintf(found,FB2KPC "/%s", buf);
   if(!dashd(found)) 
      strcpy(found,FB2KPC);
   a_menu("",found,((HAS_PERM(getCurrentUser(),PERM_ANNOUNCE) || HAS_PERM(getCurrentUser(),PERM_SYSOP) || HAS_PERM(getCurrentUser(),PERM_OBOARDS)) ? PERM_BOARDS : 0),0, NULL);
   return 1;
}

int into_PAnnounce(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
{
	Personal(NULL);
	return FULLUPDATE;
}
#endif

int sequential_read(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
{
    struct read_arg* arg=conf->arg;
    int findpos;
    
    findpos=find_nextnew(conf,0);
    if (findpos) {
        conf->new_pos=findpos;
        arg->readmode=READ_NEW;
        list_select_add_key(conf, 'r');
    }
    return SHOW_SELCHANGE;
}

int clear_new_flag(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
{
#ifdef HAVE_BRC_CONTROL
    struct read_arg* arg=conf->arg;
    /* add by stiger */
    if (conf->pos>arg->filecount||!arg->filecount)
        brc_clear(currboardent,getSession());
    else brc_clear_new_flag(fileinfo->id,currboardent,getSession());
#endif
    return PARTUPDATE;
}

int clear_all_new_flag(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
{
#ifdef HAVE_BRC_CONTROL
    brc_clear(currboardent,getSession());
#endif
    return FULLUPDATE;
}

int range_flag(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
{
    char ans[4], buf[80];
    char num1[10], num2[10];
    int inum1, inum2, total=0;
    struct stat st;
    int i,k;
    int fflag;
    struct read_arg* arg=conf->arg;
#ifdef FILTER
    int is_filter = (HAS_PERM(getCurrentUser(), PERM_SYSOP)&&(!strcmp(currboard->filename,FILTER_BOARD)));
#endif
    
    if (!chk_currBM(currBM, getCurrentUser())) return DONOTHING;
    if (arg->mode!=DIR_MODE_SUPERFITER) return DONOTHING;
    if(stat(arg->direct, &st)==-1) return DONOTHING;
    total = st.st_size/sizeof(struct fileheader);
    
    clear();
    prints("���α��, �����ʹ��");
    getdata(2, 0, "��ƪ���±��: ", num1, 10, DOECHO, NULL, true);
    inum1 = atoi(num1);
    if (inum1 <= 0) return FULLUPDATE;
    getdata(3, 0, "ĩƪ���±��: ", num2, 10, DOECHO, NULL, true);
    inum2 = atoi(num2);
    if (inum2 <= inum1) {
        prints("������\n");
        pressreturn();
        return FULLUPDATE;
    }
    sprintf(buf, "1-����m  2-���ɾ��t  3-��ժg  4-����Re  5-���#  6-���%%%s:[0]",
#ifdef FILTER
        is_filter?"  7-�����@":"");
#else
        "");
#endif
    getdata(4, 0, buf, ans, 4, DOECHO, NULL, true);
#ifdef FILTER
    if(ans[0]=='7'&&(!is_filter)) return FULLUPDATE;
#else
    if(ans[0]=='7') return FULLUPDATE;
#endif
    if(ans[0]<'1'||ans[0]>'7') return FULLUPDATE;
    if(askyn("�����ؿ���, ȷ�ϲ�����?", 0)==0) return FULLUPDATE;
    k=ans[0]-'0';
    if (k==3) {
        prints("\033[1;31m�����ܱ���ɱ, ���в������� atppp @ newsmth.net PK. \033[36m<ENTER>\033[0m");
        WAIT_RETURN;
        return FULLUPDATE;
    }
    if(k==1) fflag=FILE_MARK_FLAG;
    else if(k==2) fflag=FILE_DELETE_FLAG;
    else if(k==3) fflag=FILE_DIGEST_FLAG;
    else if(k==4) fflag=FILE_NOREPLY_FLAG;
    else if(k==5) fflag=FILE_SIGN_FLAG;
    else if(k==6) fflag=FILE_PERCENT_FLAG;
#ifdef FILTER
    else if(k==7) fflag=FILE_CENSOR_FLAG;
#endif
    else return FULLUPDATE;
    for(i=inum1;i<=inum2;i++) {
        if(i>=1&&i<=total) {
            struct write_dir_arg dirarg;
            struct fileheader data;
            data.accessed[0]=0xff;
            data.accessed[1]=0xff; /* TODO: �Ƿ������ƪ���µı�����������ϻ���ȥ�����? */
            
            init_write_dir_arg(&dirarg);
            dirarg.fd=arg->fd;
            dirarg.filename=arg->direct;
            dirarg.ent = i;
            dirarg.needlock=false;
            flock(arg->fd,LOCK_EX);
            malloc_write_dir_arg(&dirarg);
            change_post_flag(&dirarg, 
                arg->mode,
                currboard, 
                dirarg.fileptr+(i-1), 
                fflag, &data, true,getSession());
            flock(arg->fd,LOCK_UN);
            free_write_dir_arg(&dirarg);
        }
    }
    prints("\n��ɱ��\n");
    pressreturn();
    return DIRCHANGED;
}

int show_t_friends()
{
    if (!HAS_PERM(getCurrentUser(), PERM_BASIC))
        return PARTUPDATE;
    t_friends();
    return FULLUPDATE;
}

/* add by stiger, add template */
int b_note_edit_new(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
{
	char ans[4];

	if(!chk_currBM(currBM, getCurrentUser())) return DONOTHING;

    move(t_lines - 1, 0);
    clrtoeol();
    getdata(t_lines - 1, 0, "�༭: 0)ȡ�� 1)����¼ 2)����ģ��"
#ifdef FLOWBANNER
		" 3)�ײ�������Ϣ"
#endif
#ifdef NEWSMTH
		" 4)�ΰ淽��"
#endif
		" [0]: ", ans, 3, DOECHO, NULL, true);
    if (ans[0]=='1') return b_notes_edit();
#ifdef NEWSMTH
	else if (ans[0]=='4') return b_rules_edit();
#endif
	else if(ans[0]=='2'){
		int ret;	
#ifdef NEW_HELP
		int oldhelpmode=helpmode;
		helpmode = HELP_TMPL;
#endif
		ret =  m_template();
#ifdef NEW_HELP
		helpmode = oldhelpmode;
#endif
		return ret;
	}
#ifdef FLOWBANNER
	else if(ans[0]=='3'){
		return b_banner_edit();		
	}
#endif

	return FULLUPDATE;
}

/*Add by SmallPig*/
static int catnotepad(FILE * fp, char *fname)
{
    char inbuf[256];
    FILE *sfp;
    int count;

    count = 0;
    if ((sfp = fopen(fname, "r")) == NULL) {
        fprintf(fp, "\033[31m\033[41m�ѩء����������������������������������������������������������������������ء�\033[m\n\n");
        return -1;
    }
    while (fgets(inbuf, sizeof(inbuf), sfp) != NULL) {
        if (count != 0)
            fputs(inbuf, fp);
        else
            count++;
    }
    fclose(sfp);
    return 0;
}

void notepad()
{
    char tmpname[STRLEN], note1[4];
    char note[3][STRLEN - 4];
    char tmp[STRLEN];
    FILE *in;
    int i, n;
    time_t thetime = time(0);

    clear();
    move(0, 0);
    prints("��ʼ������԰ɣ��������Ŀ�Դ�....\n");
    /* sprintf(tmpname, "etc/notepad_tmp/%s.notepad", getCurrentUser()->userid); */
    gettmpfilename( tmpname, "notepad" );
    if ((in = fopen(tmpname, "w")) != NULL) {
        for (i = 0; i < 3; i++)
            memset(note[i], 0, STRLEN - 4);
        while (1) {
            for (i = 0; i < 3; i++) {
                getdata(1 + i, 0, ": ", note[i], STRLEN - 5, DOECHO, NULL, false);
                if (note[i][0] == '\0')
                    break;
            }
            if (i == 0) {
                fclose(in);
                unlink(tmpname);
                return;
            }
            getdata(5, 0, "�Ƿ����Ĵ����������԰� (Y)�ǵ� (N)��Ҫ (E)�ٱ༭ [Y]: ", note1, 3, DOECHO, NULL, true);
            if (note1[0] == 'e' || note1[0] == 'E')
                continue;
            else
                break;
        }
        if (note1[0] != 'N' && note1[0] != 'n') {
            sprintf(tmp, "\033[32m%s\033[37m��%.24s��", getCurrentUser()->userid, getCurrentUser()->username);
            fprintf(in, "\033[m\033[31m�ѩС�����������������������������\033[37m���������\033[31m�������������������������������С�\033[m\n");
            fprintf(in, "\033[31m����%-43s\033[33m�� \033[36m%.19s\033[33m �뿪ʱ���µĻ�\033[31m����\n", tmp, Ctime(thetime));
            if (i > 2)
                i = 2;
            for (n = 0; n <= i; n++) {
#ifdef FILTER
                if (check_badword_str(note[n],strlen(note[n]), getSession())) {
                    int t;
                    for (t = n; t <= i; t++) 
                        fprintf(in, "\033[31m��\033[m%-74.74s\033[31m��\033[m\n", note[t]);
                    fclose(in);

                    post_file(getCurrentUser(), "", tmpname, FILTER_BOARD, "---���԰������---", 0, 2,getSession());

                    unlink(tmpname);
                    return;
                }
#endif
                if (note[n][0] == '\0')
                    break;
                fprintf(in, "\033[31m��\033[m%-74.74s\033[31m��\033[m\n", note[n]);
            }
            fprintf(in, "\033[31m���С����������������������������������������������������������������������С�\033[m\n");
            catnotepad(in, "etc/notepad");
            fclose(in);
            f_mv(tmpname, "etc/notepad");
        } else {
            fclose(in);
            unlink(tmpname);
        }
    }
    if (talkrequest) {
        talkreply();
    }
    clear();
    return;
}

void record_exit_time()
{                               /* ��¼����ʱ��  Luzi 1998/10/23 */
	if(uinfo.invisible == true && HAS_PERM(getCurrentUser(), PERM_SYSOP) && getCurrentUser()->exittime > getCurrentUser()->lastlogin)
		return;
    getCurrentUser()->exittime = time(NULL);
    /*
     * char path[80];
     * FILE *fp;
     * time_t now;
     * sethomefile( path, getCurrentUser()->userid , "exit");
     * fp=fopen(path, "wb");
     * if (fp!=NULL)
     * {
     * now=time(NULL);
     * fwrite(&now,sizeof(time_t),1,fp);
     * fclose(fp);
     * }
     */
}

extern int icurrchar, ibufsize;

int Goodbye()
{                               /*��վ ѡ�� */
    extern int started;
    time_t stay;
    char fname[STRLEN], notename[STRLEN];
    char sysoplist[20][STRLEN], syswork[20][STRLEN], spbuf[STRLEN], buf[STRLEN], *tmp, *pbuf;
    int i, num_sysop, choose, logouts, mylogout = false;
    FILE *sysops;
    int left = (80 - 36) / 2;
    int top = (scr_lns - 11) / 2;
    struct _select_item level_conf[] = {
        {-1, -1, -1, SIT_SELECT, (void *) ""},
        {-1, -1, -1, SIT_SELECT, (void *) ""},
        {-1, -1, -1, SIT_SELECT, (void *) ""},
        {-1, -1, -1, SIT_SELECT, (void *) ""},
        {-1, -1, -1, 0, NULL}
    };
    level_conf[0].x = left + 7;
    level_conf[1].x = left + 7;
    level_conf[2].x = left + 7;
    level_conf[3].x = left + 7;
    level_conf[0].y = top + 2;
    level_conf[1].y = top + 3;
    level_conf[2].y = top + 4;
    level_conf[3].y = top + 5;

/*---	��ʾ����¼�Ĺص������Ļ����	2001-07-01	---*/
    modify_user_mode(READING);

    i = 0;
    if ((sysops = fopen("etc/sysops", "r")) != NULL) {
        while (fgets(buf, STRLEN, sysops) != NULL && i < 20) {
            if(NULL == (tmp = strtok_r(buf," \n\r\t",&pbuf) ) ) {                
                continue;
            }else{
                if(tmp[0]=='#' || tmp[0]==';'){
                        continue;
                }
                strncpy(sysoplist[i], tmp, STRLEN);
                sysoplist[i][STRLEN-1] = '\0';
            }
            if(NULL == (tmp = strtok_r(NULL," \n\r\t",&pbuf) ) ) {
                continue;
            }else{
                strncpy(syswork[i], tmp, STRLEN);
                syswork[i][STRLEN-1] = '\0';
            }
            i++;
        }
        fclose(sysops);
    }
    num_sysop = i;
    move(1, 0);
    clear();
    move(top, left);
    outs("\x1b[1;47;37m�X�T[*]�T�T�T Message �T�T�T�T�T�T�[\x1b[m");
    move(top + 1, left);
    outs("\x1b[1;47;37m�U\x1b[44;37m                                \x1b[47;37m�U\x1b[m");
    move(top + 2, left);
    prints("\x1b[1;47;37m�U\x1b[44;37m     [\x1b[33m1\x1b[37m] ���Ÿ�%-10s       \x1b[47;37m�U\x1b[m", NAME_BBS_CHINESE);
    move(top + 3, left);
    prints("\x1b[1;47;37m�U\x1b[44;37m     [\x1b[33m2\x1b[37m] \x1b[32m����%-15s\x1b[37m    \x1b[47;37m�U\x1b[m", BBS_FULL_NAME);
    move(top + 4, left);
    outs("\x1b[1;47;37m�U\x1b[44;37m     [\x1b[33m3\x1b[37m] дд*���԰�*           \x1b[47;37m�U\x1b[m");
    move(top + 5, left);
    outs("\x1b[1;47;37m�U\x1b[44;37m     [\x1b[33m4\x1b[37m] �뿪��BBSվ            \x1b[47;37m�U\x1b[m");
    move(top + 6, left);
    outs("\x1b[1;47;37m�U\x1b[0;44;34m________________________________\x1b[1;47;37m�U\x1b[m");
    move(top + 7, left);
    outs("\x1b[1;47;37m�U                                �U\x1b[m");
    move(top + 8, left);
    outs("\x1b[1;47;37m�U          \x1b[42;33m  ȡ��(ESC) \x1b[0;47;30m�z  \x1b[1;37m      �U\x1b[m");
    move(top + 9, left);
    outs("\x1b[1;47;37m�U            \x1b[0;40;37m�z�z�z�z�z�z\x1b[1;47;37m        �U\x1b[m");
    move(top + 10, left);
    outs("\x1b[1;47;37m�^�T�T�T�T�T�T�T�T�T�T�T�T�T�T�T�T�a\x1b[m");
    outs("\x1b[1;44;37m");

    choose = simple_select_loop(level_conf, SIF_SINGLE | SIF_ESCQUIT | SIF_NUMBERKEY, t_columns, t_lines, NULL);
    if (choose == 0)
        choose = 2;
    clear();
    if (strcmp(getCurrentUser()->userid, "guest") && choose == 1) {  /* д�Ÿ�վ�� */
        if (PERM_LOGINOK & getCurrentUser()->userlevel) {    /*Haohmaru.98.10.05.ûͨ��ע���ֻ�ܸ�ע��վ������ */
            prints("        ID        �����ְ��\n");
            prints("   ============ =============\n");
            for (i = 1; i <= num_sysop; i++) {
                prints("[\033[33m%1d\033[m] \033[1m%-12s %s\033[m\n", i, sysoplist[i - 1], syswork[i - 1]);
            }

            prints("[\033[33m%1d\033[m] ���������ޣ�\n", num_sysop + 1);      /*���һ��ѡ�� */

            sprintf(spbuf, "���ѡ���� [\033[32m%1d\033[m]��", num_sysop + 1);
            getdata(num_sysop + 5, 0, spbuf, genbuf, 4, DOECHO, NULL, true);
            choose = genbuf[0] - '0';
            if (0 != genbuf[1])
                choose = genbuf[1] - '0' + 10;
            if (choose >= 1 && choose <= num_sysop) {
                /*
                 * do_send(sysoplist[choose-1], "ʹ���߼����ĵĽ�����"); 
                 */
                if (choose == 1)        /*modified by Bigman : 2000.8.8 */
                    do_send(sysoplist[0], "��վ���ܹܡ�ʹ���߼����Ľ�����", "");
                else if (choose == 2)
                    do_send(sysoplist[1], "��ϵͳά����ʹ���߼����Ľ�����", "");
                else if (choose == 3)
                    do_send(sysoplist[2], "���������ʹ���߼����Ľ�����", "");
                else if (choose == 4)
                    do_send(sysoplist[3], "�����ȷ�ϡ�ʹ���߼����Ľ�����", "");
                else if (choose == 5)
                    do_send(sysoplist[4], "���ٲ����ˡ�ʹ���߼����Ľ�����", "");
            }
/* added by stephen 11/13/01 */
            choose = -1;
        } else {
            /*
             * ����ע�����ʾ��Ϣ Bigman:2000.10.31 
             */
            prints("\n    �����һֱδ�õ������֤,��ȷ�����Ƿ񵽸��˹�������д��ע�ᵥ,\n");
            prints("    ������յ����ȷ����,��û�з��������Ȩ��,����������дһ��ע�ᵥ\n\n");
            prints("     վ���� ID   �����ְ��\n");
            prints("   ============ =============\n");

            /*
             * added by Bigman: 2000.8.8  �޸���վ 
             */
            prints("[\033[33m%1d\033[m] \033[1m%-12s %s\033[m\n", 1, sysoplist[3], syswork[3]);
            prints("[\033[33m%1d\033[m] ���������ޣ�\n", 2);  /*���һ��ѡ�� */

            sprintf(spbuf, "���ѡ���� %1d��", 2);
            getdata(num_sysop + 6, 0, spbuf, genbuf, 4, DOECHO, NULL, true);
            choose = genbuf[0] - '0';
            if (choose == 1)    /*modified by Bigman : 2000.8.8 */
                do_send(sysoplist[3], "�����ȷ�ϡ�ʹ���߼����Ľ�����", "");
            choose = -1;
        }
    }
    if (choose == 2)            /*����BBS */
        return FULLUPDATE;
    if (strcmp(getCurrentUser()->userid, "guest") != 0) {
        if (choose == 3)        /*���Բ� */
            if (USE_NOTEPAD == 1 && HAS_PERM(getCurrentUser(), PERM_POST))
                notepad();
    }

    clear();
    prints("\n\n\n\n");

    if (DEFINE(getCurrentUser(), DEF_OUTNOTE /*�˳�ʱ��ʾ�û�����¼ */ )) {
        sethomefile(notename, getCurrentUser()->userid, "notes");
        if (dashf(notename))
            ansimore(notename, true);
    }

    /*
     * Leeward 98.09.24 Use SHARE MEM and disable the old code 
     */
    if (DEFINE(getCurrentUser(), DEF_LOGOUT)) {      /* ʹ���Լ�����վ���� */
        sethomefile(fname, getCurrentUser()->userid, "logout");
        if (dashf(fname))
            mylogout = true;
    }
    if (mylogout) {
        logouts = countlogouts(fname);  /* logouts Ϊ ��վ���� ���� */
        if (logouts >= 1) {
            user_display(fname, (logouts == 1) ? 1 : (getCurrentUser()->numlogins % (logouts)) + 1, true);
        }
    } else {
        logouts = countlogouts("etc/logout");   /* logouts Ϊ ��վ���� ���� */
        if (logouts > 0)
            user_display("etc/logout", rand() % logouts + 1, true);
    }

    //bbslog("user", "%s", "exit");

    if (started) {
        record_exit_time();     /* ��¼�û����˳�ʱ�� Luzi 1998.10.23 */
        stay=time(NULL)-uinfo.logintime;
        /*---	period	2000-10-19	4 debug	---*/
        newbbslog(BBSLOG_USIES, "EXIT: Stay:%3ld (%s)[%d %d]", stay / 60, getCurrentUser()->username, getSession()->utmpent, getSession()->currentuid);
        u_exit();
        started = 0;
    }

    if (num_user_logins(getCurrentUser()->userid) == 0 || !strcmp(getCurrentUser()->userid, "guest")) {   /*��黹��û���������� */
        FILE *fp;
        char buf[STRLEN], *ptr;

        if (DEFINE(getCurrentUser(), DEF_MAILMSG /*��վʱ�Ļ�������Ϣ */ ) && (get_msgcount(0, getCurrentUser()->userid))) {
                mail_msg(getCurrentUser(),getSession());
        }
#if !defined(FREE) && !defined(ZIXIA)
		else
            clear_msg(getCurrentUser()->userid);
#endif
        fp = fopen("friendbook", "r");  /*����ϵͳ Ѱ������ */
        while (fp != NULL && fgets(buf, sizeof(buf), fp) != NULL) {
            char uid[14];

            ptr = strstr(buf, "@");
            if (ptr == NULL) {
                del_from_file("friendbook", buf);
                continue;
            }
            ptr++;
            strcpy(uid, ptr);
            ptr = strstr(uid, "\n");
            *ptr = '\0';
            if (!strcmp(uid, getCurrentUser()->userid))      /*ɾ�����û��� Ѱ������ */
                del_from_file("friendbook", buf);       /*Ѱ������ֻ�ڱ���������Ч */
        }
        if (fp)                                                                                        /*---	add by period 2000-11-11 fix null hd bug	---*/
            fclose(fp);
    }
    sleep(1);
    pressreturn();              /*Haohmaru.98.10.18 */
    output("\x1b[m",3);
    output("\x1b[H\x1b[J",6);
    oflush();

    end_mmapfile(getSession()->currentmemo, sizeof(struct usermemo), -1);

    shutdown(0, 2);
    close(0);
    exit(0);
    return -1;
}



int Info()
{                               /* ��ʾ�汾��ϢVersion.Info */
    modify_user_mode(XMENU);
    ansimore("Version.Info", true);
    clear();
    return 0;
}

int Conditions()
{                               /* ��ʾ��Ȩ��ϢCOPYING */
    modify_user_mode(XMENU);
    ansimore("COPYING", true);
    clear();
    return 0;
}

int ShowWeather()
{                               /* ��ʾ�汾��ϢVersion.Info */
    modify_user_mode(XMENU);
    ansimore("WEATHER", true);
    clear();
    return 0;
}

int Welcome()
{                               /* ��ʾ��ӭ���� Welcome */
    modify_user_mode(XMENU);
    ansimore("Welcome", true);
    clear();
    return 0;
}

void RemoveAppendedSpace(char *ptr)
{                               /* Leeward 98.02.13 */
    int Offset;

    /*
     * Below block removing extra appended ' ' in article titles 
     */
    Offset = strlen(ptr);
    for (--Offset; Offset > 0; Offset--) {
        if (' ' != ptr[Offset])
            break;
        else
            ptr[Offset] = 0;
    }
}

int i_read_mail()
{
    if(!HAS_PERM(getCurrentUser(), PERM_BASIC)||!strcmp(getCurrentUser()->userid, "guest")) return DONOTHING;
    if (HAS_MAILBOX_PROP(&uinfo, MBP_MAILBOXSHORTCUT))
    	MailProc();
    else
    	m_read();
    setmailcheck(getCurrentUser()->userid);
    return FULLUPDATE;
}

#define ACL_MAX 10

#ifndef HAVE_IPV6_SMTH
#define IPBITS 32
#else
#define IPBITS 128
#endif

struct acl_struct {
#ifndef HAVE_IPV6_SMTH
    unsigned int ip;
    char len;
#else
    struct in6_addr ip;
    int len;
#endif
    char deny;
} * acl;
int aclt=0;

static int set_acl_list_show(struct _select_def *conf, int i)
{
    char buf[80];
#ifndef HAVE_IPV6_SMTH
    unsigned int ip,ip2;
    ip = acl[i-1].ip;
#else
    struct in6_addr ip;
    memcpy(&ip, &acl[i-1].ip, sizeof(struct in6_addr));
#endif
    if(i-1<aclt) {
#ifndef HAVE_IPV6_SMTH
        if(acl[i-1].len==0) ip2=ip+0xffffffff;
        else ip2=ip+((1<<(32-acl[i-1].len))-1);
        sprintf(buf, "%d.%d.%d.%d--%d.%d.%d.%d", ip>>24, (ip>>16)%0x100, (ip>>8)%0x100, ip%0x100, ip2>>24, (ip2>>16)%0x100, (ip2>>8)%0x100, ip2%0x100);
#else
        if (ISV4ADDR(acl[i-1].ip)) { 
            inet_ntop(AF_INET, &ip.s6_addr[12], buf, 80);
            sprintf(&buf[strlen(buf)], "/%d", acl[i-1].len+32-128);
        } else {
            inet_ntop(AF_INET6, &ip, buf, 80);
            sprintf(&buf[strlen(buf)], "/%d", acl[i-1].len);
        }
#endif
        prints("  %2d  %-50s %4s", i, buf, acl[i-1].deny?"�ܾ�":"����");
    }
    return SHOW_CONTINUE;
}

static int set_acl_list_prekey(struct _select_def *conf, int *key)
{
    switch (*key) {
    case 'e':
    case 'q':
        *key = KEY_LEFT;
        break;
    case 'p':
    case 'k':
        *key = KEY_UP;
        break;
    case ' ':
    case 'N':
        *key = KEY_PGDN;
        break;
    case 'n':
    case 'j':
        *key = KEY_DOWN;
        break;
    }
    return SHOW_CONTINUE;
}

static int set_acl_list_key(struct _select_def *conf, int key)
{
    int oldmode;

    switch (key) {
    case 'a':
        if (aclt<ACL_MAX) {
            char buf[IPLEN+4];
#ifndef HAVE_IPV6_SMTH
            int ip[4], i, k=0, err=0;
#else
            struct in6_addr ip, mask;
            int k=0, err=0;
#endif
            getdata(0, 0, "������IP��ַ: ", buf, IPLEN+2, 1, 0, 1);
#ifndef HAVE_IPV6_SMTH
            for(i=0;i<strlen(buf);i++) if(buf[i]=='.') k++;
            if(k!=3) err=1;
            else {
                if(sscanf(buf, "%d.%d.%d.%d", &ip[0], &ip[1], &ip[2], &ip[3])!=4) err=1;
                else {
                    if(ip[0]==0) err=1;
                    for(i=0;i<4;i++) if(ip[i]<0||ip[i]>=256) err=1;
                }
            }
#else
            if ((!strchr(buf, ':')) && (strchr(buf, '.'))) {
                memset(&ip, 0, sizeof(ip));
                ip.s6_addr[10]=0xff;
                ip.s6_addr[11]=0xff;
                err = inet_pton(AF_INET, buf, &ip.s6_addr[12])<=0;
                k = 128-32;
			} else err = inet_pton(AF_INET6, buf, &ip)<=0;
#endif
            if(err) {
                move(0, 0);
                prints("IP�������!");
                clrtoeol();
                refresh(); sleep(1);
            }
            else {
                getdata(0, 0, "�����볤��(��λ:bit): ", buf, 4, 1, 0, 1);
                acl[aclt].len = atoi(buf);
#ifdef HAVE_IPV6_SMTH
                acl[aclt].len+= k;
#endif
                if(acl[aclt].len<0 || acl[aclt].len>IPBITS) err=1;
                if(err) {
                    move(0, 0);
                    prints("�����������!");
                    clrtoeol();
                    refresh(); sleep(1);
                }
                else {
                    getdata(0, 0, "����/�ܾ�(0-����,1-�ܾ�): ", buf, 4, 1, 0, 1);
                    if(buf[0]=='0') acl[aclt].deny=0;
                    else acl[aclt].deny=1;
#ifndef HAVE_IPV6_SMTH
                    acl[aclt].ip = (ip[0]<<24)+(ip[1]<<16)+(ip[2]<<8)+ip[3];
                    if(acl[aclt].len<32)
                        acl[aclt].ip = acl[aclt].ip&(((1<<acl[aclt].len)-1)<<(32-acl[aclt].len));
#else
                    ip_mask(&ip, ip_len2mask(acl[aclt].len, &mask), &acl[aclt].ip);
#endif
                    aclt++;
                    return SHOW_DIRCHANGE;
                }
            }
            return SHOW_REFRESH;
        }
        break;
    case 'd':
        if (aclt > 0) {
            char ans[3];

            getdata(0, 0, "ȷʵҪɾ����(Y/N)? [N]: ", ans, sizeof(ans), DOECHO, NULL, true);
            if (ans[0] == 'Y' || ans[0] == 'y') {
                int i;
                aclt--;
                for(i=conf->pos-1;i<aclt;i++)
                    memcpy(acl+i, acl+i+1, sizeof(struct acl_struct));
                bzero(acl+aclt, sizeof(struct acl_struct));
            }
            return SHOW_DIRCHANGE;
        }
        break;
    case 'm':
        if (aclt > 0) {
            char ans[3];
            int d;

            getdata(0, 0, "������Ҫ�ƶ�����λ��: ", ans, 3, DOECHO, NULL, true);
            d=atoi(ans)-1;
            if (d>=0&&d<=aclt-1&&d!=conf->pos-1) {
                struct acl_struct temp;
                int i, p;
                p = conf->pos-1;
                memcpy(&temp, acl+p, sizeof(struct acl_struct));
                if(p>d) {
                    for(i=p;i>d;i--)
                        memcpy(acl+i, acl+i-1, sizeof(struct acl_struct));
                } else {
                    for(i=p;i<d;i++)
                        memcpy(acl+i, acl+i+1, sizeof(struct acl_struct));
                }
                memcpy(acl+d, &temp, sizeof(struct acl_struct));
                return SHOW_DIRCHANGE;
            }
        }
        break;
    case 'L':
    case 'l':
        oldmode = uinfo.mode;
        show_allmsgs();
        modify_user_mode(oldmode);
        return SHOW_REFRESH;
    case 'W':
    case 'w':
        oldmode = uinfo.mode;
        if (!HAS_PERM(getCurrentUser(), PERM_PAGE))
            break;
        s_msg();
        modify_user_mode(oldmode);
        return SHOW_REFRESH;
    case 'u':
        oldmode = uinfo.mode;
        clear();
        modify_user_mode(QUERY);
        t_query(NULL);
        modify_user_mode(oldmode);
        clear();
        return SHOW_REFRESH;
	case 'U':		/* pig2532 2005.12.10 */
		board_query();
        return SHOW_REFRESH;
    }

    return SHOW_CONTINUE;
}

static int set_acl_list_refresh(struct _select_def *conf)
{
    clear();
    docmdtitle("[��¼IP�����б�]",
               "�˳�[\x1b[1;32m��\x1b[0;37m,\x1b[1;32me\x1b[0;37m] ѡ��[\x1b[1;32m��\x1b[0;37m,\x1b[1;32m��\x1b[0;37m] ���[\x1b[1;32ma\x1b[0;37m] ɾ��[\x1b[1;32md\x1b[0;37m]\x1b[m");
    move(2, 0);
    prints("\033[0;1;37;44m  %4s  %-50s %-31s", "����", "IP��ַ��Χ", "����/�ܾ�");
    clrtoeol();
    update_endline();
    return SHOW_CONTINUE;
}

static int set_acl_list_getdata(struct _select_def *conf, int pos, int len)
{
    conf->item_count = aclt;
    if(conf->item_count==0)
        conf->item_count=1;

    return SHOW_CONTINUE;
}

int set_ip_acl()
{
    struct _select_def grouplist_conf;
    POINT *pts;
#ifndef HAVE_IPV6_SMTH
    int i,rip[4];
#else
    int i;
    struct in6_addr rip;
#endif
    int oldmode;
    FILE* fp;
    char fn[80],buf[80];

    clear();
    getdata(3, 0, "�������������: ", buf, 39, NOECHO, NULL, true);
    if (*buf == '\0' || !checkpasswd2(buf, getCurrentUser())) {
        prints("\n\n�ܱ�Ǹ, ����������벻��ȷ��\n");
        pressanykey();
        return 0;
    }

    acl = (struct acl_struct *) malloc(sizeof(struct acl_struct)*ACL_MAX);
    aclt=0;
    bzero(acl, sizeof(struct acl_struct)*ACL_MAX);
    sethomefile(fn, getCurrentUser()->userid, "ipacl");
    fp=fopen(fn, "r");
    if(fp){
        i=0;
        while(!feof(fp)) {
            int len,deny;
#ifndef HAVE_IPV6_SMTH
            if(fscanf(fp, "%d.%d.%d.%d %d %d", &rip[0], &rip[1], &rip[2], &rip[3], &len, &deny)<=0) break;
            acl[i].ip = (rip[0]<<24)+(rip[1]<<16)+(rip[2]<<8)+rip[3];
#else
            if(fscanf(fp, "%02hhX%02hhX:%02hhX%02hhX:%02hhX%02hhX:%02hhX%02hhX:%02hhX%02hhX:%02hhX%02hhX:%02hhX%02hhX:%02hhX%02hhX %d %d",
                &rip.s6_addr[0], &rip.s6_addr[1], &rip.s6_addr[2], &rip.s6_addr[3], &rip.s6_addr[4], &rip.s6_addr[5],
                &rip.s6_addr[6], &rip.s6_addr[7], &rip.s6_addr[8], &rip.s6_addr[9], &rip.s6_addr[10], &rip.s6_addr[11], 
                &rip.s6_addr[12], &rip.s6_addr[13], &rip.s6_addr[14], &rip.s6_addr[15], &len, &deny)<=0) break;
            memcpy(&acl[i].ip, &rip, IPBITS/8);
#endif
            acl[i].len=len;
            acl[i].deny=(char)deny;
            i++;
            if(i>=ACL_MAX) break;
        }
        aclt = i;
        fclose(fp);
    }
    clear();
    oldmode = uinfo.mode;
    modify_user_mode(SETACL);
    //TODO: ���ڴ�С��̬�ı�������������bug
    pts = (POINT *) malloc(sizeof(POINT) * BBS_PAGESIZE);
    for (i = 0; i < BBS_PAGESIZE; i++) {
        pts[i].x = 2;
        pts[i].y = i + 3;
    }
    bzero(&grouplist_conf, sizeof(struct _select_def));
    grouplist_conf.item_count = aclt;
    if(grouplist_conf.item_count==0)
        grouplist_conf.item_count=1;
    grouplist_conf.item_per_page = BBS_PAGESIZE;
    /*
     * ���� LF_VSCROLL ������ LEFT ���˳� 
     */
    grouplist_conf.flag = LF_VSCROLL | LF_BELL | LF_LOOP | LF_MULTIPAGE;
    grouplist_conf.prompt = "��";
    grouplist_conf.item_pos = pts;
    grouplist_conf.title_pos.x = 0;
    grouplist_conf.title_pos.y = 0;
    grouplist_conf.pos = 1;     /* initialize cursor on the first mailgroup */
    grouplist_conf.page_pos = 1;        /* initialize page to the first one */

    grouplist_conf.show_data = set_acl_list_show;
    grouplist_conf.pre_key_command = set_acl_list_prekey;
    grouplist_conf.key_command = set_acl_list_key;
    grouplist_conf.show_title = set_acl_list_refresh;
    grouplist_conf.get_data = set_acl_list_getdata;

    list_select_loop(&grouplist_conf);
    free(pts);
    modify_user_mode(oldmode);
    fp=fopen(fn, "w");
    if(fp){
        for(i=0;i<aclt;i++)
#ifndef HAVE_IPV6_SMTH
            fprintf(fp, "%d.%d.%d.%d %d %d\n", acl[i].ip>>24, (acl[i].ip>>16)%0x100, (acl[i].ip>>8)%0x100, acl[i].ip%0x100, acl[i].len, acl[i].deny);
#else
            fprintf(fp, "%02hhX%02hhX:%02hhX%02hhX:%02hhX%02hhX:%02hhX%02hhX:%02hhX%02hhX:%02hhX%02hhX:%02hhX%02hhX:%02hhX%02hhX %d %d\n",
                acl[i].ip.s6_addr[0], acl[i].ip.s6_addr[1], acl[i].ip.s6_addr[2], acl[i].ip.s6_addr[3], 
                acl[i].ip.s6_addr[4], acl[i].ip.s6_addr[5], acl[i].ip.s6_addr[6], acl[i].ip.s6_addr[7],
                acl[i].ip.s6_addr[8], acl[i].ip.s6_addr[9], acl[i].ip.s6_addr[10], acl[i].ip.s6_addr[11], 
                acl[i].ip.s6_addr[12], acl[i].ip.s6_addr[13], acl[i].ip.s6_addr[14], acl[i].ip.s6_addr[15], 
                acl[i].len, acl[i].deny);
#endif
        fclose(fp);
    }

    return 0;

}


int b_results(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
{
    return vote_results(currboard->filename);
}

enum {
    BM_DELETE=1,
    BM_MARK,
    BM_DELMARKDEL,
    BM_IMPORT,
    BM_TMP,
    BM_MARKDEL,
    BM_NOREPLY,
    BM_TOTAL
};

const char *SR_BMitems[] = {
    "ɾ��",
    "����",
    "ɾ����ɾ����",
    "���뾫����",
    "�����ݴ浵",
    "�趨��ɾ���",
    "�趨���ɻظ�",
    "�����ϼ�"
};
const int item_num = 8;

struct BMFunc_arg {
    bool delpostnum; /*�Ƿ��������*/
    int action;            /*����������ΪBM_DELETE��BM_TOTAL����֮һ*/
    bool saveorigin;    /*�ںϼ�������ʱ������Ƿ񱣴�ԭ��*/
    char* announce_path; /*��¼��������ʱ���λ��*/
    bool setflag; /*���û���ȡ��*/
};

/*����ͬ���⺯��������apply_record�Ļص�����*/
static int BM_thread_func(struct _select_def* conf, struct fileheader* fh,int ent, void* extraarg)
{
    struct read_arg* arg=(struct read_arg*)conf->arg;
    struct BMFunc_arg* func_arg=(struct BMFunc_arg*)extraarg;
    int ret=APPLY_CONTINUE;

    conf->pos=ent;
    if (arg->writearg) {
        arg->writearg->ent=ent;
    }
    switch (func_arg->action) {
        case BM_DELETE:
            if (!(fh->accessed[0] & (FILE_MARKED | FILE_PERCENT))) {
                if (del_post(conf,fh,(void*)(ARG_BMFUNC_FLAG|ARG_NOPROMPT_FLAG))==DIRCHANGED)
                    ret=APPLY_REAPPLY;
            }
            break;
        case BM_MARK:
	    if (func_arg->setflag)
            fh->accessed[0] |= FILE_MARKED;
	    else
            fh->accessed[0] &= ~FILE_MARKED;
            break;
        case BM_DELMARKDEL: /* etnlegend, 2005.11.28, ͬ������ɾ�� */
            if(fh->accessed[1]&FILE_DEL){
                if(!(fh->accessed[0]&(FILE_MARKED|FILE_PERCENT))){
                    if(del_post(conf,fh,(void*)(ARG_BMFUNC_FLAG|ARG_NOPROMPT_FLAG))==DIRCHANGED)
                        ret=APPLY_REAPPLY;
                }
                else
                    fh->accessed[1]&=~FILE_DEL;
            }
            break;
        case BM_MARKDEL:
	    if (func_arg->setflag) {
            if (!(fh->accessed[0] & (FILE_MARKED | FILE_PERCENT))) {
                fh->accessed[1] |= FILE_DEL;
            }
	    } else
                fh->accessed[1] &= ~FILE_DEL;
            break;
        case BM_NOREPLY:
	    if (func_arg->setflag)
                fh->accessed[1] |= FILE_READ;
	    else
                fh->accessed[1] &= ~FILE_READ;
            break;
        case BM_IMPORT:
            if (a_Import(func_arg->announce_path, 
                currboard->filename, 
                fh, 
                true, 
                arg->direct, 
                ent)==0) {
                fh->accessed[0]|=FILE_IMPORTED;
            }            
            break;
        case BM_TOTAL:
        case BM_TMP:
            a_SeSave("0Announce",
                currboard->filename,
                fh,
                true,
                arg->direct,
                ent,
                !func_arg->saveorigin,
                getCurrentUser()->userid);
            fh->accessed[0]|=FILE_IMPORTED;
            break;
    }
    return ret;
}

static int SR_BMFunc(struct _select_def* conf, struct fileheader* fh, void* extraarg)
{
    int i;
    char buf[256], ch[4], BMch;
    struct BMFunc_arg func_arg;
    bool fromfirst;
    int ent;
    struct read_arg* arg=(struct read_arg*)conf->arg;
    char linebuffer[LINELEN*3];
    char annpath[MAXPATH];
    struct write_dir_arg dirarg;

    if (fh==NULL)
        return DONOTHING;
    func_arg.delpostnum=(bool)extraarg;
    func_arg.setflag=true;
    if (!chk_currBM(currBM, getCurrentUser())) {
        return DONOTHING;
    }
    if (arg->mode != DIR_MODE_NORMAL && arg->mode != DIR_MODE_DIGEST)     /* KCN:�ݲ����� */
        return DONOTHING;
    if (conf->pos>arg->filecount) /*�ö�*/
        return DONOTHING;
    saveline(t_lines - 3, 0, linebuffer);
    saveline(t_lines - 2, 0, NULL);
    move(t_lines - 3, 0);
    clrtoeol();
    strcpy(buf, "������� (0)ȡ�� ");
    for (i = 0; i < item_num; i++) {
        char t[40];

        sprintf(t, "(%d)%s ", i + 1, SR_BMitems[i]);
        strcat(buf, t);
    };
    strcat(buf, "? [0]: ");
    if (strlen(buf) > 76) {
        char savech = buf[76];

        buf[76] = 0;
        prints("%s", buf);
        buf[76] = savech;

/*        strcpy(buf,buf+76);*/
        getdata(t_lines - 2, 0, buf + 76, ch, 3, DOECHO, NULL, true);
    } else
        getdata(t_lines - 3, 0, buf, ch, 3, DOECHO, NULL, true);
    BMch = atoi(ch);
    if (BMch <= 0 || BMch > item_num) {
        saveline(t_lines - 2, 1, NULL);
        saveline(t_lines - 3, 1, linebuffer);
        return DONOTHING;
    }
    if (arg->mode == DIR_MODE_DIGEST && BMch == 3) {
        saveline(t_lines - 2, 1, NULL);
        saveline(t_lines - 3, 1, linebuffer);
        return DONOTHING;
    }
    move(t_lines - 2, 0);
    clrtoeol();
    move(t_lines - 3, 0);
    clrtoeol();

    /*
     * Leeward 98.04.16
     */
    switch (BMch) {
        case BM_MARK:
            if ((fh->accessed[0] & FILE_MARKED)!=0)
		    func_arg.setflag=false;
            break;
        case BM_MARKDEL:
            if ((fh->accessed[1] & FILE_DEL)!=0)
		    func_arg.setflag=false;
            break;
        case BM_NOREPLY:
            if ((fh->accessed[1] & FILE_READ)!=0)
		    func_arg.setflag=false;
            break;
        default:
            break;
    }
    snprintf(buf, 256, "�������һƪ��ʼ%s%s (Y)��һƪ (N)Ŀǰ��ƪ (C)ȡ�� (Y/N/C)? [Y]: ",
              func_arg.setflag?"":"ȡ��",SR_BMitems[BMch - 1]);
    getdata(t_lines - 3, 0, buf, ch, 3, DOECHO, NULL, true);
    switch (ch[0]) {
    case 'c':
    case 'C':
        saveline(t_lines - 2, 1, NULL);
        saveline(t_lines - 3, 1, linebuffer);
        return DONOTHING;
    case 'N':
    case 'n':
        fromfirst=false;
        break;
    default:
        fromfirst=true;
        break;
    }
    bmlog(getCurrentUser()->userid, currboard->filename, 14, 1);

    if(BM_TOTAL == BMch ){ //���ϼ�
        sprintf(annpath,"tmp/bm.%s",getCurrentUser()->userid);
        if(dashf(annpath))unlink(annpath);
        snprintf(buf, 256, "�Ƿ�������(Y/N/C)? [Y]: ");
        getdata(t_lines - 2, 0, buf, ch, 3, DOECHO, NULL, true);
        switch (ch[0]){
        case 'n':
        case 'N':
            func_arg.saveorigin=false;
            break;
        case 'c':
        case 'C':
            saveline(t_lines - 2, 1, NULL);
            saveline(t_lines - 3, 1, linebuffer);
            return DONOTHING;
        default:
            func_arg.saveorigin=true;
        }
    } else if (BMch==BM_IMPORT) {
        if (set_import_path(annpath)!=0) {
            saveline(t_lines - 2, 1, NULL);
            saveline(t_lines - 3, 1, linebuffer);
            return DONOTHING;
        }
        func_arg.announce_path=annpath;
    }

    func_arg.action=BMch;
    ent=conf->pos;
    init_write_dir_arg(&dirarg);
    dirarg.fd=arg->fd;
    dirarg.needlock=false;
    arg->writearg=&dirarg;

    flock(arg->fd,LOCK_EX);
    if (fromfirst) {
        /*�ߵ���һƪ*/
        conf->new_pos=0; /* atppp 20051019 */
        apply_thread(conf,fh,fileheader_thread_read,false,false,(void*)SR_FIRST);
        if (conf->new_pos!=0)
            conf->pos=conf->new_pos;
    }
    apply_thread(conf,fh,BM_thread_func,true,true,(void*)&func_arg);
    flock(arg->fd,LOCK_UN);
    free_write_dir_arg(&dirarg);
    arg->writearg=NULL;
    conf->pos=ent; /*�ָ�ԭ����ent*/
	if(BMch == BM_DELETE){
		/* set .ORIGIN */
	}
    if(BM_TOTAL == BMch){ //���ϼ�
        char title[STRLEN];
        //create new title
        strcpy(buf,"[�ϼ�] ");
        if(!strncmp(fh->title,"Re: ",4))strcpy(buf+7,fh->title + 4);
        else
            strcpy(buf+7,fh->title);
        if(strlen(buf) >= STRLEN )buf[STRLEN-1] = 0;
        strcpy(title,buf);
        //post file to the board
        if(post_file(getCurrentUser(),"",annpath,currboard->filename,title,0,2,getSession()) < 0) {//fail
            sprintf(buf,"�������µ��������!�밴 ENTER ���˳� << ");
            a_prompt(-1,buf,title);
            saveline(t_lines - 2, 1, NULL);
            saveline(t_lines - 3, 1, linebuffer);
        }
        unlink(annpath);
        sprintf(annpath,"tmp/se.%s",getCurrentUser()->userid);
        unlink(annpath);
        return DIRCHANGED;
    }
    return DIRCHANGED;
}

#define SPLIT_THREAD_MAXNUM 1000
struct split_thread_arg{
       int num;
       int oldid[SPLIT_THREAD_MAXNUM];
};

static int split_thread_me(struct _select_def* conf, struct fileheader* fh,int ent, void* extraarg)
{
    struct read_arg* arg=(struct read_arg*)conf->arg;
    struct split_thread_arg* func_arg=(struct split_thread_arg*)extraarg;
    int ret=APPLY_CONTINUE;
       int i;

    conf->pos=ent;
    if (arg->writearg) {
        arg->writearg->ent=ent;
    }
       if( func_arg->num >= SPLIT_THREAD_MAXNUM ) return ret;
       for(i=0;i<func_arg->num; i++){
               if(fh->reid == func_arg->oldid[i]){
                       break;
               }
       }
       if(i>0 && i==func_arg->num) return ret;

       func_arg->oldid[func_arg->num] = fh->id;
       func_arg->num ++;

       fh->groupid = func_arg->oldid[0];
       if(func_arg->num == 1){
               char buf[256];
               fh->reid = func_arg->oldid[0];
               if(!strncmp(fh->title, "Re: ", 4)){
                       strncpy(buf, fh->title+4, 256);
                       if(*buf)
                               strcpy(fh->title, buf);
               }
       }

    return ret;
}

static int split_thread(struct _select_def* conf, struct fileheader* fh, void* extraarg)
{
    int ent;
    char buf[STRLEN];
    struct split_thread_arg func_arg;
    struct read_arg* arg=(struct read_arg*)conf->arg;
    struct write_dir_arg dirarg;

    if (fh==NULL)
        return DONOTHING;
    if (!chk_currBM(currBM, getCurrentUser())) {
        return DONOTHING;
    }
    if (arg->mode != DIR_MODE_NORMAL)     /* KCN:�ݲ����� */
        return DONOTHING;
    if (conf->pos>arg->filecount) /*�ö�*/
        return DONOTHING;
    if (fh->id == fh->groupid)
        return DONOTHING;

    a_prompt(-1, "�з����⣬�������޷���ԭ��ȷ����(Y/N) [N] ", buf);
    if (buf[0] != 'Y' && buf[0] != 'y') {     /* if not yes quit */
        return FULLUPDATE;
    }

    func_arg.num = 0;

    ent=conf->pos;
    init_write_dir_arg(&dirarg);
    dirarg.fd=arg->fd;
    dirarg.needlock=false;
    arg->writearg=&dirarg;

    flock(arg->fd,LOCK_EX);
    apply_thread(conf,fh,split_thread_me,true,true,(void*)&func_arg);
    flock(arg->fd,LOCK_UN);
    free_write_dir_arg(&dirarg);
    arg->writearg=NULL;
    conf->pos=ent; /*�ָ�ԭ����ent*/

    return DIRCHANGED;
}


#ifdef FB2KPC
int read_my_pc(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg)
{	
	Personal("*");
	return FULLUPDATE;
}

#endif

static int prompt_newkey(struct _select_def* conf,struct fileheader *fileinfo,void* extraarg) {
    char *prompt = (char*)extraarg;

    clear();
    move(3,0);
    prints("\t\t\033[1;4;31m%s��ݼ����յ�һ����\033[0m\n\n", BBS_FULL_NAME);
    prints("\t\tͬ־�ǣ������ǣ���ݼ���������\n\n\t\t���ղŰ��ļ��Ѿ�������վ��˽�̡�\n\n");
    prints("\t\t\033[1;32m%s\033[0m\n",prompt);
    move(13,50); prints("\033[1;31m�q����r");
	move(14,50); prints("{/-���-\\}");
 	move(15,50); prints(" ( (oo) )");
 	move(16,50); prints("    ��\033[0m");
    pressanykey();
    return FULLUPDATE;    
}

#ifdef HAPPY_BBS
/* ipv1: Let a bm modify his board's title freely. */
static int b_modify_title(struct _select_def* conf, struct fileheader* fh, void* extraarg)
{   
    char ans[STRLEN],pmt[STRLEN];
    char *buf;
    struct boardheader newfh;
    int pos;
    if (!chk_currBM(currBM, getCurrentUser())) {
        return DONOTHING;
    }
    buf=&currboard->title[13];
    strcpy(ans, buf);
    move(t_lines - 1, 0);    
    getdata(t_lines - 1, 0, "�޸ı�����������", ans, STRLEN - 1, DOECHO, NULL, false);
    if(!strcmp(ans,"")) return FULLUPDATE;
    if(!strcmp(ans,buf)) return FULLUPDATE;
    if (!(currboard->flag & BOARD_SUPER_CLUB) && !((currboard->title[0]=='P')&&(currboard->title[1]=='_'))) {
        sprintf(pmt, "�޸� %s ��������", currboard->filename); 
    	securityreport(pmt, NULL,NULL, getSession());
    }
    ans[STRLEN-14]='\0';
    pos = getboardnum(currboard->filename,&newfh);
    newfh.title[13]='\0';
    strcat(newfh.title,ans);
    set_board(pos, &newfh, NULL);
    return FULLUPDATE;
}
#endif

static struct key_command read_comms[] = { /*�Ķ�״̬�������� */
    {'r', (READ_KEY_FUNC)read_post,NULL},
    {'K', (READ_KEY_FUNC)skip_post,NULL},

    {'d', (READ_KEY_FUNC)del_post,(void*)0},
    {'D',(READ_KEY_FUNC)delete_range,NULL},
    {Ctrl('C'), (READ_KEY_FUNC)do_cross,NULL},
    {'Y', (READ_KEY_FUNC)UndeleteArticle,NULL},     /* Leeward 98.05.18 */
    {Ctrl('P'), (READ_KEY_FUNC)do_post,NULL},
    {'E', (READ_KEY_FUNC)edit_post,NULL},
    {'T', (READ_KEY_FUNC)edit_title,NULL},
    {':', (READ_KEY_FUNC)fhselect,NULL},
            
    {'m', (READ_KEY_FUNC)set_article_flag,(void*)FILE_MARK_FLAG},
    {';', (READ_KEY_FUNC)noreply_post,(void*)NULL},        /*Haohmaru.99.01.01,�趨����reģʽ */
    {'#', (READ_KEY_FUNC)set_article_flag,(void*)FILE_SIGN_FLAG},           /* Bigman: 2000.8.12  �趨���±��ģʽ */
    {'%', (READ_KEY_FUNC)set_article_flag,(void*)FILE_PERCENT_FLAG},           /* asing: 2004.4.16  �趨���±��ģʽ */
#ifdef FILTER
    {'@', (READ_KEY_FUNC)set_article_flag,(void*)FILE_CENSOR_FLAG},         /* czz: 2002.9.29 ��˱��������� */
#endif
    {'g', (READ_KEY_FUNC)set_article_flag,(void*)FILE_DIGEST_FLAG},
    {'t', (READ_KEY_FUNC)set_article_flag,(void*)FILE_DELETE_FLAG},     /*KCN 2001 */
    {'|', (READ_KEY_FUNC)split_thread,NULL},

    {'G', (READ_KEY_FUNC)range_flag,(void*)FILE_TITLE_FLAG},
        
    {'H', (READ_KEY_FUNC)read_callfunc0, (void *)read_hot_info},   /* flyriver: 2002.12.21 ����������Ϣ��ʾ */
        
    {Ctrl('G'), (READ_KEY_FUNC)change_mode,(void*)0},   /* bad : 2002.8.8 add marked mode */
    {'`', (READ_KEY_FUNC)prompt_newkey,(void*)"��ʹ�� Ctrl+G 1 ������ժ��"},
    {'.', (READ_KEY_FUNC)change_mode,(void*)DIR_MODE_DELETED},
    {'>', (READ_KEY_FUNC)change_mode,(void*)DIR_MODE_JUNK},
    {Ctrl('T'), (READ_KEY_FUNC)prompt_newkey,(void*)"��ʹ�� Ctrl+G 2 ����ͬ�����Ķ�"},

    {'s', (READ_KEY_FUNC)do_select,NULL},
    {'x', (READ_KEY_FUNC)into_announce,NULL},
    {'v', (READ_KEY_FUNC)read_callfunc0, (void *)i_read_mail},         /* period 2000-11-12 read mail in article list */

    {'i', (READ_KEY_FUNC)Save_post,NULL},
    {'J', (READ_KEY_FUNC)Semi_save,NULL},
    {'I', (READ_KEY_FUNC)Import_post,NULL},

    {'F', (READ_KEY_FUNC)mail_forward,NULL},
    {Ctrl('R'), (READ_KEY_FUNC)post_reply,NULL},

    {'c', (READ_KEY_FUNC)clear_new_flag,NULL},
    {'f', (READ_KEY_FUNC)clear_all_new_flag,NULL},

    {'n',  (READ_KEY_FUNC)thread_read,(void*)SR_FIRSTNEW},
    {Ctrl('N'), (READ_KEY_FUNC)prompt_newkey,(void*)"��ʹ�� n ��ת���������һƪδ��"},
    {'\\', (READ_KEY_FUNC)thread_read,(void*)SR_LAST},
    {'=', (READ_KEY_FUNC)thread_read,(void*)SR_FIRST},

    {'a', (READ_KEY_FUNC)auth_search,(void*)false},
    {'A', (READ_KEY_FUNC)auth_search,(void*)true},
    {'/', (READ_KEY_FUNC)title_search,(void*)false},
    {'?', (READ_KEY_FUNC)title_search,(void*)true},
    {']', (READ_KEY_FUNC)thread_read,(void*)SR_NEXT},
    {'[', (READ_KEY_FUNC)thread_read,(void*)SR_PREV},

    {Ctrl('A'), (READ_KEY_FUNC)read_showauthor,NULL},
    {'~',(READ_KEY_FUNC)read_authorinfo,NULL},
    {Ctrl('W'), (READ_KEY_FUNC)read_showauthorBM,NULL}, 
    {Ctrl('O'), (READ_KEY_FUNC)read_addauthorfriend,NULL},
#ifdef DENYANONY
    {Ctrl('Y'), (READ_KEY_FUNC)deny_anony,NULL},
#else
    {Ctrl('Y'), (READ_KEY_FUNC)read_zsend,NULL},
#endif
#ifdef PERSONAL_CORP
    {'y', (READ_KEY_FUNC)read_importpc,NULL},
#endif

    {'\'',(READ_KEY_FUNC)post_search,(void*)false},
    {'\"', (READ_KEY_FUNC)post_search,(void*)true},

    {'R',  (READ_KEY_FUNC)b_results,NULL},
    {'V',  (READ_KEY_FUNC)b_vote,NULL},
    {'M',  (READ_KEY_FUNC)b_vote_maintain,NULL},
    {'W',  (READ_KEY_FUNC)b_note_edit_new,NULL},
    {'h',  (READ_KEY_FUNC)mainreadhelp,NULL},
#ifdef FB2KPC
    {'X',  (READ_KEY_FUNC)read_my_pc,NULL},     //�༭������ٲ�ίԱ����,stephen on 2001.11.1 
#else
    {'X',  (READ_KEY_FUNC)b_jury_edit,NULL},     //�༭������ٲ�ίԱ����,stephen on 2001.11.1 
#endif
    {KEY_TAB,  (READ_KEY_FUNC)show_b_note,NULL},
    {Ctrl('D'), (READ_KEY_FUNC)deny_user,NULL},
    {Ctrl('E'), (READ_KEY_FUNC)clubmember,NULL},
    {'z', (READ_KEY_FUNC)read_sendmsgtoauthor,NULL},
    {'Z', (READ_KEY_FUNC)prompt_newkey,(void*)"��ʹ�� z �����߷�ѶϢ"},

    {'p',  (READ_KEY_FUNC)thread_read,(void*)SR_READ},
    {Ctrl('S'), (READ_KEY_FUNC)prompt_newkey,(void*)"��ʹ�� p ����ͬ�����Ķ�"},
#ifdef FB2KPC
    {Ctrl('X'), (READ_KEY_FUNC)into_PAnnounce,NULL},
#else
    {Ctrl('X'), (READ_KEY_FUNC)thread_read,(void*)SR_READX},
#endif
    
    {Ctrl('U'), (READ_KEY_FUNC)author_read,(void*)SR_READ},
    {Ctrl('H'), (READ_KEY_FUNC)author_read,(void*)SR_READX}, 
    /*----------------------------------------*/
    {'S', (READ_KEY_FUNC)sequential_read,NULL},
    
    {'b', (READ_KEY_FUNC)SR_BMFunc,(void*)true},
    {'B', (READ_KEY_FUNC)prompt_newkey,(void*)"��ʹ�� b ����ͬ�������"},
    {',', (READ_KEY_FUNC)read_splitscreen,NULL},
    {'!', (READ_KEY_FUNC)read_callfunc0,(void *)Goodbye},
    {Ctrl('Q'), (READ_KEY_FUNC)showinfo,NULL},
    {'^', (READ_KEY_FUNC)jumpReID,NULL},
    {'&',(READ_KEY_FUNC)jump_changed_title,NULL},
    {'\0', NULL},
};

int Read()
{
    char buf[2 * STRLEN];
    char notename[STRLEN];
    time_t usetime;
    struct stat st;
    int bid;
    int returnmode=CHANGEMODE;
#ifdef NEW_HELP
    int oldhelpmode;
#endif

    if (!selboard||!currboard) {
        move(2, 0);
        prints("����ѡ��������\n");
        pressreturn();
        move(2, 0);
        clrtoeol();
        return -1;
    }
    in_mail = false;
    bid = getbnum_safe(currboard->filename,getSession());

    currboardent=bid;
    currboard=(struct boardheader*)getboard(bid);

    if (currboard->flag&BOARD_GROUP) return -2;
#ifdef HAVE_BRC_CONTROL
    brc_initial(getCurrentUser()->userid, currboard->filename,getSession());
#endif

    setbdir(DIR_MODE_NORMAL, buf, currboard->filename);

    board_setcurrentuser(uinfo.currentboard, -1);
    uinfo.currentboard = currboardent;;
    UPDATE_UTMP(currentboard,uinfo);
    board_setcurrentuser(uinfo.currentboard, 1);
    
    setvfile(notename, currboard->filename, "notes");
    if (stat(notename, &st) != -1) {
        if (st.st_mtime < (time(NULL) - 7 * 86400)) {
/*            sprintf(genbuf,"touch %s",notename);
	    */
            f_touch(notename);
            setvfile(genbuf, currboard->filename, "noterec");
            my_unlink(genbuf);
        }
    }
    if (vote_flag(currboard->filename, '\0', 1 /*�������µı���¼û */ ) == 0) {
        if (dashf(notename)) {
            /*
             * period  2000-09-15  disable ActiveBoard while reading notes 
             */
            modify_user_mode(READING);
            /*-	-*/
            ansimore(notename, true);
            vote_flag(currboard->filename, 'R', 1 /*д������µı���¼ */ );
        }
    }
    usetime = time(0);
#ifdef NEW_HELP
    oldhelpmode = helpmode;
    helpmode = HELP_ARTICLE;
#endif
    while ((returnmode==CHANGEMODE)&&!(currboard->flag&BOARD_GROUP)) {
    returnmode=new_i_read(DIR_MODE_NORMAL, buf, readtitle, (READ_ENT_FUNC) readdoent, &read_comms[0], sizeof(struct fileheader));  /*���뱾�� */
    setbdir(DIR_MODE_NORMAL, buf, currboard->filename);
    }
#ifdef NEW_HELP
    helpmode = oldhelpmode;
#endif
    newbbslog(BBSLOG_BOARDUSAGE, "%-20s Stay: %5ld", currboard->filename, time(0) - usetime);
    bmlog(getCurrentUser()->userid, currboard->filename, 0, time(0) - usetime);
    bmlog(getCurrentUser()->userid, currboard->filename, 1, 1);

    board_setcurrentuser(uinfo.currentboard, -1);
    uinfo.currentboard = 0;
    UPDATE_UTMP(currentboard,uinfo);
    return returnmode;
}

/* etnlegend, 2006.05.28, �Ķ�ʮ�� ... */

#ifndef USE_PRIMORDIAL_TOP10

static void read_top_title(struct _select_def *conf){
    struct BoardStatus *bs;
    char header[PATHLEN],title[STRLEN],BM[BM_LEN],*p,*q;
    int chkmailflag,bid,online;
    if(!(bid=getbid(currboard->filename,NULL))||!(bs=getbstatus(bid)))
        return;
    if(!(currboard->BM[0]))
        sprintf(header,"����������");
    else{
        snprintf(BM,BM_LEN,"%s",currboard->BM);
        if(
#ifdef OPEN_BMONLINE
            1
#else /* OPEN_BMONLINE */
            check_board_delete_read_perm(getCurrentUser(),currboard,0)
#endif /* OPEN_BMONLINE */
            ){
            sprintf(header,"%s","����: ");
            q=&header[strlen(header)];
            for(online=0,p=strtok(BM," ");p;p=strtok(NULL," ")){
                if(apply_utmp(NULL,1,p,NULL)){
                    if(!online){
                        sprintf(q,"%s","\033[32m");
                        q=&header[strlen(header)];
                        online=1;
                    }
                }
                else{
                    if(online){
                        sprintf(q,"%s","\033[33m");
                        q=&header[strlen(header)];
                        online=0;
                    }
                }
                sprintf(q,"%s ",p);
                q=&header[strlen(header)];
            }
            sprintf(q,"%s","\033[m");
        }
        else
            sprintf(header,"����: %s",BM);
    }
    chkmailflag=chkmail();
    if(chkmailflag==2)
        sprintf(title,"%s","[�������䳬������,����������!]");
    else if(chkmailflag)
        sprintf(title,"%s","[�����ż�]");
    else
        sprintf(title,"%s",&(currboard->title[13]));
    showtitle(header,title);
    update_endline();
    move(1, 0);
    clrtoeol();
    sprintf(genbuf,"%s\033[m",DEFINE(getCurrentUser(),DEF_HIGHCOLOR)?(
        "\033[1;31m[ʮ��ģʽ] \033[1;37m�뿪[\033[1;32m��\033[1;37m,\033[1;32me\033[1;37m] "
        "ѡ��[\033[1;32m��\033[1;37m,\033[1;32m��\033[1;37m] �Ķ�[\033[1;32m��\033[1;37m,\033[1;32mr\033[1;37m] "
        "����[\033[1;32mh\033[1;37m] ͬ����[\033[1;32m^X\033[1;37m,\033[1;32mp\033[1;37m] "
        "ͬ����[\033[1;32m^U\033[1;37m,\033[1;32m^H\033[1;37m]"):(
        "\033[31m[ʮ��ģʽ] \033[37m�뿪[\033[1;32m��\033[37m,\033[32me\033[37m] "
        "ѡ��[\033[32m��\033[37m,\033[32m��\033[37m] �Ķ�[\033[32m��\033[37m,\033[32mr\033[37m] "
        "����[\033[32mh\033[37m] ͬ����[\033[32m^X\033[37m,\033[32mp\033[37m] "
        "ͬ����[\033[32m^U\033[37m,\033[32m^H\033[37m]"));
    prints("%s",genbuf);
    move(2, 0);
    setfcolor(WHITE,1);
    setbcolor(BLUE);
    clrtoeol();
    sprintf(genbuf,"%-80.80s","  ���   �� �� ��     ��  ��  ���±���");
    prints("%s",genbuf);
    sprintf(title,"����: %-4d [ʮ��ģʽ]",bs->currentusers);
    move(2,-(strlen(title)+1));
    prints("%s",title);
    resetcolor();
    return;
}

static char* read_top_ent(char *buf,int num,struct fileheader *fh,struct fileheader *read_fh,struct _select_def *conf){
    char date[8],title[48],threadprefix[2][16],threadsuffix[16],*highstr;
    int type,isreply,isthread,attachch;
    time_t ftime;
    type=get_article_flag(fh,getCurrentUser(),currboard->filename,0,NULL,getSession());
    if((ftime=get_posttime(fh))>740000000)
        snprintf(date,7,"%s",ctime(&ftime)+4);
    else
        date[0]=0;
    attachch=((fh->attachment)?'@':' ');
	strnzhcpy(title,fh->title,45);
    isthread=(read_fh&&read_fh->groupid==fh->groupid);
    isreply=((fh->groupid!=fh->id)&&(!strncasecmp(title,"Re: ",4)||!strncmp(title,"�ظ�: ",6)));
    highstr=DEFINE(getCurrentUser(),DEF_HIGHCOLOR)?"1;":"";
	if(isthread){
        sprintf(threadprefix[0],"\033[%s%dm",highstr,(isreply?36:33));
        sprintf(threadprefix[1],"\033[%s%dm.",highstr,(isreply?36:33));
        sprintf(threadsuffix,"%s","\033[m");
	}else{
		threadprefix[0][0]=0;
        threadprefix[1][0]=' ';
        threadprefix[1][1]=0;
		threadsuffix[0]=0;
	}
    sprintf(buf," %s%4d%s %c %-12.12s %s%s%c%s%s%s ",threadprefix[0],num,threadsuffix,type,fh->owner,date,
        threadprefix[1],attachch,(isreply?"":FIRSTARTICLE_SIGN" "),title,threadsuffix);
    return buf;
}

static int read_top_post(struct _select_def *conf,struct fileheader *fh,void *varg){
    struct read_arg *arg;
    char buf[PATHLEN],*p;
    int key,repeat,ret;
    if(!fh||(fh->owner[0]=='-'))
        return DONOTHING;
    snprintf(buf,PATHLEN,"%s",read_getcurrdirect(conf));
    if(!(p=strrchr(buf,'/')))
        return DONOTHING;
    *++p=0;
    clear();
    snprintf(p,(PATHLEN-(p-buf)),"%s",fh->filename);
    snprintf(quote_board,BOARDNAMELEN,"%s",currboard->filename);
    snprintf(quote_title,ARTICLE_TITLE_LEN,"%s",fh->title);
    snprintf(quote_user,OWNER_LEN,"%s",fh->owner);
    register_attach_link(board_attach_link,fh);
    key=ansimore_withzmodem(buf,false,fh->title);
    register_attach_link(NULL,NULL);
#ifdef HAVE_BRC_CONTROL
    brc_add_read(fh->id,currboardent,getSession());
#endif /* HAVE_BRC_CONTROL */
    arg=(struct read_arg*)conf->arg;
    move(t_lines-1,0);
    clrtoeol();
    switch(arg->readmode){
        case READ_THREAD:
            sprintf(genbuf,"%s","\033[36;44m[ʮ��ģʽ] \033[31;44m[ͬ�����Ķ�]\033[33;44m "
                "���� Q,�� | ��һƪ �� | ��һƪ <Space>,�� ");
            break;
        case READ_AUTHOR:
            sprintf(genbuf,"%s","\033[36;44m[ʮ��ģʽ] \033[31;44m[ͬ�����Ķ�]\033[33;44m "
                "���� Q,�� | ��һƪ �� | ��һƪ <Space>,�� ");
            break;
        default:
            sprintf(genbuf,"%s","\033[36;44m[ʮ��ģʽ] \033[31;44m[�Ķ�����]\033[33;44m "
                "���� Q,�� | ��һƪ �� | ��һƪ <Space>,�� | ͬ���� ^X,p ");
            break;
    }
    prints(DEFINE(getCurrentUser(),DEF_HIGHCOLOR)?"\033[1m%s\033[m":"%s\033[m",genbuf);
    resetcolor();
    setreadpost(conf,fh);
    ret=FULLUPDATE;
    if(!(key==KEY_RIGHT||key==KEY_PGUP||key==KEY_UP||key==KEY_DOWN)&&(!(key>0)||!strchr("RrEexp",key)))
        key=igetkey();
    repeat=0;
    do{
        if(repeat)
            key=igetkey();
        repeat=0;
        switch(key){
            case KEY_LEFT:
            case 'Q':
            case 'q':
            case KEY_REFRESH:
                break;
            case KEY_DOWN:
            case KEY_PGDN:
            case ' ':
            case 'j':
            case 'n':
                return READ_NEXT;
            case KEY_UP:
            case KEY_PGUP:
            case 'k':
            case 'l':
                return READ_PREV;
            case KEY_RIGHT:
            case 'p':
            case Ctrl('X'):
            case Ctrl('S'):
            case Ctrl('U'):
            case Ctrl('H'):
                if(arg->readmode!=READ_NORMAL)
                    return READ_NEXT;
                move(t_lines-1,0);
                clrtoeol();
                sprintf(genbuf,"%s",
                    (key==Ctrl('U')||key==Ctrl('H'))?
                    "\033[36;44m[ʮ��ģʽ] \033[31;44m[ͬ�����Ķ�]\033[33;44m "
                    "���� Q,�� | ��һƪ �� | ��һƪ <Space>,�� ":
                    "\033[36;44m[ʮ��ģʽ] \033[31;44m[ͬ�����Ķ�]\033[33;44m "
                    "���� Q,�� | ��һƪ �� | ��һƪ <Space>,�� "
                    );
                prints(DEFINE(getCurrentUser(),DEF_HIGHCOLOR)?"\033[1m%s\033[m":"%s\033[m",genbuf);
                arg->readmode=((key==Ctrl('U')||key==Ctrl('H'))?READ_AUTHOR:READ_THREAD);
                arg->oldpos=((key==KEY_RIGHT||key==Ctrl('X')||key==Ctrl('H'))?conf->pos:0);
                repeat=1;
                break;
            case 'R':
            case 'r':
            case 'Y':
            case 'y':
                clear();
                move(5,0);
                if(currboard->flag&BOARD_NOREPLY)
                    prints("\t\t\033[1;33m%s\033[0;33m<Enter>\033[m","�ð���������Ϊ���ɻظ�����...");
                else if(fh->accessed[1]&FILE_READ)
                    prints("\t\t\033[1;33m%s\033[0;33m<Enter>\033[m","����������Ϊ���ɻظ�, ������ͼ����...");
                else{
                    do_reply(conf,fh);
                    return DIRCHANGED;
                }
                WAIT_RETURN;
                break;
            case Ctrl('R'):
                post_reply(conf,fh,NULL);
                break;
            case Ctrl('A'):
                clear();
                read_showauthor(conf,fh,NULL);
                return READ_NEXT;
            case Ctrl('Z'):
            case 'H':
                r_lastmsg();
                break;
            case 'Z':
            case 'z':
                if(HAS_PERM(getCurrentUser(),PERM_PAGE)){
                    read_sendmsgtoauthor(conf,fh,NULL);
                    return READ_NEXT;
                }
                break;
            case 'u':
                clear();
                modify_user_mode(QUERY);
                t_query(NULL);
                break;
            case 'L':
                if(uinfo.mode==LOOKMSGS)
                    return DONOTHING;
                show_allmsgs();
                break;
            case 'O':
            case 'o':
                if(HAS_PERM(getCurrentUser(),PERM_BASIC)){
                    t_friends();
                }
                break;
            case 'U':
                return board_query();
            case Ctrl('O'):
                clear();
                read_addauthorfriend(conf,fh,NULL);
                return READ_NEXT;
            case '~':
                ret=read_authorinfo(conf,fh,NULL);
                break;
            case Ctrl('W'):
                ret=read_showauthorBM(conf,fh,NULL);
                break;
            case Ctrl('D'):
                zsend_attach(conf->pos,fh,read_getcurrdirect(conf));
                break;
            case Ctrl('Y'):
                read_zsend(conf,fh,NULL);
                break;
            case '!':
                Goodbye();
                break;
        }
    }
    while(repeat);
    if(ret==FULLUPDATE&&arg->oldpos!=0){
        conf->new_pos=arg->oldpos;
	    arg->oldpos=0;
	    list_select_add_key(conf,KEY_REFRESH);
        arg->readmode=READ_NORMAL;
        return SELCHANGE;
    }
    return ret;
}

static int read_top_edit_title(struct _select_def *conf,struct fileheader *fh,void *varg){
    struct read_arg *arg=(struct read_arg*)(conf->arg);
    char buf[STRLEN],path[PATHLEN];
    int change,index;
    switch(deny_modify_article(currboard,fh,arg->mode,getSession())){
        case 0:
            break;
        case -2:
            move(3,0);
            clrtobot();
            move(5,0);
            prints("\t\t\033[1;33m%s\033[0;33m<Enter>\033[m","���ѱ�������Աȡ���ڵ�ǰ����ķ���Ȩ��...");
            WAIT_RETURN;
            return FULLUPDATE;
        case -5:
            check_readonly(currboard->filename);
            return FULLUPDATE;
        default:
            return DONOTHING;
    }
    snprintf(buf,STRLEN,"%s",fh->title);
    getdata(t_lines-1,0,"�����±���: ",buf,78,DOECHO,NULL,false);
    if(!(change=(buf[0]&&strcmp(buf,fh->title))))
        return PARTUPDATE;
#ifdef FILTER
    if(check_badword_str(buf,strlen(buf),getSession())){
        move(3,0);
        clrtobot();
        prints("\t\t\033[1;33m%s\033[0;33m<Enter>\033[m","�ñ����п��ܺ��в�������, �������...");
        WAIT_RETURN;
        return PARTUPDATE;
    }
#endif /* FILTER */
    process_control_chars(buf);
    setbfile(path,currboard->filename,fh->filename);
    add_edit_mark(path,2,buf,getSession());
    newbbslog(BBSLOG_USER,"read_top_edit_title %s %s %s",currboard->filename,fh->title,buf);
    strnzhcpy(fh->title,buf,ARTICLE_TITLE_LEN);
    setbdir(DIR_MODE_NORMAL,path,currboard->filename);
    if((index=get_ent_from_id(DIR_MODE_NORMAL,fh->id,currboard->filename))!=0)
        substitute_record(path,fh,sizeof(struct fileheader),index);
    if(fh->id==fh->groupid){
        if(setboardorigin(currboard->filename,-1))
            board_regenspecial(currboard->filename,DIR_MODE_ORIGIN,NULL);
        else{
            setbdir(DIR_MODE_ORIGIN,path,currboard->filename);
            if((index=get_ent_from_id(DIR_MODE_ORIGIN,fh->id,currboard->filename))!=0)
                substitute_record(path,fh,sizeof(struct fileheader),index);
        }
    }
    if(fh->accessed[0]&FILE_MARKED){
        if(setboardmark(currboard->filename,-1))
            board_regenspecial(currboard->filename,DIR_MODE_MARK,NULL);
        else{
            setbdir(DIR_MODE_MARK,path,currboard->filename);
            if((index=get_ent_from_id(DIR_MODE_MARK,fh->id,currboard->filename))!=0)
                substitute_record(path,fh,sizeof(struct fileheader),index);
        }
    }
    setboardtitle(currboard->filename,1);
    return PARTUPDATE;
}

static struct key_command read_top_comms[]={
    {'r',(READ_KEY_FUNC)read_top_post,NULL},
    {Ctrl('A'),(READ_KEY_FUNC)read_showauthor,NULL},
    {'K',(READ_KEY_FUNC)skip_post,NULL},
    {Ctrl('C'),(READ_KEY_FUNC)do_cross,NULL},
    {'v',(READ_KEY_FUNC)read_callfunc0,(void*)i_read_mail},
    {'=',(READ_KEY_FUNC)thread_read,(void*)SR_FIRST},
    {'^',(READ_KEY_FUNC)jumpReID,NULL},
    {'&',(READ_KEY_FUNC)jump_changed_title,NULL},
    {'z',(READ_KEY_FUNC)read_sendmsgtoauthor,NULL},
    {'p',(READ_KEY_FUNC)thread_read,(void*)SR_READ},
    {'F',(READ_KEY_FUNC)mail_forward,NULL},
    {'f',(READ_KEY_FUNC)clear_all_new_flag,NULL},
    {'c',(READ_KEY_FUNC)clear_new_flag,NULL},
    {'E',(READ_KEY_FUNC)edit_post,NULL},
    {'T',(READ_KEY_FUNC)read_top_edit_title,NULL},
    {Ctrl('R'),(READ_KEY_FUNC)post_reply,NULL},
    {']',(READ_KEY_FUNC)thread_read,(void*)SR_NEXT},
    {'[',(READ_KEY_FUNC)thread_read,(void*)SR_PREV},
    {'n',(READ_KEY_FUNC)thread_read,(void*)SR_FIRSTNEW},
    {'\\',(READ_KEY_FUNC)thread_read,(void*)SR_LAST},
    {'a',(READ_KEY_FUNC)auth_search,(void*)false},
    {'A',(READ_KEY_FUNC)auth_search,(void*)true},
    {'/',(READ_KEY_FUNC)title_search,(void*)false},
    {'?',(READ_KEY_FUNC)title_search,(void*)true},
    {'\'',(READ_KEY_FUNC)post_search,(void*)false},
    {'\"',(READ_KEY_FUNC)post_search,(void*)true},
    {'h',(READ_KEY_FUNC)mainreadhelp,NULL},
    {Ctrl('X'),(READ_KEY_FUNC)thread_read,(void*)SR_READX},
    {Ctrl('U'),(READ_KEY_FUNC)author_read,(void*)SR_READ},
    {Ctrl('H'),(READ_KEY_FUNC)author_read,(void*)SR_READX},
    {',',(READ_KEY_FUNC)read_splitscreen,NULL},
    {Ctrl('Q'),(READ_KEY_FUNC)showinfo,NULL},
    {Ctrl('O'),(READ_KEY_FUNC)read_addauthorfriend,NULL},
#ifdef PERSONAL_CORP
    {'y',(READ_KEY_FUNC)read_importpc,NULL},
#endif
    {'~',(READ_KEY_FUNC)read_authorinfo,NULL},
    {Ctrl('W'),(READ_KEY_FUNC)read_showauthorBM,NULL},
    {Ctrl('Y'),(READ_KEY_FUNC)read_zsend,NULL},
    {'!',(READ_KEY_FUNC)read_callfunc0,(void*)Goodbye},
    {0,NULL}
};

static int read_top(int index,int force){
/*
 *  ���� RT_INTERVAL �� RT_INTERVAL_FORCE ������� ----
 *
 *  �������궨���ʱ����(��λΪ��)���Ƹ���ʮ���������õ�����(.TOP.<gid>)���ɳڲ���, ������
 *  ��������(.DIR)���޸�ʱ���ʮ������(.TOP.<gid>)���޸�ʱ���� RT_INTERVAL ������, ���� .DIR
 *  �� .TOP.<gid> �²��Һ����Ѿ����� RT_INTERVAL_FORCE ��δ����, ��� .DIR �������� .TOP.<gid>,
 *  ����ֱ��ʹ��ԭ�е� .TOP.<gid>, ��Ȼ, ��� .TOP.<gid> �����ڻ��߲��� `force` ����Ϊ��Ҳ��
 *  ������������ .TOP.<gid>, ������������, �Ķ�ʮ���ϵͳ���Ĳ�����ͬ��������/����ؼ�������
 *  ����, ����ڹ�ģ���Ǻܴ��վ��������������Է��ĵĶ���Ϊ 0, ��Ŀǰ�˲��ִ�����δ��ˮľ��
 *  �������ȫ���ѹ������, ��� cvs ����Ȼ���� RT_INTERVAL/RT_INTERVAL_FORCE Ϊ 15/30 ��...
 *
 *  ע���� 2006 �� 10 �� 7 ��, etnlegend ...
 *
*/
#define RT_INTERVAL         15
#define RT_INTERVAL_FORCE   30
    static const struct flock lck_set={.l_type=F_WRLCK,.l_whence=SEEK_SET,.l_start=0,.l_len=0,.l_pid=0};
    static const struct flock lck_clr={.l_type=F_UNLCK,.l_whence=SEEK_SET,.l_start=0,.l_len=0,.l_pid=0};
    struct stat st_dir,st_top;
    const struct fileheader *ptr;
    char top[PATHLEN],dir[PATHLEN];
    int bid,ret,fd,count,i,u_mode,save_currboardent,save_uinfo_currentboard,missing;
#ifdef NEW_HELP
    int save_helpmode;
#endif /* NEW_HELP */
    unsigned int gid;
    ssize_t length,writen;
    time_t read_begin,read_end;
    void *vp;
    const void *data;
    save_currboardent=currboardent;
    save_uinfo_currentboard=uinfo.currentboard;
    bid=publicshm->top[index].bid;
    gid=publicshm->top[index].gid;
    currboardent=bid;
    if(!(currboard=(struct boardheader*)getboard(bid)))
        return -1;
    if(currboard->flag&BOARD_GROUP)
        return -2;
    in_mail=false;
#ifdef HAVE_BRC_CONTROL
    brc_initial(getCurrentUser()->userid,currboard->filename,getSession());
#endif /* HAVE_BRC_CONTROL */
    board_setcurrentuser(uinfo.currentboard,-1);
    uinfo.currentboard=currboardent;
    UPDATE_UTMP(currentboard,uinfo);
    board_setcurrentuser(uinfo.currentboard,1);
#ifdef NEW_HELP
    save_helpmode=helpmode;
    helpmode=HELP_ARTICLE;
#endif /* NEW_HELP */
    snprintf(dir,PATHLEN,"boards/%s/.DIR",currboard->filename);
    if(stat(dir,&st_dir)==-1||!S_ISREG(st_dir.st_mode))
        return -3;
    snprintf(top,PATHLEN,"boards/%s/.TOP.%u",currboard->filename,gid);
    ret=stat(top,&st_top);
    if(!((missing=(ret==-1&&errno==ENOENT))||(!ret&&S_ISREG(st_top.st_mode))))
        return -4;
#define RT_UPDATE (\
    ((st_top.st_mtime+RT_INTERVAL)<st_dir.st_mtime)||\
    ((st_top.st_mtime<st_dir.st_mtime)&&((st_top.st_mtime+RT_INTERVAL_FORCE)<time(NULL)))\
    )
    do{
        if(force||missing||RT_UPDATE){
            if((fd=open(dir,O_RDONLY,0644))==-1)
                return -5;
            vp=mmap(NULL,st_dir.st_size,PROT_READ,MAP_SHARED,fd,0);
            close(fd);
            if(vp==MAP_FAILED)
                return -6;
            if((fd=open(top,
                ((!force&&!missing)?(O_WRONLY|O_CREAT):(O_WRONLY|O_CREAT|O_TRUNC)),
                0644))==-1){
                munmap(vp,st_dir.st_size);
                return -7;
            }
            if(fcntl(fd,F_SETLKW,&lck_set)==-1){
                close(fd);
                munmap(vp,st_dir.st_size);
                return -8;
            }
            if(!force&&!missing){
                if(fstat(fd,&st_top)==-1){
                    fcntl(fd,F_SETLKW,&lck_clr);
                    close(fd);
                    munmap(vp,st_dir.st_size);
                    return -9;
                }
                if(!RT_UPDATE){
                    fcntl(fd,F_SETLKW,&lck_clr);
                    close(fd);
                    munmap(vp,st_dir.st_size);
                    continue;
                }
                if(ftruncate(fd,0)==-1){
                    fcntl(fd,F_SETLKW,&lck_clr);
                    close(fd);
                    munmap(vp,st_dir.st_size);
                    unlink(top);
                    return -10;
                }
            }
            for(ptr=((const struct fileheader*)vp),
                count=(st_dir.st_size/sizeof(struct fileheader)),
                i=0;i<count;i++){
                if(ptr[i].groupid==gid){
                    for(data=&ptr[i],length=sizeof(struct fileheader),writen=0;
                        writen!=-1&&length>0;vpm(data,writen),length-=writen){
                        writen=write(fd,data,length);
                    }
                    if(writen==-1){
                        fcntl(fd,F_SETLKW,&lck_clr);
                        close(fd);
                        munmap(vp,st_dir.st_size);
                        unlink(top);
                        return -11;
                    }
                }
            }
            fcntl(fd,F_SETLKW,&lck_clr);
            close(fd);
            munmap(vp,st_dir.st_size);
        }
    }
    while(0);
#undef RT_UPDATE
    u_mode=uinfo.mode;
    read_begin=time(NULL);
    ret=new_i_read(DIR_MODE_TOP10,top,read_top_title,(READ_ENT_FUNC)read_top_ent,read_top_comms,sizeof(struct fileheader));
    read_end=time(NULL);
    modify_user_mode(u_mode);
#ifdef NEW_HELP
    helpmode=save_helpmode;
#endif /* NEW_HELP */
    newbbslog(BBSLOG_BOARDUSAGE,"%-20s Stay: %5ld",currboard->filename,(read_end-read_begin));
    bmlog(getCurrentUser()->userid,currboard->filename,0,(read_end-read_begin));
    bmlog(getCurrentUser()->userid,currboard->filename,1,1);
    board_setcurrentuser(uinfo.currentboard,-1);
    uinfo.currentboard=save_uinfo_currentboard;
    UPDATE_UTMP(currentboard,uinfo);
    board_setcurrentuser(uinfo.currentboard,1);
    currboardent=save_currboardent;
    currboard=((struct boardheader*)getboard(save_currboardent));
#ifdef HAVE_BRC_CONTROL
    brc_initial(getCurrentUser()->userid,currboard->filename,getSession());
#endif /* HAVE_BRC_CONTROL */
    return ret;
#undef RT_INTERVAL
#undef RT_INTERVAL_FORCE
}

static int select_top(void){
#define ST_UPDATE_TOPINFO()                                                             \
    do{                                                                                 \
        version=publicshm->top_version;                                                 \
        for(total=0;total<10;total++){                                                  \
            if(!(publicshm->top[total].bid)||!(publicshm->top[total].gid)){             \
                break;                                                                  \
            }                                                                           \
        }                                                                               \
        if(!total||(stat("etc/posts/day",&st)==-1||!S_ISREG(st.st_mode))){              \
            move(t_lines-1,0);                                                          \
            clrtoeol();                                                                 \
            prints("\033[1;31m%s\033[0;33m<Enter>\033[m","Ŀǰ����ʮ�����Ż���!");      \
            WAIT_RETURN;                                                                \
            return -1;                                                                  \
        }                                                                               \
    }while(0)
    struct stat st;
    int total,index,key,valid_key,old_index,update;
    unsigned int version;
    index=0;
    update=1;
    ST_UPDATE_TOPINFO();
    do{
        if(update){
            ansimore("etc/posts/day",0);
            move(t_lines-1,6);
            prints("%s","\033[1;33m��λ\033[1;37m[\033[1;32mUP\033[1;37m,\033[1;32mDOWN\033[1;37m,\033[1;32mNUM\033[1;37m,"
                "\033[1;32mHOME\033[1;37m,\033[1;32mEND\033[1;37m]/\033[1;33m�˳�\033[1;37m[\033[1;32mESC\033[1;37m,"
                "\033[1;32mLEFT\033[1;37m,\033[1;32mQ\033[1;37m]/\033[1;33mѡ��\033[1;37m[\033[1;32mENTER\033[1;37m,"
                "\033[1;32mRIGHT\033[1;37m,\033[1;32mSPACE\033[1;37m]\033[m");
            update=0;
        }
        move((2+2*index),3);
        prints("\033[1;31m%2d\033[m",(index+1));
        move((3+2*index),2);
        prints("\033[1;33m%s\033[m","��");
        do{
            valid_key=1;
            old_index=-1;
            if(version!=publicshm->top_version){
                ST_UPDATE_TOPINFO();
                update=1;
            }
            else{
                switch(toupper(key=igetkey())){
                    case KEY_DOWN:
                        old_index=index++;
                        if(index==total)
                            index=0;
                        break;
                    case KEY_UP:
                        old_index=index--;
                        if(index==-1)
                            index=(total-1);
                        break;
                    case KEY_LEFT:
                    case KEY_ESC:
                    case 'Q':
                        return 0;
                    case KEY_HOME:
                        if(index!=0){
                            old_index=index;
                            index=0;
                        }
                        else
                            valid_key=0;
                        break;
                    case KEY_END:
                        if(index!=(total-1)){
                            old_index=index;
                            index=(total-1);
                        }
                        else
                            valid_key=0;
                        break;
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                        key-='1';
                        if(index!=key&&total>key){
                            old_index=index;
                            index=key;
                        }
                        else
                            valid_key=0;
                        break;
                    case '0':
                        if(index!=9&&total==10){
                            old_index=index;
                            index=9;
                        }
                        else
                            valid_key=0;
                        break;
                    case KEY_RIGHT:
                    case '\r':
                    case '\n':
                    case ' ':
                        if(read_top(index,0)<0){
                            move(t_lines-1,6);
                            clrtoeol();
                            prints("\033[1;31m%s\033[0;33m<Enter>\033[m","����ʮ����Ϣʱ��������!");
                            WAIT_RETURN;
                        }
                        update=1;
                        break;
                    default:
                        valid_key=0;
                        break;
                }
            }
        }
        while(!valid_key);
        if(old_index!=-1){
            move((2+2*old_index),3);
            prints("\033[31m%2d\033[m",(old_index+1));
            move((3+2*old_index),2);
            prints("%s","  ");
        }
    }
    while(1);
}

#endif /* USE_PRIMORDIAL_TOP10 */

/* END -- etnlegend, 2006.05.30, �Ķ�ʮ�� ... */

