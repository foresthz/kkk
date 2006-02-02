#include "bbs.h"
#include <utime.h>
#define DEBUG


int outgo_post(struct fileheader *fh, char *board, char *title, session_t* session)
{
    FILE *foo;

    if ((foo = fopen("innd/out.bntp", "a")) != NULL) {
        fprintf(foo, "%s\t%s\t%s\t%s\t%s\n", board, fh->filename, session->currentuser->userid, session->currentuser->username, title);
        fclose(foo);
        return 0;
    }
    return -1;
}

extern char alphabet[];

int get_postfilename(char *filename, char *direct, int use_subdir)
{
    static const char post_sufix[] = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    int fp;
    time_t now;
    int i;
    char fname[255];
    int pid = getpid();
    int rn;
    int len;

    /*
     * �Զ����� POST �ļ��� 
     */
    now = time(NULL);
    len = strlen(alphabet);
    for (i = 0; i < 10; i++) {
        if (use_subdir) {
            rn = 0 + (int) (len * 1.0 * rand() / (RAND_MAX + 1.0));
            sprintf(filename, "%c/M.%lu.%c%c", alphabet[rn], now, post_sufix[(pid + i) % 62], post_sufix[(pid * i) % 62]);
        } else
//            sprintf(filename, "M.%lu.%c%c", now, post_sufix[(pid + i) % 62], post_sufix[(pid * i) % 62]);
            sprintf(filename,"M.%lu.%c%c",now,post_sufix[rand()%62],post_sufix[rand()%62]);
        sprintf(fname, "%s/%s", direct, filename);
        if ((fp = open(fname, O_CREAT | O_EXCL | O_WRONLY, 0644)) != -1) {
            break;
        };
    }
    if (fp == -1)
        return -1;
    close(fp);
    return 0;
}

int isowner(struct userec *user, struct fileheader *fileinfo)
{
    time_t posttime;

    if (strcmp(fileinfo->owner, user->userid))
        return 0;
    posttime = get_posttime(fileinfo);
    if (posttime < user->firstlogin)
        return 0;
    return 1;
}

int cmpname(fhdr, name)         /* Haohmaru.99.3.30.�Ƚ� ĳ�ļ����Ƿ�� ��ǰ�ļ� ��ͬ */
struct fileheader *fhdr;
char name[STRLEN];
{
    if (!strncmp(fhdr->filename, name, FILENAME_LEN))
        return 1;
    return 0;
}

/*
  * �жϵ�ǰģʽ�Ƿ����ʹ��id����
  */
bool is_sorted_mode(int mode)
{
    switch (mode) {
    case DIR_MODE_NORMAL:
    case DIR_MODE_THREAD:
    case DIR_MODE_MARK:
    case DIR_MODE_ORIGIN:
    case DIR_MODE_AUTHOR:
    case DIR_MODE_TITLE:
    case DIR_MODE_SUPERFITER:
    case DIR_MODE_WEB_THREAD:
        return true;
    }
    return false;
}

/** ��ʼ��filearg�ṹ
  */
void init_write_dir_arg(struct write_dir_arg *filearg)
{
    filearg->filename = NULL;
    filearg->fileptr = MAP_FAILED;
    filearg->ent = -1;
    filearg->fd = -1;
    filearg->size = -1;
    filearg->needclosefd = false;
    filearg->needlock = true;
}

/** ��ʼ��filearg�ṹ,�Ѹ�������mmap��
  */
int malloc_write_dir_arg(struct write_dir_arg *filearg)
{
    if (filearg->fileptr == MAP_FAILED) {
        if (filearg->fd == -1) {
            if (safe_mmapfile(filearg->filename, O_RDWR, PROT_READ | PROT_WRITE, MAP_SHARED, (void **) &filearg->fileptr, &filearg->size, &filearg->fd) == 0)
                return -1;
            filearg->needclosefd = true;
        } else {                //��fd����
            if (safe_mmapfile_handle(filearg->fd, PROT_READ | PROT_WRITE, MAP_SHARED, (void **) &filearg->fileptr, &filearg->size) == 0)
                return -1;
        }
    }
    return 0;
}

/*
 *  �ͷ�filearg���������Դ��
 */
void free_write_dir_arg(struct write_dir_arg *filearg)
{
    if (filearg->needclosefd && (filearg->fd != -1)) {
        close(filearg->fd);
    }
    if (filearg->fileptr != MAP_FAILED) {
        end_mmapfile((void *) filearg->fileptr, filearg->size, -1);
        filearg->fileptr = MAP_FAILED;
    }
}

/*
 * дdir�ļ�֮ǰ���ļ���λ����ȷ��λ�ã�lockס����������Ӧ������
 * ����߲�������free_write_dir_arg���ͷ���Դ����Ҫ�ϲ��Լ���
 * ���ô˺�������ɹ����ǵü�ʱflock(filearg->fd,LOCK_UN)
 * @param filearg ����Ľṹ�����filearg->fd��Ϊ-1��˵����Ҫ��dirarg->direct�ļ�
 *                        ����ʹ��filearg->fd��Ϊ�ļ������
 *                        filearg->entΪԤ�Ƶ�λ�ã�ent>=1
 *                        filearg->fileptr���������MAP_FAILED,��ô���������mmap�ļ���
 *                        ��дfilearg->fileptr��filearg->size���ڷ�������
 * @param fileinfo ���ڶ�λ�Ķ�������Ϊ�յ�ʱ����Ҫ��λ
 * @param mode ��ǰģʽ��ֻ��sorted�Ŀ���ʹ�ö���
 * @return 0�ɹ�,��ʱmmap��ϡ�
 */
int prepare_write_dir(struct write_dir_arg *filearg, struct fileheader *fileinfo, int mode)
{
    int ret = 0;
    bool needrelocation = false;

    BBS_TRY {
        int count;
        struct fileheader *nowFh;

        if (malloc_write_dir_arg(filearg) != 0)
            BBS_RETURN(-1);
        count = filearg->size / sizeof(struct fileheader);
        if (filearg->needlock)
            flock(filearg->fd, LOCK_EX);
        if (fileinfo) {         //��λһ��
            if ((filearg->ent > count) || (filearg->ent <= 0))
                needrelocation = true;
            else {
                nowFh = filearg->fileptr + (filearg->ent - 1);
                needrelocation = strcmp(fileinfo->filename, nowFh->filename);
            }

        }
        if (needrelocation) {   //�ض�λ���λ��
            int i;

            if (is_sorted_mode(mode)) {
                filearg->ent = Search_Bin(filearg->fileptr, fileinfo->id, 0, count - 1) + 1;
            } else {            //ƥ���ļ���
                int oldent = filearg->ent;

                nowFh = filearg->fileptr;
                filearg->ent = -1;
                /*
                 * �ȴӵ�ǰλ����ǰ�ң���Ϊһ�㶼�Ǳ�ɾ��������ǰ��
                 */
                nowFh = filearg->fileptr + (oldent - 1);
                for (i = oldent - 1; i >= 0; i--, nowFh--) {
                    if (!strcmp(fileinfo->filename, nowFh->filename)) {
                        filearg->ent = i + 1;
                        break;
                    }
                }
                /*
                 * �ٴӵ�ǰλ��������
                 */
                nowFh = filearg->fileptr + oldent;
                for (i = oldent; i < count; i++, nowFh++) {
                    if (!strcmp(fileinfo->filename, nowFh->filename)) {
                        filearg->ent = i + 1;
                        break;
                    }
                }
            }
            if (filearg->ent <= 0)
                ret = -1;
        }
    }
    BBS_CATCH {
        ret = -1;
    }
    BBS_END;
    if (ret != 0)
        flock(filearg->fd, LOCK_UN);
    return ret;
}

int del_origin(char *board, struct fileheader *fileinfo)
{
    struct write_dir_arg dirarg;
    char olddirect[PATHLEN];
    struct fileheader fh;

    if (setboardorigin(board, -1)) {
        board_regenspecial(board, DIR_MODE_ORIGIN, NULL);
        return 0;
    }

    init_write_dir_arg(&dirarg);

    setbdir(DIR_MODE_ORIGIN, olddirect, board);
    dirarg.filename = olddirect;

    if (prepare_write_dir(&dirarg, fileinfo, DIR_MODE_ORIGIN) != 0) {
        free_write_dir_arg(&dirarg);
        return -1;
    }

    BBS_TRY {
        fh = *(dirarg.fileptr + (dirarg.ent - 1));
        memmove(dirarg.fileptr + (dirarg.ent - 1), dirarg.fileptr + dirarg.ent, dirarg.size - sizeof(struct fileheader) * dirarg.ent);
    }
    BBS_CATCH {
    }
    BBS_END;

    dirarg.needclosefd = false;
    free_write_dir_arg(&dirarg);
    dirarg.size -= sizeof(struct fileheader);
    ftruncate(dirarg.fd, dirarg.size);
    if (dirarg.needlock)
        flock(dirarg.fd, LOCK_UN);
    close(dirarg.fd);

    return 0;
}

int deny_modify_article(struct boardheader *bh, struct fileheader *fileinfo, int mode, session_t* session)
{
    if (session->currentuser==NULL) {
        return -1;
    }

    if (deny_me(session->currentuser->userid, bh->filename) && (!HAS_PERM(session->currentuser, PERM_SYSOP))) {
        return -2;
    }

    if (!strcmp(bh->filename, "syssecurity")) {
        return -3;
    }

    if ((mode>= DIR_MODE_THREAD) && (mode<= DIR_MODE_WEB_THREAD)) /*��Դdirect�����޸�*/
        return -4;
    if (checkreadonly(bh->filename))      /* Leeward 98.03.28 */
        return -5;

    if (fileinfo && !HAS_PERM(session->currentuser, PERM_SYSOP)
        && !chk_currBM(bh->BM, session->currentuser)
        && !isowner(session->currentuser, fileinfo)) {
        return -6;
    }
    return 0;
}


int do_del_post(struct userec *user,struct write_dir_arg *dirarg,struct fileheader *fileinfo,
    char *board,int currmode,int flag,session_t* session){
    int owned;
    struct fileheader fh;

#ifdef CYGWIN
    bool old_needclosefd;
#endif

    if (prepare_write_dir(dirarg, fileinfo, currmode) != 0)
        return -1;
    BBS_TRY {
        fh = *(dirarg->fileptr + (dirarg->ent - 1));
        memmove(dirarg->fileptr + (dirarg->ent - 1), dirarg->fileptr + dirarg->ent, dirarg->size - sizeof(struct fileheader) * dirarg->ent);
#ifdef DEBUG
#ifdef BBSMAIN
        newbbslog(BBSLOG_DEBUG, "%s ftruncate %d", dirarg->filename ? dirarg->filename : currboard->filename, dirarg->size);
#endif
#endif
#ifdef CYGWIN
        old_needclosefd = dirarg->needclosefd;
        dirarg->needclosefd = false;
        free_write_dir_arg(dirarg);
        dirarg->size -= sizeof(struct fileheader);
        ftruncate(dirarg->fd, dirarg->size);
        dirarg->needclosefd = old_needclosefd;
        malloc_write_dir_arg(dirarg);
#else
        dirarg->size -= sizeof(struct fileheader);
        ftruncate(dirarg->fd, dirarg->size);
#endif
    }
    BBS_CATCH {
    }
    BBS_END;
    if (dirarg->needlock)
        flock(dirarg->fd, LOCK_UN);     /*�������Ҫ�Ͻ����� */

    if (fh.id == fh.groupid) {
        del_origin(board, fileinfo);
    }
    setboardtitle(board, 1);


    owned=(!(flag&ARG_BMFUNC_FLAG)&&isowner(user,&fh));
    cancelpost(board, user->userid, &fh, owned, 1, session);
    updatelastpost(board);
    if (fh.accessed[0] & FILE_MARKED)
        setboardmark(board, 1);
    if (DIR_MODE_NORMAL == currmode) {    /* Leeward 98.06.17 ����ժ��ɾ�Ĳ���������Ŀ */
        if (owned) {
            if ((int) user->numposts > 0 && !junkboard(board)) {
                user->numposts--;       /*�Լ�ɾ�������£�����post�� */
            }
        } else if ((flag&ARG_DELDECPOST_FLAG) /*����ɾ��,����POST�� */ && !strstr(fh.owner, ".")) {
            struct userec *lookupuser;
            int id = getuser(fh.owner, &lookupuser);

            if (id && (int) lookupuser->numposts > 0 && !junkboard(board) && strcmp(board, SYSMAIL_BOARD)) {    /* SYSOP MAIL��ɾ�Ĳ������� Bigman: 2000.8.12 *//* Leeward 98.06.21 adds above later 2 conditions */
                lookupuser->numposts--;
            }
        }
    }
    if (user != NULL)
        bmlog(user->userid, board, 8, 1);
    newbbslog(BBSLOG_USER, "Del '%s' on '%s'", fh.title, board);        /* bbslog */
    return 0;
}

