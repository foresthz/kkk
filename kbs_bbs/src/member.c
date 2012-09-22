#include "bbs.h"
#include "read.h"

#ifdef ENABLE_BOARD_MEMBER

#define MEMBER_BOARD_ARTICLE_BOARD_HOT 1
#define MEMBER_BOARD_ARTICLE_BOARD_NOTE 2

#define MEMBER_BOARD_ARTICLE_ACTION_AUTH_INFO 1
#define MEMBER_BOARD_ARTICLE_ACTION_AUTH_DETAIL 2
#define MEMBER_BOARD_ARTICLE_ACTION_FORWARD 3
#define MEMBER_BOARD_ARTICLE_ACTION_MAIL 4
#define MEMBER_BOARD_ARTICLE_ACTION_POST 5
#define MEMBER_BOARD_ARTICLE_ACTION_CROSS 6
#define MEMBER_BOARD_ARTICLE_ACTION_AUTH_BM 7
#define MEMBER_BOARD_ARTICLE_ACTION_AUTH_FRIEND 8
#define MEMBER_BOARD_ARTICLE_ACTION_ZSEND 9
#define MEMBER_BOARD_ARTICLE_ACTION_BLOG 10
#define MEMBER_BOARD_ARTICLE_ACTION_HELP 11
#define MEMBER_BOARD_ARTICLE_ACTION_DENY 12
#define MEMBER_BOARD_ARTICLE_ACTION_CLUB 13
#define MEMBER_BOARD_ARTICLE_ACTION_MSG 14
#define MEMBER_BOARD_ARTICLE_ACTION_INFO 15

struct board_member *b_members = NULL;
int board_member_sort=BOARD_MEMBER_SORT_DEFAULT;
int board_member_is_manager, board_member_is_joined;
static const char *b_member_item_prefix[10]={
    "是否审核","最大成员","登 录 数","发 文 数","用户积分",
    "用户等级","版面发文","版面原创","版面 M文","版面 G文"
};

static inline int bmc_digit_string(const char *s) {
    while (isdigit(*s++))
        continue;
    return (!*--s);
}

int b_member_set_show(struct board_member_config *config, struct board_member_config *old) {
    int i, same, changed;
    char buf[STRLEN], old_value[STRLEN];
    int values[2][10];
    
    values[0][0]=config->approve;
    values[0][1]=config->max_members;
    values[0][2]=config->logins;
    values[0][3]=config->posts;
    values[0][4]=config->score;
    values[0][5]=config->level;
    values[0][6]=config->board_posts;
    values[0][7]=config->board_origins;
    values[0][8]=config->board_marks;
    values[0][9]=config->board_digests;
    
    values[1][0]=old->approve;
    values[1][1]=old->max_members;
    values[1][2]=old->logins;
    values[1][3]=old->posts;
    values[1][4]=old->score;
    values[1][5]=old->level;
    values[1][6]=old->board_posts;
    values[1][7]=old->board_origins;
    values[1][8]=old->board_marks;
    values[1][9]=old->board_digests;
    
    changed=0;
    for (i=0;i<10;i++) {
        move(3+i, 0);
        clrtobot();
        same=(values[0][i]==values[1][i]);
        
        if (!same) changed=1;
        
        if (same) 
            old_value[0]=0;
        else if (i==0)
            sprintf(old_value, "  (%s)", values[1][i]>0?"是":"否");
        else
            sprintf(old_value, "  (%d)", values[1][i]);
        
        if (i==0)
            sprintf(buf, "%s%s\033[m%s", 
                same?"":"\033[1;32m",
                values[0][i]>0?"是":"否",
                old_value
            );
        else
            sprintf(buf, "%s%d\033[m%s", 
                same?"":"\033[1;32m",
                values[0][i],
                old_value
            );
        prints(" [\033[1;31m%d\033[m]%s: %s", i, b_member_item_prefix[i], buf);
    }
    update_endline();
    return changed;
}

int b_member_set_msg(char *msg) {
    move(t_lines-2,0);
    clrtobot();
    prints("                    \033[1;31m%s\033[m", msg);
    pressanykey();
    move(t_lines-2,0);
    clrtobot();    
    
    return 0;
}

