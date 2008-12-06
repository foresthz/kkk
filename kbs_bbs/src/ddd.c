
#include "bbs.h"

static int check_newpost(struct newpostdata *ptr);


/* */

int ddd_gs_init(struct ddd_global_status* gs) {
    gs->type = GS_NONE;
    gs->sec = 0;
    gs->favid = 0;
    gs->bid = 0;
    gs->mode = DIR_MODE_NORMAL;
    gs->pos = 0;
    gs->filter = 0;
    return 0;
}

int ddd_entry() {
    char ans[2];

    ddd_gs_init(&DDD_GS_CURR); 
    ddd_gs_init(&DDD_GS_NEW);

    while(true) {
        clear();
        ddd_header();
        move(3, 3);
        prints("大打倒测试主菜单");
        move(5, 3);
        prints("(B) 所有版面列表");
        move(6, 3);
        prints("(0-9) 分类讨论区");
        move(7, 3);
        prints("(X) 新分类讨论区");
        move(8, 3);
        prints("(F) 个人定制区");
        move(9, 3);
        prints("(S) 选择版面");
        move(10, 3);
        prints("(M) 信箱");
        update_endline();
        getdata(12, 3, "请选择: ", ans, 2, DOECHO, NULL, true);
        if((ans[0] == 'b') || (ans[0] == 'B')) {
            DDD_GS_CURR.type = GS_ALL;
            DDD_GS_CURR.sec = 0;
            DDD_GS_CURR.pos = 1;
        }
        else if((ans[0] >= '0') && (ans[0] <= '9')) {
            DDD_GS_CURR.type = GS_ALL;
            DDD_GS_CURR.sec = (int)ans[0];
            DDD_GS_CURR.pos = 1;
        }
        else if(ans[0] == 0)
            break;
        ddd_read_loop();
    }

    return 0;
}

int ddd_read_loop() {
    struct ddd_global_status gs_this_level;
    while(true) {
        memcpy(&DDD_GS_NEW, &DDD_GS_CURR, sizeof(struct ddd_global_status));
        switch(DDD_GS_CURR.type) {
        
        case GS_ALL:
            ddd_read_all();
            break;

        default:
            ddd_read_unknown();
            break;
        }

        memcpy(&DDD_GS_CURR, &DDD_GS_NEW, sizeof(struct ddd_global_status));
        if(DDD_GS_CURR.type == GS_NONE)
            break;
    }
    return 0;
}

int ddd_header() {
    int colour, centerpos, rightpos, l1, l2;
    char lefttxt[STRLEN], righttxt[STRLEN];

    if(DEFINE(getCurrentUser(), DEF_TITLECOLOR)) {
        colour = 4;
    }
    else {
        colour = getCurrentUser()->numlogins % 4 + 3;
        if(colour == 3)
            colour = 1;
    }

    move(0, 0);
    setbcolor(colour);
    switch(DDD_GS_CURR.type) {
    case GS_ALL:
        strcpy(lefttxt, "[讨论区列表]");
        if(DDD_GS_CURR.sec == 0)
            strcpy(righttxt, "所有版面");
        else
            sprintf(righttxt, "第%c区", (char)(DDD_GS_CURR.sec));
        break;
    case GS_NEW:
        strcpy(lefttxt, "[讨论区列表]");
        strcpy(righttxt, "新分类讨论区");
        break;
    case GS_FAV:
        strcpy(lefttxt, "[个人定制区]");
        strcpy(righttxt, "");
        break;
    case GS_GROUP:
        strcpy(lefttxt, "[讨论区列表]");
        sprintf(righttxt, "目录[%d]", DDD_GS_CURR.bid);
        break;
    case GS_BOARD:
        strcpy(lefttxt, "版主:");
        sprintf(righttxt, "版面[%d]", DDD_GS_CURR.bid);
        break;
    case GS_MAIL:
        strcpy(lefttxt, "[信箱]");
        strcpy(righttxt, "");
        break;
    default:
        strcpy(lefttxt, "我在哪里？");
        strcpy(righttxt, "我在哪里？");
        break;
    }
    l1 = strlen(lefttxt);
    l2 = strlen(righttxt);
    centerpos = l1 + (scr_cols - l1 - l2 - strlen(BBS_FULL_NAME)) / 2;
    rightpos = scr_cols - l2;
    prints("\033[1;33m%s", lefttxt);
    clrtoeol();
    move(0, centerpos);
    prints("\033[1;37m%s", BBS_FULL_NAME);
    move(0, rightpos);
    prints("\033[1;33m%s", righttxt);
    prints("\033[m\n");
    prints(" \033[1;32m大打倒全局状态\033[m  type=%d  sec=%d  favid=%d  bid=%d  mode=%d  filter=%d", DDD_GS_CURR.type, DDD_GS_CURR.sec, DDD_GS_CURR.favid, DDD_GS_CURR.bid, DDD_GS_CURR.mode, DDD_GS_CURR.filter);
    return 0;
}


/* common */

static int check_newpost(struct newpostdata *ptr)
{
    struct BoardStatus *bptr;

    if (ptr->dir)
        return 0;

    ptr->total = ptr->unread = 0;

    bptr = getbstatus(ptr->pos+1);
    if (bptr == NULL)
        return 0;
    ptr->total = bptr->total;
    ptr->currentusers = bptr->currentusers;

#ifdef HAVE_BRC_CONTROL
    if (!brc_initial(getCurrentUser()->userid, ptr->name, getSession())) {
        ptr->unread = 1;
    } else {
        if (brc_board_unread(ptr->pos+1, getSession())) {
            ptr->unread = 1;
        }
    }
#endif
    ptr->lastpost = bptr->lastpost;
    return 1;
}