/* del top article, by pig2532 on 2005.12.22
this function is made from the original "del_ding"
parameters:
    boardname
    ent: top article's number
    fh: fileheader of which top article to be deleted
    session: usually transfer the current session
retuen:
    -2: fileheader is NULL
    -1: "delete_record" function return failed
    0: success
*/
/* add by stiger,delete �ö����� */
int do_del_ding(char *boardname, int bid, int ent, struct fileheader *fh, session_t* session)
{
    int failed;
    char dingdirect[PATHLEN];

    if (fh==NULL)
        return -2;  /* nothing in fileheader */

    setbdir(DIR_MODE_ZHIDING, dingdirect, boardname);
    failed = delete_record(dingdirect, sizeof(struct fileheader), ent, (RECORD_FUNC_ARG) cmpname, fh->filename);

    if(failed){
        return -1;  /* failed to delete */
    }
    else
    {
		char buf[256];
		struct fileheader postfile;

        memcpy(&postfile, fh, sizeof(postfile));
    	snprintf(postfile.title, ARTICLE_TITLE_LEN, "%-32.32s - %s", fh->title, session->currentuser->userid);
    	postfile.accessed[sizeof(postfile.accessed) - 1] = time(0) / (3600 * 24) % 100;

        setbdir(DIR_MODE_DELETED, buf, boardname);
        append_record(buf, &postfile, sizeof(postfile));
        board_update_toptitle(bid, true);

    }
    return 0;   /* success */
}

static int insert_func(int fd, struct fileheader *start, int ent, int total, struct fileheader *data, bool match)
{
    int i;
    struct fileheader UFile;

    if (match||!total)
        return 0;
    UFile = start[total - 1];
    for (i = total - 1; i >= ent; i--)
        start[i] = start[i - 1];
    lseek(fd, 0, SEEK_END);
    if (safewrite(fd, &UFile, sizeof(UFile)) == -1)
        bbslog("user", "%s", "apprec write err!");
    start[ent - 1] = *data;
    return ent;
}

/* do undel an article to board
	modified from original UndeleteArticle by pig2532 on 2005.12.18
	parameters:
		boardname: the board's name to undelete at
		dirfname: index file name, usually boards/(boardname)/.DELETED
		fileheader: the file header of which article to be undeleted
		title: to RETURN the original article title
	return:
		-1: file not exists
		0: unable to open file
		1: success
*/
/* undelete һƪ���� Leeward 98.05.18 */
/* modified by ylsdd */
int do_undel_post(char* boardname, char *dirfname, int num, struct fileheader *fileinfo, char *title, session_t* session)
{
    char *p, buf[1024], genbuf[1024];
    char UTitle[128];
    struct fileheader UFile;
    int i;
    FILE *fp;
    int fd;

    sprintf(buf, "boards/%s/%s", boardname, fileinfo->filename);
    if (!dashf(buf)) {
        return -1;
    }
    fp = fopen(buf, "r");
    if (!fp)
        return 0;

    strcpy(UTitle, fileinfo->title);
    if ((p = strrchr(UTitle, '-')) != NULL) {   /* create default article title */
        *p = 0;
        for (i = strlen(UTitle) - 1; i >= 0; i--) {
            if (UTitle[i] != ' ')
                break;
            else
                UTitle[i] = 0;
        }
    }

    i = 0;
    while (!feof(fp) && i < 2) {
        skip_attach_fgets(buf, 1024, fp);
        if (feof(fp))
            break;
        if (strstr(buf, "������: ") && strstr(buf, "), ����: ")) {
            i++;
        } else if (strstr(buf, "��  ��: ")) {
            i++;
            strncpy(UTitle, buf + 8, sizeof(UTitle));
            UTitle[sizeof(UTitle)-1] = '\0';
            if ((p = strchr(UTitle, '\n')) != NULL)
                *p = 0;
        }
    }
    fclose(fp);

    memcpy(&UFile, fileinfo, sizeof(UFile));
    strnzhcpy(UFile.title, UTitle, ARTICLE_TITLE_LEN);
    UFile.accessed[1] &= ~FILE_DEL;
    if (UFile.filename[1] == '/')
        UFile.filename[2] = 'M';
    else
        UFile.filename[0] = 'M';

    setbfile(genbuf, boardname, fileinfo->filename);
    setbfile(buf, boardname, UFile.filename);
    if (dashf(buf)) {
        return -1;
    }
    f_mv(genbuf, buf);

    sprintf(buf, "boards/%s/.DIR", boardname);
    if ((fd = open(buf, O_RDWR | O_CREAT, 0644)) != -1) {
        if ((UFile.id == 0) || mmap_search_apply(fd, &UFile, insert_func) == 0) {
            flock(fd, LOCK_EX);
            UFile.id = get_nextid(boardname);
            UFile.groupid = UFile.id;
            UFile.reid = UFile.id;
            lseek(fd, 0, SEEK_END);
            if (safewrite(fd, &UFile, sizeof(UFile)) == -1)
                bbslog("user", "%s", "apprec write err!");
            flock(fd, LOCK_UN);
        }
        close(fd);
    }

    updatelastpost(boardname);
    fileinfo->filename[0] = '\0';
    substitute_record(dirfname, fileinfo, sizeof(*fileinfo), num);
    sprintf(buf, "undeleted %s's ��%s�� on %s", UFile.owner, UFile.title, boardname);
    bbslog("user", "%s", buf);

	if(title != NULL)
	{
		sprintf(title, "%s", UFile.title);
	}

    bmlog(session->currentuser->userid, boardname, 9, 1);

    return 1;
}

/* by ylsdd 
   unlink action is taked within cancelpost if in mail mode,
   otherwise this item is added to the file '.DELETED' under
   the board's directory, the filename is not changed. 
   Unlike the fb code which moves the file to the deleted
   board.
*/
void cancelpost(const char *board, const char *userid, struct fileheader *fh, int owned, int autoappend, session_t* session)
{
    char oldpath[PATHLEN];
    char newpath[PATHLEN];
    time_t now = time(NULL);

#ifdef BBSMAIN
    if (uinfo.mode == RMAIL) {
        sprintf(oldpath, "mail/%c/%s/%s", toupper(session->currentuser->userid[0]), session->currentuser->userid, fh->filename);
        my_unlink(oldpath);
        return;
    }
#endif

/*
    sprintf(oldpath, "/board/%s/%s.html", board, fh->filename);
    ca_expire_file(oldpath);*/

    if ((fh->innflag[1] == 'S')
        && (fh->innflag[0] == 'S')
        && (get_posttime(fh) > now - 14 * 86400)) {
        FILE *fp;
        char buf[256];
        char from[STRLEN];
        int len;
        char *ptr;

        setbfile(buf, board, fh->filename);
        if ((fp = fopen(buf, "rb")) == NULL)
            return;
        while (skip_attach_fgets(buf, sizeof(buf), fp) != NULL) {
            /*
             * �����˵����з� 
             */
            len = strlen(buf) - 1;
            buf[len] = '\0';
            if (len < 8)
                break;
            if (strncmp("������: ", buf, 8) == 0) {
                if ((ptr = strrchr(buf, ')')) == NULL)
                    break;
                *ptr = '\0';
                if ((ptr = strchr(buf, '(')) == NULL)
                    break;
                strncpy(from, ptr + 1, sizeof(from) - 1);
                from[sizeof(from) - 1] = '\0';
                break;
            }
        }
        fclose(fp);
        sprintf(buf, "%s\t%s\t%s\t%s\t%s\n", board, fh->filename, fh->owner, from, fh->title);
        if ((fp = fopen("innd/cancel.bntp", "a")) != NULL) {
            fputs(buf, fp);
            fclose(fp);
        }
    }

    setbfile(oldpath, board, fh->filename);
    if (fh->filename[1] == '/')
        fh->filename[2] = (owned) ? 'J' : 'D';
    else
        fh->filename[0] = (owned) ? 'J' : 'D';
    setbfile(newpath, board, fh->filename);
    f_mv(oldpath, newpath);

    sprintf(oldpath, "%-32.32s - %s", fh->title, userid);
    strncpy(fh->title, oldpath, ARTICLE_TITLE_LEN - 1);
    fh->title[ARTICLE_TITLE_LEN - 1] = 0;
    fh->accessed[sizeof(fh->accessed) - 1] = now / (3600 * 24) % 100;
    if (autoappend) {
        setbdir((owned) ? DIR_MODE_JUNK : DIR_MODE_DELETED, oldpath, board);
        append_record(oldpath, fh, sizeof(struct fileheader));
    }
}


void add_loginfo(char *filepath, struct userec *user, char *currboard, int Anony, session_t* session)
{                               /* POST ���һ�� ��� */
    FILE *fp;
    int color, noidboard;
    char fname[STRLEN];

    noidboard = (anonymousboard(currboard) && Anony);   /* etc/anonymous�ļ��� ����������� */
    color = (user->numlogins % 7) + 31; /* ��ɫ����仯 */
    sethomefile(fname, user->userid, "signatures");
    fp = fopen(filepath, "ab");
    if (!dashf(fname) ||        /* �ж��Ƿ��Ѿ� ���� ǩ���� */
        user->signature == 0 || noidboard) {
        fputs("\n--\n", fp);
    } else {                    /*Bigman 2000.8.10�޸�,���ٴ��� */
        fprintf(fp, "\n");
    }
    /*
     * ��Bigman����:2000.8.10 Announce�������������� 
     */
    if (!strcmp(currboard, "Announce"))
        fprintf(fp, "\033[m\033[1;%2dm�� ��Դ:��%s %s��[FROM: %s]\033[m\n", color, BBS_FULL_NAME, NAME_BBS_ENGLISH, BBS_FULL_NAME);
    else
        fprintf(fp, "\n\033[m\033[1;%2dm�� ��Դ:��%s %s��[FROM: %s]\033[m\n", color, BBS_FULL_NAME, NAME_BBS_ENGLISH, (noidboard) ? NAME_ANONYMOUS_FROM : SHOW_USERIP(session->currentuser, session->fromhost));
    fclose(fp);
    return;
}

void addsignature(FILE * fp, struct userec *user, int sig)
{
    FILE *sigfile;
    int i, valid_ln = 0;
    char tmpsig[MAXSIGLINES][256];
    char inbuf[256];
    char fname[STRLEN];

    if (sig == 0)
        return;
    sethomefile(fname, user->userid, "signatures");
    if ((sigfile = fopen(fname, "r")) == NULL) {
        return;
    }
    fputs("\n--\n", fp);
    for (i = 1; i <= (sig - 1) * MAXSIGLINES && sig != 1; i++) {
        if (!fgets(inbuf, sizeof(inbuf), sigfile)) {
            fclose(sigfile);
            return;
        }
    }
    for (i = 1; i <= MAXSIGLINES; i++) {
        if (fgets(inbuf, sizeof(inbuf), sigfile)) {
            if (inbuf[0] != '\n')
                valid_ln = i;
            strcpy(tmpsig[i - 1], inbuf);
        } else
            break;
    }
    fclose(sigfile);
    for (i = 1; i <= valid_ln; i++)
        fputs(tmpsig[i - 1], fp);
}

int write_posts(char *id, char *board, unsigned int groupid)
{
    time_t now;
    struct posttop postlog, pl;
    char xpostfile[PATHLEN];

#ifdef BLESS_BOARD
    if (strcasecmp(board, BLESS_BOARD) && (!poststatboard(board) || normal_board(board) != 1))
#else
    if (!poststatboard(board) || normal_board(board) != 1)
#endif
        return 0;
    now = time(0);
//    strcpy(postlog.author, id);
    strcpy(postlog.board, board);
    postlog.groupid = groupid;
    postlog.date = now;
    postlog.number = 1;

    sprintf(xpostfile, "tmp/Xpost/%s", id);

    {                           /* added by Leeward 98.04.25 
                                 * TODO: ����ط��е㲻��,ÿ�η���Ҫ����һ��,���浽.Xpost��,
                                 * �������ʮ����ͳ�����ID����������.����
                                 * KCN */
        int log = 1;
        FILE *fp = fopen(xpostfile, "r");

        if (fp) {
            while (!feof(fp)) {
                fread(&pl, sizeof(pl), 1, fp);
                if (feof(fp))
                    break;

                if (pl.groupid == groupid && !strcmp(pl.board, board)) {
                    log = 0;
                    break;
                }
            }
            fclose(fp);
        }

        if (log) {
            append_record(xpostfile, &postlog, sizeof(postlog));
            append_record(".newpost", &postlog, sizeof(postlog));
        }
    }

/*    append_record(".post.X", &postlog, sizeof(postlog));
*/
    return 0;
}

