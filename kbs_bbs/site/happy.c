#include "bbs.h"

const char alphabet[] = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";

const char seccode[SECNUM][5]={
	"0", "1", "2", "3", "4", "5", "6", "7", "8"
};

const char *permstrings[] = {
        "����Ȩ��",             /* PERM_BASIC */
        "����������",           /* PERM_CHAT */
        "������������",         /* PERM_PAGE */
        "��������",             /* PERM_POST */
        "ʹ����������ȷ",       /* PERM_LOGINOK */
        "��ֹ��������",         /* PERM_DENYPOST */
        "������",               /* PERM_CLOAK */
        "�ɼ�����",             /* PERM_SEECLOAK */
        "�����ʺ�",         /* PERM_XEMPT */
        "�༭ϵͳ����",         /* PERM_WELCOME */
        "����",                 /* PERM_BOARDS */
        "�ʺŹ���Ա",           /* PERM_ACCOUNTS */
        "HAPPY ������",       /* PERM_CHATCLOAK */
        "�������Ȩ��",           /* PERM_DENYRELAX */
        "ϵͳά������Ա",       /* PERM_SYSOP */
        "Read/Post ����",       /* PERM_POSTMASK */
        "�������ܹ�",           /* PERM_ANNOUNCE*/
        "�������ܹ�",           /* PERM_OBOARDS*/
        "������ܹ�",         /* PERM_ACBOARD*/
        "���� ZAP(������ר��)", /* PERM_NOZAP*/
        "������OP(Ԫ��Ժר��)", /* PERM_CHATOP */
        "ϵͳ�ܹ���Ա",         /* PERM_ADMIN */
        "�����ʺ�",           /* PERM_HONOR*/
        "���ֲ�����",           /* PERM_SECANC*/
        "�ٲ�ίԱ",           /* PERM_JURY*/
        "����Ȩ�� 7",           /* PERM_UNUSE?*/
        "��ɱ������",        /*PERM_SUICIDE*/
        "����Ȩ�� 9",           /* PERM_UNUSE?*/
        "��ϵͳ���۰�",           /* PERM_UNUSE?*/
        "���Mail",           /* PERM_DENYMAIL*/

};

/* You might want to put more descriptive strings for SPECIAL1 and SPECIAL2
   depending on how/if you use them. */
const char *user_definestr[] = {
    "�����",                 /* DEF_ACBOARD */
    "ʹ�ò�ɫ",                 /* DEF_COLOR */
    "�༭ʱ��ʾ״̬��",         /* DEF_EDITMSG */
    "������������ New ��ʾ",    /* DEF_NEWPOST */
    "ѡ����ѶϢ��",             /* DEF_ENDLINE */
    "��վʱ��ʾ��������",       /* DEF_LOGFRIEND */
    "�ú��Ѻ���",               /* DEF_FRIENDCALL */
    "ʹ���Լ�����վ����",       /* DEF_LOGOUT */
    "��վʱ��ʾ����¼",         /* DEF_INNOTE */
    "��վʱ��ʾ����¼",         /* DEF_OUTNOTE */
    "ѶϢ��ģʽ��������/����",  /* DEF_NOTMSGFRIEND */
    "�˵�ģʽѡ��һ��/����",  /* DEF_NORMALSCR */
    "�Ķ������Ƿ�ʹ���ƾ�ѡ��", /* DEF_CIRCLE */
    "�Ķ������α�ͣ춵�һƪδ��",       /* DEF_FIRSTNEW */
    "��Ļ����ɫ�ʣ�һ��/�任",  /* DEF_TITLECOLOR */
    "���������˵�ѶϢ",         /* DEF_ALLMSG */
    "���ܺ��ѵ�ѶϢ",           /* DEF_FRIENDMSG */
    "�յ�ѶϢ��������",         /* DEF_SOUNDMSG */
    "��վ��Ļ�����ѶϢ",       /* DEF_MAILMSG */
    "������ʱʵʱ��ʾѶϢ",     /*"���к�����վ��֪ͨ",    DEF_LOGININFORM */
    "�˵�����ʾ������Ϣ",       /* DEF_SHOWSCREEN */
    "��վʱ��ʾʮ������",       /* DEF_SHOWHOT */
    "��վʱ�ۿ����԰�",         /* DEF_NOTEPAD */
    "����ѶϢ���ܼ�: Enter/Esc",        /* DEF_IGNOREMSG */
    "ʹ�ø�������",                   /* DEF_HIGHCOLOR */
    "��վʱ�ۿ���վ����ͳ��ͼ", /* DEF_SHOWSTATISTIC Haohmaru 98.09.24 */
    "δ�����ʹ�� *",           /* DEF_UNREADMARK Luzi 99.01.12 */
    "ʹ��GB���Ķ�",             /* DEF_USEGB KCN 99.09.03 */
    "�Ժ��ֽ������ִ���",  
    "��ʾ��ϸ�û�����",  /*DEF_SHOWDETAILUSERDATA 2003.7.31 */
    "��ʾ��ʵ�û�����", /*DEF_REALDETAILUSERDATA 2003.7.31 */
    "",
	"��ʾ�Լ�������",           /* DEF_SHOWHOROSCOPE */
/*    "����ip", */             
	"��ʾ�ײ�������Ϣ"
};

