#include <pwd.h>
#include "bbs.h"
#include "system.h"

/* �������ԣ���doc/README.SYSOP 1.2 */
enum board_t {
    nil,
    V,
    X,
    o,
};

/* ${BBSHOME} */
/* fancy Aug 4 2009, WHY NOT USE BBSHOME MACRO??? */
char bbshome[PATHLEN] = BBSHOME;
/* SYSOP������ */
char SYSOP_pass[PASSLEN];

void error(char *fmt, ...)
{
    va_list args;

    va_start(args, fmt);
    fprintf(stderr, "error: ");
    vfprintf(stderr, fmt, args);
    fprintf(stderr, "\n");
    va_end(args);
    exit(EXIT_FAILURE);
}

void end_bootstrap(void)
{
    char cmd[PATHLEN];

    strcpy(cmd, "killall bbslogd");
    system(cmd);
    strcpy(cmd, bbshome);
    strcat(cmd, "/bin/miscd flush");
    system(cmd);
    strcpy(cmd, "killall miscd");
    system(cmd);

    /* ��������ڴ� */
    struct passwd *pw;
    if (!(pw = getpwuid(BBSUID)))
        error("%s", strerror(errno));
    sprintf(cmd, "ipcs -m|awk '$0~/%s/{system(sprintf(\"ipcrm -m %%s\",$2));}'", pw->pw_name);
    system(cmd);
    sprintf(cmd, "ipcs -q|awk '$0~/%s/{system(sprintf(\"ipcrm -q %%s\",$2));}'", pw->pw_name);
    system(cmd);
}

void sig_usr(int signo)
{
    if (signo == SIGINT || signo == SIGTERM) {
        end_bootstrap();
        error("receive signal: %d", signo);
    }
}

/* ���.PASSWDS�ļ���.BOARDS�ļ� */
void cleanids(void)
{
    char path[PATHLEN];
    int fd;

    strcpy(path, bbshome);
    strcat(path, "/.PASSWDS");
    if ((fd = open(path, O_WRONLY | O_CREAT | O_TRUNC, 0644)) == -1)
        error("clean the .PASSWDS error");
    close(fd);
    strcpy(path, bbshome);
    strcat(path, "/.BOARDS");
    if ((fd = open(path, O_WRONLY | O_CREAT | O_TRUNC, 0644)) == -1)
        error("clean the .BOARDS error");
    close(fd);
}

/* ������usernameΪ������passwdΪ������û� */
void do_makeid(char *username, char *passwd)
{
    struct userec *newuser;
    int unum;
    char path[PATHLEN];
    char buf[PATHLEN];

    unum = getnewuserid(username);
    if (unum > MAXUSERS || unum <= 0) {
        end_bootstrap();
        error("%s: unum error", username);
    }
    strcpy(buf, username);
    unum = getuser(buf, &newuser);
    setpasswd(passwd, newuser);

    if (!strcmp(username, "SYSOP")) {
        newuser->flags = (char)1;
        strcpy(newuser->username, "ϵͳ����Ա");
        newuser->userdefine[0] = (unsigned int)3221224447U;
        newuser->userdefine[1] = (unsigned int)4294967295U;
        newuser->userlevel = (unsigned int)0xDAFFDFFF;
    } else if (!strcmp(username, "guest")) {
        newuser->flags = (char)9;
        newuser->userlevel = (unsigned int)536879104U;
        strcpy(newuser->username, "��г");
        newuser->userdefine[0] = (unsigned int)3221224447U;
        newuser->userdefine[1] = (unsigned int)4294967295U;
    }

    update_user(newuser, unum, 1);

    newbbslog(BBSLOG_USIES, "APPLY: %s uid %d from %s", 
            newuser->userid, unum, "127.0.0.1");
    bbslog("user", "%s", "new account");

    sethomepath(path, newuser->userid);
    sprintf(buf, "/bin/mv -f %s %s/homeback/%s", path, bbshome, newuser->userid);
    system(buf);
    sprintf(buf, "/bin/mv -f %s %s/mailback/%s", path, bbshome, newuser->userid);
    system(buf);
    mail_file(DELIVER, "etc/tonewuser", newuser->userid, 
            "����ע���û�����", 0, NULL);
}

void makeids(void)
{
    do_makeid("SYSOP", SYSOP_pass);
    do_makeid("guest", "anypassword");
}

void do_makeboard(enum board_t type, char *name)
{
    struct boardheader newboard;
    char vbuf[PATHLEN], buf[PATHLEN];
    int bid;

    memset(&newboard, 0, sizeof(newboard));
    strcpy(newboard.filename, name);
    strcpy(newboard.title, "0[����]      �����������ƴ���");
    if (getbid(newboard.filename, NULL) > 0) {
        end_bootstrap();
        error("%s: getbid error", newboard.filename);
    }
    strcpy(vbuf, "vote/");
    strcat(vbuf, newboard.filename);
    setbpath(buf, newboard.filename);
    /* fancy Aug 4 2009, �ǿ�Ŀ¼ɾ�ĵ�??
    remove(buf);
    remove(vbuf);
    */
    f_rm(buf);
    f_rm(vbuf);
    if (mkdir(buf, 0755) == -1 || mkdir(vbuf, 0755) == -1) {
        end_bootstrap();
        error("%s: mkdir error", newboard.filename);
    }
    snprintf(newboard.ann_path, 127, "%s/%s", groups[0], newboard.filename);
    newboard.ann_path[127] = '\0';

    /* Ĭ��Ȩ��Ϊ: ������ΪPERM_SYSOP */
    newboard.level = PERM_SYSOP;
#if 0
    //������Ϊ�������趨����Ȩ�޵Ĵ���
    //����������ע�͵���Ĭ�ϸ���ֻ��PERM_SYSOP���ŷ���Ȩ��
    //use it at your own risk

    if (type == X)
        /* д����ΪPERM_SYSOP */
        newboard.level |= PERM_POSTMASK;
    else if (type == o)
        /* ������ΪPERM_BASIC */
        newboard.level |= PERM_BASIC;
#endif
/* fancy Aug 4 2009, ������δ���܇�, û PERM_BASIC �ϲ���վ��-_- */
    if (add_board(&newboard) == -1) {
        end_bootstrap();
        error("%s: add_board error", newboard.filename);
    }
    if ((bid = getbid(newboard.filename, NULL)) == 0) {
        end_bootstrap();
        error("%s: getbid error", newboard.filename);
    }

    sprintf(buf, "add brd %s", newboard.filename);
    bbslog("user", "%s", buf);
}