int b_member_set() {
    struct board_member_config config;
    struct board_member_config old;
    char ans[4], ans2[20], buf[STRLEN];
    int i, changed;
    
    clear();
    if (load_board_member_config(currboard->filename, &config)<0) {
            move(10, 10);
            prints("加载驻版配置文件出错");
            pressanykey();
            return 0;
    }
    
    old.approve=config.approve;
    old.max_members=config.max_members;
    old.logins=config.logins;
    old.posts=config.posts;
    old.score=config.score;
    old.level=config.level;
    old.board_posts=config.board_posts;
    old.board_origins=config.board_origins;
    old.board_marks=config.board_marks;
    old.board_digests=config.board_digests;
    
    static const char *title="\033[1;32m[设定驻版条件]\033[m";
    
    move(0,0);
    prints("%s",title);
    move(0,40);
    prints("版面: \033[1;33m%s\033[m", currboard->filename);
    b_member_set_show(&config, &old);
    
    changed=0;
    while(1) {
        move(t_lines-1, 0);
        clrtobot();
        getdata(t_lines - 1, 0, "请选择修改项(\033[1;33m0\033[m-\033[1;33m9\033[m)/保存(\033[1;33mY\033[m)/退出(\033[1;33mN\033[m): ", ans, 2, DOECHO, NULL, true);
    
        switch(ans[0]) {
            case 'y':
            case 'Y':
                if (!changed) {
                    b_member_set_msg("设定并未修改!");
                } else {
                    move(t_lines-1,0);
                    clrtobot();
                    getdata(t_lines-1, 0, "您确定要修改驻版条件? (Y/N) [N]", ans2, 2, DOECHO, NULL, true);
                    if (ans2[0]=='y'||ans2[0]=='Y') {
                        if (save_board_member_config(currboard->filename, &config) < 0)
                            b_member_set_msg("驻版条件修改失败");
                        else
                            b_member_set_msg("驻版条件修改成功");
                    } else {
                        b_member_set_msg("设定并未修改!");
                    }
                }
                return 0;
            case 'n':
            case 'N':
                return 0;
            case '0':
                move(t_lines-1,0);
                clrtobot();
                getdata(t_lines-1, 0, "用户申请驻版时是否需要版主批准? (Y/N)", ans2, 8, DOECHO, NULL, true);
                
                if (ans2[0]=='y'||ans2[0]=='Y')
                    config.approve=1;
                else if (ans2[0]=='n'||ans2[0]=='N')
                    config.approve=0;
                else
                    break;
                    
                changed=b_member_set_show(&config, &old);    
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
                if ('1'==ans[0]&&!HAS_PERM(getCurrentUser(),PERM_SYSOP)) {
                    b_member_set_msg("最大成员数请联系主管站务进行更改!");
                    break;
                }
                
                i=atoi(&ans[0]);
                sprintf(buf, "请设定[\033[1;31m%d\033[m] \033[1;33m%s\033[m 的值: ", i, b_member_item_prefix[i]);
                
                move(t_lines-1,0);
                clrtobot();
                getdata(t_lines-1, 0, buf, ans2, 8, DOECHO, NULL, true);
                
                if (!bmc_digit_string(ans2)) {
                    b_member_set_msg("请输入整数值!");
                } else {
                    switch(i) {
                        case 1: config.max_members=atoi(ans2); break;
                        case 2: config.logins=atoi(ans2); break;
                        case 3: config.posts=atoi(ans2); break;
                        case 4: config.score=atoi(ans2); break;
                        case 5: config.level=atoi(ans2); break;
                        case 6: config.board_posts=atoi(ans2); break;
                        case 7: config.board_origins=atoi(ans2); break;
                        case 8: config.board_marks=atoi(ans2); break;
                        case 9: config.board_digests=atoi(ans2); break;
                    }
                    
                    changed=b_member_set_show(&config, &old);
                }
                break;
        }
    }
    
    return 0;
}

static int b_member_show(struct _select_def *conf, int i) {
    struct userec *lookupuser;
    char buf[STRLEN], color[STRLEN];
    
    if (getuser(b_members[i - conf->page_pos].user, &lookupuser)==0) {
        remove_board_member(b_members[i - conf->page_pos].board, b_members[i - conf->page_pos].user);
    } else {
        switch(b_members[i-conf->page_pos].status) {
            case BOARD_MEMBER_STATUS_NORMAL:
                strcpy(color, "\x1b[1;32m");
                break;
            case BOARD_MEMBER_STATUS_MANAGER:
                strcpy(color, "\x1b[1;31m");
                break;
            case BOARD_MEMBER_STATUS_CANDIDATE:
            default:
                strcpy(color, "\x1b[1;33m");
        }
        prints("%4d %s%-12s\x1b[m %-12s %8d %8d %8d %10d %-8s", i, color, lookupuser->userid, lookupuser->username, b_members[i - conf->page_pos].score, lookupuser->numlogins, lookupuser->numposts, lookupuser->score_user, tt2timestamp(b_members[i - conf->page_pos].time, buf));
    }
    
    return SHOW_CONTINUE;
}

static int b_member_title(struct _select_def *conf) {
    char buf[300];
    char tmp[300];
    
    if (board_member_is_joined)
        strcpy(buf, "取消驻版[\x1b[1;32mt\x1b[m] ");
    else
        strcpy(buf, "加入驻版[\x1b[1;32mj\x1b[m] ");
        
    if (board_member_is_manager) {
        strcpy(tmp, "通过[\x1b[1;32my\x1b[m] 拒绝[\x1b[1;32mn\x1b[m] 删除[\x1b[1;32md\x1b[m] 状态[\x1b[1;32ms\x1b[m] 设置[\x1b[1;32me\x1b[m] ");
        strcat(buf, tmp);
    }
    
    strcpy(tmp, "排序[\x1b[1;32mc\x1b[m] 寄信[\x1b[1;32mm\x1b[m] 查看[\x1b[1;32m→\x1b[m,\x1b[1;32mr\x1b[m]");
    strcat(buf, tmp);
    
    docmdtitle("[驻版用户列表]", buf);
    move(2, 0);
    clrtobot();
    prints("\033[0;1;44m  %-4s %-12s %-12s %8s %8s %8s %10s %-8s \033[m", "编号", "用户ID",  "用户昵称", "驻版积分", "上站数", "发文数", "用户积分", "驻版时间");    
    update_endline();

    return 0;
}

static int b_member_prekey(struct _select_def *conf, int *key)
{
    switch (*key) {
        case 'q':
            *key = KEY_LEFT;
            break;
        case 'r': // 查看
            *key = KEY_RIGHT;
            break;
        case ' ':
            *key = KEY_PGDN;
    }
    return SHOW_CONTINUE;
}

static int b_member_select(struct _select_def *conf) {
    t_query(b_members[conf->pos-conf->page_pos].user);
    
    return SHOW_REFRESH;
}