void write_header(FILE * fp, struct userec *user, int in_mail, char *board, char *title, int Anony, int mode, session_t* session)
{
    int noname;
    char uid[20];
    char uname[40];
    time_t now;

    now = time(0);
    strncpy(uid, user->userid, 20);
    uid[19] = '\0';
    if (in_mail)
#if defined(MAIL_REALNAMES)
        strncpy(uname, user->realname, NAMELEN);
#else
        strncpy(uname, user->username, NAMELEN);
#endif
    else
#if defined(POSTS_REALNAMES)
        strncpy(uname, user->realname, NAMELEN);
#else
        strncpy(uname, user->username, NAMELEN);
#endif
    /*
     * uid[39] = '\0' ; SO FUNNY:-) ����� 20 ����ȴ�� 39 !
     * Leeward: 1997.12.11 
     */
    uname[39] = 0;              /* ��ʵ��д���������! �ٺ� */
    if (in_mail)
        fprintf(fp, "������: %s (%s)\n", uid, uname);
    else {
        noname = anonymousboard(board);
        /*
         * if (((mode == 0) || (mode == 2)) && !(noname && Anony)) {
         * *
         * * mode=0�������ķ��Ĳ���local save 
         * * * mode=1�ǲ���Ҫ��¼��
         * * * mode=2�Ƿ�local save��
         * *
         * write_posts(user->userid, board, title);
         * }
         */
#ifdef SMTH
        if (!strcmp(board, "Announce") && Anony)
            /*
             * added By Bigman 
             */
            fprintf(fp, "������: %s (%s), ����: %s\n", "SYSOP", NAME_SYSOP, board);
        else
#endif
            fprintf(fp, "������: %s (%s), ����: %s\n", (noname && Anony) ? board : uid, (noname && Anony) ? NAME_ANONYMOUS : uname, board);
    }

    fprintf(fp, "��  ��: %s\n", title);
    /*
     * ����ת�ű�� czz 020819 
     */
    if (in_mail)
        fprintf(fp, "����վ: %s (%24.24s)\n", BBS_FULL_NAME, ctime(&now));
    else if (mode != 2)
        fprintf(fp, "����վ: %s (%24.24s), վ��\n", BBS_FULL_NAME, ctime(&now));
    else
        fprintf(fp, "����վ: %s (%24.24s), ת��\n", BBS_FULL_NAME, ctime(&now));
    if (in_mail)
        fprintf(fp, "��  Դ: %s \n", session->fromhost);
    fprintf(fp, "\n");

}

void getcross(char *filepath, char *quote_file, struct userec *user, int in_mail, char *board, char *title, int Anony, int mode, int local_article, char *sourceboard, session_t* session)
{                               /* ��quote_file���Ƶ�filepath (ת�����Զ�����) */
    FILE *inf, *of;
    char buf[256];
    char owner[256];
    int count;
    time_t now;
    int asize;

    now = time(0);
    inf = fopen(quote_file, "rb");
    of = fopen(filepath, "w");
    if (inf == NULL || of == NULL) {
        /*---	---*/
        if (NULL != inf)
            fclose(inf);
        if (NULL != of)
            fclose(of);
        /*---	---*/
#ifdef BBSMAIN
        bbslog("user", "%s", "Cross Post error");
#endif
        return;
    }
    if (mode == 0 /*ת�� */ ) {
        int normal_file;
        int header_count;

        normal_file = 1;

        write_header(of, user, in_mail, sourceboard, title, Anony, (local_article ? 1 : 2 ), session /*��д�� .posts */ );
        if (skip_attach_fgets(buf, 256, inf) != NULL) {
            for (count = 8; buf[count] != ' ' && count < 256; count++)
                owner[count - 8] = buf[count];
            owner[count - 8] = '\0';
        } else
            strcpy(owner, "");
        if (in_mail == true)
            fprintf(of, "\033[1;37m�� ��������ת���� \033[32m%s \033[37m������ ��\033[m\n", user->userid);
        else
            fprintf(of, "�� ��������ת���� %s ������ ��\n", board);
        if (id_invalid(owner))
            normal_file = 0;
        if (normal_file) {
            for (header_count = 0; header_count < 3; header_count++) {
                if (skip_attach_fgets(buf, 256, inf) == NULL)
                    break;      /*Clear Post header */
            }
            if ((header_count != 2) || (buf[0] != '\n'))
                normal_file = 0;
        }
        if (normal_file)
            fprintf(of, "�� ԭ���� %s ������ ��\n", owner);
        else
            fseek(inf, 0, SEEK_SET);

    } else if (mode == 1 /*�Զ����� */ ) {
        fprintf(of, "������: "DELIVER" (�Զ�����ϵͳ), ����: %s\n", board);
        fprintf(of, "��  ��: %s\n", title);
        fprintf(of, "����վ: %s�Զ�����ϵͳ (%24.24s)\n\n", BBS_FULL_NAME, ctime(&now));
        fprintf(of, "����ƪ���������Զ�����ϵͳ��������\n\n");
    } else if (mode == 2) {
        write_header(of, user, in_mail, sourceboard, title, Anony, 0 /*д�� .posts */ ,session);
    }
    while ((asize = -attach_fgets(buf, 256, inf)) != 0) {
        if ((strstr(buf, "�� ��������ת���� ") && strstr(buf, "������ ��")) || (strstr(buf, "�� ԭ����") && strstr(buf, "������ ��")))
            continue;           /* ���������ظ� */
        if(asize<0)
            fprintf(of, "%s", buf);
        put_attach(inf, of, asize);
    }
    fclose(inf);
    fclose(of);
    /*
     * don't know why 
     * *quote_file = '\0';
     */
}

#ifdef COMMEND_ARTICLE
int post_commend(struct userec *user, char *fromboard, struct fileheader *fileinfo)
{                               /* �Ƽ� */
    struct fileheader postfile;
    char filepath[STRLEN];
    char oldfilepath[STRLEN];
    char buf[256];
    int aborted;
    int fd, err = 0, nowid = 0;

    memset(&postfile, 0, sizeof(postfile));

    setbfile(filepath, COMMEND_ARTICLE, "");

    if ((aborted = GET_POSTFILENAME(postfile.filename, filepath)) != 0) {
#ifdef BBSMAIN
        move(3, 0);
        clrtobot();
        prints("\n\n�޷������ļ�:%d...\n", aborted);
        pressreturn();
        clear();
#endif
        return -1;
    }

    setbfile(filepath, COMMEND_ARTICLE, postfile.filename);
    setbfile(oldfilepath, fromboard, fileinfo->filename);

    if (f_cp(oldfilepath, filepath, 0) < 0)
        return -1;

    strcpy(postfile.title, fileinfo->title);
    strncpy(postfile.owner, user->userid, OWNER_LEN);
    postfile.owner[OWNER_LEN - 1] = 1;
    postfile.eff_size = get_effsize(oldfilepath);
    postfile.o_id = fileinfo->id;
    postfile.o_groupid = fileinfo->groupid;
    postfile.o_reid = fileinfo->reid;
    postfile.o_bid = getboardnum(fromboard, NULL);
    //strncpy(postfile.o_board, fromboard, STRLEN- BM_LEN);
    //postfile.o_board[STRLEN-BM_LEN-1]=0;

    setbfile(buf, COMMEND_ARTICLE, DOT_DIR);

    if ((fd = open(buf, O_WRONLY | O_CREAT, 0664)) == -1) {
        err = 1;
    }

    if (!err) {
        flock(fd, LOCK_EX);
        nowid = get_nextid(COMMEND_ARTICLE);
        postfile.id = nowid;
        postfile.groupid = postfile.id;
        postfile.reid = postfile.id;
        set_posttime(&postfile);
        lseek(fd, 0, SEEK_END);
        if (safewrite(fd, &postfile, sizeof(fileheader)) == -1) {
            bbslog("user", "%s", "apprec write err!");
            err = 1;
        }
        flock(fd, LOCK_UN);
        close(fd);
    }
    if (err) {
        bbslog("3error", "Posting '%s' on '%s': append_record failed!", postfile.title, COMMEND_ARTICLE);
        my_unlink(filepath);
#ifdef BBSMAIN
        pressreturn();
        clear();
#endif
        return -1;
    }
    updatelastpost(COMMEND_ARTICLE);

    setboardorigin(COMMEND_ARTICLE, 1);
    setboardtitle(COMMEND_ARTICLE, 1);

    return 0;
}
#endif

/* Add by SmallPig */
int post_cross(struct userec *user, char *toboard, char *fromboard, char *title, char *filename, int Anony, int in_mail, char islocal, int mode, session_t* session)
{                               /* (�Զ������ļ���) ת�����Զ����� */
    struct fileheader postfile;
    char filepath[STRLEN];
    char buf4[STRLEN], whopost[IDLEN], save_title[STRLEN];
    int aborted, local_article;

#ifdef BBSMAIN
    int oldmode;
#endif

    if (!mode && !haspostperm(user, toboard)) {
#ifdef BBSMAIN
        move(1, 0);
        prints("������Ȩ���� %s ��������.\n", toboard);
        prints("�������δע�ᣬ���ڸ��˹���������ϸע�����\n");
        prints("δͨ�����ע����֤���û���û�з������µ�Ȩ�ޡ�\n");
        prints("лл������ :-) \n");
#endif
        return -1;
    }

    memset(&postfile, 0, sizeof(postfile));

    if (!mode) {
        if (!strstr(title, "(ת��)"))
            sprintf(buf4, "%s (ת��)", title);
        else
            strcpy(buf4, title);
    } else
        strcpy(buf4, title);
    strncpy(save_title, buf4, STRLEN);

    setbfile(filepath, toboard, "");

    if ((aborted = GET_POSTFILENAME(postfile.filename, filepath)) != 0) {
#ifdef BBSMAIN
        move(3, 0);
        clrtobot();
        prints("\n\n�޷������ļ�:%d...\n", aborted);
        pressreturn();
        clear();
#endif
        return -2;
    }

    if (mode == 1)
        strcpy(whopost, DELIVER);     /* mode==1Ϊ�Զ����� */
    else
        strcpy(whopost, user->userid);

    strncpy(postfile.owner, whopost, OWNER_LEN);
    postfile.owner[OWNER_LEN - 1] = '\0';
    setbfile(filepath, toboard, postfile.filename);

    local_article = 1;          /* default is local article */
    if (islocal != 'l' && islocal != 'L') {
        if (is_outgo_board(toboard))
            local_article = 0;
    }
#ifdef BBSMAIN
    oldmode = uinfo.mode;
    modify_user_mode(POSTING);
#endif
    getcross(filepath, filename, user, in_mail, fromboard, title, Anony, mode, local_article, toboard, session); /*����fname��� �ļ����� */

    postfile.eff_size = get_effsize_attach(filepath, &postfile.attachment);     /* FreeWizard: get effsize & attachment */

    strnzhcpy(postfile.title, save_title, ARTICLE_TITLE_LEN);

    if (local_article == 1) {   /* local save */
        postfile.innflag[1] = 'L';
        postfile.innflag[0] = 'L';
    } else {
        postfile.innflag[1] = 'S';
        postfile.innflag[0] = 'S';
        outgo_post(&postfile, toboard, save_title, session);
    }
    if (!strcmp(toboard, "syssecurity")
        && strstr(title, "�޸� ")
        && strstr(title, " ��Ȩ��"))
        postfile.accessed[0] |= FILE_MARKED;    /* Leeward 98.03.29 */
    if (strstr(title, "����Ȩ��") && mode == 2) {
#ifndef NINE_BUILD
        /*
         * disable here to avoid Mark deny articles on the board Bigman.2002.11.17
         */
        /*
         * postfile.accessed[0] |= FILE_MARKED;
         *//*
         * Haohmaru 99.11.10 
         */
        postfile.accessed[1] |= FILE_READ;
#endif
    }
    after_post(user, &postfile, toboard, NULL, !(Anony), session);
#ifdef BBSMAIN
    modify_user_mode(oldmode);
#endif
    return 1;
}


int post_file(struct userec *user, char *fromboard, char *filename, char *nboard, char *posttitle, int Anony, int mode, session_t* session)
/* ��ĳ�ļ� POST ��ĳ�� */
{
    if (getboardnum(nboard, NULL) <= 0) {       /* ����ҪPOST�İ� ,�ж��Ƿ���ڸð� */
        return -1;
    }
    post_cross(user, nboard, fromboard, posttitle, filename, Anony, false, 'l', mode, session);  /* post �ļ� */
    return 0;
}


/*
 * ע�⣺fh->attachment = 0           ���û�и��� (���ʱ�������ļ���Ҫ����)
 *                      = ���ĳ���    ����и���   (���ʱ������Ĳ��ֹ���)
 * ����һ����֤ attachment ����ֶε���ȷ��������ܻ�©���ˣ�����
 * atppp 20051127
 */