const char    *explain[] = {
    "��վϵͳ",
    "�������",
    "Ⱥ����֯",
    "���˿ռ�",
    "�����Ļ�",
    "���Լ���",
    "��������",
    "֪�Ը���",
    "��������",
    NULL
};

const char    *groups[] = {
    "system.faq", /* GROUP_0 */
    "assoc.faq",  /* GROUP_1 */
    "comp.faq",   /* GROUP_2 */
    "game.faq",   /* GROUP_3 */
    "literal.faq",/* GROUP_4 */
    "inn.faq",    /* GROUP_5 */
    "sport.faq",  /* GROUP_6 */
    "talk.faq",   /* GROUP_7 */
    "news.faq",   /* GROUP_8 */
    NULL
};

const char *mailbox_prop_str[] =
{
	"����ʱ�����ż���������",
	"ɾ���ż�ʱ�����浽������",
	"���水 'v' ʱ����: �ռ���(OFF) / ����������(ON)",
};

const char secname[SECNUM][2][20]={
	{"��վϵͳ", "[HAPPYBBSϵͳ��]"},
	{"�������", "[WWW][FTP][У԰��]"},
	{"Ⱥ����֯", "[Ժϵ][Э��][����]"},
	{"���˿ռ�", "[��] [��] [��] [��]"},
	{"�����Ļ�", "[����][����][ѧ��]"},
	{"���Լ���", "[����][ϵͳ][��·]"},
	{"��������", "[����][����][��Ϸ]"},
	{"֪�Ը���", "[����][����]"},
	{"��������", "[δ����]"}
};

struct _shmkey
{
	char key[20];
	int value;
};

static const struct _shmkey shmkeys[]= {
{ "BCACHE_SHMKEY",  3693 },
{ "UCACHE_SHMKEY",  3696 },
{ "UTMP_SHMKEY",    3699 },
{ "ACBOARD_SHMKEY", 9013 },
{ "ISSUE_SHMKEY",   5010 },
{ "GOODBYE_SHMKEY", 5020 },
{ "PASSWDCACHE_SHMKEY", 3697 },
{ "STAT_SHMKEY",    5100 },
{ "CONVTABLE_SHMKEY",    5101 },
{ "MSG_SHMKEY",    5200 },
{    "",   0 }
};

int get_shmkey(char *s)
{
	int n=0;
	while(shmkeys[n].key!=0)
	{
		if(!strcasecmp(shmkeys[n].key, s))
			return shmkeys[n].value;
		n++;
	}
	return 0;
}

