/******************************************************
���������2003, ����: bad@smth.org  Qian Wenjie
��ˮľ�廪bbsϵͳ������

����Ϸ����������������⸴����ֲ
�����޸ĺ���ļ�ͷ��������Ȩ��Ϣ
******************************************************/

#define BBSMAIN
#include "bbs.h"

#include <sys/types.h>
#include <sys/socket.h>
#include <sys/un.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include <unistd.h>
#include <stdio.h>

#define MAX_KEEP 100

char res_title[MAX_KEEP][80],res_filename[MAX_KEEP][200];
int res_total=0,toomany=0,wh=0;

char qn[60];

int get_word()
{
    clear();
    prints("���������");
    move(6, 0);
    prints("    �����                    �ؼ���");
    move(7, 0);
    prints("-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-");
    move(8, 0);
    prints("    ��ӰӰ��                  movie �޼��");
    move(9, 0);
    prints("    �����ָ��                popmusic �һ�����һ�������");
    move(10, 0);
    prints("    ����Ҫ��                  news ������");
    move(11, 0);
    prints("    ����С˵                  emprise ��������");
    move(12, 0);
    prints("    ʳ��ʳ��                  food �Ǵ��Ź�");
    move(13, 0);
    prints("    ����Ҫ��                  estar ��׿�� ����ͩ");
    move(14, 0);
    prints("    ��ȫ����                  ʹ��softice");
    move(15, 0);
    prints("    ����Ӳ��                  Һ����ʾ��");
    move(16, 0);
    prints("    ��Ϸ����                  palsword ֧��");
    move(17, 0);
    prints("    ����㷨                  ������ �㷨");
    move(18, 0);
    prints("    ��ĬЦ��                  joke ż��żmm");
    move(19, 0);
    prints("    ����bad������good����     bad -good");
    move(20, 0);
    prints("-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-");
    getdata(2, 0, "��ѯ�ؼ���: ", qn, 60, 1, 0, 0);
    move(3, 0);
    prints("��ʼ��ѯ....");
    refresh();
    return qn[0];
}

void do_query_all(int w, char * s)
{
    struct sockaddr_in addr;
    FILE* sockfp;
    int sockfd, i, j;
    char buf[256];
    char ip[20];
    
    if(rand()%2==0)strcpy(ip,"166.111.3.125");
    else strcpy(ip,"166.111.8.235");
    
    res_total = -2;
    if(strstr(s, "���ֹ�")||strstr(s, "kcn")||strstr(s, "����֮��")||
        strstr(s, "������")) {
        res_total = -1;
        return;
    }
    
    j=1;
    for(i=0;i<strlen(s);i++)
        if(s[i]>='a'&&s[i]<='z'||s[i]>='A'&&s[i]<='Z'||s[i]>='0'&&s[i]<='9'||s[i]<0) {
            j=0;
            break;
        }
    if(j) {
        res_total = -1;
        return;
    }

    if((sockfd=socket(AF_INET, SOCK_STREAM, 0))==-1) return;
    memset(&addr, 0, sizeof(addr));
    addr.sin_family=AF_INET;    
    addr.sin_addr.s_addr=inet_addr(ip);
    addr.sin_port=htons(4875);
    if(connect(sockfd, (struct sockaddr*)&addr, sizeof(addr))<0) return;
    sockfp=fdopen(sockfd, "r+");
    fprintf(sockfp, "\n%d\n%s\n", w, s);
    fflush(sockfp);
    fscanf(sockfp, "%d %d %d\n", &toomany, &i, &res_total);
    for(i=0;i<res_total;i++) {
        fgets(buf, 256, sockfp);
        if(buf[0]&&buf[strlen(buf)-1]=='\n') buf[strlen(buf)-1]=0;
        strncpy(res_title[i], buf, 80);
        res_title[i][79] = 0;

        fgets(buf, 256, sockfp);
        if(buf[0]&&buf[strlen(buf)-1]=='\n') buf[strlen(buf)-1]=0;
        strncpy(res_filename[i], buf, 200);
        res_filename[i][199] = 0;
    }
    fclose(sockfp);
    close(sockfd);
}

static int choose_file_refresh(struct _select_def *conf)
{
    clear();
    docmdtitle("[���������]",
              "  �˳�[\x1b[1;32m��\x1b[0;37m,\x1b[1;32me\x1b[0;37m] �쿴[\x1b[1;32mEnter\x1b[0;37m] ѡ��[\x1b[1;32m��\x1b[0;37m,\x1b[1;32m��\x1b[0;37m] ��ҳ����[\x1b[1;32m[\x1b[0;37m,\x1b[1;32m]\x1b[0;37m]              ����: \x1b[31;1mbad@smth.org\x1b[m");
    move(2, 0);
    prints("[0;1;37;44m    %4s %-30s %s        %d-%d ��%d  �ؼ���:%s", "���", "����", "·��", wh*MAX_KEEP+1, wh*MAX_KEEP+res_total, toomany, qn);
    clrtoeol();
    resetcolor();
    update_endline();
    return SHOW_CONTINUE;
}