static int b_member_getdata(struct _select_def *conf, int pos, int len) {
    int i;

    bzero(b_members, sizeof(struct board_member) * BBS_PAGESIZE);
    i=load_board_members(currboard->filename, b_members, board_member_sort, conf->page_pos-1, BBS_PAGESIZE);

    if (i < 0)
        return SHOW_QUIT;

    if (i == 0 && conf->pos>1) {
        conf->pos = 1;
        i = load_board_members(currboard->filename, b_members, board_member_sort, 0, BBS_PAGESIZE);
        if (i < 0)
            return SHOW_QUIT;
    }

    return SHOW_CONTINUE;
}

static int b_member_join(struct _select_def *conf) {
    struct board_member_config config;
    struct board_member_config mine;
    int my_total, my_max;
    char ans[4];
    
    if (load_board_member_config(currboard->filename, &config)<0)
        return -1;
    if (load_board_member_request(currboard->filename, &mine)<0)
        return -2;
    my_total=get_member_boards(getCurrentUser()->userid);    
    if (my_total<0)
        return -3;
    my_max=(mine.level>MEMBER_USER_MAX_DEFAULT)?mine.level:MEMBER_USER_MAX_DEFAULT;
    
    clear();
    move(0, 0);
    prints("    \033[1;33m申请成为驻版用户\033[m\n\n");
    prints("当前版面名称: %s\n", currboard->filename);
    prints("是否需要审批: %s\n", (config.approve>0)?"\033[1;31m是\033[m":"\033[1;32m否\033[m");
    prints("驻版用户人数: %s%d\033[m / \033[1;33m%d\033[m \n\n", (conf->item_count>=config.max_members)?"\033[1;31m":"\033[1;32m", conf->item_count, config.max_members);
    
    prints("驻版要求  %8s  %8s\n", "最低值", "当前值");
    prints("上 站 数: %8d  %s%8d\033[m\n", config.logins, (config.logins>mine.logins)?"\033[1;31m":"\033[1;32m", mine.logins);
    prints("发 文 数: %8d  %s%8d\033[m\n", config.posts, (config.posts>mine.posts)?"\033[1;31m":"\033[1;32m", mine.posts);
#if defined(NEWSMTH) && !defined(SECONDSITE)    
    prints("用户积分: %8d  %s%8d\033[m\n", config.score, (config.score>mine.score)?"\033[1;31m":"\033[1;32m", mine.score);
    prints("用户等级: %8d  %s%8d\033[m\n", config.level, (config.level>mine.level)?"\033[1;31m":"\033[1;32m", mine.level);
#endif    
    prints("本版发文: %8d  %s%8d\033[m\n", config.board_posts, (config.board_posts>mine.board_posts)?"\033[1;31m":"\033[1;32m", mine.board_posts);
    prints("本版原创: %8d  %s%8d\033[m\n", config.board_origins, (config.board_origins>mine.board_origins)?"\033[1;31m":"\033[1;32m", mine.board_origins);
    prints("本版 M文: %8d  %s%8d\033[m\n", config.board_marks, (config.board_marks>mine.board_marks)?"\033[1;31m":"\033[1;32m", mine.board_marks);
    prints("本版 G文: %8d  %s%8d\033[m\n", config.board_digests, (config.board_digests>mine.board_digests)?"\033[1;31m":"\033[1;32m", mine.board_digests);
    
    prints("\n\n我已加入的版面: %s%d\033[m / \033[1;33m%d\033[m \n\n", (my_total>=my_max)?"\033[1;31m":"\033[1;32m", my_total, my_max);
    
    if (conf->item_count>=config.max_members ||
        config.logins>mine.logins ||
        config.posts>mine.posts ||
#if defined(NEWSMTH) && !defined(SECONDSITE)
        config.score>mine.score ||
        config.level>mine.level ||
#endif        
        config.board_posts>mine.board_posts ||
        config.board_origins>mine.board_origins ||
        config.board_marks>mine.board_marks ||
        config.board_digests>mine.board_digests ||
        my_total>=my_max
    ) {
        move(t_lines-3, 0);
        prints("\033[1;31m当前无法进行申请\033[m");
        pressanykey();
        return 0;
    }
    
    ans[0]=0;
    getdata(t_lines - 1, 0, "您要申请成为驻版用户吗?(Y/N) [N]: ", ans, 3, DOECHO, NULL, true);
    if (ans[0] != 'y' && ans[0]!='Y') 
        return 0;
    else if (join_board_member(currboard->filename)>0) 
        return 1;
    else
        return 0;
}