int
uleveltochar( char *buf, struct userec *lookupuser ) /* ȡ�û�Ȩ������˵�� Bigman 2001.6.24*/
{
    unsigned lvl;
    char userid[IDLEN + 2];

    lvl = lookupuser->userlevel;
    strncpy(userid, lookupuser->userid, IDLEN + 2);

    if (!(lvl & PERM_BASIC) && !(lookupuser->flags & GIVEUP_FLAG)) {
        strcpy(buf, "����");
        return 0;
    }
/*    if( lvl < PERM_DEFAULT )
    {
        strcpy( buf, "- --" );
        return 1;
    }
*/

    /* Bigman: �������Ĳ�ѯ��ʾ 2000.8.10 */
    /*if( lvl & PERM_ZHANWU ) strcpy(buf,"վ��"); */
    if ((lvl & PERM_ANNOUNCE) && (lvl & PERM_OBOARDS))
        strcpy(buf, "վ��");
    else if (lvl & PERM_JURY)
        strcpy(buf, "�ٲ�");    /* stephen :�������Ĳ�ѯ"�ٲ�" 2001.10.31 */
    else if (lvl & PERM_CHATCLOAK)
        strcpy(buf, "Ԫ��");
    else if (lvl & PERM_CHATOP)
        strcpy(buf, "ChatOP");
    else if (lvl & PERM_BOARDS)
        strcpy(buf, "����");
    else if (lvl & PERM_HORNOR)
        strcpy(buf, "����");
    /* Bigman: �޸���ʾ 2001.6.24 */
    else if (lvl & (PERM_LOGINOK)) {
        if (lookupuser->flags & GIVEUP_FLAG)
            strcpy(buf, "����");
        else if (!(lvl & (PERM_CHAT)) || !(lvl & (PERM_PAGE)) || !(lvl & (PERM_POST)) || (lvl & (PERM_DENYMAIL)) || (lvl & (PERM_DENYRELAX)))
            strcpy(buf, "����");
        else
            strcpy(buf, "�û�");
    } else if (lookupuser->flags & GIVEUP_FLAG)
        strcpy(buf, "����");
    else if (!(lvl & (PERM_CHAT)) && !(lvl & (PERM_PAGE)) && !(lvl & (PERM_POST)))
        strcpy(buf, "����");
    else
        strcpy(buf, "����");

/*    else {
        buf[0] = (lvl & (PERM_SYSOP)) ? 'C' : ' ';
        buf[1] = (lvl & (PERM_XEMPT)) ? 'L' : ' ';
        buf[2] = (lvl & (PERM_BOARDS)) ? 'B' : ' ';
        buf[3] = !(lvl & (PERM_POST)) ? 'p' : ' ';
        if( lvl & PERM_ACCOUNTS ) buf[3] = 'A';
        if( lvl & PERM_SYSOP ) buf[3] = 'S'; 
        buf[4] = '\0';
    }
*/

    return 1;
}
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

/* rrr - This is separated so I can suck it into the IRC source for use
   there too */

#include "modes.h"

char *
ModeType(mode)
int     mode;
{
    switch (mode) {
    case IDLE:
        return "";
    case NEW:
        return "��վ��ע��";
    case LOGIN:
        return "���뱾վ";
    case CSIE_ANNOUNCE:
        return "��ȡ����";
    case CSIE_TIN:
        return "ʹ��TIN";
    case CSIE_GOPHER:
        return "ʹ��Gopher";
    case MMENU:
        return "���˵�";
    case ADMIN:
        return "ϵͳά��";
    case SELECT:
        return "ѡ��������";
    case READBRD:
        return "���������";
    case READNEW:
        return "�Ķ�������";
    case READING:
        return "�Ķ�����";
    case POSTING:
        return "��������";
    case MAIL:
        return "�ż�ѡ��";
    case SMAIL:
        return "������";
    case RMAIL:
        return "������";
    case TMENU:
        return "̸��˵����";
    case LUSERS:
        return "��˭������";
    case FRIEND:
        return "�����Ϻ���";
    case MONITOR:
        return "�࿴��";
    case QUERY:
        return "��ѯ����";
    case TALK:
        return "����";
    case PAGE:
        return "��������";
    case CHAT2:
        return "�λù���";
    case CHAT1:
        return "��������";
    case CHAT3:
        return "����ͤ";
    case CHAT4:
        return "�ϴ�������";
    case IRCCHAT:
        return "��̸IRC";
    case LAUSERS:
        return "̽������";
    case XMENU:
        return "ϵͳ��Ѷ";
    case VOTING:
        return "ͶƱ";
    case BBSNET:
        return "��������";
    case EDITWELC:
        return "�༭ Welc";
    case EDITUFILE:
        return "�༭����";
    case EDITSFILE:
        return "ϵͳ����";
        /*        case  EDITSIG:  return "��ӡ";
           case  EDITPLAN: return "��ƻ�"; */
    case ZAP:
        return "����������";
    case EXCE_MJ:
        return "Χ������";
    case EXCE_BIG2:
        return "�ȴ�Ӫ";
    case EXCE_CHESS:
        return "���Ӻ���";
    case NOTEPAD:
        return "���԰�";
    case GMENU:
        return "������";
    case FOURM:
        return "4m Chat";
    case ULDL:
        return "UL/DL";
    case MSGING:
        return "ѶϢ��";
    case USERDEF:
        return "�Զ�����";
    case EDIT:
        return "�޸�����";
    case OFFLINE:
        return "��ɱ��..";
    case EDITANN:
        return "���޾���";
    case WEBEXPLORE:
        return "Web���";
    case CCUGOPHER:
        return "��վ����";
    case LOOKMSGS:
        return "�쿴ѶϢ";
    case WFRIEND:
        return "Ѱ������";
    case LOCKSCREEN:
        return "��Ļ����";
    case IMAIL:
	return "��վ������";
    case GIVEUPNET:
        return "������..";
    case SERVICES:    return "��������..";
	case FRIENDTEST:  return "������Ϭ";
    case KILLER:        return "ɱ����Ϸ";
    case CALENDAR:  return "�ռ�����";
    case CALENEDIT: return "�ޱ��ռ�";
    default:
        return "ȥ������!?";
    }
}

