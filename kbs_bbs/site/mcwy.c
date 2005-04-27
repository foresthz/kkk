#include "bbs.h"

const char seccode[SECNUM][5] = {
    "1", "2", "3", "4", "5", "6", "0"
};
const char * const groups[] = {
        "GROUP_0",
        "GROUP_1",
        "GROUP_2",
        "GROUP_3",
        "GROUP_4",
        "GROUP_5",
        "GROUP_6",
         NULL
};
const char secname[SECNUM][2][20] = {
    {"��վϵͳ", "[��վ]"},
    {"��������", "[У԰][��ҵ]"},
    {"������", "[��ѧ][ѧУ][����]"},
    {"ѧ������", "[ѧ��][��ѧ][����]"},
    {"��������", "[����][����][����]"},
    {"֪�Ը���", "[֪��][����][����]"},
    {"�ڲ�����", "[�������]"}
};

const char * const explain[] = {
        "��վϵͳ",
        "��������",
        "������",
        "ѧ������",
        "��������",
        "֪�Ը���",
        "�ڲ�����",
        NULL
};

const char * const permstrings[] = {
    "����Ȩ��",                 /* PERM_BASIC */
    "����������",               /* PERM_CHAT */
    "������������",             /* PERM_PAGE */
    "��������",                 /* PERM_POST */
    "ʹ����������ȷ",           /* PERM_LOGINOK */
    "ʵϰվ��",                 /* PERM_BMMANAGER */
    "������",                   /* PERM_CLOAK */
    "�ɼ�����",                 /* PERM_SEECLOAK */
    "�����ʺ�",                 /* PERM_XEMPT */
    "�༭ϵͳ����",             /* PERM_WELCOME */
    "����",                     /* PERM_BOARDS */
    "�ʺŹ���Ա",               /* PERM_ACCOUNTS */
    "��������������",           /* PERM_CHATCLOAK */
    "�������Ȩ��",             /* PERM_DENYRELAX */
    "ϵͳά������Ա",           /* PERM_SYSOP */
    "Read/Post ����",           /* PERM_POSTMASK */
    "�������ܹ�",               /* PERM_ANNOUNCE */
    "�������ܹ�",               /* PERM_OBOARDS */
    "������ܹ�",             /* PERM_ACBOARD */
    "���� ZAP(������ר��)",     /* PERM_NOZAP */
    "������OP(Ԫ��Ժר��)",     /* PERM_CHATOP */
    "ϵͳ�ܹ���Ա",             /* PERM_ADMIN */
    "�����ʺ�",                 /* PERM_HONOR */
    "6 ���ܹ�",                 /* PERM_6SEC */
    "�ٲ�ίԱ",                 /* PERM_JURY */
    "����Ȩ�� 7",               /* PERM_UNUSE? */
    "��ɱ������",               /*PERM_SUICIDE */
    "����ר���ʺ�",             /* PERM_COLLECTIVE */
    "��ϵͳ���۰�",             /* PERM_UNUSE? */
    "���Mail",                 /* PERM_DENYMAIL */

};

struct count_arg {
    int www_count;
    int telnet_count;
};

int countuser(struct user_info *uinfo, struct count_arg *arg, int pos)
{
    if (uinfo->mode == WEBEXPLORE)
        arg->www_count++;
    else
        arg->telnet_count++;
    return COUNT;
}