int after_post(struct userec *user, struct fileheader *fh, char *boardname, struct fileheader *re, int poststat, session_t* session)
{
    char buf[256];
    int fd, err = 0, nowid = 0;
    char *p;

#ifdef FILTER
    char oldpath[50], newpath[50];
    int filtered;
#endif
    struct boardheader *bh = NULL;
    int bid;

    if ((re == NULL) && (!strncmp(fh->title, "Re: ", 4))) {
        strncpy(fh->title, fh->title + 4, ARTICLE_TITLE_LEN);
    }
    bid = getbid(boardname, &bh);
#ifdef FILTER
    setbfile(oldpath, boardname, fh->filename);
    filtered = 0;
    if (strcmp(fh->owner, DELIVER)) {
        if (((bh && bh->level & PERM_POSTMASK) || normal_board(boardname)) && strcmp(boardname, FILTER_BOARD)
#if 0
            && strcmp(boardname, "NewsClub")
#endif
            ) {
#ifdef SMTH
#if 0
            int isnews;

            isnews = !strcmp(boardname, "News");
            if (isnews || check_badword_str(fh->title, strlen(fh->title)) || check_badword(oldpath))
#else
            if (check_badword_str(fh->title, strlen(fh->title), session) || check_badword(oldpath, fh->attachment, session))
#endif
#else
            if (check_badword_str(fh->title, strlen(fh->title), session) || check_badword(oldpath, fh->attachment, session))
#endif
            {
                /*
                 * FIXME: There is a potential bug here. 
                 */
#ifdef SMTH
#if  0
                if (isnews)
                    setbfile(newpath, "NewsClub", fh->filename);
                else
#endif
#endif
                setbfile(newpath, FILTER_BOARD, fh->filename);
                f_mv(oldpath, newpath);
                fh->o_bid = bid;
                //strncpy(fh->o_board, boardname, STRLEN - BM_LEN);
                nowid = get_nextid_bid(bid);
                fh->o_id = nowid;
                if (re == NULL) {
                    fh->o_groupid = fh->o_id;
                    fh->o_reid = fh->o_id;
                } else {
                    fh->o_groupid = re->groupid;
                    fh->o_reid = re->id;
                }
#ifdef ZIXIA
                {
                    char newtitle[STRLEN];
                    snprintf(newtitle, ARTICLE_TITLE_LEN, "[��Ⱥ����]%s", fh->title);
                    mail_file(session->currentuser->userid, newpath, session->currentuser->userid, newtitle, 0, fh);
                }
#endif

#ifdef SMTH
#if  0
                if (isnews)
                    boardname = "NewsClub";
                else
#endif
#endif
                    boardname = FILTER_BOARD;
                filtered = 1;
            };
        }
    }
#endif
    setbfile(buf, boardname, DOT_DIR);

    if ((fd = open(buf, O_WRONLY | O_CREAT, 0664)) == -1) {
#ifdef BBSMAIN
        perror(buf);
#endif
        err = 1;
    }
    /*
     * ���˲�ɫ����
     */
    for (p = fh->title; *p; p++)
        if (*p == '\x1b')
            *p = ' ';

    if (!err) {
        flock(fd, LOCK_EX);
        nowid = get_nextid(boardname);
        fh->id = nowid;
        if (re == NULL) {
            fh->groupid = fh->id;
            fh->reid = fh->id;
        } else {
            fh->groupid = re->groupid;
            fh->reid = re->id;
        }
        set_posttime(fh);
        lseek(fd, 0, SEEK_END);
        if (safewrite(fd, fh, sizeof(fileheader)) == -1) {
            bbslog("user", "%s", "apprec write err!");
            err = 1;
        }
        flock(fd, LOCK_UN);
        close(fd);
    }
    if (err) {
        bbslog("3error", "Posting '%s' on '%s': append_record failed!", fh->title, boardname);
        setbfile(buf, boardname, fh->filename);
        my_unlink(buf);
#ifdef BBSMAIN
        pressreturn();
        clear();
#endif
        return 1;
    }
    updatelastpost(boardname);
#ifdef FILTER
    if (filtered)
        sprintf(buf, "posted '%s' on '%s' filtered", fh->title, getboard(fh->o_bid)->filename);
    else {
#endif
#ifdef HAVE_BRC_CONTROL
        brc_add_read(fh->id, bid, session);
#endif

        /*
         * ���ļĵ�ԭ��������, stiger 
         */
        if (re) {
            if (re->accessed[1] & FILE_MAILBACK) {

                struct userec *lookupuser;
                char newtitle[STRLEN];

                if (getuser(re->owner, &lookupuser) != 0) {
                    if ((false != canIsend2(session->currentuser, re->owner)) && (check_mail_perm(NULL, lookupuser) == 0)) {
                        setbfile(buf, boardname, fh->filename);
                        snprintf(newtitle, ARTICLE_TITLE_LEN, "[����ת��]%s", fh->title);
                        mail_file(session->currentuser->userid, buf, re->owner, newtitle, 0, fh);
                    }
                }
            }
        }


        sprintf(buf, "posted '%s' on '%s'", fh->title, boardname);

#ifndef NEWPOSTSTAT
        if (poststat && user)
            write_posts(user->userid, boardname, fh->groupid);
#endif

#ifdef DENYANONY
		if (user && !poststat){
			char anonybuf[256];
			struct fileheader tmpf;

			setbfile(anonybuf, boardname, ".ANONYDIR");
			memcpy(&tmpf, fh, sizeof(tmpf));

			strcpy(tmpf.owner, session->currentuser->userid);

		    if ((fd = open(anonybuf, O_WRONLY | O_CREAT, 0664)) != -1) {
		        flock(fd, LOCK_EX);
		        lseek(fd, 0, SEEK_END);
		        safewrite(fd, &tmpf, sizeof(fileheader));
		        flock(fd, LOCK_UN);
		        close(fd);
		    }
		}
#endif

#ifdef FILTER
    }
#endif
#ifdef NEWPOSTLOG
	if(user)
		newpostlog(user->userid, boardname, fh->title, fh->groupid);
#else
    newbbslog(BBSLOG_USER, "%s", buf);
#endif

    if (fh->id == fh->groupid) {
        if (setboardorigin(boardname, -1)) {
            board_regenspecial(boardname, DIR_MODE_ORIGIN, NULL);
        } else {
            setbdir(DIR_MODE_ORIGIN, buf, boardname);
            if ((fd = open(buf, O_WRONLY | O_CREAT, 0664)) >= 0) {
                flock(fd, LOCK_EX);
                lseek(fd, 0, SEEK_END);
                if (safewrite(fd, fh, sizeof(fileheader)) == -1) {
                    bbslog("user", "%s", "apprec origin write err!");
                }
                flock(fd, LOCK_UN);
                close(fd);
            }
        }
    }

    setboardtitle(boardname, 1);
    if (fh->accessed[0] & FILE_MARKED)
        setboardmark(boardname, 1);
    if (user != NULL)
        bmlog(user->userid, boardname, 2, 1);
#ifdef FILTER
    if (filtered)
        return 2;
    else
#endif
        return 0;
}

int mmap_search_apply(int fd, struct fileheader *buf, DIR_APPLY_FUNC func)
{
    struct fileheader *data;
    off_t filesize;
    int total;
    int low, high;
    int ret = 0;

    if (flock(fd, LOCK_EX) == -1)
        return 0;
    BBS_TRY {
        if (safe_mmapfile_handle(fd, PROT_READ | PROT_WRITE, MAP_SHARED, (void **) &data, &filesize) == 0) {
            flock(fd, LOCK_UN);
            BBS_RETURN(0);
        }
        total = filesize / sizeof(struct fileheader);
        low = 0;
        high = total - 1;
        while (low <= high) {
            int mid, comp;

            mid = (high + low) / 2;
            comp = (buf->id) - ((data + mid)->id);
            if (comp == 0) {
                ret = (*func) (fd, data, mid + 1, total, buf, true);
                end_mmapfile((void *) data, filesize, -1);
                flock(fd, LOCK_UN);
                BBS_RETURN(ret);
            } else if (comp < 0)
                high = mid - 1;
            else
                low = mid + 1;
        }
        ret = (*func) (fd, data, low + 1, total, buf, false);
    }
    BBS_CATCH {
    }
    BBS_END end_mmapfile((void *) data, filesize, -1);

    flock(fd, LOCK_UN);
    return ret;
}

int mmap_dir_search(int fd, const fileheader_t * key, search_handler_t func, void *arg)
{
    struct fileheader *data;
    off_t filesize;
    int total;
    int low, high;
    int mid, comp;
    int ret = 0;

    if (flock(fd, LOCK_EX) == -1)
        return 0;
    BBS_TRY {
        if (safe_mmapfile_handle(fd, PROT_READ | PROT_WRITE, MAP_SHARED, (void **) &data, &filesize) == 0) {
            flock(fd, LOCK_UN);
            BBS_RETURN(0);
        }
        total = filesize / sizeof(fileheader_t);
        low = 0;
        high = total - 1;
        while (low <= high) {
            mid = (high + low) / 2;
            comp = (key->id) - ((data + mid)->id);
            if (comp == 0) {
                ret = (*func) (fd, data, mid + 1, total, true, arg);
                end_mmapfile((void *) data, filesize, -1);
                flock(fd, LOCK_UN);
                BBS_RETURN(ret);
            } else if (comp < 0)
                high = mid - 1;
            else
                low = mid + 1;
        }
        ret = (*func) (fd, data, low + 1, total, false, arg);
    }
    BBS_CATCH {
    }
    BBS_END end_mmapfile((void *) data, filesize, -1);

    flock(fd, LOCK_UN);

    return ret;
}

struct dir_record_set {
    fileheader_t *records;
    int num;
    int rec_no;                 /* ��¼�����м��¼�������ļ��еļ�¼�ţ��� 1 �ģ�
                                 * ������¼�ļ�¼�ſ���ͨ�� num �� rec_no ��� */
};

static int get_dir_records(int fd, fileheader_t * base, int ent, int total, bool match, void *arg)
{
    if (match) {
        struct dir_record_set *rs = (struct dir_record_set *) arg;
        int i;
        int off;
        int count = 0;

        off = ent - rs->num / 2;
        rs->rec_no = ent;       /* �����ﱣ���¼�� */
        for (i = 0; i < rs->num; i++) {
            if (off < 1 || off > total)
                bzero(rs->records + i, sizeof(fileheader_t));
            else {
                memcpy(rs->records + i, base + off - 1, sizeof(fileheader_t));
                count++;
            }
            off++;
        }
        return count;
    }

    return 0;
}

int get_records_from_id(int fd, int id, fileheader_t * buf, int num, int *index)
{
    struct dir_record_set rs;
    fileheader_t key;
    int ret;

    rs.records = buf;
    rs.num = num;
    rs.rec_no = 0;
    bzero(&key, sizeof(key));
    key.id = id;
    ret = mmap_dir_search(fd, &key, get_dir_records, &rs);
    if (index != NULL)
        *index = rs.rec_no;

    return ret;
}

struct dir_thread_set {
    fileheader_t *records;
    int num;
};

static int get_dir_threads(int fd, fileheader_t * base, int ent, int total, bool match, void *arg)
{
    if (match) {
        struct dir_thread_set *ts = (struct dir_thread_set *) arg;
        int i;
        int off = 1;
        int count = 0;
        int start = ent + 1;
        int end = total;

        if (ts->num < 0) {
            off = -1;
            start = ent - 1;
            end = 1;
            ts->num = -ts->num;
            for (i = start; i >= end; i--) {
                if (count == ts->num)
                    break;
                if (base[i - 1].groupid == base[ent - 1].groupid) {
                    memcpy(ts->records + count, base + i - 1, sizeof(fileheader_t));
                    count++;
                }
            }
        } else {
            for (i = start; i <= end; i++) {
                if (count == ts->num)
                    break;
                if (base[i - 1].groupid == base[ent - 1].groupid) {
                    memcpy(ts->records + count, base + i - 1, sizeof(fileheader_t));
                    count++;
                }
            }
        }
        return count;
    }

    return 0;
}

/* ���� num ��ʾȡ id ��ͬ����� num ƪ���£�
   ���� num ��ʾȡ id ��ͬ����ǰ |num| ƪ���� */
int get_threads_from_id(const char *filename, int id, fileheader_t * buf, int num)
{
    struct dir_thread_set ts;
    fileheader_t key;
    int fd;
    int ret;

    if (num == 0)
        return 0;
    ts.records = buf;
    ts.num = num;
    bzero(&key, sizeof(key));
    key.id = id;
    if ((fd = open(filename, O_RDWR, 0644)) < 0)
        return -1;
    ret = mmap_dir_search(fd, &key, get_dir_threads, &ts);
    close(fd);

    return ret;
}

struct dir_gthread_set {
    fileheader_t *records;
    int num;
    int groupid;
    int start;
    int haveprev;
};

static int get_dir_gthreads(int fd, fileheader_t * base, int ent, int total, bool match, void *arg)
{
    struct dir_gthread_set *ts = (struct dir_gthread_set *) arg;
    int i;
    int count = 0;
    int start = ent;
    int end = total;
    int passprev = 0;

    for (i = start; i <= end; i++) {
        if (count == ts->num)
            break;
        if (base[i - 1].groupid == ts->groupid) {
            if (base[i - 1].id < ts->start) {
                passprev = i;
                continue;
            }
            memcpy(ts->records + count, base + i - 1, sizeof(fileheader_t));
            count++;
        }
    }

    if (passprev) {
        int i = 1;

        for (; passprev >= start; passprev--) {
            if (i >= ts->num)
                break;
            if (base[passprev - 1].groupid == ts->groupid) {
                ts->haveprev = base[passprev - 1].id;
                i++;
            }
        }
    }
    return count;
}

int get_threads_from_gid(const char *filename, int gid, fileheader_t * buf, int num, int startid, int *haveprev)
{
    struct dir_gthread_set ts;
    fileheader_t key;
    int fd;
    int ret;

    if (num == 0)
        return 0;
    ts.records = buf;
    ts.num = num;
    ts.groupid = gid;
    ts.start = startid;
    ts.haveprev = 0;
    bzero(&key, sizeof(key));
    key.id = gid;
    if ((fd = open(filename, O_RDWR, 0644)) < 0)
        return -1;
    ret = mmap_dir_search(fd, &key, get_dir_gthreads, &ts);
    close(fd);

    *haveprev = ts.haveprev;
    return ret;
}