static int choose_file_show(struct _select_def *conf, int i)
{
    struct room_struct * r;
    char f1[160],f2[160];
    strcpy(f1, res_title[i-1]);
    strcpy(f2, res_filename[i-1]);
    prints("  %3d  %-30s %s", i+wh*MAX_KEEP, f1, f2);
    return SHOW_CONTINUE;
}

static int choose_file_select(struct _select_def *conf)
{
    char * ss;
    int ch;
again:
    ss = res_filename[conf->pos-1];
    ch = ansimore_withzmodem(ss, 0, res_title[conf->pos-1]);
    move(t_lines-1, 0);
    prints("[0;1;31;44m[��%d/%dƪ]  [33m���� Q,�� ����һ�� ������һ�� <Space>,���������ؼ���:%s", conf->pos+wh*MAX_KEEP, toomany, qn);
    clrtoeol();
    if(ch==0) ch=igetkey();
    switch(ch){
        case KEY_UP:
        case 'l':
        case 'k':
            conf->pos--;
            if(conf->pos<=0) conf->pos = res_total;
            goto again;
        case KEY_DOWN:
        case ' ':
        case 'n':
        case 'j':
            conf->pos++;
            if(conf->pos>res_total) conf->pos = 1;
            goto again;
        case Ctrl('Y'):
            zsend_file(ss, res_title[conf->pos-1]);
            break;
    }
    return SHOW_REFRESH;
}

static int choose_file_getdata(struct _select_def *conf, int pos, int len)
{
    conf->item_count = res_total;
    return SHOW_CONTINUE;
}

static int choose_file_prekey(struct _select_def *conf, int *key)
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

static int choose_file_key(struct _select_def *conf, int key)
{
    switch (key) {
    case ']':
        if((wh+1)*MAX_KEEP+1<=toomany) {
            move(t_lines-1, 0);
            prints("��������....");
            clrtoeol();
            refresh();
            wh++;
            do_query_all(wh, qn);
            return SHOW_DIRCHANGE;
        }
    case '[':
        if(wh>0) {
            move(t_lines-1, 0);
            prints("��������....");
            clrtoeol();
            refresh();
            wh--;
            do_query_all(wh, qn);
            return SHOW_DIRCHANGE;
        }
    }
    return SHOW_CONTINUE;
}

int choose_file()
{
    struct _select_def grouplist_conf;
    int i;
    POINT *pts;

    bzero(&grouplist_conf, sizeof(struct _select_def));
    grouplist_conf.item_count = res_total;
    pts = (POINT *) malloc(sizeof(POINT) * BBS_PAGESIZE);
    for (i = 0; i < BBS_PAGESIZE; i++) {
        pts[i].x = 2;
        pts[i].y = i + 3;
    }
    grouplist_conf.item_per_page = BBS_PAGESIZE;
    grouplist_conf.flag = LF_VSCROLL | LF_BELL | LF_LOOP | LF_MULTIPAGE;
    grouplist_conf.prompt = "��";
    grouplist_conf.item_pos = pts;
    grouplist_conf.arg = NULL;
    grouplist_conf.title_pos.x = 0;
    grouplist_conf.title_pos.y = 0;
    grouplist_conf.pos = 1;     /* initialize cursor on the first mailgroup */
    grouplist_conf.page_pos = 1;        /* initialize page to the first one */

    grouplist_conf.on_select = choose_file_select;
    grouplist_conf.show_data = choose_file_show;
    grouplist_conf.pre_key_command = choose_file_prekey;
    grouplist_conf.key_command = choose_file_key;
    grouplist_conf.show_title = choose_file_refresh;
    grouplist_conf.get_data = choose_file_getdata;
    list_select_loop(&grouplist_conf);
    free(pts);
}

int show_res()
{
    int i;
    if(res_total<=0) {
        move(4,0);
        if(res_total==-2) prints("����̫�أ����Ժ���ʹ��");
        else prints("ʲô��û�ѵ���");
        igetkey();
    }
    else {
        choose_file();
    }
}

int iquery_main()
{
    qn[0] = 0;
    while(get_word()) {
        wh = 0;
        do_query_all(wh, qn);
        show_res();
    }
}