static int b_member_key(struct _select_def *conf, int key) {
    char ans[4];
    char buf[STRLEN];
    int del;
    
    if (conf->item_count<=0 && 'v'!=key && 'j'!=key && 'e'!=key) {
        return SHOW_CONTINUE;
    }
    
    del=0;
    switch (key) {
        case 'v':
            i_read_mail();
            return SHOW_REFRESH;
        case 'c':
        case 'C':
            board_member_sort=(board_member_sort+1)%BOARD_MEMBER_SORT_TYPES;
            return SHOW_DIRCHANGE;
        case 'y': // 通过申请
            if (!board_member_is_manager)
                return SHOW_CONTINUE;
            if (b_members[conf->pos-conf->page_pos].status != BOARD_MEMBER_STATUS_CANDIDATE)
                return SHOW_CONTINUE;
            move(t_lines-1, 0);
            clrtoeol();
            ans[0]=0;
            sprintf(buf, "您确定要通过%s的驻版申请吗?(Y/N) [N]:", b_members[conf->pos-conf->page_pos].user);
            getdata(t_lines-1, 0, buf, ans, 3, DOECHO, NULL, true);
            if (ans[0] != 'y' && ans[0]!='Y') 
                return SHOW_REFRESH;
            else if (approve_board_member(currboard->filename, b_members[conf->pos-conf->page_pos].user)>=0) {
                return SHOW_DIRCHANGE;
            } else
                return SHOW_REFRESH;
        case 'd': // 踢出用户
            del=1;
        case 'n': // 拒绝申请
            if (!board_member_is_manager)
                return SHOW_CONTINUE;
            if (!del && b_members[conf->pos-conf->page_pos].status != BOARD_MEMBER_STATUS_CANDIDATE)
                return SHOW_CONTINUE;
                
            move(t_lines-1, 0);
            clrtoeol();
            ans[0]=0;
            if (del)
                sprintf(buf, "您确定要从驻版用户列表中删除%s吗?(Y/N) [N]:", b_members[conf->pos-conf->page_pos].user);
            else
                sprintf(buf, "您确定要拒绝%s的驻版申请吗?(Y/N) [N]:", b_members[conf->pos-conf->page_pos].user);
            
            getdata(t_lines-1, 0, buf, ans, 3, DOECHO, NULL, true);
            if (ans[0] != 'y' && ans[0]!='Y') 
                return SHOW_REFRESH;
            else if (remove_board_member(currboard->filename, b_members[conf->pos-conf->page_pos].user)>=0) {
                conf->item_count = get_board_members(currboard->filename);
                if (0==strncasecmp(getCurrentUser()->userid,b_members[conf->pos-conf->page_pos].user, IDLEN)) {
                    board_member_is_joined=0;
                    b_member_title(conf);
                }
                return SHOW_DIRCHANGE;
            } else
                return SHOW_REFRESH;    
        case 'j': // 加入驻版
            if (board_member_is_joined) 
                return SHOW_CONTINUE;
            if (b_member_join(conf)>0) {
                board_member_is_joined=1;
                conf->item_count = get_board_members(currboard->filename);
                b_member_title(conf);
                return SHOW_DIRCHANGE;
            } else
                return SHOW_REFRESH;
        case 't': // 取消驻版
            if (!board_member_is_joined)
                return SHOW_CONTINUE;
            move(t_lines-1, 0);
            clrtoeol();
            ans[0]=0;
            getdata(t_lines-1, 0, "您确定要取消驻版吗?(Y/N) [N]: ", ans, 3, DOECHO, NULL, true);
            if (ans[0] != 'y' && ans[0]!='Y') 
                return SHOW_REFRESH;
            else if (leave_board_member(currboard->filename)>=0) {
                board_member_is_joined=0;
                conf->item_count = get_board_members(currboard->filename);
                b_member_title(conf);
                return SHOW_DIRCHANGE;
            } else
                return SHOW_REFRESH;
        case 'm': // 寄信
            if (HAS_PERM(getCurrentUser(), PERM_LOGINOK)&&!HAS_PERM(getCurrentUser(), PERM_DENYMAIL)&&strcmp(getCurrentUser()->userid, "guest")!=0) {
                m_send(b_members[conf->pos-conf->page_pos].user);
                return SHOW_REFRESH;
            }
            break;
        case 'e':
            if (!board_member_is_manager)
                return SHOW_CONTINUE;
            b_member_set();    
            return SHOW_REFRESH;
        case 's':
            if (!board_member_is_manager)
                return SHOW_CONTINUE;
            if (b_members[conf->pos-conf->page_pos].status == BOARD_MEMBER_STATUS_NORMAL) {
                sprintf(buf, "您要将%s设置为\033[1;31m核心驻版用户\033[m吗? (Y/N) [N]", b_members[conf->pos-conf->page_pos].user);
                del=BOARD_MEMBER_STATUS_MANAGER;
            } else if (b_members[conf->pos-conf->page_pos].status == BOARD_MEMBER_STATUS_MANAGER) {
                sprintf(buf, "您要将%s设置为\033[1;32m普通驻版用户\033[m吗? (Y/N) [N]", b_members[conf->pos-conf->page_pos].user);
                del=BOARD_MEMBER_STATUS_NORMAL;
            } else 
                return SHOW_CONTINUE;    
            
            move(t_lines-1, 0);
            clrtoeol();
            ans[0]=0;
            getdata(t_lines-1, 0, buf, ans, 3, DOECHO, NULL, true);
            if (ans[0] != 'y' && ans[0]!='Y') 
                return SHOW_REFRESH;
            else if (set_board_member_status(currboard->filename, b_members[conf->pos-conf->page_pos].user, del)>=0) 
                return SHOW_DIRCHANGE;
            else
                return SHOW_REFRESH;
    }
    return SHOW_CONTINUE;
}

int t_board_members(void) {
    struct _select_def group_conf;
    struct board_member mine;
    POINT *pts;
    int i;
    
    board_member_is_manager=(!HAS_PERM(getCurrentUser(),PERM_SYSOP)&&!chk_currBM(currboard->BM,getCurrentUser()))?0:1;
    board_member_is_joined=is_board_member(currboard->filename, getCurrentUser()->userid, &mine);
    b_members=(struct board_member *) malloc(sizeof(struct board_member) * BBS_PAGESIZE);
    bzero(&group_conf, sizeof(struct _select_def));
    
    pts = (POINT *) malloc(sizeof(POINT) * BBS_PAGESIZE);
    for (i = 0; i < BBS_PAGESIZE; i++) {
        pts[i].x = 2;
        pts[i].y = i + 3;
    }
    
    group_conf.item_per_page = BBS_PAGESIZE;
    group_conf.flag = LF_VSCROLL | LF_BELL | LF_MULTIPAGE | LF_LOOP;
    group_conf.prompt = "◆";
    group_conf.item_pos = pts;
    group_conf.title_pos.x = 0;
    group_conf.title_pos.y = 0;
    group_conf.pos = 1;
    group_conf.page_pos = 1;

    group_conf.item_count = get_board_members(currboard->filename);
    group_conf.show_data = b_member_show;
    group_conf.show_title = b_member_title;
    group_conf.pre_key_command = b_member_prekey;
    group_conf.on_select = b_member_select;
    group_conf.get_data = b_member_getdata;
    group_conf.key_command = b_member_key;

    
    clear();
    list_select_loop(&group_conf);

    free(pts);
    free(b_members);
    b_members=NULL;

    return 0;
}