/* GS_ALL */

struct ddd_read_all_arg {
    struct newpostdata *boardlist;
};

static int ddd_read_all_getdata(struct _select_def* conf, int pos, int len) {
    struct ddd_read_all_arg *arg;
    char *prefix, buf[STRLEN];
    
    arg = (struct ddd_read_all_arg *)(conf->arg);
    if(DDD_GS_CURR.sec == 0)
        prefix = NULL;
    else {
        sprintf(buf, "EGROUP%c", (char)(DDD_GS_CURR.sec));
        prefix = sysconf_str(buf);
    }
    conf->item_count = load_boards(arg->boardlist, prefix, 0, pos, len, 0, 0, NULL, getSession());
    return SHOW_CONTINUE;
}

static int ddd_read_all_onselect(struct _select_def* conf) {
    return SHOW_SELECT;
}

static int ddd_read_all_showdata(struct _select_def* conf, int pos) {
    struct ddd_read_all_arg *arg;
    struct newpostdata *ptr;
    char flag[20], f, onlines[20], tmpBM[BM_LEN + 1];

    arg = (struct ddd_read_all_arg *)(conf->arg); 
    ptr = &(arg->boardlist[pos - conf->page_pos]);
    
    if(ptr->flag & BOARD_GROUP) {
        prints(" %4d  ＋ ", ptr->total);
        strcpy(onlines, "    ");
    }
    else {
        check_newpost(ptr);
        prints(" %4d%s%s ", ptr->total, (ptr->total > 99999) ? "" : ((ptr->total > 9999) ? " " : "  "), ptr->unread ? "◆" : "◇");
        sprintf(onlines, "%4d", (ptr->currentusers > 9999) ? 9999 : ptr->currentusers);
    }
    
    if((ptr->flag & BOARD_CLUB_READ) && (ptr->flag & BOARD_CLUB_WRITE))
        f = 'A';
    else if(ptr->flag & BOARD_CLUB_READ)
        f = 'c';
    else if(ptr->flag & BOARD_CLUB_WRITE)
        f = 'p';
    else
        f = ' ';
    sprintf(flag, "\033[1;3%cm%c", (ptr->flag & BOARD_CLUB_HIDE) ? '1' : '3', f);
    prints(" %-16s%s%s", ptr->name, (ptr->flag & BOARD_VOTEFLAG) ? "\033[1;31mV" : " ", flag);
    if(checkreadonly(ptr->name))
        prints("\033[1;32m[只读]\033[m%-32s", ptr->title + 7);
    else
        prints("\033[m%-32s", ptr->title + 1);
    strncpy(tmpBM, ptr->BM, BM_LEN);
    tmpBM[BM_LEN] = 0;
    prints(" %s %-12s", onlines, (ptr->BM[0] <= ' ') ? "诚征版主中" : strtok(tmpBM, " "));
    return SHOW_CONTINUE;
}

static int ddd_read_all_prekeycommand(struct _select_def* conf, int* command) {
    return SHOW_CONTINUE;
}

static int ddd_read_all_keycommand(struct _select_def* conf, int command) {
    
    return SHOW_CONTINUE;
}

static int ddd_read_all_showtitle(struct _select_def* conf) {
    clear();
    ddd_header();
    move(2, 0);
    prints("\033[1;37;44m  全部 未读 讨论区名称       V 类别 转信  中  文  叙  述       在线 版  主");
    clrtoeol();
    prints("\033[m");
    update_endline();
    return SHOW_CONTINUE;
}

int ddd_read_all() {
    struct _select_def conf;
    struct ddd_read_all_arg arg;
    POINT *pts;
    int i, ret;

    pts = (POINT *)malloc(BBS_PAGESIZE * sizeof(POINT));
    for(i=0; i<BBS_PAGESIZE; i++) {
        pts[i].x = 1;
        pts[i].y = i + 3;
    }

    bzero((char *)&conf, sizeof(struct _select_def));
    bzero((char *)&arg, sizeof(struct ddd_read_all_arg));
    arg.boardlist = (struct newpostdata *)malloc(BBS_PAGESIZE * sizeof(struct newpostdata));

    conf.item_per_page = BBS_PAGESIZE;
    conf.flag = LF_NUMSEL | LF_VSCROLL | LF_BELL | LF_LOOP | LF_MULTIPAGE;
    conf.prompt = ">";
    conf.item_pos = pts;
    conf.arg = &arg;
    conf.pos = DDD_GS_CURR.pos;
    conf.page_pos = ((conf.pos - 1) / BBS_PAGESIZE) * BBS_PAGESIZE + 1;
    conf.get_data = ddd_read_all_getdata;
    conf.on_select = ddd_read_all_onselect;
    conf.show_data = ddd_read_all_showdata;
    conf.pre_key_command = ddd_read_all_prekeycommand;
    conf.key_command = ddd_read_all_keycommand;
    conf.show_title = ddd_read_all_showtitle;

    ret = list_select_loop(&conf);

    switch(ret) {
    case SHOW_QUIT:
        DDD_GS_NEW.type = GS_NONE;
        break;
    }

    free(arg.boardlist);
    free(pts);
    return 0;
}


/* unknown global status */

int ddd_read_unknown() {
    clear();
    move(3, 3);
    prints("未知阅读状态。");
    WAIT_RETURN;
    return 0;
}