void makeboards(void)
{
    struct _boards {
        enum board_t type;
        char *name;
    } boards[] = {
        {V, "bbsnet"},
        {V, "denypost"},
        {V, "Filter"},
        {V, "GiveupNotice"},
        {V, "Goodbye"},
        {V, "Recommend"},
        {V, "Registry"},
        {V, "reject_registry"},
        {V, "syssecurity"},
        {V, "undenypost"},
        {V, "sysmail"},
        {X, "BBSLists"},
        {X, "newcomers"},
        {X, "notepad"},
        {X, "vote"},
        {o, "Blessing"},
        {nil, ""},
    };
    struct _boards *ptr = boards;

    for (; ptr->type != nil; ++ptr) {
        do_makeboard(ptr->type, ptr->name);
    }

    char buf1[PATHLEN], buf2[PATHLEN];

    sprintf(buf1, "%s/boards/sysmail", bbshome);
    sprintf(buf2, "%s/mail/S/SYSOP", bbshome);
    remove(buf1);
    symlink(buf2, buf1);
}

void do_bootstrap(void)
{
    char cmd[PATHLEN];
    int ret;
    int c;
    char *prompt[] = {
        "Input SYSOP's passwd",
        "Input SYSOP's passwd again",
    };
    char pw[2][PASSLEN];
    int i;
    int len;

    while (1) {
        for (i=0; i<2; ++i) {
            len = 0;

            puts(prompt[i]);
            while (len < PASSLEN - 1 && (c = getchar()) != EOF && c != '\n' && c != '\r')
                pw[i][len++] = c;

            /* i == 0ʱ�����ߵ�һ������ʱ����ⲻ�������룬�ڶ��β���� */
            if (i == 0 && (len < 4 || !strcmp(pw[i], "SYSOP"))) {
                printf("����̫�̻���ʹ���ߴ�����ͬ������������\n");
                break;
#if 0
            } else if (i == 0 && len >= LEN - 100) {      /* make enough space */
                printf("����̫��������������\n");
                break;
#endif
            } else if (i == 0 && c == EOF)
                error("encounter 'EOF' when asking password");
            else
                pw[i][len] = '\0';
        }

        if (strcmp(pw[0], pw[1]))
            printf("���������������������\n");
        else {
            printf("SYSOP's passwd is '%s'\n", pw[0]);
            strcpy(SYSOP_pass, pw[0]);
            break;
        }
    }

    cleanids();

    if (signal(SIGTERM, sig_usr) == SIG_ERR)
        error("can't catch SIGTERM");
    if (signal(SIGINT, sig_usr) == SIG_ERR)
        error("can't catch SIGINT");

    strcpy(cmd, bbshome);
    strcat(cmd, "/bin/miscd daemon");
    if ((ret = system(cmd)) != 0)
        error("error running miscd, return value = %d", ret);
    strcpy(cmd, bbshome);
    strcat(cmd, "/bin/bbslogd");
    if ((ret = system(cmd)) != 0)
        error("error running bbslogd, return value = %d", ret);

    chdir(bbshome);
    resolve_boards();
    resolve_utmp();
    load_ucache();

    makeids();
    makeboards();

    end_bootstrap();
}

void bootstrap(void) 
{
    char buf[STRLEN];
    printf("Danger! This may destroy ALL OF YOUR USER AND BOARD DATA!\n");
    printf("Type `Go ahead!' exactly without quotes and press ENTER to continue\n");
    printf("Are you ready? ");
    fgets(buf, STRLEN, stdin);
    trimstr(buf);
    if (!strcmp("Go ahead!", buf))
        do_bootstrap();
    else
        printf("User aborted.\n");
}

void show_help(void)
{
    printf("NO Help Now.\nPlease read the source file or kick skybluee.\n");
    exit(EXIT_SUCCESS);
}

int main(int argc, char **argv)
{
    int ch;
    int i;

    while ((ch = getopt(argc, argv, "hd:")) != -1)
        switch (ch) {
        case 'd' :
            if (optarg[0] == '\0')
                error("invalid path");
            strncpy(bbshome, optarg, PATHLEN);
            bbshome[PATHLEN - 1] = '\0';
            for (i=0; bbshome[i] != '\0'; ++i)
                ;
            /* remove the tailing '/' */
            if (i != 0 && bbshome[i-1] == '/')
                bbshome[i-1] = '\0';
            break;
        case 'h' :
            show_help();
            break;
        default :
            break;
        }

    bootstrap();

    exit(EXIT_SUCCESS);
}

