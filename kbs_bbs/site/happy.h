#ifndef __SYSNAME_H_
#define __SYSNAME_H_

#define HAVE_MYSQL_SMTH		1
#define HAVE_WFORUM		1
#define PERSONAL_CORP		1
#define CONV_PASS		1
#define NEW_COMERS		1	/* ע����� newcomers ���Զ����� */
#define HAPPY_BBS		1
#define HAVE_COLOR_DATE		1
#define HAVE_FRIENDS_NUM 	1	/* ��ʾ������Ŀ */
#define HAVE_REVERSE_DNS 	1	/* �������� */
#define FILTER			1
#define CHINESE_CHARACTER	1
#define CNBBS_TOPIC		1	/* �Ƿ��ڽ�վ��������ʾ cn.bbs.* ʮ�����Ż��� */
#define MAIL2BOARD		0	/* �Ƿ�����ֱ�� mail to any board */
#define MAILOUT			1	/* �Ƿ�������վ���������� */

#define BUILD_PHP_EXTENSION 1 /*��php lib���php extension*/

#define FLOWBANNER   			/* �ײ������� */

#define IDLE_TIMEOUT    (60*20) 

#define BBSUID          30001
#define BBSGID          504


/* for bbs2www, by flyriver, 2001.3.9 */
#define SECNUM 9
#define BBS_PAGE_SIZE 20

#define DEFAULTBOARD    	"sysop"
#define FILTER_BOARD        "Filter"
#define SYSMAIL_BOARD       "SYSOPMail"
#define BLESS_BOARD "Blessing"
#define MAXUSERS  		20000
#define MAXCLUB         128
#define MAXBOARD  		400
#define MAXACTIVE 		512
#define MAX_GUEST_NUM		800
#define WWW_MAX_LOGIN 256

#define POP3PORT		110
#define POP3SPORT		995
/* ASCIIArt, by czz, 2002.7.5 */
#define       LENGTH_SCREEN_LINE      256
#define       LENGTH_FILE_BUFFER      256
#define       LENGTH_ACBOARD_BUFFER   255
#define       LENGTH_ACBOARD_LINE     300

#define LIFE_DAY_USER		120
#define LIFE_DAY_YEAR          365
#define LIFE_DAY_LONG		666
#define LIFE_DAY_SYSOP		120
#define LIFE_DAY_NODIE		999
#define LIFE_DAY_NEW		15
#define LIFE_DAY_SUICIDE	15

#define DAY_DELETED_CLEAN	97
#define SEC_DELETED_OLDHOME	2592000/* 3600*24*30��ע�����û������������û���Ŀ¼������ʱ��*/

#define	REGISTER_WAIT_TIME	(1)
#define	REGISTER_WAIT_TIME_NAME	"1 ����"

#define MAIL_BBSDOMAIN      "happynet.org"
#define MAIL_MAILSERVER     "127.0.0.1:25"

#define NAME_BBS_ENGLISH	"happynet.org"
#define	NAME_BBS_CHINESE	"HAPPY"
#define NAME_BBS_NICK		"BBS վ"

#define BBS_FULL_NAME "HAPPY"

#define FOOTER_MOVIE		"��  ӭ  Ͷ  ��"
/*#define ISSUE_LOGIN		"��վʹ����⹫˾������ݷ�����"*/
#define ISSUE_LOGIN		"���PC  ��21����˻�ָ��������"
#define ISSUE_LOGOUT		"����������"

#define NAME_USER_SHORT		"�û�"
#define NAME_USER_LONG		"HAPPY �û�"
#define NAME_SYSOP		"System Operator"
#define NAME_BM			"����"
#define NAME_POLICE		"����"
#define	NAME_SYSOP_GROUP	"վ����"
#define NAME_ANONYMOUS		"����������ʹ"
#define NAME_ANONYMOUS_FROM	"������ʹ�ļ�"
#define ANONYMOUS_DEFAULT 1