int multilogin_user(struct userec *user, int usernum, int mode)
{
    int logincount;
    int curr_login_num;
    struct count_arg arg;

    bzero(&arg, sizeof(arg));
    logincount = apply_utmpuid((APPLY_UTMP_FUNC) countuser, usernum, &arg);

    if (logincount < 1)
        RemoveMsgCountFile(user->userid);

    if (HAS_PERM(user, PERM_SYSOP))
        return 0;               /* don't check sysops */
    curr_login_num = get_utmp_number();
    /*
     * binxun 2003.5 �ٲã�������Chatop���ȶ���������
     */
    if ((HAS_PERM(user, PERM_BOARDS) || HAS_PERM(user, PERM_CHATOP)
         || HAS_PERM(user, PERM_JURY) || HAS_PERM(user, PERM_CHATCLOAK)
         || HAS_PERM(user, PERM_BMAMANGER))
        && logincount < 3)
        return 0;
    
    if (!strcmp("farmers", user->userid)) return 0;
    
    /*
     * allow multiple guest user 
     */
    if (!strcmp("guest", user->userid)) {
        if (logincount > MAX_GUEST_NUM) {
            return 2;
        }
        return 0;
    } else if (((curr_login_num < 30) && (logincount >= 3))     /*С��700�������� */
               ||((curr_login_num >= 30) && (logincount >= 2)   /*700������ */
                  &&!(((arg.telnet_count == 0) && (mode == 0))  /* telnet����Ϊ������ٵ�һ��telnet */
                      ||(((arg.www_count == 0) && (mode == 1))))))      /*user login limit */
        return 1;
    return 0;
}