int multilogin_user(struct userec* user,int usernum,int mode)
{
    int logincount;
    int curr_login_num;

    logincount = apply_utmpuid(NULL, usernum, 0);

    if (logincount < 1)
        RemoveMsgCountFile(user->userid);

    if (HAS_PERM(user, PERM_MULTILOG))
        return 0;               /* don't check sysops */
    curr_login_num = get_utmp_number();
    /* Leeward: 97.12.22 BMs may open 2 windows at any time */
    /* Bigman: 2000.8.17 �������ܹ���2������ */
    /* stephen: 2001.10.30 �ٲÿ��Կ��������� */
    if ((HAS_PERM(user, PERM_BOARDS) || HAS_PERM(user, PERM_CHATOP) || HAS_PERM(user, PERM_JURY) || HAS_PERM(user, PERM_CHATCLOAK))
        && logincount < 2)
        return 0;
    /* allow multiple guest user */
    if (!strcmp("guest", user->userid)) {
        if (logincount > MAX_GUEST_NUM) {
            return 2;
        }
        return 0;
    } else if (((curr_login_num < 700) && (logincount >= 2))
               || ((curr_login_num >= 700) && (logincount >= 1)))       /*user login limit */
        return 1;
    return 0;
}

int compute_user_value( struct userec *urec)
{
    int value;
    int registeryear;
    int basiclife;

    /* if (urec) has CHATCLOAK permission, don't kick it */
    /* Ԫ�Ϻ������ʺ� �ڲ���ɱ������£� ������999 Bigman 2001.6.23 */
    /* 
       * zixia 2001-11-20 ���е���������ʹ�ú��滻��
       * �� smth.h/zixia.h �ж��� 
       * */
    /* ���⴦�����ƶ���cvs ���� */

    /* ��������˵�id,sigh */
    if ((urec->userlevel & PERM_HORNOR) && !(urec->userlevel & PERM_LOGINOK))
        return LIFE_DAY_LONG;
	/* �����ʺ� */
    if ((urec->userlevel & PERM_XEMPT) && (!(urec->userlevel & PERM_SUICIDE)) )
        return LIFE_DAY_NODIE;

    if (((urec->userlevel & PERM_HORNOR) || (urec->userlevel & PERM_CHATCLOAK)) && (!(urec->userlevel & PERM_SUICIDE)))
        return LIFE_DAY_NODIE;

    if ((urec->userlevel & PERM_ANNOUNCE) && (urec->userlevel & PERM_OBOARDS))
        return LIFE_DAY_SYSOP;
    /* վ����Ա���������� Bigman 2001.6.23 */


    value = (time(0) - urec->lastlogin) / 60;   /* min */
    if (0 == value)
        value = 1;              /* Leeward 98.03.30 */

    /* new user should register in 30 mins */
    if (strcmp(urec->userid, "new") == 0) {
        return (LIFE_DAY_NEW - value) / 60;     /* *->/ modified by dong, 1998.12.3 */
    }

    /* ��ɱ����,Luzi 1998.10.10 */
    if (urec->userlevel & PERM_SUICIDE)
        return (LIFE_DAY_SUICIDE * 24 * 60 - value) / (60 * 24);
    /**********************/
    if (urec->numlogins <= 3)
        return (LIFE_DAY_SUICIDE * 24 * 60 - value) / (60 * 24);
    if (!(urec->userlevel & PERM_LOGINOK))
        return (LIFE_DAY_NEW * 24 * 60 - value) / (60 * 24);
    /* if (urec->userlevel & PERM_LONGID)
       return (667 * 24 * 60 - value)/(60*24); */
    registeryear = (time(0) - urec->firstlogin) / 31536000;
    if (registeryear < 2)
        basiclife = LIFE_DAY_USER + 1;
    else if (registeryear >= 5)
        basiclife = LIFE_DAY_LONG + 1;
    else
        basiclife = LIFE_DAY_YEAR + 1;
    return (basiclife * 24 * 60 - value) / (60 * 24);
}