//�������ַ���    by yuhuan
//��flyriverͬѧ����������������
int Search_Bin(struct fileheader *ptr, int key, int start, int end)
{
    // ����������۰������ؼ��ֵ���key������Ԫ�ء�
    // �����ҵ�����������
    // ����Ϊ����key����С����Ԫ������m������(-m-1)
    // -1 ���Ź�������ʹ��
    int low, high, mid;
    struct fileheader *totest;

    low = start;
    high = end;
    while (low <= high) {
        mid = (low + high) / 2;
        totest = (struct fileheader *) (ptr + mid);
        if (key == totest->id)
            return mid;
        else if (key < totest->id)
            high = mid - 1;
        else
            low = mid + 1;
    }
    return -(low + 1);
}

char get_article_flag(struct fileheader *ent, struct userec *user, char *boardname, int is_bm, session_t* session)
{
    char unread_mark = (DEFINE(user, DEF_UNREADMARK) ? UNREAD_SIGN : 'N');
    char type;

#ifdef HAVE_BRC_CONTROL
    if (strcmp(user->userid, "guest"))
        type = brc_unread(ent->id, session) ? unread_mark : ' ';
    else
#endif
        type = ' ';
    /*
     * add by stiger 
     */
    if (POSTFILE_BASENAME(ent->filename)[0] == 'Z') {
        if (type == ' ')
            type = 'd';
        else
            type = 'D';
        return type;
    }
    /*
     * add end 
     */
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
#ifdef FREE
	if (0)
#elif defined(OPEN_NOREPLY)
    if (ent->accessed[1] & FILE_READ)
#else
    if (is_bm && (ent->accessed[1] & FILE_READ))
#endif
    {
        switch (type) {
        case 'g':
        case 'G':
            type = 'O';
            break;
        case 'm':
        case 'M':
            type = 'U';
            break;
        case 'b':
        case 'B':
            type = '8';
            break;
        case ' ':
        case '*':
        case 'N':
        default:
            type = ';';
            break;
        }
    } else if ((is_bm || HAS_PERM(user, PERM_OBOARDS)) && (ent->accessed[0] & FILE_SIGN)) {
        type = '#';
    } else if ((is_bm || HAS_PERM(user, PERM_OBOARDS)) && (ent->accessed[0] & FILE_PERCENT)) {
        type = '%';
#ifdef FILTER
#ifdef NEWSMTH
    } else if ((ent->accessed[1] & FILE_CENSOR)
               && ((!strcmp(boardname, FILTER_BOARD) && is_bm)
                   || (!strcmp(boardname, "NewsClub") && (haspostperm(user, "NewsClub") || HAS_PERM(user, PERM_OBOARDS))))) {
        type = '@';
#else
    } else if (is_bm && (ent->accessed[1] & FILE_CENSOR) && !strcmp(boardname, FILTER_BOARD)) {
        type = '@';
#endif
#endif
    }

    if (is_bm && (ent->accessed[1] & FILE_DEL)) {
        type = 'X';
    }

    return type;
}

int Origin2(text)
char text[256];
{
    char tmp[STRLEN];

    sprintf(tmp, "�� ��Դ:��%s ", BBS_FULL_NAME);

    return (strstr(text, tmp) != NULL);
}

int add_edit_mark(char *fname, int mode, char *title, session_t* session)
{
    FILE *fp, *out;
    char buf[256];
    time_t now;
    char outname[STRLEN];
    int step = 0;
    int added = 0;
    int asize;

    if ((fp = fopen(fname, "rb")) == NULL)
        return 0;
    sprintf(outname, "tmp/%d.editpost", getpid());
    if ((out = fopen(outname, "w")) == NULL) {
        fclose(fp);
        return 0;
    }

    while ((asize = -attach_fgets(buf, 256, fp)) != 0) {
        if (asize <= 0) {
            if (mode & 2) {
                if (step != 3 && !strncmp(buf, "��  ��: ", 8)) {
                    step = 3;
                    fprintf(out, "��  ��: %s\n", title);
                    continue;
                }
            }

            if (!strncmp(buf, "\033[36m�� �޸�:��", 15))
                continue;
            if (Origin2(buf) && (!added)) {
                now = time(0);
                if (mode & 1)
                    fprintf(out, "\033[36m�� �޸�:��%s �� %15.15s �޸ı��š�[FROM: %s]\033[m\n", session->currentuser->userid, ctime(&now) + 4, SHOW_USERIP(session->currentuser, session->fromhost));
                else
                    fprintf(out, "\033[36m�� �޸�:��%s �� %15.15s �޸ı��ġ�[FROM: %s]\033[m\n", session->currentuser->userid, ctime(&now) + 4, SHOW_USERIP(session->currentuser, session->fromhost));
                step = 3;
                added = 1;
            }
            fputs(buf, out);
        } else
            put_attach(fp, out, asize);
    }
    if (!added) {
        now = time(0);
        if (mode & 1)
            fprintf(out, "\033[36m�� �޸�:��%s �� %15.15s �޸ı��š�[FROM: %s]\033[m\n", session->currentuser->userid, ctime(&now) + 4, SHOW_USERIP(session->currentuser, session->fromhost));
        else
            fprintf(out, "\033[36m�� �޸�:��%s �� %15.15s �޸ı��ġ�[FROM: %s]\033[m\n", session->currentuser->userid, ctime(&now) + 4, SHOW_USERIP(session->currentuser, session->fromhost));
    }
    fclose(fp);
    fclose(out);

    f_cp(outname, fname, O_TRUNC);
    unlink(outname);

    return 1;
}

/*
 * get attachment type. This function might be useful for innbbsd/ code. - atppp
 */
int get_attachment_type(char *attachfilename) {
    char *extension;
    extension = strrchr(attachfilename, '.');
    if (extension != NULL)
    {
        extension++;
        if (!strcasecmp(extension, "jpg")
            || !strcasecmp(extension, "ico")
            || !strcasecmp(extension, "gif"))
        {
            return ATTACH_IMG;
        }
        else if (!strcasecmp(extension, "swf"))
            return ATTACH_FLASH;
        else if (!strcasecmp(extension, "jpeg")
            || !strcasecmp(extension, "png")
            || !strcasecmp(extension, "pcx")
            || !strcasecmp(extension, "bmp"))
        {
            return ATTACH_IMG;
        }
    }
    return ATTACH_OTHERS;
    
}

char *checkattach(char *buf, long size, long *len, char **attachptr)
{
    char *ptr;
    if (size < ATTACHMENT_SIZE + sizeof(int) + 2)
        return NULL;
    if (memcmp(buf, ATTACHMENT_PAD, ATTACHMENT_SIZE))
        return NULL;
    buf += ATTACHMENT_SIZE;
    size -= ATTACHMENT_SIZE;

    ptr = buf;
    for (; size > 0 && *buf != 0; buf++, size--);
    if (size == 0)
        return NULL;
    buf++;
    size--;
    if (size < sizeof(int))
        return NULL;
    *len = ntohl(*(int *) buf);
    if (*len > size)
        *len = size;
    *attachptr = buf + sizeof(int);
    return ptr;
}

/**
 * һ���ܼ��attach��fgets
 * �ļ�β����0,���򷵻�1
 */
int skip_attach_fgets(char *s, int size, FILE * stream)
{
    int matchpos = 0;
    int ch;
    char *ptr;

    ptr = s;
    while ((ch = fgetc(stream)) != EOF) {
        if (ch == ATTACHMENT_PAD[matchpos]) {
            matchpos++;
            if (matchpos == ATTACHMENT_SIZE) {
                int size, d;

                while ((ch = fgetc(stream)) != 0);
                fread(&d, 1, 4, stream);
                size = htonl(d);
                fseek(stream, size, SEEK_CUR);
                ptr -= (ATTACHMENT_SIZE - 1);
                matchpos = 0;
                continue;
            }
        }
        *ptr = ch;
        ptr++;
        *ptr = 0;
        if ((ptr - s) == size - 1) {
//           *(ptr-1)=0;
            return 1;
        }
        if (ch == '\n') {
//        *ptr=0;
            return 1;
        }
    }
    /*
     * if(ptr!=s) return 1;
     * else return 0;
     */
    return (ptr != s);
}

int attach_fgets(char *s, int size, FILE * stream)
{
    int matchpos = 0;
    int ch;
    char *ptr;

    ptr = s;
    while ((ch = fgetc(stream)) != EOF) {
        if (ch == ATTACHMENT_PAD[matchpos]) {
            matchpos++;
            if (matchpos == ATTACHMENT_SIZE) {
                int size, d = 0, count = ATTACHMENT_SIZE + 4 + 1;

                ptr = s;
                while ((ch = fgetc(stream)) != 0) {
                    *ptr = ch;
                    ptr++;
                    count++;
                }
                fread(&d, 1, 4, stream);
                size = htonl(d);
                fseek(stream, -count, SEEK_CUR);
                *ptr = 0;
                return -(count + size);
            }
        }
        *ptr = ch;
        ptr++;
        if ((ptr - s) >= size - 2) {
            *ptr = 0;
            return 1;
        }
        if (ch == '\n') {
            *ptr = 0;
            return 1;
        }
    }
    /*
     * if(ptr!=s) return 1;
     * else return 0;
     */
    return (ptr != s);
}

int put_attach(FILE * in, FILE * out, int size)
{
    char buf[1024 * 16];
    int o;

    if (size <= 0)
        return -1;
    while ((o = fread(buf, 1, size > 1024 * 16 ? 1024 * 16 : size, in)) != 0) {
        size -= o;
        fwrite(buf, 1, o, out);
    }
    return 0;
}

int get_effsize_attach(char *ffn, unsigned int *att)
{
    char *p, *op, *attach;
    long attach_len;
    int j;
    off_t fsize;
    int k, abssize = 0, entercount = 0, ignoreline = 0;

    j = safe_mmapfile(ffn, O_RDONLY, PROT_READ, MAP_SHARED, (void **) &p, &fsize, NULL);
    op = p;
    if (att) *att = 0;
    if (j) {
        k = fsize;
        while (k) {
            if (NULL != (checkattach(p, k, &attach_len, &attach))) {
                if (att && !*att)
                    *att = p - op;
                k -= (attach - p) + attach_len;
                p = attach + attach_len;
                continue;
            }
            if (j) { //Ϊ�˼�������ǰһ��,������j���ж�bool��,��������eff_done_flag��...
                if (*p == '\n') {
                    if (k >= 3 && *(p + 1) == '-' && *(p + 2) == '-' && *(p + 3) == '\n') {
                        if (att) j = 0;
                        else break; //no need to determine attachment, so we are done.
                    } else if (k >= 5 && *(p + 1) == '\xa1' && *(p + 2) == '\xbe' && *(p + 3) == ' ' && *(p + 4) == '\xd4' && *(p + 5) == '\xda') {
                        ignoreline = 1;
                    } else if (k >= 2 && *(p + 1) == ':' && *(p + 2) == ' ') {
                        ignoreline = 2;
                    } else {
                        entercount++;
                        ignoreline = 0;
                    }
                } else if (k >= 2 && *p == KEY_ESC && *(p + 1) == '[' && *(p + 2) == 'm') {
                    ignoreline = 3;
                } else {
                    if (entercount >= 4 && !ignoreline)
                        abssize++;
                }
            }
            k--;
            p++;
        }
    }
    end_mmapfile((void *) op, fsize, -1);
    return abssize;
}

int get_effsize(char *ffn)
{
    return get_effsize_attach(ffn, NULL);
}