#define NAME_MATTER		"վ��"
#define NAME_SYS_MANAGE		"ϵͳά��"
#define NAME_SEND_MSG		"��ѶϢ"
#define NAME_VIEW_MSG		"��ѶϢ"

#define CHAT_MAIN_ROOM		"main"
#define	CHAT_TOPIC		"�����������İ�"
#define CHAT_MSG_NOT_OP		"*** �����Ǳ������ҵ�op ***"
#define	CHAT_ROOM_NAME		"������"
#define	CHAT_SERVER		"����㳡"
#define CHAT_MSG_QUIT		"����ϵͳ"
#define CHAT_OP			"������ op"
#define CHAT_SYSTEM		"ϵͳ"
#define	CHAT_PARTY		"���"

#define DEFAULT_NICK		"��ԶHAPPY"

#define MSG_ERR_USERID		"�����ʹ���ߴ���..."
#define LOGIN_PROMPT		"���������"
#define PASSWD_PROMPT		"����������"

#define BOARD_SUPER_CLUB 0x01000000 /* �������ֲ�������Ա�ɼ� */



/* �û��Զ��������� */
#define DEF_ACBOARD      000001
#define DEF_COLOR        000002
#define DEF_EDITMSG      000004
#define DEF_NEWPOST      000010
#define DEF_ENDLINE      000020
#define DEF_LOGFRIEND    000040
#define DEF_FRIENDCALL   000100
#define DEF_LOGOUT       000200
#define DEF_INNOTE       000400
#define DEF_OUTNOTE      001000
#define DEF_NOTMSGFRIEND 002000
#define DEF_NORMALSCR    004000
#define DEF_CIRCLE       010000
#define DEF_FIRSTNEW     020000
#define DEF_TITLECOLOR   040000
#define DEF_ALLMSG       0100000
#define DEF_FRIENDMSG    0200000
#define DEF_SOUNDMSG     0400000
#define DEF_MAILMSG      01000000
#define DEF_LOGININFORM  02000000
#define DEF_SHOWSCREEN   04000000
#define DEF_SHOWHOT      010000000
#define DEF_NOTEPAD      020000000
#define DEF_IGNOREMSG    040000000      /* Added by Marco */
#define DEF_HIGHCOLOR    0100000000   /*Leeward 98.01.12 */
#define DEF_SHOWSTATISTIC 0200000000    /* Haohmaru */
#define DEF_UNREADMARK   0400000000       /* Luzi 99.01.12 */
#define DEF_USEGB        01000000000       /* KCN,99.09.05 */
#define DEF_CHCHAR 		 02000000000
#define DEF_SHOWDETAILUSERDATA  04000000000
#define DEF_SHOWREALUSERDATA   010000000000

#define DEF_HIDEIP       040000000001
#define DEF_SHOWHOROSCOPE	040000000001
#define DEF_SHOWBANNER   040000000002
#define NUMDEFINES 		 34

extern const char *permstrings[];
extern const char    *groups[];
extern const char    *explain[];
extern const char *user_definestr[];
extern const char *mailbox_prop_str[];

/**
 * �������û�ʱ�İ��������ַ���
 */
#define UL_CHANGE_NICK_UPPER   'C'
#define UL_CHANGE_NICK_LOWER   'c'
#define UL_SWITCH_FRIEND_UPPER 'F'
#define UL_SWITCH_FRIEND_LOWER 'f'

/** ʹ��ȱʡ��FILEHeader�ṹ*/
#define HAVE_FILEHEADER_DEFINE

#define GET_POSTFILENAME(x,y) get_postfilename(x,y,1)
#define GET_MAILFILENAME(x,y) get_postfilename(x,y,0)
#define VALID_FILENAME(x) valid_filename(x,1)
#define POSTFILE_BASENAME(x) (((char *)(x))+2)
#define MAILFILE_BASENAME(x) (x)

/**
attach define
*/
#define ATTACHTMPPATH "boards/_attach"
#endif