void member_board_article_title(struct _select_def* conf) {
	char title[STRLEN];
	int chkmailflag = 0;
	
	chkmailflag = chkmail();
    if (chkmailflag == 2)       /*Haohmaru.99.4.4.对收信也加限制 */
        strcpy(title, "[您的信箱超过容量,不能再收信!]");
#ifdef ENABLE_REFER
/* added by windinsn, Jan 28, 2012, 检查是否有 @或回复提醒 */
     else if (chkmailflag==1)
         strcpy(title, "[您有信件]");
     else if (chkmailflag==3)
         strcpy(title, "[您有@提醒]");
     else if (chkmailflag==4)
         strcpy(title, "[您有回复提醒]");
#else
    else if (chkmailflag)       /* 信件检查 */
        strcpy(title, "[您有信件]");
#endif /* ENABLE_REFER */
    else
        strcpy(title, BBS_FULL_NAME);
	
    showtitle("[驻版阅读模式]", title);
    update_endline();
    move(1, 0);
    prints("离开[←,e] 选择[↑,↓] 阅读[→,r] 版面[s] 删除[d] 标题[?,/] 作者[a,A] 寻版[\',\"]\033[m\n");
    prints("\033[44m  编号   发布者       日期    讨论区名称   主题");
    clrtoeol();
    prints("\n");
    resetcolor();
}

char *member_board_article_ent(char *buf, int num, struct member_board_article *ent, struct member_board_article *readfh, struct _select_def* conf) {
    char *date;
    char c1[8],c2[8];
    int same=false, same_board=false, orig=0;
	char type;
	
	char unread_mark = (DEFINE(getCurrentUser(), DEF_UNREADMARK) ? UNREAD_SIGN : 'N');
    date=ctime((time_t *)&ent->posttime)+((ent->posttime/86400==time(NULL)/86400)?10:4);
    if (DEFINE(getCurrentUser(), DEF_HIGHCOLOR)) {
        strcpy(c1, "\033[1;33m");
        strcpy(c2, "\033[1;36m");
	} else {
        strcpy(c1, "\033[33m");
        strcpy(c2, "\033[36m");
	}
    if (readfh&&ent->s_groupid==readfh->s_groupid)
        same=true;
	else if (readfh&&0==strncasecmp(ent->board, readfh->board, STRLEN-1)) {
		same_board=true;
	} else
		same_board=false;
		
    if (strncmp(ent->title, "Re: ", 4))
        orig=1;

#ifdef HAVE_BRC_CONTROL
	brc_initial(getCurrentUser()->userid, ent->board, getSession());
    type = brc_unread(ent->id, getSession()) ? unread_mark : ' ';
#else
    type = ' ';	
#endif
		
	if ((ent->accessed[0] & FILE_DIGEST)) {
        if (type == ' ')
            type = 'g';
        else
            type = 'G';
    }
	
	if (ent->accessed[0] & FILE_MARKED) {
        switch (type) {
            case ' ':
                type = 'm';
                break;
            case UNREAD_SIGN:
            case 'N':
                type = 'M';
                break;
            case 'g':
                type = 'b';
                break;
            case 'G':
                type = 'B';
                break;
        }
    }
	
    sprintf(buf, " %s%4d %c %-12.12s %6.6s  %s%-12.12s%s %s%s\033[m", same?(ent->id==ent->groupid?c1:c2):"", num, type, ent->owner, date, same_board?c2:"", ent->board, same_board?"\033[m":"", orig?FIRSTARTICLE_SIGN" ":"", ent->title);

    return buf;
}