#if 0 /* atppp 20050310 */
long calc_effsize(char *fname)
{
    FILE *fp;
    int matched;
    char *ptr;
    off_t size;
    long effsize = 0;
    int insign = 0;
    long signsize = 0;

    if ((fp = fopen(fname, "r+b")) == NULL) {
        return 0;
    }

    matched = 0;

    BBS_TRY {
        if (safe_mmapfile_handle(fileno(fp), PROT_READ, MAP_SHARED, (void **) &ptr, (off_t *) & size) == 1) {
            char *data;
            long not;

            data = ptr;

            not = 0;

            if (!strncmp(data, "������:", 7)) {
                for (; not < size; not++, data++) {
                    if (*data == '\r' || *data == '\n') {
                        not++;
                        data++;
                        if (*data == '\r' || *data == '\n') {
                            not++;
                            data++;
                        }
                        break;
                    }
                }
            }

            if (!strncmp(data, "��  ��:", 7)) {
                for (; not < size; not++, data++) {
                    if (*data == '\r' || *data == '\n') {
                        not++;
                        data++;
                        if (*data == '\r' || *data == '\n') {
                            not++;
                            data++;
                        }
                        break;
                    }
                }
            }

            if (!strncmp(data, "����վ:", 7)) {
                for (; not < size; not++, data++) {
                    if (*data == '\r' || *data == '\n') {
                        not++;
                        data++;
                        if (*data == '\r' || *data == '\n') {
                            not++;
                            data++;
                        }
                        break;
                    }
                }
            }

            for (; not < size; not++, data++) {
                if (*data == 0) {
                    matched++;
                    if (matched == ATTACHMENT_SIZE) {
                        int d, size;

                        data++;
                        not++;
                        while (*data) {
                            data++;
                            not++;
                        }
                        data++;
                        not++;
                        memcpy(&d, data, 4);
                        size = htonl(d);
                        data += 4 + size - 1;
                        not += 4 + size - 1;
                        matched = 0;
                        effsize += size;
                    }
                    continue;
                } else {
                    if (*data != '\r' && *data != '\n') {
                        if (insign == 0)
                            effsize++;
                        else
                            signsize++;
                    }
                }
            }
        } else {
            BBS_RETURN(-1);
        }
    }
    BBS_CATCH {
    }
    BBS_END end_mmapfile((void *) ptr, size, -1);

    fclose(fp);
    return effsize;
}
#endif
/*
  dirarg��Ҫ������dir�ṹ
  id1,id2, ��ʼ���
  ɾ��ģʽ [del_mode = 0]���ɾ�� [1]��ͨɾ�� [2]ǿ��ɾ��
  TODO: use mmap
*/
int delete_range(struct write_dir_arg *dirarg, int id1, int id2, int del_mode, int curmode, const struct boardheader *board, session_t* session)
{
#define DEL_RANGE_BUF 2048
    struct fileheader *savefhdr;
    struct fileheader *readfhdr;
    struct fileheader *delfhdr;
    int count, totalcount, delcount, remaincount, keepcount;
    int pos_read, pos_write, pos_end;
    int i;
    char genbuf[1024];

#ifdef BBSMAIN
    /*
     * curmode=4, 5�����λ�����������ɾ��,���߲�����,�������
     * ���ú����л����κεط�����, ����Ĵ����ǰ��ղ�����ɾ��д��,
     * ����Ϊ�������κ�Ե����ɵ���ʱ�ļ�����(�����Զ�ɾ����), ����
     * ������һ�´򿪲���; tmpfile�Ƿ��ÿ��ģʽ����, �������ֵ��
     * ��ȶ��.  -- ylsdd 
     */
    if ((curmode != DIR_MODE_NORMAL) && (curmode != DIR_MODE_DIGEST) && (curmode != DIR_MODE_MAIL)) {   /* KCN:�ݲ����� */
        return 0;
    }
#endif                          /* 
                                 */
    prepare_write_dir(dirarg, NULL, curmode);
    pos_end = lseek(dirarg->fd, 0, SEEK_END);
    delcount = 0;
    if (pos_end == -1) {
        return -2;
    }
    totalcount = pos_end / sizeof(struct fileheader);
    pos_end = totalcount * sizeof(struct fileheader);
    if (id2 != -1) {
        pos_read = sizeof(struct fileheader) * id2;
    } else
        pos_read = pos_end;
    if (id2 == -1)
        id2 = totalcount;
    if (id1 != 0) {
        pos_write = sizeof(struct fileheader) * (id1 - 1);
        count = id1;
        if (id1 > totalcount) {
            if (dirarg->needlock)
                flock(dirarg->fd, LOCK_UN);
#ifdef BBSMAIN
            prints("��ʼ���ºŴ�����������");
            pressanykey();
#endif                          /* 
                                 */
            return 0;
        }
    } else {
        pos_write = 0;
        count = 1;
        id2 = totalcount;
    }
    if (id2 > totalcount) {
#ifdef BBSMAIN
        char buf[3];

        if (dirarg->needlock)
            flock(dirarg->fd, LOCK_UN);
        getdata(6, 0, "���±�Ŵ�������������ȷ��ɾ�� (Y/N)? [N]: ", buf, 2, DOECHO, NULL, true);
        if (dirarg->needlock)
            flock(dirarg->fd, LOCK_EX);
        if (*buf != 'Y' && *buf != 'y') {
            return -3;
        }
#else
        if (dirarg->needlock)
            flock(dirarg->fd, LOCK_UN);
        return -3;
#endif
        pos_read = pos_end;
        id2 = totalcount;
    }
    savefhdr = (struct fileheader *) malloc(DEL_RANGE_BUF * sizeof(struct fileheader));
    readfhdr = (struct fileheader *) malloc(DEL_RANGE_BUF * sizeof(struct fileheader));
    delfhdr = (struct fileheader *) malloc(DEL_RANGE_BUF * sizeof(struct fileheader));
    if ((id1 != 0) && ((del_mode == 0) || (del_mode == 3))) {   /*rangle mark del */
        while (count <= id2) {
            int i;
            int readcount;

            lseek(dirarg->fd, pos_write, SEEK_SET);
            readcount = read(dirarg->fd, savefhdr, DEL_RANGE_BUF * sizeof(struct fileheader)) / sizeof(struct fileheader);
            for (i = 0; i < readcount; i++, count++) {
                if (count > id2)
                    break;      /*del end */
                if (del_mode == 0) {
                    if (!((savefhdr[i].accessed[0] & FILE_MARKED)||(savefhdr[i].accessed[0] & FILE_PERCENT)))
                        savefhdr[i].accessed[1] |= FILE_DEL;
                } else {
                    savefhdr[i].accessed[1] &= ~FILE_DEL;
                }
            }
            lseek(dirarg->fd, pos_write, SEEK_SET);
            write(dirarg->fd, savefhdr, i * sizeof(struct fileheader));
            pos_write += i * sizeof(struct fileheader);
        }
        if (dirarg->needlock)
            flock(dirarg->fd, LOCK_UN);
        free(savefhdr);
        free(readfhdr);
        free(delfhdr);
        return 0;
    }
    remaincount = count - 1;
    keepcount = 0;
    lseek(dirarg->fd, pos_write, SEEK_SET);
    while (count <= id2) {
        int readcount;
        lseek(dirarg->fd, (count - 1) * sizeof(struct fileheader), SEEK_SET);
        readcount = read(dirarg->fd, savefhdr, DEL_RANGE_BUF * sizeof(struct fileheader)) / sizeof(struct fileheader);
/*        if (readcount==0) break; */
        for (i = 0; i < readcount; i++, count++) {
            if (count > id2)
                break;          /*del end */
            if (((savefhdr[i].accessed[0] & FILE_MARKED
#ifdef FREE
                || savefhdr[i].accessed[0] & FILE_DIGEST
#endif
                || savefhdr[i].accessed[0] & FILE_PERCENT
               ) && del_mode != 2)
                || ((id1 == 0 || del_mode==4) && (!(savefhdr[i].accessed[1] & FILE_DEL)))) {
                memcpy(&readfhdr[keepcount], &savefhdr[i], sizeof(struct fileheader));
                readfhdr[keepcount].accessed[1] &= ~FILE_DEL;
                keepcount++;
                remaincount++;
                if (keepcount >= DEL_RANGE_BUF) {
                    lseek(dirarg->fd, pos_write, SEEK_SET);
                    write(dirarg->fd, readfhdr, DEL_RANGE_BUF * sizeof(struct fileheader));
                    pos_write += keepcount * sizeof(struct fileheader);
                    keepcount = 0;
                }
            } else if (curmode == DIR_MODE_NORMAL) {
                int j;
                memcpy(&delfhdr[delcount], &savefhdr[i], sizeof(struct fileheader));
                delcount++;
                if (delcount >= DEL_RANGE_BUF) {
                    for (j = 0; j < DEL_RANGE_BUF; j++)
                        cancelpost(board->filename, session->currentuser->userid, &delfhdr[j], !strcmp(delfhdr[j].owner, session->currentuser->userid), 0, session);
                    delcount = 0;
                    setbdir(DIR_MODE_DELETED, genbuf, board->filename);
                    append_record(genbuf, (char *) delfhdr, DEL_RANGE_BUF * sizeof(struct fileheader));
                }
                /*
                 * need clear delcount 
                 */
            } else if (curmode == DIR_MODE_MAIL) {
                if (!strstr(dirarg->filename, ".DELETED")) {    //add to ������,todo:����ʼ����
                    memcpy(&delfhdr[delcount], &savefhdr[i], sizeof(struct fileheader));
                    delcount++;
                    if (delcount >= DEL_RANGE_BUF) {
                        delcount = 0;
                        setmailfile(genbuf, session->currentuser->userid, ".DELETED");
                        append_record(genbuf, (char *) delfhdr, DEL_RANGE_BUF * sizeof(struct fileheader));
                    }
                } else {
                    int j;
                    struct stat st;
                    memcpy(&delfhdr[delcount], &savefhdr[i], sizeof(struct fileheader));
                    delcount++;
                    if (delcount >= DEL_RANGE_BUF) {
                        delcount = 0;
                        for (j = 0; j < DEL_RANGE_BUF; j++) {
                            setmailfile(genbuf, session->currentuser->userid, delfhdr[j].filename);
                            if (lstat(genbuf, &st) == 0 && S_ISREG(st.st_mode) && st.st_nlink == 1) {
                                if (session->currentuser->usedspace > st.st_size)
                                    session->currentuser->usedspace -= st.st_size;
                                else
                                    session->currentuser->usedspace = 0;
                            }
                            unlink(genbuf);
                        }
                    }
                }
            }                   //in mail mode
        }                       /*for readcount */
    }
    if (keepcount) {
        lseek(dirarg->fd, pos_write, SEEK_SET);
        write(dirarg->fd, readfhdr, keepcount * sizeof(struct fileheader));
    }
    while (1) {
        int readcount;

        lseek(dirarg->fd, pos_read, SEEK_SET);
        readcount = read(dirarg->fd, savefhdr, DEL_RANGE_BUF * sizeof(struct fileheader)) / sizeof(struct fileheader);
        if (readcount == 0)
            break;
        lseek(dirarg->fd, remaincount * sizeof(struct fileheader), SEEK_SET);
        write(dirarg->fd, savefhdr, readcount * sizeof(struct fileheader));
        pos_read += readcount * sizeof(struct fileheader);
        remaincount += readcount;
    }
#ifdef DEBUG
#ifdef BBSMAIN
    newbbslog(BBSLOG_DEBUG, "%s range ftruncate %d", dirarg->filename ? dirarg->filename : board->filename, remaincount * sizeof(struct fileheader));
#endif
#endif
    ftruncate(dirarg->fd, remaincount * sizeof(struct fileheader));
    if ((curmode == DIR_MODE_NORMAL) && delcount) {
        int j;

        for (j = 0; j < delcount; j++)
            cancelpost(board->filename, session->currentuser->userid, &delfhdr[j], !strcmp(delfhdr[j].owner, session->currentuser->userid), 0, session);
        setbdir(DIR_MODE_DELETED, genbuf, board->filename);
        append_record(genbuf, (char *) delfhdr, delcount * sizeof(struct fileheader));
        setboardorigin(board->filename, 1);
    } else if (curmode == DIR_MODE_MAIL && !strstr(dirarg->filename, ".DELETED")) {
        setmailfile(genbuf, session->currentuser->userid, ".DELETED");
        append_record(genbuf, (char *) delfhdr, delcount * sizeof(struct fileheader));
    } else if (curmode == DIR_MODE_MAIL) {
        struct stat st;
        int j;

        for (j = 0; j < delcount; j++) {
            setmailfile(genbuf, session->currentuser->userid, delfhdr[j].filename);
            if (lstat(genbuf, &st) == 0 && S_ISREG(st.st_mode) && st.st_nlink == 1) {
                if (session->currentuser->usedspace > st.st_size)
                    session->currentuser->usedspace -= st.st_size;
                else
                    session->currentuser->usedspace = 0;
            }
            unlink(genbuf);
        }
    }
    if (dirarg->needlock)
        flock(dirarg->fd, LOCK_UN);
    free(savefhdr);
    free(readfhdr);
    free(delfhdr);
    return 0;
}

/* �����ö�����*/
int add_top(struct fileheader *fileinfo, char *boardname, int flag)
{
    struct fileheader top;
    char path[MAXPATH], newpath[MAXPATH], dirpath[MAXPATH];

    if (POSTFILE_BASENAME(fileinfo->filename)[0] == 'Z')
        return 3;
    memcpy(&top, fileinfo, sizeof(top));
    POSTFILE_BASENAME(top.filename)[0] = 'Z';
    setbfile(path, boardname, top.filename);
    top.accessed[0] = flag;
    setbfile(newpath, boardname, fileinfo->filename);
    setbdir(DIR_MODE_ZHIDING, dirpath, boardname);
    if (get_num_records(dirpath, sizeof(top)) > MAX_DING) {
        return 4;
    }
    link(newpath, path);
    append_record(dirpath, &top, sizeof(top));
    board_update_toptitle(getboardnum(boardname, NULL), true);
    return 0;
}

/*������ժ*/
int add_digest(struct fileheader *fileinfo, char *boardname)
{
    struct fileheader digest;
    char path[MAXPATH], newpath[MAXPATH], dirpath[MAXPATH];

    memcpy(&digest, fileinfo, sizeof(digest));
    digest.accessed[0] &= ~FILE_DIGEST;
    POSTFILE_BASENAME(digest.filename)[0] = 'G';
    setbfile(path, boardname, digest.filename);
    digest.accessed[0] = 0;
    setbfile(newpath, boardname, fileinfo->filename);
    setbdir(DIR_MODE_DIGEST, dirpath, boardname);
    if (get_num_records(dirpath, sizeof(digest)) > MAX_DIGEST) {
        return 4;
    }
    link(newpath, path);
    append_record(dirpath, &digest, sizeof(digest));    /* ��ժĿ¼����� .DIR */
    fileinfo->accessed[0] = fileinfo->accessed[0] | FILE_DIGEST;
    return 0;
}