//�Զ�ͨ��ע��ĺ���  binxun
int auto_register(char *userid, char *email, int msize)
{
    struct userdata ud;
    struct userec *uc;
    //char *item;
    char fdata[7][STRLEN];
    char genbuf[STRLEN];
    char buf[STRLEN];
    char fname[STRLEN];
    int unum, isGreen = false;
    FILE *fout;
    int n;
    struct userec deliveruser;
    static const char *finfo[] = { "�ʺ�λ��", "�������", "��ʵ����", "����λ",
        "Ŀǰסַ", "����绰", "��    ��", NULL
    };
    static const char *field[] = { "usernum", "userid", "realname", "career",
        "addr", "phone", "birth", NULL
    };

    bzero(&deliveruser, sizeof(struct userec));
    strcpy(deliveruser.userid, "deliver");
    deliveruser.userlevel = -1;
    strcpy(deliveruser.username, "�Զ�����ϵͳ");

    {
        char buf[STRLEN];
        char *ptr;
        FILE *fp;

        if ((fp = fopen("etc/GreenChannel", "r")) != NULL) {
            while (fgets(buf, STRLEN, fp) != NULL) {
                ptr = strtok(buf, " \n\t\r");
                if (ptr != NULL && *ptr != '#') {
                    if (strcasecmp(ptr, email) == 0) {
                        isGreen = true;
                        break;
                    }
                }
                bzero(buf, STRLEN);
            }
            fclose(fp);
        }
    }
    if (!isGreen) return -1;

    bzero(fdata, 7 * STRLEN);

    if ((unum = getuser(userid, &uc)) == 0)
        return -1;              //faild
    if (read_userdata(userid, &ud) < 0)
        return -1;

    strncpy(ud.realemail, email, STRLEN-16);
    /*if (item)
        strncpy(ud.realname, item, NAMELEN);
    item = strtok(NULL, "#");   //ѧ��
    item = strtok(NULL, "#");
    if (item)
        strncpy(ud.address, item, STRLEN);

    email[strlen(email) - 3] = '@';
    strncpy(ud.realemail, email, STRLEN - 16);  //email length must be less STRLEN-16
    */
    

    sprintf(fdata[0], "%d", unum);
    strncpy(fdata[2], ud.realname, NAMELEN);
    strncpy(fdata[4], ud.address, STRLEN);
    strncpy(fdata[5], ud.email, STRLEN);
    strncpy(fdata[1], userid, IDLEN);

    sprintf(buf, "tmp/email/%s", userid);
    if ((fout = fopen(buf, "w")) != NULL) {
        fprintf(fout, "%s\n", email);
        fclose(fout);
    }

    if (write_userdata(userid, &ud) < 0)
        return -1;
    mail_file("deliver", "etc/s_fill", userid, "��ϲ��,���Ѿ����ע��.", 0, 0);
    //sprintf(genbuf,"deliver �� %s �Զ�ͨ�����ȷ��.",uinfo.userid);

    sprintf(fname, "tmp/security.%d", getpid());
    if ((fout = fopen(fname, "w")) != NULL) {
        fprintf(fout, "ϵͳ��ȫ��¼ϵͳ\n\033[32mԭ��%s�Զ�ͨ��ע��\033[m\n", userid);
        fprintf(fout, "������ͨ���߸�������");
        fprintf(fout, "\n\n���Ĵ���     : %s\n", ud.userid);
        fprintf(fout, "�����ǳ�     : %s\n", uc->username);
        fprintf(fout, "��ʵ����     : %s\n", ud.realname);
        fprintf(fout, "�����ʼ����� : %s\n", ud.email);
        fprintf(fout, "��ʵ E-mail  : %s\n", ud.realemail);
        fprintf(fout, "����λ     : %s\n", "");
        fprintf(fout, "Ŀǰסַ     : %s\n", ud.address);
        fprintf(fout, "����绰     : %s\n", "");
        fprintf(fout, "ע������     : %s", ctime(&uc->firstlogin));
        fprintf(fout, "����������� : %s", ctime(&uc->lastlogin));
        fprintf(fout, "������ٻ��� : %s\n", uc->lasthost);
        fprintf(fout, "��վ����     : %d ��\n", uc->numlogins);
        fprintf(fout, "������Ŀ     : %d(Board)\n", uc->numposts);
        fprintf(fout, "��    ��     : %s\n", "");

        fclose(fout);
        //post_file(currentuser, "", fname, "Registry", str, 0, 2);

        sprintf(genbuf, "%s �Զ�ͨ��ע��", ud.userid);
        post_file(&deliveruser, "", fname, "syslog", genbuf, 0, 1, getSession());
        /*
         * if (( fout = fopen(logfile,"a")) != NULL)
         * {
         * fclose(fout);
         * }
         */
    }

    sethomefile(buf, userid, "/register");
    if ((fout = fopen(buf, "w")) != NULL) {
        for (n = 0; field[n] != NULL; n++)
            fprintf(fout, "%s     : %s\n", finfo[n], fdata[n]);
        fprintf(fout, "�����ǳ�     : %s\n", uc->username);
        fprintf(fout, "�����ʼ����� : %s\n", ud.email);
        fprintf(fout, "��ʵ E-mail  : %s\n", ud.realemail);
        fprintf(fout, "ע������     : %s\n", ctime(&uc->firstlogin));
        fprintf(fout, "ע��ʱ�Ļ��� : %s\n", uc->lasthost);
        fprintf(fout, "Approved: %s\n", userid);
        fclose(fout);
    }

    return 0;
}


char * showuserip(struct userec *user, char *fromhost)
{
    static char *maskip[] = {"128.12.", "171.64.", "171.65.", "171.66.", "171.67.", "192.168.0.", "136.152.", "128.32.", "169.229.", NULL};
    static char *masks[]  = {"�ƣ��ң�","�ƣ��ң�","�ƣ��ң�","�ƣ��ң�","�ƣ��ң�","�ƣ��ң�",   "���ܵ���", "���ܵ���","���ܵ���", NULL};
	static char sip[25];
    int i;
	char *c;
	    
	if( user!=NULL && (!DEFINE(user, DEF_HIDEIP)) )
		return "�Ͳ�������"; //(user->userid);

    for(i=0;maskip[i]!=NULL;i++) {
        if (!strncmp(fromhost, maskip[i], strlen(maskip[i]))) { 
	        return masks[i];
       }
    }
	strncpy(sip, fromhost, 24);
	sip[24]=0;
	if( (c=strrchr(sip, '.')) != NULL){
		*(++c)='*';
		*(++c)='\0';
	}
	return sip;
}