/**
 * ��������غ�����
 */
int ann_get_postfilename(char *filename, struct fileheader *fileinfo,
						MENU *pm)
{
	char fname[PATHLEN];
	char *ip;

	if (fileinfo->filename[1] == '/')
		strcpy(filename, fileinfo->filename + 2);
	else
		strcpy(filename, fileinfo->filename);
	sprintf(fname, "%s/%s", pm->path, filename);
	ip = &filename[strlen(filename) - 1];
	while (dashf(fname)) {
		if (*ip == 'Z')
			ip++, *ip = 'A', *(ip + 1) = '\0';
		else
			(*ip)++;
		sprintf(fname, "%s/%s", pm->path, filename);
	}
    return 0;
}

/**
 * ������غ�����
 */
time_t get_posttime(const struct fileheader *fileinfo)
{
	if (fileinfo->filename[1] == '/')
		return fileinfo->posttime;
	else
		return atoi(fileinfo->filename + 2);
}

void set_posttime(struct fileheader *fileinfo)
{
	if (fileinfo->filename[1] == '/')
		fileinfo->posttime = atoi(fileinfo->filename + 4);
}

void set_posttime2(struct fileheader *dest, struct fileheader *src)
{
	dest->posttime = src->posttime;
}

/**
 * ������ء�
 */
void build_board_structure(const char *board)
{
	int i;
	int len;
	char buf[STRLEN];

	len = strlen(alphabet);
    for (i = 0; i < len; i++)
    {
		snprintf(buf, sizeof(buf), "boards/%s/%c", board, alphabet[i]);
		mkdir(buf, 0755);
    }
	return;
}

void get_mail_limit(struct userec* user,int *sumlimit,int * numlimit)
{
    *sumlimit=10000;
    *numlimit=10000;
}

/* board permissions control */
int check_read_perm(struct userec *user, const struct boardheader *board)
{
    if (board == NULL)
        return 0;
	if (HAS_PERM(user, PERM_SECANC)) return 1;
    if (board->level & PERM_POSTMASK || HAS_PERM(user, board->level) || (board->level & PERM_NOZAP)) {
        if (board->flag & BOARD_CLUB_READ) {    /*���ֲ�*/
			/* only club members can access super club */
			if (board->flag & BOARD_SUPER_CLUB)
			{
				if (user->club_read_rights[(board->clubnum - 1) >> 5] & (1 << ((board->clubnum - 1) & 0x1f)))
					return 1;
				else
					return 0;
			}
            if (HAS_PERM(user,PERM_OBOARDS)&&HAS_PERM(user, PERM_SYSOP))
                return 1;
            if (board->clubnum <= 0 || board->clubnum >= MAXCLUB)
                return 0;
            if (user->club_read_rights[(board->clubnum - 1) >> 5] & (1 << ((board->clubnum - 1) & 0x1f)))
                return 1;
            else
                return 0;
        }
        return 1;
    }
	if (!(board->flag & BOARD_SUPER_CLUB) && HAS_PERM(user, PERM_ADMIN))
		return 1;

    return 0;
}

int check_see_perm(struct userec* user,const struct boardheader* board)
{
    if (board == NULL)
        return 0;
    if (board->level & PERM_POSTMASK
    	|| ((user==NULL)&&(board->level==0))
    	|| ((user!=NULL)&& HAS_PERM(user, board->level) )
    	|| (board->level & PERM_NOZAP))
	{
        if (board->flag & BOARD_CLUB_HIDE)     /*���ؾ��ֲ�*/
		{
			if (user==NULL) return 0;
			return check_read_perm(user,board);
		}
        return 1;
    }
    return 0;
}