/*ɾ����ժ*/
int dele_digest(char *dname, const char *boardname)
{                               /* ɾ����ժ��һƪPOST, dname=post�ļ���,boardname�������� */
    char digest_name[STRLEN];
    char new_dir[STRLEN];
    char buf[STRLEN];
    struct fileheader fh;
    int pos;

    strcpy(digest_name, dname);
    setbdir(DIR_MODE_DIGEST, new_dir, boardname);

    POSTFILE_BASENAME(digest_name)[0] = 'G';
    //TODO: ����Ҳ�и�ͬ������
    pos = search_record(new_dir, &fh, sizeof(fh), (RECORD_FUNC_ARG) cmpname, digest_name);      /* ��ժĿ¼�� .DIR�� ���� ��POST */
    if (pos <= 0) {
        return 2;
    }
    delete_record(new_dir, sizeof(struct fileheader), pos, (RECORD_FUNC_ARG) cmpname, digest_name);
    setbfile(buf, boardname, digest_name);
    my_unlink(buf);
    return 0;
}

#ifdef FILTER
int pass_filter(struct fileheader *fileinfo, struct boardheader *board, session_t* session)
{
#ifdef SMTH
    if ((!strcmp(board->filename, FILTER_BOARD)) || (!strcmp(board->filename, "NewsClub")))
#else
    if (!strcmp(board->filename, FILTER_BOARD))
#endif
    {
        if (fileinfo->accessed[1] & FILE_CENSOR || fileinfo->o_bid == 0) {
            return 3;
        } else {
            char oldpath[MAXPATH], newpath[MAXPATH], dirpath[MAXPATH];
            struct fileheader newfh;
            int nowid;
            int filedes;

            fileinfo->accessed[1] |= FILE_CENSOR;
            setbfile(oldpath, board->filename, fileinfo->filename);
            setbfile(newpath, getboard(fileinfo->o_bid)->filename, fileinfo->filename);
            f_cp(oldpath, newpath, 0);

            setbfile(dirpath, getboard(fileinfo->o_bid)->filename, DOT_DIR);
            if ((filedes = open(dirpath, O_WRONLY | O_CREAT, 0664)) == -1) {
                return -1;
            }
            newfh = *fileinfo;
            flock(filedes, LOCK_EX);
            nowid = get_nextid_bid(fileinfo->o_bid);
            newfh.id = nowid;
            if (fileinfo->o_id == fileinfo->o_groupid)
                newfh.groupid = newfh.reid = newfh.id;
            else {
                newfh.groupid = fileinfo->o_groupid;
                newfh.reid = fileinfo->o_reid;
            }
            lseek(filedes, 0, SEEK_END);
            if (safewrite(filedes, &newfh, sizeof(fileheader)) == -1) {
                bbslog("user", "apprec write err! %s", newfh.filename);
            }
            flock(filedes, LOCK_UN);
            close(filedes);

            updatelastpost(getboard(fileinfo->o_bid)->filename);
#if 0 /* ����������Ǵ���� - atppp 20051228 */
#ifdef HAVE_BRC_CONTROL
            brc_add_read(newfh.id, session);
#endif
#endif
            if (newfh.id == newfh.groupid)
                setboardorigin(getboard(fileinfo->o_bid)->filename, 1);
            setboardtitle(getboard(fileinfo->o_bid)->filename, 1);
            if (newfh.accessed[0] & FILE_MARKED)
                setboardmark(getboard(fileinfo->o_bid)->filename, 1);
        }
    }
    return 0;
}
#endif                          /* FILTER */

/**
  ����fileheader������
  @param dirarg ��Ҫ������.DIR
  @param currmode ��ǰdir��ģʽ
  @param board ��ǰ����
  @param fileinfo ���½ṹ
  @param flag Ҫ�����ı�־
  @param data ���еĲ������ݡ�
  @param bmlog �Ƿ�������������¼
  TODO: ��������������ĵط����붼������Ȩ��
  @return 0 �ɹ�
              1 ����������
              2 �Ҳ���ԭ��
              3 ���������
              4 ��ժ��(�ö���)��
              -1 �ļ��򿪴���
  */
int change_post_flag(struct write_dir_arg *dirarg, int currmode, struct boardheader *board, struct fileheader *fileinfo, int flag, struct fileheader *data, bool dobmlog, session_t* session)
{
    char buf[MAXPATH];
    struct fileheader *originFh;
    int ret = 0;

    if (fileinfo && POSTFILE_BASENAME(fileinfo->filename)[0] == 'Z')
        /*
         * �ö������²���������
         */
        return 1;

    /*
     * ����ժ����������ժ����
     */
    if ((flag == FILE_DIGEST_FLAG) && (currmode == DIR_MODE_DIGEST))
        return 1;
    /*
     * if ((flag == FILE_DIGEST_FLAG) && (currmode != DIR_MODE_NORMAL))
     * return 1;
     */

    if (currmode == DIR_MODE_DELETED || currmode == DIR_MODE_JUNK)
        /*
         * ��ɾ��������ɾ������������
         */
        return 1;

    if ((flag == FILE_MARK_FLAG || flag == FILE_DELETE_FLAG) && (!strcmp(board->filename, "syssecurity")
                                                                 || !strcmp(board->filename, FILTER_BOARD)))
        /*
         * �ڹ��˰壬ϵͳ��¼���治����mark,���ɾ������
         */
        return 1;               /* Leeward 98.03.29 */

    if (flag == FILE_TITLE_FLAG && currmode != DIR_MODE_NORMAL)
        /*
         * ����ͨģʽ�²����޸�����
         */
        return 1;

#ifdef COMMEND_ARTICLE
    if (flag == FILE_COMMEND_FLAG && currmode != DIR_MODE_NORMAL)
        /*
         * ����ͨģʽ�²����Ƽ�
         */
        return 1;
#endif
    if (prepare_write_dir(dirarg, fileinfo, currmode) != 0)
        return 2;

    originFh = dirarg->fileptr + (dirarg->ent - 1);     /*�µ�fileheader */
    setbfile(buf, board->filename, originFh->filename);

    /*
     * mark ����
     */
    if (flag & FILE_MARK_FLAG) {
        if (data->accessed[0] & FILE_MARKED) {
            originFh->accessed[0] |= FILE_MARKED;
            if (dobmlog)
                bmlog(session->currentuser->userid, board->filename, 7, 1);
        } else {
            originFh->accessed[0] &= ~FILE_MARKED;
            if (dobmlog)
                bmlog(session->currentuser->userid, board->filename, 6, 1);
        }
        setboardmark(board->filename, 1);
    }

    /*
     * ���ɻظ� ����
     */
    if (flag & FILE_NOREPLY_FLAG) {
        if (!strcmp(board->filename, SYSMAIL_BOARD)) {
            char ans[STRLEN];

            snprintf(ans, STRLEN, "��%s�� ����: %s", session->currentuser->userid, fileinfo->title);
            strnzhcpy(originFh->title, ans, ARTICLE_TITLE_LEN);
        }
        if (data->accessed[1] & FILE_READ) {
            originFh->accessed[1] |= FILE_READ;
        } else {
            originFh->accessed[1] &= ~FILE_READ;
        }
    }
#ifdef COMMEND_ARTICLE
    if (flag & FILE_COMMEND_FLAG) {
        if (data->accessed[1] & FILE_COMMEND)
            originFh->accessed[1] |= FILE_COMMEND;
        else
            originFh->accessed[1] &= ~FILE_COMMEND;
    }
#endif

    /*
     * ��� ����
     */
    if (flag & FILE_SIGN_FLAG) {
        if (data->accessed[0] & FILE_SIGN)
            originFh->accessed[0] |= FILE_SIGN;
        else
            originFh->accessed[0] &= ~FILE_SIGN;
    }
    if (flag & FILE_PERCENT_FLAG) {
        if (data->accessed[0] & FILE_PERCENT)
            originFh->accessed[0] |= FILE_PERCENT;
        else
            originFh->accessed[0] &= ~FILE_PERCENT;
    }
    /*
     * ���ɾ�� ����
     */
    if (flag & FILE_DELETE_FLAG) {
        if (data->accessed[1] & FILE_DEL)
            originFh->accessed[1] |= FILE_DEL;
        else
            originFh->accessed[1] &= ~FILE_DEL;
    }

    /*
     * ������ժ����
     */
    if (flag & FILE_DIGEST_FLAG) {
        if (data->accessed[0] & FILE_DIGEST) {  /*����DIGEST */
            if (dobmlog) {
                bmlog(session->currentuser->userid, board->filename, 3, 1);
                ret = add_digest(originFh, board->filename);
            } else {            /*��ʵ��ʱ��ֻ��Ҫ��һ�±�־�͹��� */
                originFh->accessed[0] |= FILE_DIGEST;
            }
        } else {                /* ����Ѿ�����ժ�Ļ��������ժ��ɾ����post */
            originFh->accessed[0] = (originFh->accessed[0] & ~FILE_DIGEST);
            if (dobmlog) {
                bmlog(session->currentuser->userid, board->filename, 4, 1);
                ret = dele_digest(originFh->filename, board->filename);
            }
        }
    }
    if (ret == 0) {
        if (flag & FILE_TITLE_FLAG) {
            originFh->groupid = originFh->id;
            originFh->reid = originFh->id;
            if (!strncmp(originFh->title, "Re: ", 4)) {
                strcpy(buf, originFh->title + 4);
                if (*buf != 0)
                    strcpy(originFh->title, buf);
            }
        }
        if (flag & FILE_IMPORT_FLAG) {
            if (data->accessed[0] & FILE_IMPORTED)
                originFh->accessed[0] |= FILE_IMPORTED;
            else
                originFh->accessed[0] &= ~FILE_IMPORTED;
        }
#ifdef FILTER
        if (flag & FILE_CENSOR_FLAG) {
            ret = pass_filter(originFh, board, session);

            if (!ret && !strcmp(board->filename, FILTER_BOARD)){
                char ans[STRLEN];
                snprintf(ans, STRLEN, "��%s������: %s", session->currentuser->userid, fileinfo->title);
                strnzhcpy(originFh->title, ans, ARTICLE_TITLE_LEN);
            }
        }
#endif
        if (flag & FILE_ATTACHPOS_FLAG) {
            originFh->attachment = data->attachment;
        }
        if (flag & FILE_DING_FLAG) {
            ret = add_top(originFh, board->filename, data->accessed[0]);
        }
        if (flag & FILE_EFFSIZE_FLAG) {
            originFh->eff_size = data->eff_size;
        }
    }
    if ((currmode != DIR_MODE_NORMAL) && (currmode != DIR_MODE_DIGEST)) {
        /*
         * ��Ҫ����.DIR�ļ�
         */
        char dirpath[MAXPATH];
        struct write_dir_arg dotdirarg;

        init_write_dir_arg(&dotdirarg);
        setbdir(DIR_MODE_NORMAL, dirpath, board->filename);
        dotdirarg.filename = dirpath;
        change_post_flag(&dotdirarg, DIR_MODE_NORMAL, board, originFh, flag, data, false, session);
        free_write_dir_arg(&dotdirarg);
    }
    if (dirarg->needlock)
        flock(dirarg->fd, LOCK_UN);
    return ret;
}