int member_board_article_read(struct _select_def* conf, struct member_board_article *article, void* extraarg) {
	struct read_arg *arg;
	struct boardheader *board;
	char buf[PATHLEN];
	int fd, num, retnum, key, save_currboardent, save_uinfo_currentboard, ret, repeat, force_update;
	fileheader_t post[1];
	
	if (article==NULL)
		return DONOTHING;
	
	arg=(struct read_arg*)conf->arg;
	board=(struct boardheader *)getbcache(article->board);
	if (!board||board->flag&BOARD_GROUP||!check_read_perm(getCurrentUser(), board)) {
        clear();
        prints("指定版面不存在...");
        pressreturn();
        return FULLUPDATE;
    }
	
	setbdir(DIR_MODE_NORMAL, buf, board->filename);
	if ((fd=open(buf, O_RDWR, 0644))<0) {
        clear();
        prints("无法打开版面文章列表...");
        pressreturn();
        return FULLUPDATE;
    }
	
	retnum=get_records_from_id(fd, article->id, post, 1, &num);
	close(fd);
	
	if (0==retnum) {
        clear();
        prints("文章不存在，可能已被删除...");
        pressreturn();
        return FULLUPDATE;
    }
	
	setbfile(buf, board->filename, post->filename);
#ifdef NOREPLY
    key=ansimore_withzmodem(buf, true, post->title); 
#else
    key=ansimore_withzmodem(buf, false, post->title); 
#endif 	

	save_currboardent=currboardent;
    save_uinfo_currentboard=uinfo.currentboard;

    currboardent=getbid(board->filename, NULL);
    currboard=board;
    uinfo.currentboard=currboardent;

#ifdef HAVE_BRC_CONTROL
    brc_initial(getCurrentUser()->userid, board->filename, getSession());
    brc_add_read(post->id, currboardent, getSession());
#endif

	if (arg->readdata==NULL)
        arg->readdata=malloc(sizeof(struct member_board_article));
    memcpy(arg->readdata, article, sizeof(struct member_board_article));

    ret=FULLUPDATE;
#ifndef NOREPLY
	move(t_lines-1, 0);
	prints("\033[44m\033[36m[通知模式] \033[32m[阅读文章]\033[33m 结束Q,| 上一篇 | 下一篇<空格>, | 同主题^x,p ");
	clrtoeol();
    resetcolor();
	
	if (!(key==KEY_RIGHT||key==KEY_PGUP||key==KEY_UP||key==KEY_DOWN)&&(!(key>0)||!strchr("RrEexp", key)))
        key=igetkey();
	
	repeat=0;
	force_update=0;
    do {
        if (repeat)
            key=igetkey();
    
        repeat=0;
        switch(key) {
            case KEY_LEFT:
            case 'Q':
            case 'q':
            case KEY_REFRESH:
                break;
            case 'R':
            case 'r':
            case 'Y':
            case 'y':
                clear();
                move(5,0);
                if(board->flag&BOARD_NOREPLY) {
                    prints("\t\t\033[1;33m%s\033[0;33m<Enter>\033[m","该版面已设置为不可回复文章...");
                    WAIT_RETURN;
                } else if (post->accessed[1]&FILE_READ && !HAS_PERM(getCurrentUser(), PERM_SYSOP)) {
                    prints("\t\t\033[1;33m%s\033[0;33m<Enter>\033[m","本文已设置为不可回复, 请勿试图讨论...");
                    WAIT_RETURN;
                } else {
                    do_reply(conf, post);
					ret=DIRCHANGED;
					force_update=1;
				}
                break;
            case Ctrl('R'):
                post_reply(conf, post, NULL);
                break; 
            case Ctrl('A'):
                clear();
                read_showauthor(conf,post,NULL);
                ret=READ_NEXT;
                break;
            case Ctrl('Z'):
            case 'H':
                r_lastmsg();
                break;
            case 'Z':
            case 'z':
                if (HAS_PERM(getCurrentUser(), PERM_PAGE)) {
                    read_sendmsgtoauthor(conf, post, NULL);
                    ret=READ_NEXT;
                }
                break;
            case 'u':
                clear();
                modify_user_mode(QUERY);
                t_query(NULL);
                break;
            case 'L':
                if (uinfo.mode==LOOKMSGS)
                    ret=DONOTHING;
                else
                    show_allmsgs();
                break;
            case 'o':
            case 'O':
                if (HAS_PERM(getCurrentUser(), PERM_BASIC)) {
                    t_friends();
                }
                break;
            case 'U':
                ret=board_query();
                break;
            case Ctrl('O'):
                clear();
                read_addauthorfriend(conf, post, NULL);
                ret=READ_NEXT;
                break;
            case '~':
                ret=read_authorinfo(conf, post, NULL);
                break;
            case Ctrl('W'):
                ret=read_showauthorBM(conf, post, NULL);
                break;  
            case KEY_RIGHT:
            case KEY_DOWN:
            case KEY_PGDN:
                ret=READ_NEXT;
                break;
            case KEY_UP:
                ret=READ_PREV;
                break;
            case 's':
            case 'S':
                savePos(DIR_MODE_NORMAL, NULL, num, board);
                ReadBoard();
                break;
            case '!':
                Goodbye();
                break;
        }
	} while(repeat);
#endif /* NOREPLY */
	
	uinfo.currentboard=save_uinfo_currentboard;
    currboardent=save_currboardent;
    currboard=((struct boardheader*)getboard(save_currboardent));
	
#ifdef HAVE_BRC_CONTROL
    if (currboard) {
        brc_initial(getCurrentUser()->userid, currboard->filename, getSession());
    }
#endif

	if (ret==FULLUPDATE&&arg->oldpos!=0) {
        conf->new_pos=arg->oldpos;
        arg->oldpos=0;
        list_select_add_key(conf, KEY_REFRESH);
        arg->readmode=READ_NORMAL;
        return SELCHANGE;
    }
	
	if (flush_member_board_articles(DIR_MODE_NORMAL, getCurrentUser(), force_update))
		return DIRCHANGED;
		
	return ret;
}

