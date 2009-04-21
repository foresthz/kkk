
#include "bbs.h"

// ��ʼ��״̬�ṹ��
int ddd_gs_init(struct ddd_global_status* gs)
{
    gs->type = GS_NONE;
    gs->sec = 0;
    gs->favid = 0;
    gs->bid = 0;
    gs->mode = DIR_MODE_NORMAL;
    gs->pos = 0;
    gs->filter = 0;
    gs->recur = 0;
    return 0;
}

// �������
int ddd_entry()
{
    char ans[2];

    ddd_gs_init(&DDD_GS_CURR);
    ddd_gs_init(&DDD_GS_NEW);

    while (true) {
        clear();
        ddd_header();
        move(3, 3);
        prints("��򵹲������˵�");
        move(5, 3);
        prints("(B) ���а����б�");
        move(6, 3);
        prints("(0-9) ����������");
        move(7, 3);
        prints("(X) �·���������");
        move(8, 3);
        prints("(F) ���˶�����");
        move(9, 3);
        prints("(S) ѡ�����");
        move(10, 3);
        prints("(M) ����");
        update_endline();
        getdata(12, 3, "��ѡ��: ", ans, 2, DOECHO, NULL, true);
        if ((ans[0] == 'b') || (ans[0] == 'B')) {
            DDD_GS_CURR.type = GS_ALL;
            DDD_GS_CURR.sec = 0;
            DDD_GS_CURR.pos = 1;
        } else if ((ans[0] >= '0') && (ans[0] <= '9')) {
            DDD_GS_CURR.type = GS_ALL;
            DDD_GS_CURR.sec = (int)ans[0];
            DDD_GS_CURR.pos = 1;
        } else if ((ans[0] == 'x') || (ans[0] == 'X')) {
            DDD_GS_CURR.type = GS_NEW;
            DDD_GS_CURR.favid = 0;
            DDD_GS_CURR.pos = 1;
        } else if ((ans[0] == 'f') || (ans[0] == 'F')) {
            DDD_GS_CURR.type = GS_FAV;
            DDD_GS_CURR.favid = 0;
            DDD_GS_CURR.pos = 1;
        } else if ((ans[0] == 's') || (ans[0] == 'S')) {
            int bid, type;
            if(ddd_choose_board(&bid, &type)) {
                DDD_GS_CURR.type = type;
                DDD_GS_CURR.bid = bid;
                DDD_GS_CURR.pos = 1;
            }
            else
                DDD_GS_CURR.type = GS_NONE;
        } else if (ans[0] == 0)
            break;
        ddd_read_loop();
    }

    return 0;
}

// ��ѭ��
int ddd_read_loop()
{
    struct ddd_global_status gs_this_level;
    while (true) {
        memcpy(&DDD_GS_NEW, &DDD_GS_CURR, sizeof(struct ddd_global_status));
        // ���ݵ�ǰ״̬�����ͽ��벻ͬ���Ķ�����
        switch (DDD_GS_CURR.type) {

            case GS_NONE:
                break;

            case GS_ALL:
                ddd_read_all();
                break;

            case GS_NEW:
                ddd_read_new();
                break;

            case GS_FAV:
                ddd_read_fav();
                break;

            case GS_GROUP:
                ddd_read_group();
                break;

            default:
                ddd_read_unknown();
                break;
        }
        // �����״̬��Ҫ�ݹ�
        if (DDD_GS_NEW.recur) {
            // ��¼�˲�״̬
            DDD_GS_NEW.recur = 0;
            memcpy(&gs_this_level, &DDD_GS_CURR, sizeof(struct ddd_global_status));
            memcpy(&DDD_GS_CURR, &DDD_GS_NEW, sizeof(struct ddd_global_status));
            // �ݹ��ȥ
            ddd_read_loop();
            // �ָ��˲�״̬
            memcpy(&DDD_GS_CURR, &gs_this_level, sizeof(struct ddd_global_status));
        }
        // �����״̬����Ҫ�ݹ�
        else
            memcpy(&DDD_GS_CURR, &DDD_GS_NEW, sizeof(struct ddd_global_status));
        if (DDD_GS_CURR.type == GS_NONE)
            break;
    }
    return 0;
}

// ��ʾվ�����
int ddd_header()
{
    int colour, centerpos, rightpos, l1, l2;
    char lefttxt[STRLEN], righttxt[STRLEN];

    // �Զ���ɫ�任
    if (DEFINE(getCurrentUser(), DEF_TITLECOLOR)) {
        colour = 4;
    } else {
        colour = getCurrentUser()->numlogins % 4 + 3;
        if (colour == 3)
            colour = 1;
    }

    move(0, 0);
    setbcolor(colour);
    switch (DDD_GS_CURR.type) {
        case GS_ALL:
            strcpy(lefttxt, "[�������б�]");
            if (DDD_GS_CURR.sec == 0)
                strcpy(righttxt, "���а���");
            else
                sprintf(righttxt, "��%c��", (char)(DDD_GS_CURR.sec));
            break;
        case GS_NEW:
            strcpy(lefttxt, "[�������б�]");
            strcpy(righttxt, "�·���������");
            break;
        case GS_FAV:
            strcpy(lefttxt, "[���˶�����]");
            strcpy(righttxt, "");
            break;
        case GS_GROUP:
            strcpy(lefttxt, "[�������б�]");
            sprintf(righttxt, "Ŀ¼[%d]", DDD_GS_CURR.bid);
            break;
        case GS_BOARD:
            strcpy(lefttxt, "����:");
            sprintf(righttxt, "����[%d]", DDD_GS_CURR.bid);
            break;
        case GS_MAIL:
            strcpy(lefttxt, "[����]");
            strcpy(righttxt, "");
            break;
        default:
            strcpy(lefttxt, "�������");
            strcpy(righttxt, "�������");
            break;
    }
    // �����м��λ��
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
    prints(" \033[1;32m���ȫ��״̬\033[m  type=%d  sec=%d  favid=%d  bid=%d  mode=%d  filter=%d", DDD_GS_CURR.type, DDD_GS_CURR.sec, DDD_GS_CURR.favid, DDD_GS_CURR.bid, DDD_GS_CURR.mode, DDD_GS_CURR.filter);
    return 0;
}

// �㲻�����ʱ�����ʾ���
int ddd_read_unknown()
{
    clear();
    ddd_header();
    move(3, 3);
    prints("δ֪�Ķ�״̬��");
    update_endline();
    WAIT_RETURN;
    DDD_GS_NEW.type = GS_NONE;
    DDD_GS_NEW.recur = 0;
    return 0;
}

// ���û�ѡ�����
int ddd_choose_board(int* bid, int* type) {
    char bname[STRLEN];
    int ret;
    struct boardheader *bh;
    // ��ʾѡ��������
    move(1, 0);
    clrtoeol();
    prints("ѡ��������: ");
    make_blist(0, 1);
    // �����Զ�����
    ret = namecomplete(NULL, bname);
    // ���ûѡ��
    if(bname[0] == 0)
        return 0;
    *bid = getbnum_safe(bname, getSession(), 1);
    if(*bid == 0) {
        move(2, 0);
        prints("�������������");
        return 0;
    }
    bh = getboard(*bid);
    if(bh->flag & BOARD_GROUP)
        *type = GS_GROUP;
    else
        *type = GS_BOARD;
    return 1;
}

