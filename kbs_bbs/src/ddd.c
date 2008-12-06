#include "bbs.h"

int ddd_gs_init(struct ddd_global_status* gs) {
    gs->type = GS_NONE;
    gs->sec = 0;
    gs->favid = 0;
    gs->bid = 0;
    gs->mode = DIR_MODE_NORMAL;
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
        if(ans[0] == 0)
            break;
        ddd_read_loop();
    }

    return 0;
}

int ddd_read_loop() {
    struct ddd_global_status gs_this_level;
    while(true) {
        switch(DDD_GS_CURR.type) {
            
        default:
            ddd_unknown();
            break;
        }
        if(DDD_GS_CURR.type == GS_NONE)
            break;
    }
    return 0;
}

int ddd_header() {
    move(0, 0);
    prints("\033[1;36;44m(ddd) \033[37m%s  \033[33m", BBS_FULL_NAME);
    switch(DDD_GS_CURR.type) {
    case GS_ALL:
        if(DDD_GS_CURR.sec == -1)
            prints("���а���");
        else
            prints("������������%d��", DDD_GS_CURR.sec);
        break;
    case GS_NEW:
        prints("�·���������");
        break;
    case GS_FAV:
        prints("���˶�����");
        break;
    case GS_GROUP:
        prints("Ŀ¼����[%d]", DDD_GS_CURR.bid);
        break;
    case GS_BOARD:
        prints("����[%d]", DDD_GS_CURR.bid);
        break;
    case GS_MAIL:
        prints("����");
        break;
    default:
        prints("û�ڴ����ѭ��");
        break;
    }
    clrtoeol();
    prints("\033[m\n");
    prints(" type=%d  sec=%d  favid=%d  bid=%d  mode=%d  filter=%d", DDD_GS_CURR.type, DDD_GS_CURR.sec, DDD_GS_CURR.favid, DDD_GS_CURR.bid, DDD_GS_CURR.mode, DDD_GS_CURR.filter);
    return 0;
}

int ddd_unknown() {
    clear();
    move(3, 3);
    prints("δ֪�Ķ�״̬��");
    WAIT_RETURN;
    return 0;
}