int member_board_article_board_info(struct _select_def* conf,struct member_board_article *article, int act)
{
	int save_currboardent, save_uinfo_currentboard;
	struct boardheader *board;
	char save_bm[BM_LEN];
	char path[PATHLEN];
	
	if (article==NULL)
		return DONOTHING;
	board=(struct boardheader *)getbcache(article->board);
	if (!board||board->flag&BOARD_GROUP||!check_read_perm(getCurrentUser(), board)) {
        clear();
        prints("指定版面不存在...");
        pressreturn();
        return FULLUPDATE;
    }
	
	save_currboardent=currboardent;
    save_uinfo_currentboard=uinfo.currentboard;
	memcpy(save_bm, currBM, BM_LEN - 1);
	
    currboardent=getbid(board->filename, NULL);
    currboard=board;
    uinfo.currentboard=currboardent;
	memcpy(currBM, board->BM, BM_LEN - 1);
#ifdef HAVE_BRC_CONTROL
    brc_initial(getCurrentUser()->userid, board->filename, getSession());
#endif		
	
	switch (act) {
		case MEMBER_BOARD_ARTICLE_BOARD_HOT:
			read_hot_info(conf, NULL, NULL);
			break;
		case MEMBER_BOARD_ARTICLE_BOARD_NOTE:
			clear();
			sprintf(path, "vote/%s/notes", currboard->filename);
			if (dashf(path)) {
				ansimore2(path, false, 0, 0);
			} else if (dashf("vote/notes")) {
				ansimore2("vote/notes", false, 0, 23);
			} else {
				move(3, 30);
				prints("此讨论区尚无「备忘录」。");
			}
			pressanykey();
			break;
	}
	
	uinfo.currentboard=save_uinfo_currentboard;
    currboardent=save_currboardent;
    currboard=((struct boardheader*)getboard(save_currboardent));
	memcpy(currBM, save_bm, BM_LEN - 1);
#ifdef HAVE_BRC_CONTROL
    if (currboard) {
        brc_initial(getCurrentUser()->userid, currboard->filename, getSession());
    }
#endif		
	
	if (flush_member_board_articles(DIR_MODE_NORMAL, getCurrentUser(), 0))
		return DIRCHANGED;
		
	return FULLUPDATE;
}

int member_board_article_select(struct _select_def* conf,struct member_board_article *article,void* extraarg)
{
	Select();
	
	return DOQUIT;
}

int member_board_article_clear_new_flag(struct _select_def* conf,struct member_board_article *article,void* extraarg)
{
#ifdef HAVE_BRC_CONTROL
	struct board_member *members;
	struct boardheader *board;
	int total, i, bid;
	
	total=get_member_boards(getCurrentUser()->userid);
	if (total <= 0)
		return DONOTHING;
	
	members=(struct board_member *) malloc(sizeof(struct board_member) * total);
	bzero(members, sizeof(struct board_member) * total);
	total=load_member_boards(getCurrentUser()->userid, members, 0, 0, total);
	if (total <= 0) {
		free(members);
		members=NULL;
		return DONOTHING;
	}
	
	for (i=0;i<total;i++) {
		bid=getbid(members[i].board, &board);
		if (bid) {
			brc_initial(getCurrentUser()->userid, board->filename, getSession());
			brc_clear(bid, getSession());
		}
	}
	
	free(members);
	members=NULL;
	
	if (currboard) {
        brc_initial(getCurrentUser()->userid, currboard->filename, getSession());
    }
	
	flush_member_board_articles(DIR_MODE_NORMAL, getCurrentUser(), 1);
	return DIRCHANGED;
#else
    return DONOTHING;
#endif
}

int member_board_article_info(struct _select_def* conf,struct member_board_article *article, int act)
{
	struct boardheader *board;
	fileheader_t post[1];
	int fd, num, retnum, save_currboardent, save_uinfo_currentboard;;
	char buf[STRLEN];
	int flush;
	char save_bm[BM_LEN];
	
	if (article==NULL)
		return DONOTHING;
	board=(struct boardheader *)getbcache(article->board);
	if (!board||board->flag&BOARD_GROUP||!check_read_perm(getCurrentUser(), board)) {
        clear();
        prints("指定版面不存在...");
        pressreturn();
        return FULLUPDATE;
    }
	
	setbdir(DIR_MODE_NORMAL, buf, board->filename);
	if ((fd=open(buf, O_RDWR, 0644))<0) {
        clear();
        prints("无法打开版面文章列表...");
        pressreturn();
        return FULLUPDATE;
    }
	
	retnum=get_records_from_id(fd, article->id, post, 1, &num);
	close(fd);
	
	if (0==retnum) {
        clear();
        prints("文章不存在，可能已被删除...");
        pressreturn();
        return FULLUPDATE;
    }	

	save_currboardent=currboardent;
    save_uinfo_currentboard=uinfo.currentboard;
	memcpy(save_bm, currBM, BM_LEN - 1);

    currboardent=getbid(board->filename, NULL);
    currboard=board;
    uinfo.currentboard=currboardent;
	memcpy(currBM, board->BM, BM_LEN - 1);
#ifdef HAVE_BRC_CONTROL
    brc_initial(getCurrentUser()->userid, board->filename, getSession());
#endif	
	
	flush=0;
	switch (act) {
		case MEMBER_BOARD_ARTICLE_ACTION_AUTH_INFO:
			read_showauthor(conf, post, NULL);
			break;
		case MEMBER_BOARD_ARTICLE_ACTION_AUTH_DETAIL:
			read_authorinfo(conf, post, NULL);
			break;
		case MEMBER_BOARD_ARTICLE_ACTION_MAIL:
			post_reply(conf, post, NULL);
			break;
		case MEMBER_BOARD_ARTICLE_ACTION_FORWARD:
			mail_forward(conf, post, NULL);
			break;
		case MEMBER_BOARD_ARTICLE_ACTION_POST:
			Post();
			flush=1;
			break;
		case MEMBER_BOARD_ARTICLE_ACTION_CROSS:
			do_cross(conf, post, NULL);
			flush=1;
			break;
		case MEMBER_BOARD_ARTICLE_ACTION_AUTH_BM:
			read_showauthorBM(conf, post, NULL);
			break;
		case MEMBER_BOARD_ARTICLE_ACTION_AUTH_FRIEND:
			read_addauthorfriend(conf, post, NULL);
			break;
		case MEMBER_BOARD_ARTICLE_ACTION_ZSEND:
			read_zsend(conf, post, NULL);
			break;
#ifdef PERSONAL_CORP			
		case MEMBER_BOARD_ARTICLE_ACTION_BLOG:
			read_importpc(conf, post, NULL);
			break;
#endif
		case MEMBER_BOARD_ARTICLE_ACTION_HELP:
			mainreadhelp(conf, post, NULL);
			break;
		case MEMBER_BOARD_ARTICLE_ACTION_DENY:
			deny_user(conf, post, NULL);
			break;
		case MEMBER_BOARD_ARTICLE_ACTION_CLUB:
			clubmember(conf, post, NULL);
			break;	
		case MEMBER_BOARD_ARTICLE_ACTION_MSG:
			read_sendmsgtoauthor(conf, post, NULL);
			break;
		case MEMBER_BOARD_ARTICLE_ACTION_INFO:
			showinfo(conf, post, NULL);
			break;
	}

	uinfo.currentboard=save_uinfo_currentboard;
    currboardent=save_currboardent;
    currboard=((struct boardheader*)getboard(save_currboardent));
	memcpy(currBM, save_bm, BM_LEN - 1);
#ifdef HAVE_BRC_CONTROL
    if (currboard) {
        brc_initial(getCurrentUser()->userid, currboard->filename, getSession());
    }
#endif	
	
	if (flush && flush_member_board_articles(DIR_MODE_NORMAL, getCurrentUser(), 1))
		return DIRCHANGED;
		
	return FULLUPDATE;
}