/* etnlegend - �޸ĸ������� */
long ea_dump(int fd_src,int fd_dst,long offset){
    char buf[2048];
    long length,ret,len;
    void *p;
    if(fd_src==fd_dst||offset<0)
        return -1;
    if(ftruncate(fd_dst,offset)==-1)
        return -1;
    lseek(fd_src,offset,SEEK_SET);
    lseek(fd_dst,offset,SEEK_SET);
    length=0;
    do{                                             //�����ļ�
        if((ret=read(fd_src,buf,2048*sizeof(char)))>0)
            for(p=buf,len=ret;len>0&&ret!=-1;p+=ret,len-=ret)
                length+=(ret=write(fd_dst,p,len));
    }while(ret>0);
    if(ret==-1)
        return -1;
    return length;
}
static long ea_parse(int fd,struct ea_attach_info *ai){
    char buf[8],c;
    int count,n;
    long ret,length,end;
    unsigned int size;
    if(!ai)
        return -2;
    bzero(ai,MAXATTACHMENTCOUNT*sizeof(struct ea_attach_info));
    count=0;length=lseek(fd,0,SEEK_CUR);
    end=lseek(fd,0,SEEK_END);lseek(fd,length,SEEK_SET);
    do{
        if((ret=read(fd,buf,ATTACHMENT_SIZE*sizeof(char)))>0){
            if(ret==ATTACHMENT_SIZE*sizeof(char)){
                if(!memcmp(buf,ATTACHMENT_PAD,ATTACHMENT_SIZE*sizeof(char))){
                    ai[count].offset=length;        //��ǰ��������ʼλ��
                    n=0;
                    do{                             //��ǰ�����ļ�����
                        if((ret=read(fd,&c,sizeof(char)))>0){
                            if(ret==sizeof(char)){
                                ai[count].name[n++]=c;
                                if(!c||n>60)        //������ﵽ�����ļ����Ƴ������� /* TODO: filenamename length - atppp*/
                                    break;
                            }
                            else{
                                if(lseek(fd,0,SEEK_CUR)==end)
                                    break;
                                else
                                    lseek(fd,-ret,SEEK_CUR);
                            }
                        }
                    }while(ret>0);
                    if(ret==-1||!ret)
                        break;
                    do{                             //��ǰ�����ļ�����
                        if((ret=read(fd,&size,sizeof(unsigned int)))>0){
                            if(ret==sizeof(unsigned int)){
                                ai[count].size=ntohl(size);
                                ai[count].length=((ATTACHMENT_SIZE+n)*sizeof(char)+
                                    sizeof(unsigned int)+ai[count].size);
                                break;
                            }
                            else{
                                if(lseek(fd,0,SEEK_CUR)==end)
                                    break;
                                else
                                    lseek(fd,-ret,SEEK_CUR);
                            }
                        }
                    }while(ret>0);
                    if(ret==-1||(length+ai[count].length)>end||!(count<MAXATTACHMENTCOUNT))
                        break;
                    length=lseek(fd,ai[count].size,SEEK_CUR);count++;
                }
                else
                    break;
            }
            else{
                if(lseek(fd,0,SEEK_CUR)==end)
                    break;
                else
                    lseek(fd,-ret,SEEK_CUR);
            }
        }
    }while(ret>0);
    if(ret==-1)
        return -1;
    return length;
}
long ea_locate(int fd,struct ea_attach_info *ai){
    char c;
    int n;
    long ret,offset,end;
    end=lseek(fd,0,SEEK_END);
    n=0;lseek(fd,0,SEEK_SET);
    do{                                             //��λ������ʶ ATTACHMENT_PAD
        if((ret=read(fd,&c,sizeof(char)))>0){
            if(ret==sizeof(char)){
                if(c==ATTACHMENT_PAD[n])
                    n++;
                else
                    n=0;
            }
            else{
                if(lseek(fd,0,SEEK_CUR)==end)
                    break;
                else
                    lseek(fd,-ret,SEEK_CUR);
            }
        }
    }while(ret>0&&n<ATTACHMENT_SIZE*sizeof(char));
    if(ret==-1)
        return -1;
    offset=lseek(fd,-(n*sizeof(char)),SEEK_CUR);
    ret=ea_parse(fd,ai);
    if(ret==-1)
        return -1;
    if(!(ret<0)&&ret!=end&&ftruncate(fd,ret)==-1)
        return -1;
    return offset;
}
long ea_append(int fd,struct ea_attach_info *ai,const char *fn){
    char buf[2048];
    const char *base;
    int fd_recv,count;
    unsigned int size;
    long ret,len,end;
    void *p;
    if(!ai)
        return -1;
    for(count=0;count<MAXATTACHMENTCOUNT&&ai[count].name[0];count++)
        continue;
    if(count==MAXATTACHMENTCOUNT)                         //�Ѵﵽ��������
        return -1;
    if((fd_recv=open(fn,O_RDONLY,0644))==-1)
        return -1;
    flock(fd_recv,LOCK_SH);
    if(!(base=strrchr(fn,'/')))
        base=fn;
    else
        base++;

    len = strlen(base);
    if (!len)
        return -1;
    if (len > 60)
        base += (len-60);
    strcpy(ai[count].name,base);
    filter_upload_filename(ai[count].name);

    end=lseek(fd_recv,0,SEEK_END);ai[count].size=(unsigned int)end;
    ai[count].length=((ATTACHMENT_SIZE+strlen(ai[count].name)+1)*sizeof(char)
        +sizeof(unsigned int)+ai[count].size);
    lseek(fd_recv,0,SEEK_SET);ai[count].offset=lseek(fd,0,SEEK_END);
    ret=0;
    for(p=ATTACHMENT_PAD,len=ATTACHMENT_SIZE*sizeof(char);len>0&&ret!=-1;p+=ret,len-=ret)
        ret=write(fd,p,len);                        //д�븽����ʶ ATTACHMENT_PAD
    for(p=ai[count].name,len=(strlen(ai[count].name)+1)*sizeof(char);len>0&&ret!=-1;p+=ret,len-=ret)
        ret=write(fd,p,len);                        //д�븽���ļ�����
    for(size=htonl(end),p=&size,len=sizeof(unsigned int);len>0&&ret!=-1;p+=ret,len-=ret)
        ret=write(fd,p,len);                        //д�븽���ļ���С(�����ֽ���)
    while(ret>0){                                   //д�븽���ļ�����
        if((ret=read(fd_recv,buf,2048*sizeof(char)))>0)
            for(p=buf,len=ret;len>0&&ret!=-1;p+=ret,len-=ret)
                ret=write(fd,p,len);
    }
    len=lseek(fd_recv,0,SEEK_CUR);
    close(fd_recv);
    if(ret==-1||len!=end){
        bzero(&ai[count],sizeof(struct ea_attach_info));
        if(ftruncate(fd,ai[count].offset)==-1)
            return -2;
        return -1;
    }
    return end;
}
long ea_delete(int fd,struct ea_attach_info *ai,int pos){
    int count,n;
    long ret,end;
    void *p;
    if(!ai)
        return -1;
    for(count=0;count<MAXATTACHMENTCOUNT&&ai[count].name[0];count++)
        continue;
    if(!count)                                      //�޸���
        return -1;
    if(!(pos>0)||pos>count)                         //��������
        return -1;
    ret=ai[pos-1].size;
    if(pos==count){                                 //���λ�õĸ���
        if(ftruncate(fd,ai[pos-1].offset)==-1)
            return -1;
        bzero(&ai[pos-1],sizeof(struct ea_attach_info));
    }
    else{                                           //����λ�õĸ���
        end=lseek(fd,0,SEEK_END);
        p=mmap(NULL,end,PROT_READ|PROT_WRITE,MAP_SHARED,fd,0);
        if(p==(void*)-1)
            return -1;
        memmove(p+ai[pos-1].offset,p+ai[pos].offset,end-ai[pos].offset);
        munmap(p,end);
        if(ftruncate(fd,end-ai[pos-1].length)==-1)
            return -2;
        for(n=pos;n<count;n++)
            ai[n].offset-=ai[pos-1].length;
        memmove(&ai[pos-1],&ai[pos],(count-pos)*sizeof(struct ea_attach_info));
        bzero(&ai[count-1],sizeof(struct ea_attach_info));
    }
    return ret;
}
/* �޸ĸ������Ľ��� */




int getattachtmppath(char *buf, size_t buf_len, session_t *session)
{
#if USE_TMPFS==1 && ! defined(FREE)
    /* setcachehomefile() ������ buf_len ��������ֱ����ôд�� */
    snprintf(buf,buf_len,"%s/home/%c/%s/%d/upload",TMPFSROOT,toupper(session->currentuser->userid[0]),
			session->currentuser->userid, session->utmpent);
#else
    snprintf(buf,buf_len,"%s/%s_%d",ATTACHTMPPATH,session->currentuser->userid,  session->utmpent);
#endif
    buf[buf_len-1] = '\0';
    return 0;
}

int upload_post_append(FILE *fp, struct fileheader *post_file, session_t *session)
{
    char buf[256];
    char attachdir[MAXPATH], attachfile[MAXPATH];
    FILE *fp2;
    int fd, n=0;

    getattachtmppath(attachdir, MAXPATH, session);
    snprintf(attachfile, MAXPATH, "%s/.index", attachdir);
    if ((fp2 = fopen(attachfile, "r")) != NULL) {
        fputs("\n", fp);
        while (fgets(buf, 256, fp2)) {
            char *name;
            long begin = 0;
            unsigned int save_size;
            char *ptr;
            off_t size;

            name = strchr(buf, ' ');
            if (name == NULL)
                continue;
            *name = 0;
            name++;
            ptr = strchr(name, '\n');
            if (ptr)
                *ptr = 0;

            if (-1 == (fd = open(buf, O_RDONLY)))
                continue;
            if (post_file->attachment == 0) {
                post_file->attachment = ftell(fp) + 1;
            }
            fwrite(ATTACHMENT_PAD, ATTACHMENT_SIZE, 1, fp);
            fwrite(name, strlen(name) + 1, 1, fp);
            BBS_TRY {
                if (safe_mmapfile_handle(fd,  PROT_READ, MAP_SHARED, (void **) &ptr, & size) == 0) {
                    size = 0;
                    save_size = htonl(size);
                    fwrite(&save_size, sizeof(save_size), 1, fp);
                } else {
                    save_size = htonl(size);
                    fwrite(&save_size, sizeof(save_size), 1, fp);
                    begin = ftell(fp);
                    fwrite(ptr, size, 1, fp);
                    n++;
                }
            }
            BBS_CATCH {
                ftruncate(fileno(fp), begin + size);
                fseek(fp, begin + size, SEEK_SET);
            }
            BBS_END end_mmapfile((void *) ptr, size, -1);

            close(fd);
        }
		fclose(fp2);
    }
    f_rm(attachdir);
    return n;
}

int upload_read_fileinfo(struct ea_attach_info *ai, session_t *session) {
    char buf[256];
    char attachdir[MAXPATH], attachfile[MAXPATH];
    FILE *fp2;
    int n=0;
    struct stat stat_buf;

    getattachtmppath(attachdir, MAXPATH, session);
    snprintf(attachfile, MAXPATH, "%s/.index", attachdir);
    if ((fp2 = fopen(attachfile, "r")) != NULL) {
        while (fgets(buf, 256, fp2)) {
            char *name;
            char *ptr;

            name = strchr(buf, ' ');
            if (name == NULL)
                continue;
            *name = 0;
            name++;
            ptr = strchr(name, '\n');
            if (ptr)
                *ptr = 0;

            strncpy(ai[n].name, name, 60);
            ai[n].name[60] = '\0';

            if (stat(buf, &stat_buf) != -1 && S_ISREG(stat_buf.st_mode)) {
                ai[n].size = stat_buf.st_size;
            } else {
                ai[n].size = 0;
            }

            n++;
            if (n >= MAXATTACHMENTCOUNT) break;
        }
		fclose(fp2);
    }
    return n;

}

int upload_del_file(const char *original_file, session_t *session) {
    char buf[256];
    char attachdir[MAXPATH], attachfile[MAXPATH], attachfile2[MAXPATH];
    FILE *fp, *fp2;
    int ret = -2;

    getattachtmppath(attachdir, MAXPATH, session);
    snprintf(attachfile, MAXPATH, "%s/.index", attachdir);
    snprintf(attachfile2, MAXPATH, "%s/.index2", attachdir);
    if ((fp = fopen(attachfile2, "w")) == NULL) {
        return -1;
    }
    if ((fp2 = fopen(attachfile, "r")) == NULL) {
        fclose(fp);
        return -1;
    }
    while (fgets(buf, 256, fp2)) {
        char *name;
        char *ptr;

        name = strchr(buf, ' ');
        if (name == NULL)
            continue;
        *name = 0;
        name++;
        ptr = strchr(name, '\n');
        if (ptr)
            *ptr = 0;

        if (strcmp(original_file, name)) {
            fprintf(fp, "%s %s\n", buf, name);
            continue;
        }

        ret = 0;
        unlink(buf);
    }
	fclose(fp2);
    fclose(fp);
    f_mv(attachfile2, attachfile);
    return ret;
}

static int upload_add_file_helper(const char *filename, char *original_filename, session_t *session) {
    struct ea_attach_info ai[MAXATTACHMENTCOUNT];
    char attachdir[MAXPATH], attachfile[MAXPATH];
    FILE *fp;
    char buf[256];
    int i, n, len;
    int totalsize=0;
    char *pos1, *pos2;
    n = upload_read_fileinfo(ai, session);
    if (n >= MAXATTACHMENTCOUNT)
        return -2;

    pos1 = strrchr(original_filename, '\\');
    pos2 = strrchr(original_filename, '/');
    if (pos1 && pos2) {
        if (pos1 < pos2) pos1 = pos2;
        original_filename = pos1 + 1;
    } else {
        pos1 = pos1 ? pos1 : pos2;
        if (pos1) original_filename = pos1 + 1;
    }
    len = strlen(original_filename);
    if (!len)
        return -3;
    if (len > 60)
        original_filename += (len-60);
    filter_upload_filename(original_filename);

    for (i=0;i<n;i++) {
        if (strcmp(ai[i].name, original_filename) == 0) return -4;
        totalsize+=ai[i].size;
    }
    struct stat stat_buf;
    if (stat(filename, &stat_buf) != -1 && S_ISREG(stat_buf.st_mode)) {
        totalsize += stat_buf.st_size;
    } else {
        return -5;
    }
    if (!HAS_PERM(session->currentuser, PERM_SYSOP) && totalsize > MAXATTACHMENTSIZE) return -6;
    
    getattachtmppath(attachdir, MAXPATH, session);
    mkdir(attachdir, 0700);

    snprintf(attachfile, MAXPATH, "%s/%d_%d", attachdir, (int)(time(NULL)%10000), rand()%10000);
    f_mv(filename, attachfile);

    snprintf(buf, sizeof(buf), "%s %s\n", attachfile, original_filename);

    snprintf(attachfile, MAXPATH, "%s/.index", attachdir);
    if ((fp = fopen(attachfile, "a")) == NULL)
        return -1;
    fprintf(fp, "%s", buf);
    fclose(fp);
    return(0);
}

int upload_add_file(const char *filename, char *original_filename, session_t *session) {
    int ret = upload_add_file_helper(filename, original_filename, session);
    if (ret) unlink(filename);
    return(ret);
}