struct key_command member_board_article_comms[]={
    {'r', (READ_KEY_FUNC)member_board_article_read, NULL},
	{Ctrl('P'), (READ_KEY_FUNC)member_board_article_info,(void *)MEMBER_BOARD_ARTICLE_ACTION_POST},
	{Ctrl('C'), (READ_KEY_FUNC)member_board_article_info,(void *)MEMBER_BOARD_ARTICLE_ACTION_CROSS},
	{'H', (READ_KEY_FUNC)member_board_article_board_info, (void *)MEMBER_BOARD_ARTICLE_BOARD_HOT},
	{'s', (READ_KEY_FUNC)member_board_article_select,NULL},
	{'v', (READ_KEY_FUNC)read_callfunc0, (void *)i_read_mail},
	{'F', (READ_KEY_FUNC)member_board_article_info,(void *)MEMBER_BOARD_ARTICLE_ACTION_FORWARD},
	{Ctrl('R'), (READ_KEY_FUNC)member_board_article_info,(void *)MEMBER_BOARD_ARTICLE_ACTION_MAIL},
	{'f', (READ_KEY_FUNC)member_board_article_clear_new_flag,NULL},
	{Ctrl('A'), (READ_KEY_FUNC)member_board_article_info,(void *)MEMBER_BOARD_ARTICLE_ACTION_AUTH_INFO},
	{'~',(READ_KEY_FUNC)member_board_article_info,(void *)MEMBER_BOARD_ARTICLE_ACTION_AUTH_DETAIL},
	{Ctrl('W'), (READ_KEY_FUNC)member_board_article_info,(void *)MEMBER_BOARD_ARTICLE_ACTION_AUTH_BM},
	{Ctrl('O'), (READ_KEY_FUNC)member_board_article_info,(void *)MEMBER_BOARD_ARTICLE_ACTION_AUTH_FRIEND},
	{Ctrl('Y'), (READ_KEY_FUNC)member_board_article_info,(void *)MEMBER_BOARD_ARTICLE_ACTION_ZSEND},
#ifdef PERSONAL_CORP
    {'y', (READ_KEY_FUNC)member_board_article_info,(void *)MEMBER_BOARD_ARTICLE_ACTION_BLOG},
#endif	
	{'h', (READ_KEY_FUNC)member_board_article_info,(void *)MEMBER_BOARD_ARTICLE_ACTION_HELP},
	{KEY_TAB, (READ_KEY_FUNC)member_board_article_board_info,(void *)MEMBER_BOARD_ARTICLE_BOARD_NOTE},
	{Ctrl('D'), (READ_KEY_FUNC)member_board_article_info,(void *)MEMBER_BOARD_ARTICLE_ACTION_DENY},
    {Ctrl('E'), (READ_KEY_FUNC)member_board_article_info,(void *)MEMBER_BOARD_ARTICLE_ACTION_CLUB},
	{'z', (READ_KEY_FUNC)member_board_article_info,(void *)MEMBER_BOARD_ARTICLE_ACTION_MSG},
	{'!', (READ_KEY_FUNC)read_callfunc0,(void *)Goodbye},
	{Ctrl('Q'), (READ_KEY_FUNC)member_board_article_info,(void *)MEMBER_BOARD_ARTICLE_ACTION_INFO},
    {'\n', NULL},
};

int t_member_board_articles(void) {
	static int mode=DIR_MODE_NORMAL;
	char path[PATHLEN];
	int returnmode=CHANGEMODE;
	
	set_member_board_article_dir(mode, path, getCurrentUser()->userid);
	clear();
    if (load_member_board_articles(path, mode, getCurrentUser(), 0)<0) {
        move(10, 10);
		prints("加载驻版信息出错");
		pressanykey();
        return 0;
    }
	
	while(returnmode==CHANGEMODE) {
        returnmode=new_i_read(DIR_MODE_MEMBER_ARTICLE, path, member_board_article_title, (READ_ENT_FUNC)member_board_article_ent, &member_board_article_comms[0], sizeof(struct member_board_article));
    }
	
	return 0;
}

#endif /* ENABLE_BOARD_MEMBER */

