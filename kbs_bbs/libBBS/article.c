#include "bbs.h"
#include <utime.h>

int outgo_post(struct fileheader *fh, const char *board, const char *title, session_t* session)
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
     * 自动生成 POST 文件名
     */
    now = time(NULL);
    len = strlen(alphabet);
    for (i = 0; i < 10; i++) {
        if (use_subdir) {
            rn = 0 + (int)(len * 1.0 * rand() / (RAND_MAX + 1.0));
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

int isowner(const struct userec *user, const struct fileheader *fileinfo)
{
    time_t posttime;
    if (!strcmp(user->userid, "guest"))
        return 0;
    /* fancyrabbit Sep 14 2007, 这里改成 case insensitive 的吧 ... */
    if (strcasecmp(fileinfo->owner, user->userid))
        return 0;
    posttime = get_posttime(fileinfo);
    if (posttime < user->firstlogin)
        return 0;
    if (POSTFILE_BASENAME(fileinfo->filename)[0]=='E')
        return 0;
    return 1;
}

int cmpname(fhdr, name)         /* Haohmaru.99.3.30.比较 某文件名是否和 当前文件 相同 */
struct fileheader *fhdr;
char name[STRLEN];
{
    if (!strncmp(fhdr->filename, name, FILENAME_LEN))
        return 1;
    return 0;
}

/*
  * 判断当前模式是否可以使用id二分
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

/** 初始化filearg结构
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

/** 初始化filearg结构,把各个东西mmap上
  */
int malloc_write_dir_arg(struct write_dir_arg *filearg)
{
    int ret;
    char *ret_ptr;
    if (filearg->fileptr == MAP_FAILED) {
        if (filearg->fd == -1) {
            ret = safe_mmapfile(filearg->filename, O_RDWR, PROT_READ | PROT_WRITE, MAP_SHARED, &ret_ptr, &filearg->size, &filearg->fd);
            filearg->fileptr = (struct fileheader*)ret_ptr;
            if (ret == 0)
                return -1;
            filearg->needclosefd = true;
        } else {                //用fd来打开
            ret = safe_mmapfile_handle(filearg->fd, PROT_READ | PROT_WRITE, MAP_SHARED, &ret_ptr, &filearg->size);
            filearg->fileptr = (struct fileheader*)ret_ptr;
            if (ret == 0)
                return -1;
        }
    }
    return 0;
}

/*
 *  释放filearg所分配的资源。
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
 * 写dir文件之前把文件定位到正确的位置，lock住，并返回相应的数据
 * 这里边并不调用free_write_dir_arg来释放资源。需要上层自己来
 * 调用此函数如果成功，记得及时un_lock(filearg->fd,0,SEEK_SET,0)
 * @param filearg 传入的结构。如果filearg->fd不为-1，说明需要打开dirarg->direct文件
 *                        否则使用filearg->fd作为文件句柄。
 *                        filearg->ent为预计的位置，ent>=1
 *                        filearg->fileptr如果不等于MAP_FAILED,那么这个函数会mmap文件并
 *                        填写filearg->fileptr和filearg->size用于返回数据
 * @param fileinfo 用于定位的东西，不为空的时候需要定位
 * @param mode 当前模式，只有sorted的可以使用二分
 * @return 0成功,此时mmap完毕。
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
            writew_lock(filearg->fd, 0, SEEK_SET, 0);
        if (fileinfo) {         //定位一下
            if ((filearg->ent > count) || (filearg->ent <= 0))
                needrelocation = true;
            else {
                nowFh = filearg->fileptr + (filearg->ent - 1);
                needrelocation = strcmp(fileinfo->filename, nowFh->filename);
            }

        }
        if (needrelocation) {   //重定位这个位置
            int i;

            if (is_sorted_mode(mode)) {
                filearg->ent = Search_Bin(filearg->fileptr, fileinfo->id, 0, count - 1) + 1;
            } else {            //匹配文件名
                int oldent = (filearg->ent > 0) ? filearg->ent : count;

                nowFh = filearg->fileptr;
                filearg->ent = -1;
                /*
                 * 先从当前位置往前找，因为一般都是被删除导致向前了
                 */
                nowFh = filearg->fileptr + (oldent - 1);
                for (i = oldent - 1; i >= 0; i--, nowFh--) {
                    if (!strcmp(fileinfo->filename, nowFh->filename)) {
                        filearg->ent = i + 1;
                        break;
                    }
                }
                /*
                 * 再从当前位置往后找
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
        un_lock(filearg->fd, 0, SEEK_SET, 0);
    return ret;
}

int del_origin(const char *board, struct fileheader *fileinfo)
{
    struct write_dir_arg dirarg;
    char olddirect[PATHLEN];
//    struct fileheader fh;

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
//        fh = *(dirarg.fileptr + (dirarg.ent - 1));
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
        un_lock(dirarg.fd, 0, SEEK_SET, 0);
    close(dirarg.fd);

    return 0;
}

int deny_modify_article(const struct boardheader *bh, const struct fileheader *fileinfo, int mode, session_t* session)
{
    if (fileinfo==NULL || session->currentuser==NULL) {
        return -1;
    }

    if (!strcmp(bh->filename, "syssecurity")) {
        return -3;
    }

    if ((mode>= DIR_MODE_THREAD) && (mode<= DIR_MODE_WEB_THREAD)) /*非源direct不能修改*/
        return -4;

    if (mode==DIR_MODE_SELF)
        return -4;

#ifdef BOARD_SECURITY_LOG
    if (mode == DIR_MODE_BOARD)
        return -4;
#endif

#ifdef HAVE_USERSCORE
    if (mode == DIR_MODE_SCORE)
        return -4;
#endif

    if (checkreadonly(bh->filename))      /* Leeward 98.03.28 */
        return -5;

    if (!HAS_PERM(session->currentuser, PERM_SYSOP)
            && !chk_currBM(bh->BM, session->currentuser)
            && !isowner(session->currentuser, fileinfo)) {
#ifdef ENABLE_BOARD_MEMBER
        if (!check_board_member_manager(NULL, bh, BMP_MODIFY))
#endif            
        return -6;
    }

    if (deny_me(session->currentuser->userid, bh->filename) && (!HAS_PERM(session->currentuser, PERM_SYSOP))) {
        return -2;
    }

    return 0;
}

#ifdef MEMBER_MANAGER
int deny_del_article(const struct boardheader *bh,struct board_member_status *status,const struct fileheader *fileinfo,session_t* session)
#else
int deny_del_article(const struct boardheader *bh,const struct fileheader *fileinfo,session_t* session)
#endif
{
    if (!session||!(session->currentuser)||!bh||!(bh->filename[0]))
        return -1;
    if (!strcmp(bh->filename,"syssecurity"))
        return -3;
    if (HAS_PERM(session->currentuser,PERM_SYSOP))
        return 0;
#ifdef MEMBER_MANAGER
    if (check_board_member_manager(status, bh, BMP_DELETE)||check_board_member_manager(status, bh, BMP_RANGE)||check_board_member_manager(status, bh, BMP_THREAD))
#else
    if (chk_currBM(bh->BM,session->currentuser))
#endif
        return 0;
    if (fileinfo) {
        if (!isowner(session->currentuser,fileinfo))
            return -6;
    /* 文摘区不允许自删, jiangjun 2011-12-05 */
        if (POSTFILE_BASENAME(fileinfo->filename)[0]=='G')
            return -6;
#ifdef HAPPY_BBS
        if (!strcmp(bh->filename,"newcomers"))
            return -6;
#endif
#ifdef COMMEND_ARTICLE
        if (!strcmp(bh->filename,COMMEND_ARTICLE))
            return -6;
#endif
        return 0;
    }
    return -6;
}

int do_del_post(struct userec *user,struct write_dir_arg *dirarg,struct fileheader *fileinfo,
                const char *board,int currmode,int flag,session_t* session)
{
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
#if 0 //atppp 20060422
        newbbslog(BBSLOG_DEBUG, "%s ftruncate %d", dirarg->filename ? dirarg->filename : currboard->filename, dirarg->size);
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
        un_lock(dirarg->fd, 0, SEEK_SET, 0);     /*这个是需要赶紧做的 */

    if (fh.id == fh.groupid) {
        del_origin(board, &fh);
    }
    setboardtitle(board, 1);

    /* reduce reply count, pig2532 */
#ifdef HAVE_REPLY_COUNT
    if (!(flag & ARG_BMFUNC_FLAG))
        if (fh.id != fh.groupid)
            modify_reply_count(board, fh.groupid, -1, 0, NULL);
#endif /* HAVE_REPLY_COUNT */

    owned=(!(flag&ARG_BMFUNC_FLAG)&&isowner(user,&fh));
    cancelpost(board, user->userid, &fh, owned, 1, session);
    updatelastpost(board);
    if (fh.accessed[0] & FILE_MARKED)
        setboardmark(board, 1);
    if (DIR_MODE_NORMAL == currmode) {    /* Leeward 98.06.17 在文摘区删文不减文章数目 */
        if (owned) {
            if ((int) user->numposts > 0 && !junkboard(board)) {
                user->numposts--;       /*自己删除的文章，减少post数 */
            }
        } else if ((flag&ARG_DELDECPOST_FLAG) /*版主删除,减少POST数 */ && !strstr(fh.owner, ".")) {
            struct userec *lookupuser;
            int id = getuser(fh.owner, &lookupuser);

            if (id && (int) lookupuser->numposts > 0 && !junkboard(board) && strcmp(board, SYSMAIL_BOARD)) {    /* SYSOP MAIL版删文不减文章 Bigman: 2000.8.12 *//* Leeward 98.06.21 adds above later 2 conditions */
                lookupuser->numposts--;
            }
        }
    }

    /* 删除文摘区文章时，去掉原文的文摘标记 */
    if (POSTFILE_BASENAME(fileinfo->filename)[0]=='G') {
        struct write_dir_arg dirarg;
        struct fileheader xfh;
        const struct boardheader *bh;
        char buf[STRLEN];

        bh = getbcache(board);
        init_write_dir_arg(&dirarg);
        memcpy(&xfh, fileinfo, sizeof(struct fileheader));
        POSTFILE_BASENAME(xfh.filename)[0]='M';
        setbdir(DIR_MODE_NORMAL, buf, board);
        dirarg.filename = buf;
        change_post_flag(&dirarg, DIR_MODE_NORMAL, bh, &xfh, FILE_DIGEST_FLAG, &xfh, 0, session);
        free_write_dir_arg(&dirarg);
    }

    if (user != NULL && !(flag & ARG_BMFUNC_FLAG)) /* b1/b3 操作不再重复计算 bmlog, fancyrabbit Oct 12 2007 */
        bmlog(user->userid, board, 8, 1);
    newbbslog(BBSLOG_USER, "Del '%s' on '%s'", fh.title, board);        /* bbslog */
#ifdef BOARD_SECURITY_LOG
    /* 非同主题、非自删时记录 */
    if (!(flag&ARG_BMFUNC_FLAG) && !isowner(user,&fh))
        board_security_report(NULL, user, "删除", board, fileinfo);
#endif
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
/* add by stiger,delete 置顶文章 */
int do_del_ding(char *boardname, int bid, int ent, struct fileheader *fh, session_t* session)
{
    int failed;
    char dingdirect[PATHLEN];

    if (fh==NULL)
        return -2;  /* nothing in fileheader */

    setbdir(DIR_MODE_ZHIDING, dingdirect, boardname);
    failed = delete_record(dingdirect, sizeof(struct fileheader), ent, (RECORD_FUNC_ARG) cmpname, fh->filename);

    if (failed) {
        return -1;  /* failed to delete */
    } else {
        char buf[PATHLEN],fn_old[PATHLEN],fn_new[PATHLEN];
        struct fileheader postfile;

        memcpy(&postfile, fh, sizeof(postfile));
        snprintf(postfile.title, ARTICLE_TITLE_LEN, "%-32.32s - %s", fh->title, session->currentuser->userid);
        postfile.accessed[sizeof(postfile.accessed) - 1] = time(0) / (3600 * 24) % 100;

        setbfile(fn_old,boardname,postfile.filename);
        postfile.filename[(postfile.filename[1]=='/')?2:0]='Y';
        setbfile(fn_new,boardname,postfile.filename);

        setbdir(DIR_MODE_DELETED, buf, boardname);
        append_record(buf, &postfile, sizeof(postfile));

        f_mv(fn_old,fn_new);
        board_update_toptitle(bid, true);
#ifdef BOARD_SECURITY_LOG
        board_security_report(NULL, getCurrentUser(), "删除置顶", boardname, fh);
#endif

    }
    return 0;   /* success */
}
#ifdef BATCHRECOVERY

/*---------------------------------------恢复一篇文章--------------------------------------*/

/* 把文章的标题恢复成原来的标题并改变其属性为版面文章。      benogy@bupt 20080807
 * 因为区段恢复需要，所以把这个功能从原来的do_undel_post中独立出来
 * 成功返回0，失败返回-1
 * */
int undelete_change_file_attr(char* board,struct fileheader* fhptr)
{
   char *p,UTitle[128],buf[128],genbuf[128];
   FILE* fp;
   int i;

   sprintf(buf, "boards/%s/%s", board, fhptr->filename);
   if (!dashf(buf)) {
      return -1;
   }
   if (POSTFILE_BASENAME(fhptr->filename)[0]=='E') {
       return -1;
   }
   fp = fopen(buf, "r");
   if (!fp)
      return -1;

   strcpy(UTitle, fhptr->title);
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
      if (strstr(buf, "发信人: ") && strstr(buf, "), 信区: ")) {
         i++;
      } else if (strstr(buf, "标  题: ")) {
         i++;
         strncpy(UTitle, buf + 8, sizeof(UTitle));
         UTitle[sizeof(UTitle)-1] = '\0';
         if ((p = strchr(UTitle, '\n')) != NULL)
            *p = 0;
      }
   }
   fclose(fp);

   setbfile(genbuf, board, fhptr->filename);
   fhptr->accessed[1] &= ~FILE_DEL;
   if (fhptr->filename[1] == '/')
      fhptr->filename[2] = 'M';
   else
      fhptr->filename[0] = 'M';

   setbfile(buf, board, fhptr->filename);
   if (dashf(buf))
      return -1;

   f_mv(genbuf, buf);
   strnzhcpy(fhptr->title, UTitle, ARTICLE_TITLE_LEN);

   return 0;
}


/* 二分查找插入的位置。确保base按关键字升序排列，key小于base中的最大元素，返回指向插入位置的指针 */
struct fileheader* undelete_bsearch(struct fileheader* key,struct fileheader* base,int total)
{
   int low=0,high=total-1,mid;

   while(low<=high){
      mid=(low+high)/2;

      if(key->id > base[mid].id)
         low=mid+1;
      else
         high=mid-1;
   }

   return (base+low);
}


/* 将某篇文章插入到版面的相应位置 */
void undelete_insert_file(struct fileheader* key,struct fileheader* base,int* total)
{
   struct fileheader *p;

   p=base+(*total-1);/* 版面最后一篇文章 */

   if(*total==0)/* 版面没有文章 */
      *base=*key;
   else if(key->id > p->id){/* 直接插在最后 */
      ++p;
      *p=*key;
   }
   else{
      p=undelete_bsearch(key,base,*total);
      memmove(p+1,p,(*total-(p-base))*sizeof(struct fileheader));
      *p=*key;
   }

   ++(*total);
}


#else

#endif /*BATCHRECOVERY*/


int insert_func(int fd, struct fileheader *start, int ent, int total, struct fileheader *data, bool match)
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
/* undelete 一篇文章 Leeward 98.05.18 */
/* modified by ylsdd */
int do_undel_post(char* boardname, char *dirfname, int num, struct fileheader *fileinfo, char *title, session_t* session)
{
    char *p, buf[1024], genbuf[1024];
    char UTitle[128];
    struct fileheader UFile;
    int i;
    FILE *fp;
    int fd;
#ifdef BOARD_SECURITY_LOG
    char opt[IDLEN+2]="";
#endif

    sprintf(buf, "boards/%s/%s", boardname, fileinfo->filename);
    if (!dashf(buf)) {
        return -1;
    }
    if (POSTFILE_BASENAME(fileinfo->filename)[0]=='E') {
        return -1;
    }
    fp = fopen(buf, "r");
    if (!fp)
        return 0;

    strcpy(UTitle, fileinfo->title);
    if ((p = strrchr(UTitle, '-')) != NULL) {   /* create default article title */
#ifdef BOARD_SECURITY_LOG
        strcpy(opt, p+2);
#endif
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
        if (strstr(buf, "发信人: ") && strstr(buf, "), 信区: ")) {
            i++;
        } else if (strstr(buf, "标  题: ")) {
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
            writew_lock(fd, 0, SEEK_SET, 0);
            UFile.id = get_nextid(boardname);
            UFile.groupid = UFile.id;
            UFile.reid = UFile.id;
            lseek(fd, 0, SEEK_END);
            if (safewrite(fd, &UFile, sizeof(UFile)) == -1)
                bbslog("user", "%s", "apprec write err!");
            un_lock(fd, 0, SEEK_SET, 0);
        }
        close(fd);
    }

    /* restore origin, pig2532, 2007.6 */
    if (UFile.id == UFile.groupid) {
        if (setboardorigin(boardname, -1))
            board_regenspecial(boardname, DIR_MODE_ORIGIN, NULL);
        else {
            sprintf(buf, "boards/%s/.ORIGIN", boardname);
            if ((fd = open(buf, O_RDWR | O_CREAT, 0644)) != -1) {
                if ((UFile.id == 0) || mmap_search_apply(fd, &UFile, insert_func) == 0) {
                    writew_lock(fd, 0, SEEK_SET, 0);
                    UFile.id = get_nextid(boardname);
                    UFile.groupid = UFile.id;
                    UFile.reid = UFile.id;
                    lseek(fd, 0, SEEK_END);
                    if (safewrite(fd, &UFile, sizeof(UFile)) == -1)
                        bbslog("user", "%s", "apprec origin write err!");
                    un_lock(fd, 0, SEEK_SET, 0);
                }
                close(fd);
            }
        }
    }

    updatelastpost(boardname);
    fileinfo->filename[0] = '\0';
    substitute_record(dirfname, fileinfo, sizeof(*fileinfo), num, (RECORD_FUNC_ARG) cmpname, fileinfo->filename);
    sprintf(buf, "undeleted %s's “%s” on %s", UFile.owner, UFile.title, boardname);
    bbslog("user", "%s", buf);

#ifdef HAVE_REPLY_COUNT
    if (fileinfo->id == fileinfo->groupid)
        refresh_reply_count(boardname, fileinfo->groupid);
    else
        modify_reply_count(boardname, fileinfo->groupid, 1, 0, NULL);
#endif /* HAVE_REPLY_COUNT */

#ifdef BOARD_SECURITY_LOG
    p = strrchr(dirfname, '/') + 1;
    if (p && strcmp(p, ".DELETED")==0) {
        FILE *fn;
        gettmpfilename(genbuf, "undel_post");
        if ((fn=fopen(genbuf, "w"))!=NULL) {
            fprintf(fn, "\033[33m本文由 \033[31m%s\033[33m 删除\033[m\n", opt);
            fclose(fn);
            board_security_report(genbuf, getCurrentUser(), "恢复", boardname, &UFile);
            unlink(genbuf);
        }
    }
#endif
    if (title != NULL) {
        sprintf(title, "%s", UFile.title);
    }

    bmlog(session->currentuser->userid, boardname, 9, 1);

    return 1;
}

/* by ylsdd
   this item is added to the file '.DELETED' under
   the board's directory,
   Unlike the fb code which moves the file to the deleted
   board.
*/
void cancelpost(const char *board, const char *userid, struct fileheader *fh, int owned, int autoappend, session_t* session)
{
    char oldpath[PATHLEN];
    char newpath[PATHLEN];
    time_t now = time(NULL);

    /*
        sprintf(oldpath, "/board/%s/%s.html", board, fh->filename);
        ca_expire_file(oldpath);*/

    if ((fh->innflag[1] == 'S') && (fh->innflag[0] == 'S')
            && (get_posttime(fh) > now - 14 * 86400)) {
        cancel_inn(board, fh);
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

void edit_backup(const char *board, const char *userid, const char *oldpath, struct fileheader *fh, session_t* session)
{
    static const char post_sufix[] = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    char newpath[PATHLEN], buf[STRLEN];
    time_t now = time(NULL);
    //struct tm *t;

    //t = localtime(&now);
    if (fh->filename[1] == '/')
        fh->filename[2] = 'E';
    else
        fh->filename[0] = 'E';
    setbfile(newpath, board, fh->filename);
    while (dashf(newpath)) {
        fh->filename[strlen(fh->filename)-1] = post_sufix[rand()%62];
        fh->filename[strlen(fh->filename)-2] = post_sufix[rand()%62];
        setbfile(newpath, board, fh->filename);
    }
    f_cp(oldpath, newpath, 0);

    //sprintf(buf, "文章修改备份(%4d-%02d-%02d %02d:%02d:%02d) - %s", t->tm_year+1900, t->tm_mon+1, t->tm_mday, t->tm_hour, t->tm_min, t->tm_sec, userid);
    sprintf(buf, "%-32.32s + %s", fh->title, userid);
    strncpy(fh->title, buf, ARTICLE_TITLE_LEN - 1);
    fh->title[ARTICLE_TITLE_LEN - 1] = 0;
    fh->accessed[sizeof(fh->accessed) - 1] = now / (3600 * 24) % 100;
    setbdir(DIR_MODE_JUNK, buf, board);
    append_record(buf, fh, sizeof(struct fileheader));
    return;
}

void add_loginfo(char *filepath, struct userec *user, char *currboard, int Anony, session_t* session)
{                               /* POST 最后一行 添加 */
    FILE *fp;
    int color, noidboard;
    char fname[STRLEN];

    noidboard = (anonymousboard(currboard) && Anony);   /* etc/anonymous文件中 是匿名版版名 */
    color = (user->numlogins % 7) + 31; /* 颜色随机变化 */
    sethomefile(fname, user->userid, "signatures");
    fp = fopen(filepath, "ab");
    if (!dashf(fname) ||        /* 判断是否已经 存在 签名档 */
            user->signature == 0 || noidboard) {
        fputs("\n--\n", fp);
    } else {                    /*Bigman 2000.8.10修改,减少代码 */
        fprintf(fp, "\n");
    }
    /*
     * 由Bigman增加:2000.8.10 Announce版匿名发文问题
     */
    if (!strcmp(currboard, "Announce") || !strcmp(currboard, "Penalty"))
        fprintf(fp, "\033[m\033[1;%2dm※ 来源:·%s %s·[FROM: %s]\033[m\n", color, BBS_FULL_NAME, NAME_BBS_ENGLISH, BBS_FULL_NAME);
    else
        fprintf(fp, "\n\033[m\033[1;%2dm※ 来源:·%s %s·[FROM: %s]\033[m\n", color, BBS_FULL_NAME, NAME_BBS_ENGLISH, (noidboard) ? NAME_ANONYMOUS_FROM : SHOW_USERIP(session->currentuser, session->fromhost));
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

int write_posts(char *id, const char *board, unsigned int groupid)
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
    strncpy(postlog.board, board, sizeof(postlog.board));
    postlog.board[sizeof(postlog.board)-1]='\0';
    postlog.groupid = groupid;
    postlog.date = now;
    postlog.number = 1;

    sprintf(xpostfile, "tmp/Xpost/%s", id);

    {                           /* added by Leeward 98.04.25
                                 * TODO: 这个地方有点不妥,每次发文要遍历一次,保存到.Xpost中,
                                 * 用来完成十大发文统计针对ID而不是文章.不好
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

void write_header(FILE * fp, struct userec *user, int in_mail, const char *board, const char *title, int Anony, int mode, session_t* session)
{
    int noname;
    char uid[20];
    char uname[40];
    time_t now;
    char timebuf[STRLEN];

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
     * uid[39] = '\0' ; SO FUNNY:-) 定义的 20 这里却用 39 !
     * Leeward: 1997.12.11
     */
    uname[39] = 0;              /* 其实是写错变量名了! 嘿嘿 */
    if (in_mail)
        fprintf(fp, "寄信人: %s (%s)\n", uid, uname);
    else {
        noname = anonymousboard(board);
        /*
         * if (((mode == 0) || (mode == 2)) && !(noname && Anony)) {
         * *
         * * mode=0是正常的发文并且local save
         * * * mode=1是不需要记录的
         * * * mode=2是非local save的
         * *
         * write_posts(user->userid, board, title);
         * }
         */
#ifdef SMTH
        if ((!strcmp(board, "Announce") || !strcmp(board, "Penalty")) && Anony)
            /*
             * added By Bigman
             */
            fprintf(fp, "发信人: %s (%s), 信区: %s\n", "SYSOP", NAME_SYSOP, board);
        else
#endif
#ifdef SECONDSITE
        {
            char anonynick[40];
            sprintf(anonynick, "%s-%d", NAME_ANONYMOUS, session->anonyindex);
            fprintf(fp, "发信人: %s (%s), 信区: %s\n", (noname && Anony) ? "guest" : uid, (noname && Anony) ? anonynick : uname, board);
        }
#else /* SECONDSITE */
            fprintf(fp, "发信人: %s (%s), 信区: %s\n", (noname && Anony) ? board : uid, (noname && Anony) ? NAME_ANONYMOUS : uname, board);
#endif /* SECONDSITE */
    }

    fprintf(fp, "标  题: %s\n", title);
    /*
     * 增加转信标记 czz 020819
     */
    if (in_mail)
        fprintf(fp, "发信站: %s (%24.24s)\n", BBS_FULL_NAME, ctime_r(&now, timebuf));
    else if (mode != 2)
        fprintf(fp, "发信站: %s (%24.24s), 站内\n", BBS_FULL_NAME, ctime_r(&now, timebuf));
    else
        fprintf(fp, "发信站: %s (%24.24s), 转信\n", BBS_FULL_NAME, ctime_r(&now, timebuf));
    if (in_mail)
        fprintf(fp, "来  源: %s \n", session->fromhost);
    fprintf(fp, "\n");

}

static int getcross(const char *filepath,const char *quote_file,struct userec *user,int in_mail,const char *sourceboard,
                    const char *title,int Anony,int mode,int local_article,const struct boardheader *toboard,session_t* session)
{
    FILE *fin,*fout;
    char buf[256];
    int size;
    if (!(fin=fopen(quote_file,"r")))
        return -1;
    if (!(fout=fopen(filepath,"w"))) {
        fclose(fin);
        return -1;
    }
    if (mode==0||(mode==3&&in_mail)) {
        write_header(fout,user,in_mail,toboard->filename,title,Anony,(local_article?1:2),session);
        if (in_mail)
            fprintf(fout,"\033[1;37m【 以下文字转载自 \033[1;32m%s \033[1;37m的信箱 】\033[m\n",user->userid);
        else
            fprintf(fout,"【 以下文字转载自 %s 讨论区 】\n",sourceboard);
    } else if (mode==1) {
        time_t current=time(NULL);
        fprintf(fout,"发信人: %s (自动发信系统), 信区: %s\n", DELIVER, toboard -> filename);
        fprintf(fout,"标  题: %s\n",title);
        fprintf(fout,"发信站: %s自动发信系统 (%24.24s)\n\n",BBS_FULL_NAME,ctime_r(&current, buf));
        fprintf(fout,"【此篇文章是由自动发信系统所张贴】\n\n");
    }
    /* fancyrabbit May 28 2007 mode == 5 说明是精华转出或者做合集，含有某些特殊字样的就别不可 re 了 ...*/
    else if (mode==2 || mode == 5) {
        write_header(fout,user,in_mail,toboard->filename,title,Anony,(local_article?1:2),session);
    } else if (mode==3) {
        write_header(fout,user,in_mail,toboard->filename,title,Anony,(local_article?1:2),session);
        if (!skip_attach_fgets(buf,256,fin)||strncmp(buf,"发信人: ",8)) {
            fseek(fin,0,SEEK_SET);
        } else {
            fprintf(fout,"\033[0;33m[ 用户 %s 在转载时选择了隐藏内部转载来源 ]\033[m\n",user->userid);
            char *pos;
            if (((pos = strrchr(buf, ':')) != NULL) && (pos - buf < 255) && !strncmp(pos-4, "信区", 4)) {
                *(pos+1) = '\0';
                fprintf(fout, "%s\n", buf);
            }
            while (skip_attach_fgets(buf,256,fin)&&buf[0]!='\n')
                fprintf(fout, "%s", buf);
            fprintf(fout, "\n");
        }
    } else if (mode==4) {
        write_header(fout,user,in_mail,toboard->filename,title,Anony,(local_article?1:2),session);
        fprintf(fout,"\033[0;33m[ 用户 %s 在转载时对%s内容进行了编辑 ]\033[m\n\n",user->userid,(!in_mail?"文章":"信件"));
    }
    if ((toboard->flag&BOARD_ATTACH)||(!user||HAS_PERM(user,PERM_SYSOP))) {
        while ((size=-attach_fgets(buf,256,fin))) {
            if (!strncmp(buf,"【 以下文字转载自 ",18)&&strstr(&buf[18]," 讨论区 】"))
                continue;
            if (size<0)
                fprintf(fout,"%s",buf);
            else
                put_attach(fin,fout,size);
        }
    } else {
        while ((size=skip_attach_fgets(buf,256,fin))) {
            if (!strncmp(buf,"【 以下文字转载自 ",18)&&strstr(&buf[18]," 讨论区 】"))
                continue;
            if (size>0)
                fprintf(fout,"%s",buf);
        }
    }
    fclose(fin);
    fclose(fout);
    return 0;
}

#ifdef COMMEND_ARTICLE
static int post_commend_core(struct userec *user, const char *fromboard, const char *toboard, struct fileheader *fileinfo)
{                               /* 推荐 */
    struct fileheader postfile;
    char filepath[STRLEN];
    char oldfilepath[STRLEN];
    char buf[256];
    int aborted;
    int fd, err = 0, nowid = 0;

    memset(&postfile, 0, sizeof(postfile));

    setbfile(filepath, toboard, "");

    if ((aborted = GET_POSTFILENAME(postfile.filename, filepath)) != 0) {
        return -1;
    }

    setbfile(filepath, toboard, postfile.filename);
    setbfile(oldfilepath, fromboard, fileinfo->filename);

    if (f_cp(oldfilepath, filepath, 0) < 0)
        return -1;

    strcpy(postfile.title, fileinfo->title);
    strncpy(postfile.owner, user->userid, OWNER_LEN);
    postfile.owner[OWNER_LEN - 1] = 1;
    postfile.eff_size = get_effsize_attach(oldfilepath, &postfile.attachment);
    postfile.o_id = fileinfo->id;
    postfile.o_groupid = fileinfo->groupid;
    postfile.o_reid = fileinfo->reid;
    postfile.o_bid = getbid(fromboard, NULL);
    //strncpy(postfile.o_board, fromboard, STRLEN- BM_LEN);
    //postfile.o_board[STRLEN-BM_LEN-1]=0;

#ifdef HAVE_REPLY_COUNT
    postfile.replycount = 1;
#endif /* HAVE_REPLY_COUNT */

    setbfile(buf, toboard, DOT_DIR);

    if ((fd = open(buf, O_WRONLY | O_CREAT, 0664)) == -1) {
        err = 1;
    }

    if (!err) {
        writew_lock(fd, 0, SEEK_SET, 0);
        nowid = get_nextid(toboard);
        postfile.id = nowid;
        postfile.groupid = postfile.id;
        postfile.reid = postfile.id;
        set_posttime(&postfile);
        lseek(fd, 0, SEEK_END);
        if (safewrite(fd, &postfile, sizeof(fileheader)) == -1) {
            bbslog("user", "%s", "apprec write err!");
            err = 1;
        }
        un_lock(fd, 0, SEEK_SET, 0);
        close(fd);
    }
    if (err) {
        bbslog("3error", "Posting '%s' on '%s': append_record failed!", postfile.title, toboard);
        my_unlink(filepath);
        return -1;
    }
    updatelastpost(toboard);

    setboardorigin(toboard, 1);
    setboardtitle(toboard, 1);

    return 0;
}

int post_commend(struct userec *user, const char *fromboard, struct fileheader *fileinfo) {
    return post_commend_core(user, fromboard, COMMEND_ARTICLE, fileinfo);
}

#endif

/* Add by SmallPig */
/* 注意此函数不检查权限，caller 须保证发帖的合法性 */
int post_cross(struct userec *user, const struct boardheader *toboard, const char *fromboard, const char *title, const char *filename, int Anony, int in_mail, char islocal, int mode, session_t* session)
{                               /* (自动生成文件名) 转贴或自动发信 */
    struct fileheader postfile;
    char filepath[STRLEN];
    char buf4[STRLEN], whopost[IDLEN], save_title[STRLEN];
    int aborted, local_article;
#ifdef NEWSMTH
    int ret;
#endif    

    memset(&postfile, 0, sizeof(postfile));

    strcpy(buf4,title);
    if ((mode==0||mode==3||mode==4)&&!strstr(buf4," (转载)"))
        strcat(buf4," (转载)");
    strncpy(save_title,buf4,STRLEN);

    setbfile(filepath, toboard->filename, "");

    if ((aborted = GET_POSTFILENAME(postfile.filename, filepath)) != 0) {
        return -1;
    }

    if (mode == 1)
        strcpy(whopost, DELIVER);     /* mode==1为自动发信 */
    else
        strcpy(whopost, user->userid);

    strncpy(postfile.owner, whopost, OWNER_LEN);
    postfile.owner[OWNER_LEN - 1] = '\0';
    setbfile(filepath, toboard->filename, postfile.filename);

    local_article=((toupper(islocal)!='L'&&(toboard->flag&BOARD_OUTFLAG))?0:1);

    if (-1 == getcross(filepath, filename, user, in_mail, fromboard, title, Anony, mode, local_article, toboard, session)) {
        return -1;
    }
#ifdef ENABLE_LIKE
    unsigned int like_count=0;
    int like_score=0;
    if(get_like_count_score(filepath, &like_count, &like_score)>0) {
        postfile.like=like_count;
        postfile.score=like_score;
    }
#endif
    postfile.eff_size = get_effsize_attach(filepath, &postfile.attachment);     /* FreeWizard: get effsize & attachment */

    strnzhcpy(postfile.title, save_title, ARTICLE_TITLE_LEN);

    if (local_article == 1) {   /* local save */
        postfile.innflag[1] = 'L';
        postfile.innflag[0] = 'L';
    } else {
        postfile.innflag[1] = 'S';
        postfile.innflag[0] = 'S';
        outgo_post(&postfile, toboard->filename, save_title, session);
    }
    if (!strcmp(toboard->filename, "syssecurity")
            && strstr(title, "修改 ")
            && strstr(title, " 的权限"))
        postfile.accessed[0] |= FILE_MARKED;    /* Leeward 98.03.29 */
#ifdef NEWSMTH
    if (strstr(title, "发文权限") && (mode == 1 || mode == 2))
#else
    if (strstr(title, "发文权限") && mode == 2)
#endif
    {
        postfile.accessed[1] |= FILE_READ;
    }
    /* fancyrabbit May 28 2007 这个破玩意 U 上置底吧 ...*/
#ifdef SMTH
    if (!strcmp(title, "请版面尽快产生一名或多名版主") && mode == 2) {
        struct BoardStatus *bs;
        postfile.accessed[0] |= FILE_MARKED;
        postfile.accessed[1] |= FILE_READ;
        set_posttime(&postfile);
        bs = getbstatus(getbid(toboard->filename, NULL));
        postfile.id = postfile.groupid = postfile.reid = bs->nowid+1;
        add_top(&postfile, toboard -> filename, 0);
    }
    if (strstr(title, "通过") && strstr(title, "治版方针")) {
        postfile.accessed[0] |= FILE_MARKED;
        set_posttime(&postfile);
        sprintf(buf4, "审核通过%s版治版方针", toboard->filename);
        if (!strcmp(title, buf4) && mode==2) {
            struct BoardStatus *bs;
            postfile.accessed[1] |= FILE_READ;
            bs = getbstatus(getbid(toboard->filename, NULL));
            postfile.id = postfile.groupid = postfile.reid = bs->nowid+1;
            add_top(&postfile, toboard->filename, 0);
        }
    }
#endif /* SMTH */

#ifdef NEWSMTH
    ret = after_post(user, &postfile, toboard->filename, NULL, !(Anony), session);
    if (ret==-2||ret==-3||ret==-4)
        return ret;
#else
    if (after_post(user, &postfile, toboard->filename, NULL, !(Anony), session) == -2)
        return -2;
#endif
    return postfile.id;
}


/* 注意此函数不检查权限，caller 须保证发帖的合法性 */
int post_file(struct userec *user, const char *fromboard, const char *filename, const char *nboard, const char *posttitle, int Anony, int mode, session_t* session)
/* 将某文件 POST 在某版 */
{
    const struct boardheader *toboard;
    if (mode == 0) {
        return -1; // 对此有意见者找 atppp 单挑 20060425
    }
    if (getbid(nboard, &toboard) <= 0) {       /* 搜索要POST的版 ,判断是否存在该版 */
        return -1;
    }
    return post_cross(user, toboard, fromboard, posttitle, filename, Anony, false, 'l', mode, session);  /* post 文件 */
}


/* 支持带标记的 post_file ... 重新造个轮子吧
 * 修改自 etnlegend 的 post_announce(), 感谢之 ...
 * caller 须保证发帖的合法性 ...
 * 不统计 owner 的 bmlog(文) ... 暂不支持匿名发文/过滤 ...
 * mode: 0x01 deliver 发文
 *       0x02 转信
 *       0x04 调用 write_header, 同时指定 0x01 时写入 deliver header ...
 * fancyrabbit Oct 12 2007
 */

int post_file_alt(const char *filename, struct userec *user, const char *title, const char *to_board, const char *from_board, unsigned char mode, const unsigned char accessed[2])
{
    FILE *fp_in, *fp_out;
    struct fileheader fh;
    bool conf_cross;
    char buf[PATHLEN], bufcp[READ_BUFFER_SIZE], save_title[STRLEN], timebuf[STRLEN];
    int fd;
#ifdef HAVE_BRC_CONTROL
    int brc_save;
#endif
    time_t now;
    size_t size;
    struct boardheader bh;
    if ((!(mode & 0x01) && !user) || !to_board || !title)
        return 7;
    bzero(&fh, sizeof(struct fileheader));
    conf_cross = (from_board && (*from_board));
    if (!getboardnum(to_board, &bh))
        return 1;
    setbpath(buf, to_board);
    if (GET_POSTFILENAME(fh.filename, buf))
        return 2;
    set_posttime(&fh);
    if (mode & 0x01)
        memcpy(fh.owner, DELIVER, OWNER_LEN);
    else
        memcpy(fh.owner, user -> userid, OWNER_LEN);
    fh.owner[OWNER_LEN - 1] = 0;
    sprintf(save_title, "%s%s", title, conf_cross ? " (转载)" : "");
    strnzhcpy(fh.title, save_title, ARTICLE_TITLE_LEN);
    if (mode & 0x02) {
        fh.innflag[0] = 'S';
        fh.innflag[1] = 'S';
    } else {
        fh.innflag[0] = 'L';
        fh.innflag[1] = 'L';
    }
    fh.accessed[0] = accessed[0]; fh.accessed[1] = accessed[1];
    setbfile(buf, to_board, fh.filename);
    if (!(fp_out = fopen(buf, "w")))
        return 3;
    if (!(fp_in = fopen(filename, "r"))) {
        fclose(fp_out);
        return 4;
    }
    if (mode & 0x04) {
        if (mode & 0x01) {
            now = time(NULL);
            fprintf(fp_out, "发信人: %s (自动发信系统), 信区: %s\n", DELIVER, to_board);
            fprintf(fp_out, "标  题: %s\n", fh.title);
            fprintf(fp_out, "发信站: %s自动发信系统 (%24.24s)\n\n", BBS_FULL_NAME, ctime_r(&now, timebuf));
            fprintf(fp_out, "【此篇文章是由自动发信系统所张贴】\n\n");
        } else
            write_header(fp_out, user, 0, to_board, fh.title, 0, (mode & 0x02) ? 2 : 0, getSession());
    }
    if (conf_cross)
        fprintf(fp_out, "【 以下文字转载自 %s 讨论区 】\n", from_board);
    if ((bh.flag&BOARD_ATTACH)||(!user||HAS_PERM(user,PERM_SYSOP))) {
        while (true) {
            size = fread(bufcp, 1, READ_BUFFER_SIZE, fp_in);
            if (size == 0)
                break;
            fwrite(bufcp, size, 1, fp_out);
        }
    } else {
        while (skip_attach_fgets(bufcp, READ_BUFFER_SIZE, fp_in))
            fputs(bufcp, fp_out);
    }
    fclose(fp_in);
    fclose(fp_out);
    fh.eff_size = get_effsize_attach(buf, &fh.attachment);
#ifdef HAVE_BRC_CONTROL
    brc_save = getSession()->brc_currcache;
    getSession() -> brc_currcache = -1;
#endif
    if (mode & 0x02)
        outgo_post(&fh, to_board, save_title, getSession());
    setbdir(DIR_MODE_NORMAL, buf, to_board);
    if ((fd = open(buf, O_WRONLY|O_CREAT, S_IRUSR | S_IWUSR | S_IRGRP | S_IWGRP | S_IROTH)) == -1) {
#ifdef HAVE_BRC_CONTROL
        getSession() -> brc_currcache = brc_save;
#endif
        return 5;
    }
    writew_lock(fd, 0, SEEK_SET, 0);
    fh.id = get_nextid(to_board);
    fh.groupid = fh.id;
    fh.reid = fh.id;
    lseek(fd, 0, SEEK_END);
    if (safewrite(fd, &fh, sizeof(struct fileheader)) == -1) {
        un_lock(fd, 0, SEEK_SET, 0);
        close(fd);
#ifdef HAVE_BRC_CONTROL
        getSession() -> brc_currcache = brc_save;
#endif
        setbfile(buf, to_board, fh.filename);
        unlink(buf);
        return 6;
    }
    un_lock(fd, 0, SEEK_SET, 0);
    close(fd);
    updatelastpost(to_board);
    if (setboardorigin(to_board, -1))
        board_regenspecial(to_board, DIR_MODE_ORIGIN, NULL);
    else {
        setbdir(DIR_MODE_ORIGIN, buf, to_board);
        if (!((fd = open(buf, O_WRONLY|O_CREAT, S_IRUSR | S_IWUSR | S_IRGRP | S_IWGRP | S_IROTH)) < 0)) {
            writew_lock(fd, 0, SEEK_SET, 0);
            lseek(fd, 0, SEEK_END);
            safewrite(fd, &fh, sizeof(struct fileheader));
            un_lock(fd, 0, SEEK_SET, 0);
            close(fd);
        }
    }
    /* 初始化 replycount */
#ifdef HAVE_REPLY_COUNT
    modify_reply_count(to_board, fh.id, 1, 1, &fh);
#endif
    setboardtitle(to_board, 1);
    if (fh.accessed[0] & FILE_MARKED)
        setboardmark(to_board, 1);
#ifdef HAVE_BRC_CONTROL
    getSession() -> brc_currcache = brc_save;
#endif
    /* 写 postlog */
#ifndef NEWPOSTSTAT
    if (!(mode & 0x01) && user)
        write_posts(user -> userid, to_board, fh.groupid);
#endif
#ifdef NEWPOSTLOG
    if (!(mode & 0x01) && user)
        newpostlog(user -> userid, to_board, fh.title, fh.groupid, fh.id);
#else
    sprintf(buf, "posted '%s' on '%s'", fh.title, to_board); /* deliver 也计我自己的 ... ? 原来貌似是这么做的 ... */
    newbbslog(BBSLOG_USER, "%s", buf);
#endif
    return 0;
}


/*
 * 注意：fh->attachment = 0           如果没有附件 (这个时候整个文件需要过滤)
 *                      = 正文长度    如果有附件   (这个时候仅正文部分过滤)
 * 调用一定保证 attachment 这个字段的正确，否则可能会漏过滤！！！
 * atppp 20051127
 *
 * 返回值：
 * -1: 系统错误
 * -2: 被过滤了
 * -3: 先审后发版面设置出错
 * -4: 先审后发版面
 * >0: 新发出去文章的 ID
 *
 */
int after_post(struct userec *user, struct fileheader *fh, const char *boardname, struct fileheader *re, int poststat, session_t* session)
{
    char buf[256];
    int fd, err = 0, nowid = 0;
    
#ifdef FILTER
    char oldpath[50], newpath[50];
    int filtered;
#endif

/* 先审后发策略 , windinsn, Sep 13, 2012 */
/* 先过Filter， 通过后再过版面审核 */
#ifdef NEWSMTH
    int filter_bid=0;
    char filter_boardname[STRLEN];
    const struct boardheader *filter_bh=NULL;
#endif /* NEWSMTH */

    const struct boardheader *bh = NULL;
    int bid;

    if ((re == NULL) && (!strncmp(fh->title, "Re: ", 4))) {
        memmove(fh->title, fh->title + 4, ARTICLE_TITLE_LEN);
    }
    bid = getbid(boardname, &bh);

/* 先审后发策略, windinsn, Sep 13,2012 */
#ifdef NEWSMTH
    if (bh && bh->flag&BOARD_CENSOR) {
        sprintf(filter_boardname, "%sFilter", bh->filename);
        filter_bid=getbid(filter_boardname, &filter_bh);
        
        if (filter_bid<=0 || !filter_bh || !(filter_bh->flag&BOARD_CENSOR_FILTER))
            return -3;
    }
#endif /* NEWSMTH */
    
#ifdef FILTER
    setbfile(oldpath, boardname, fh->filename);
    filtered = 0;
    if (strcmp(fh->owner, DELIVER)) {
        if (((bh && bh->level & PERM_POSTMASK) || normal_board(boardname)) && strcmp(boardname, FILTER_BOARD)) {
            if (check_badword_str(fh->title, strlen(fh->title), session) || check_badword(oldpath, fh->attachment, session)) {
                /*
                 * FIXME: There is a potential bug here.
                 */
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
                    snprintf(newtitle, ARTICLE_TITLE_LEN, "[请等候审核]%s", fh->title);
                    mail_file(session->currentuser->userid, newpath, session->currentuser->userid, newtitle, 0, fh);
                }
#endif /* ZIXIA */
                boardname = FILTER_BOARD;
                filtered = 1;
            };
        }
    }
#endif /* FILTER */

/* 先审核后版面, windinsn, Sep 13, 2012 */
#ifdef NEWSMTH
    if (!filtered && bh && bh->flag&BOARD_CENSOR && strcmp(fh->owner, DELIVER)) {
        setbfile(oldpath, bh->filename, fh->filename);
        setbfile(newpath, filter_bh->filename, fh->filename);
        f_mv(oldpath, newpath);
        
        fh->o_bid=bid;
        nowid=get_nextid_bid(bid);
        fh->o_id=nowid;
        if (re == NULL) {
            fh->o_groupid = fh->o_id;
            fh->o_reid = fh->o_id;
        } else {
            fh->o_groupid = re->groupid;
            fh->o_reid = re->id;
        }
        boardname=filter_bh->filename;
        filtered=2;
    }
#endif /* NEWSMTH */

    setbfile(buf, boardname, DOT_DIR);

    if ((fd = open(buf, O_WRONLY | O_CREAT, 0664)) == -1) {
        err = 1;
    }
    process_control_chars(fh->title,NULL);
    if (!err) {
        writew_lock(fd, 0, SEEK_SET, 0);
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
        un_lock(fd, 0, SEEK_SET, 0);
        close(fd);
    }
    if (err) {
        bbslog("3error", "Posting '%s' on '%s': append_record failed!", fh->title, boardname);
        setbfile(buf, boardname, fh->filename);
        my_unlink(buf);
        return -1;
    }
    updatelastpost(boardname);

#ifdef FILTER
    if (filtered==1)
        sprintf(buf, "posted '%s' on '%s' filtered", fh->title, getboard(fh->o_bid)->filename);
#ifdef NEWSMTH
    else if (filtered==2)
        sprintf(buf, "posted '%s' on '%s' wait for censor", fh->title, getboard(fh->o_bid)->filename);
#endif /* NEWSMTH */
    else {
#endif /* FILTER */
#ifdef HAVE_BRC_CONTROL
        brc_add_read(fh->id, bid, session);
#endif /* HAVE_BRC_CONTROL */

        /*
         * 回文寄到原作者信箱, stiger
         */
        if (re) {
            if (re->accessed[1] & FILE_MAILBACK) {

                struct userec *lookupuser;
                char newtitle[STRLEN];

                if (getuser(re->owner, &lookupuser) != 0) {
                    if ((false != canIsend2(session->currentuser, re->owner)) && (check_mail_perm(NULL, lookupuser) == 0)) {
                        setbfile(buf, boardname, fh->filename);
                        snprintf(newtitle, ARTICLE_TITLE_LEN, "[回文转寄]%s", fh->title);
                        mail_file(/*session->currentuser->userid*/fh->owner, buf, re->owner, newtitle, 0, fh);
                    }
                }
            }
        }

#if defined(NEWSMTH) && !defined(SECONDSITE)
    setbfile(buf, boardname, fh->filename);
    record_first_post(session, bh, fh, buf);
#endif
		
#ifdef ENABLE_REFER
    setbfile(buf, boardname, fh->filename);
    send_refer_msg(boardname, fh, re, buf);
#endif

        sprintf(buf, "posted '%s' on '%s'", fh->title, boardname);

#ifndef NEWPOSTSTAT
        if (poststat && user)
            write_posts(user->userid, boardname, fh->groupid);
#endif

#ifdef DENYANONY
        if (user && !poststat) {
            char anonybuf[256];
            struct fileheader tmpf;

            setbfile(anonybuf, boardname, ".ANONYDIR");
            memcpy(&tmpf, fh, sizeof(tmpf));

            strcpy(tmpf.owner, session->currentuser->userid);

            if ((fd = open(anonybuf, O_WRONLY | O_CREAT, 0664)) != -1) {
                writew_lock(fd, 0, SEEK_SET, 0);
                lseek(fd, 0, SEEK_END);
                safewrite(fd, &tmpf, sizeof(fileheader));
                un_lock(fd, 0, SEEK_SET, 0);
                close(fd);
            }
        }
#endif

#ifdef FILTER
    }
#endif
#ifdef NEWPOSTLOG
    if (user)
        newpostlog(user->userid, boardname, fh->title, fh->groupid, fh->id);
#else
    newbbslog(BBSLOG_USER, "%s", buf);
#endif

    if (fh->id == fh->groupid) {
        if (setboardorigin(boardname, -1)) {
            board_regenspecial(boardname, DIR_MODE_ORIGIN, NULL);
        } else {
            setbdir(DIR_MODE_ORIGIN, buf, boardname);
            if ((fd = open(buf, O_WRONLY | O_CREAT, 0664)) >= 0) {
                writew_lock(fd, 0, SEEK_SET, 0);
                lseek(fd, 0, SEEK_END);
                if (safewrite(fd, fh, sizeof(fileheader)) == -1) {
                    bbslog("user", "%s", "apprec origin write err!");
                }
                un_lock(fd, 0, SEEK_SET, 0);
                close(fd);
            }
        }
    }

    /* add to reply count in .ORIGIN, pig2532 */
#ifdef HAVE_REPLY_COUNT
    if (fh->groupid == fh->id)
        modify_reply_count(boardname, fh->id, 1, 1, fh);
    else
        modify_reply_count(boardname, fh->groupid, 1, 0, fh);
#endif /* HAVE_REPLY_COUNT */

    setboardtitle(boardname, 1);
    if (fh->accessed[0] & FILE_MARKED)
        setboardmark(boardname, 1);
    if (user != NULL)
        bmlog(user->userid, boardname, 2, 1);
#ifdef FILTER
    if (filtered==1)
        return -2;
#ifdef NEWSMTH
    else if (filtered==2)
        return -4;
#endif
    else
#endif
        return nowid;
}

int mmap_search_apply(int fd, struct fileheader *buf, DIR_APPLY_FUNC func)
{
    char *datac;
    struct fileheader *dataf;
    off_t filesize;
    int total;
    int low, high;
    int ret = 0;

    if (writew_lock(fd, 0, SEEK_SET, 0) == -1)
        return 0;
    BBS_TRY {
        if (safe_mmapfile_handle(fd, PROT_READ | PROT_WRITE, MAP_SHARED, &datac, &filesize) == 0) {
            un_lock(fd, 0, SEEK_SET, 0);
            BBS_RETURN(0);
        }
        dataf = (struct fileheader*)datac;
        total = filesize / sizeof(struct fileheader);
        low = 0;
        high = total - 1;
        while (low <= high) {
            int mid, comp;

            mid = (high + low) / 2;
            comp = (buf->id) - ((dataf + mid)->id);
            if (comp == 0) {
                ret = (*func)(fd, dataf, mid + 1, total, buf, true);
                end_mmapfile(datac, filesize, -1);

                BBS_RETURN(ret);
            } else if (comp < 0)
                high = mid - 1;
            else
                low = mid + 1;
        }
        ret = (*func)(fd, dataf, low + 1, total, buf, false);
    }
    BBS_CATCH {
    }
    BBS_END;
    end_mmapfile((void *) datac, filesize, -1);

    un_lock(fd, 0, SEEK_SET, 0);
    return ret;
}

int mmap_dir_search(int fd, const fileheader_t * key, search_handler_t func, void *arg)
{
    char *datac;
    struct fileheader *dataf;
    off_t filesize;
    int total;
    int low, high;
    int mid, comp;
    int ret = 0;

    if (writew_lock(fd, 0, SEEK_SET, 0) == -1)
        return 0;
    BBS_TRY {
        if (safe_mmapfile_handle(fd, PROT_READ | PROT_WRITE, MAP_SHARED, &datac, &filesize) == 0) {
            un_lock(fd, 0, SEEK_SET, 0);
            BBS_RETURN(0);
        }
        dataf = (struct fileheader*)datac;
        total = filesize / sizeof(fileheader_t);
        low = 0;
        high = total - 1;
        while (low <= high) {
            mid = (high + low) / 2;
            comp = (key->id) - ((dataf + mid)->id);
            if (comp == 0) {
                ret = (*func)(fd, dataf, mid + 1, total, true, arg);
                end_mmapfile(datac, filesize, -1);
                un_lock(fd, 0, SEEK_SET, 0);
                BBS_RETURN(ret);
            } else if (comp < 0)
                high = mid - 1;
            else
                low = mid + 1;
        }
        ret = (*func)(fd, dataf, low + 1, total, false, arg);
    }
    BBS_CATCH {
    }
    BBS_END;
    end_mmapfile((void *) datac, filesize, -1);

    un_lock(fd, 0, SEEK_SET, 0);

    return ret;
}

struct dir_record_set {
    fileheader_t *records;
    int num;
    int rec_no;                 /* 记录集的中间记录在索引文件中的记录号，基 1 的，
                                 * 其他记录的记录号可以通过 num 和 rec_no 算出 */
};

static int get_dir_records(int fd, fileheader_t * base, int ent, int total, bool match, void *arg)
{
    if (match) {
        struct dir_record_set *rs = (struct dir_record_set *) arg;
        int i;
        int off;
        int count = 0;

        off = ent - rs->num / 2;
        rs->rec_no = ent;       /* 在这里保存记录号 */
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

int get_ent_from_id_ext(int mode, int id, const char *bname, fileheader_t *fh)
{
    char dir[PATHLEN];
    int fd, index;
    setbdir(mode, dir, bname);

    fd = open(dir, O_RDWR, 0644);
    if (fd < 0) {
        return(-1);
    }
    if (!get_records_from_id(fd, id, fh, 1, &index)) {
        close(fd);
        return(-2);
    }
    close(fd);
    return(index);
}

static int get_ent_id(int fd, fileheader_t * base, int ent, int total, bool match, void *arg)
{
    if (match) {
        return ent;
    } else
        return 0;
}

int get_ent_from_id(int mode, int id, const char *bname)
{
    char direct[PATHLEN];
    int fd;
    int ret=0;
    fileheader_t key;

    setbdir(mode, direct, bname);
    if ((fd = open(direct, O_RDWR, 0)) == -1) {
        return 0;
    }

    key.id = id;
    ret = mmap_dir_search(fd, &key, get_ent_id, NULL);

    close(fd);
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

/* 正数 num 表示取 id 的同主题后 num 篇文章；
   负数 num 表示取 id 的同主题前 |num| 篇文章 */
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


/* add article to announce clipboard. pig2532 2006-03-04
create new clipboard file when bname is not empty
return 0 when success, -1 when fail.
*/
int ann_article_import(const char *bname, const char *title, const char *fname, const char *userid)
{
    char clipfile[PATHLEN];
    FILE *fp;
    const struct boardheader *bp;

    sprintf(clipfile, "tmp/clip/%s.announce", userid);
    if (bname) {
        if ((bp = getbcache(bname)) == NULL) {
            return -1;
        }
        if ((fp = fopen(clipfile, "w")) == NULL)
            return -1;
        fprintf(fp, "DelSource=OnBoard\n");
        fprintf(fp, "Board=%s\n", bp->filename);
    } else {
        if ((fp = fopen(clipfile, "a")) == NULL)
            return -1;
    }
    if ((title != NULL) && (fname != NULL)) {
        fprintf(fp, "Title=%s\n", title);
        fprintf(fp, "Filename=%s\n", fname);
    }
    fclose(fp);
    return 0;
}


struct dir_gthread_set {
    fileheader_t *records;
    int num;
    int groupid;
    int start;
    int haveprev;
    int operate;
    char *userid;
};

#define GDG_INI_SIZE 512
#define GDG_INC_SIZE 512
static int get_dir_gthreads(int fd, fileheader_t * base, int ent, int total, bool match, void *arg)
{
    struct dir_gthread_set *ts = (struct dir_gthread_set *) arg;
    int i;
    int count = 0;
    int start = ent;
    int end = total;
    int passprev = 0;
    struct fileheader *fh;

    int dyn_size = 0;

    /* fancy Sep 30 2011, 动态分配内存 */
    if (ts->num == -1) {
        dyn_size = GDG_INI_SIZE;
        if (!(ts->records = (fileheader_t *)malloc(dyn_size * sizeof(fileheader_t))))
            return 0;
    }
    for (i = start; i <= end; i++) {
        if (count == ts->num)
            break;
        if (base[i - 1].groupid == ts->groupid) {
            if (base[i - 1].id < ts->start) {
                passprev = i;
                continue;
            }
            if (ts->records) {
                if (ts->num == -1 && count == dyn_size) {
                    dyn_size += GDG_INC_SIZE;
                    if (!(ts->records = (fileheader_t *)realloc(ts->records, dyn_size * sizeof(fileheader_t)))) {
                        free(ts->records);
                        return 0;
                    }
                }
                memcpy(ts->records + count, base + i - 1, sizeof(fileheader_t));
            }
            count++;

            fh = base + i - 1;
            switch (ts->operate) {
                case 2:    /* mark */
                    fh->accessed[0] |= FILE_MARKED;
                    break;
                case 3:    /* unmark */
                    fh->accessed[0] &= ~FILE_MARKED;
                    break;
                case 5:
                    ann_article_import(NULL, fh->title, fh->filename, ts->userid);
                    fh->accessed[0] |= FILE_IMPORTED;
                    break;
                case 6:    /* X */
                    fh->accessed[1] |= FILE_DEL;
                    break;
                case 7:    /* unX */
                    fh->accessed[1] &= ~FILE_DEL;
                    break;
                case 8:    /* no reply */
                    fh->accessed[1] |= FILE_READ;
                    break;
                case 9:    /* cancel no reply */
                    fh->accessed[1] &= ~FILE_READ;
                    break;
                case 10:
                case 11:
                    /* do not add import flag when making total, pig2532 */
                    break;
                case 12:
                case 13:
                    fh->accessed[0] |= FILE_IMPORTED;
                    break;
            }

        }
    }

    if (passprev) {
        int i = 1;

        for (; passprev >= start; passprev--) {
            if (ts->num > 0 && i >= ts->num)
                break;
            if (base[passprev - 1].groupid == ts->groupid) {
                ts->haveprev = base[passprev - 1].id;
                i++;
            }
        }
    }
    return count;
}

// num = -1 表示没有上限，这个时候调用者需要在外面 free() 掉 *buf，切记！！
int get_threads_from_gid(const char *filename, int gid, fileheader_t **buf, int num, int startid, int *haveprev, int operate, struct userec *user)
{
    struct dir_gthread_set ts;
    fileheader_t key;
    int fd;
    int ret;

    if (num == 0)
        return 0;
    ts.records = buf ? *buf : NULL;
    ts.num = num;
    ts.groupid = gid;
    ts.start = startid;
    ts.haveprev = 0;
    ts.operate = operate;
    ts.userid = user->userid;
    bzero(&key, sizeof(key));
    key.id = gid;
    if ((fd = open(filename, O_RDWR, 0644)) < 0)
        return -1;
    ret = mmap_dir_search(fd, &key, get_dir_gthreads, &ts);
    close(fd);
    if (num == -1)
        *buf = ts.records;

    *haveprev = ts.haveprev;
    return ret;
}

//土鳖两分法，    by yuhuan
//请flyriver同学或其他人自行整合
int Search_Bin(struct fileheader *ptr, int key, int start, int end)
{
    // 在有序表中折半查找其关键字等于key的数据元素。
    // 若查找到，返回索引
    // 否则为大于key的最小数据元素索引m，返回(-m-1)
    // -1 留着供出错处理使用
    int low, high, mid;
    struct fileheader *totest;

    low = start;
    high = end;
    while (low <= high) {
        mid = (low + high) / 2;
        totest = (struct fileheader *)(ptr + mid);
        if (key == totest->id)
            return mid;
        else if (key < totest->id)
            high = mid - 1;
        else
            low = mid + 1;
    }
    return -(low + 1);
}

/* *common_flag 返回普通用户可见的标记(不带未读标记) */
char get_article_flag(struct fileheader *ent, struct userec *user, const struct boardheader *board, int is_bm, char *common_flag, session_t* session)
{
    char unread_mark = (DEFINE(user, DEF_UNREADMARK) ? UNREAD_SIGN : 'N');
    char type, common_type = ' ';

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
        if (common_flag) *common_flag = 'd';
        return type;
    }

    if (POSTFILE_BASENAME(ent->filename)[0]=='Y') {
        type=(type==' '?'y':'Y');
        if (common_flag)
            *common_flag='y';
        return type;
    }

    /*
     * add end
     */
    /* 修改文章备份 */
    if (POSTFILE_BASENAME(ent->filename)[0]=='E') {
        type=(type==' '?'e':'E');
        if (common_flag)
            *common_flag='e';
        return type;
    }

    if ((ent->accessed[0] & FILE_DIGEST)) {
        common_type = 'g';
        if (type == ' ')
            type = 'g';
        else
            type = 'G';
    }
    if (ent->accessed[0] & FILE_MARKED) {
        switch (type) {
            case ' ':
                common_type = type = 'm';
                break;
            case UNREAD_SIGN:
            case 'N':
                common_type = 'm';
                type = 'M';
                break;
            case 'g':
                common_type = type = 'b';
                break;
            case 'G':
                common_type = 'b';
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
               && 
               (
                      (!strcmp(board->filename, FILTER_BOARD) && is_bm)
                   || (!strcmp(board->filename, "NewsClub") && (haspostperm(user, "NewsClub") || HAS_PERM(user, PERM_OBOARDS)))
                   || (board->flag&BOARD_CENSOR_FILTER && (haspostperm(user, board->filename) || HAS_PERM(user, PERM_OBOARDS)))   
                )
                ) {
        type = '@';
#else
    } else if (is_bm && (ent->accessed[1] & FILE_CENSOR) && !strcmp(board->filename, FILTER_BOARD)) {
        type = '@';
#endif
#endif
    }

    if (is_bm && (ent->accessed[1] & FILE_DEL)) {
        type = 'X';
    }
    if (common_flag) *common_flag = common_type;
    return type;
}

int Origin2(text)
char text[256];
{
    char tmp[STRLEN];

    sprintf(tmp, "※ 来源:·%s ", BBS_FULL_NAME);

    return (strstr(text, tmp) != NULL);
}

int add_edit_mark(char *fname, int mode, char *title, session_t* session)
{
    FILE *fp, *out;
    char buf[256];
    time_t now;
    char outname[STRLEN], timebuf[STRLEN];
    int step = 0;
    int added = 0;
    int asize;

    if ((fp = fopen(fname, "rb")) == NULL)
        return 0;
    sprintf(outname, "tmp/%d.editpost", (int)getpid());
    if ((out = fopen(outname, "w")) == NULL) {
        fclose(fp);
        return 0;
    }

    while ((asize = -attach_fgets(buf, 256, fp)) != 0) {
        if (asize <= 0) {
            if (mode & 2) {
                if (step != 3 && !strncmp(buf, "标  题: ", 8)) {
                    step = 3;
                    fprintf(out, "标  题: %s\n", title);
                    continue;
                }
            }

            if (!strncmp(buf, "\033[36m※ 修改:·", 15))
                continue;
            if (Origin2(buf) && (!added)) {
                now = time(0);
                if (mode & 1)
                    fprintf(out, "\033[36m※ 修改:·%s 于 %20.20s 修改本信·[FROM: %s]\033[m\n", session->currentuser->userid, ctime_r(&now, timebuf) + 4, SHOW_USERIP(session->currentuser, session->fromhost));
                else
                    fprintf(out, "\033[36m※ 修改:·%s 于 %20.20s 修改本文·[FROM: %s]\033[m\n", session->currentuser->userid, ctime_r(&now, timebuf) + 4, SHOW_USERIP(session->currentuser, session->fromhost));
                step = 3;
                added = 1;
            }
            fputs(buf, out);
        } else {
            /* 考虑从精华区转出的、没有来源且带附件的帖子 */
            if (!added) {
                added = 1;
                now = time(0);
                if (mode & 1)
                    fprintf(out, "\033[36m※ 修改:·%s 于 %20.20s 修改本信·[FROM: %s]\033[m\n", session->currentuser->userid, ctime_r(&now, timebuf) + 4, SHOW_USERIP(session->currentuser, session->fromhost));
                else
                    fprintf(out, "\033[36m※ 修改:·%s 于 %20.20s 修改本文·[FROM: %s]\033[m\n", session->currentuser->userid, ctime_r(&now, timebuf) + 4, SHOW_USERIP(session->currentuser, session->fromhost));
            }
            put_attach(fp, out, asize);
        }
    }
    if (!added) {
        now = time(0);
        if (mode & 1)
            fprintf(out, "\033[36m※ 修改:·%s 于 %20.20s 修改本信·[FROM: %s]\033[m\n", session->currentuser->userid, ctime_r(&now, timebuf) + 4, SHOW_USERIP(session->currentuser, session->fromhost));
        else
            fprintf(out, "\033[36m※ 修改:·%s 于 %20.20s 修改本文·[FROM: %s]\033[m\n", session->currentuser->userid, ctime_r(&now, timebuf) + 4, SHOW_USERIP(session->currentuser, session->fromhost));
    }
    fclose(fp);
    fclose(out);

    f_cp(outname, fname, O_TRUNC);
    unlink(outname);

    return 1;
}

const char *get_mime_type_from_ext(const char *ext)
{
    if (ext == NULL)
        return "text/plain";
    if (strcasecmp(ext, ".jpg") == 0 || strcasecmp(ext, ".jpeg") == 0)
        return "image/jpeg";
    if (strcasecmp(ext, ".gif") == 0)
        return "image/gif";
    if (strcasecmp(ext, ".png") == 0)
        return "image/png";
    if (strcasecmp(ext, ".pcx") == 0)
        return "image/pcx";
    if (strcasecmp(ext, ".au") == 0)
        return "audio/basic";
    if (strcasecmp(ext, ".wav") == 0)
        return "audio/wav";
    if (strcasecmp(ext, ".avi") == 0)
        return "video/x-msvideo";
    if (strcasecmp(ext, ".mov") == 0 || strcasecmp(ext, ".qt") == 0)
        return "video/quicktime";
    if (strcasecmp(ext, ".mpeg") == 0 || strcasecmp(ext, ".mpe") == 0)
        return "video/mpeg";
    if (strcasecmp(ext, ".vrml") == 0 || strcasecmp(ext, ".wrl") == 0)
        return "model/vrml";
    if (strcasecmp(ext, ".midi") == 0 || strcasecmp(ext, ".mid") == 0)
        return "audio/midi";
    if (strcasecmp(ext, ".mp3") == 0)
        return "audio/mpeg";
    if (strcasecmp(ext, ".txt") == 0)
        return "text/plain";
    if (strcasecmp(ext, ".xht") == 0 || strcasecmp(ext, ".xhtml") == 0)
        return "application/xhtml+xml";
    if (strcasecmp(ext, ".xml") == 0)
        return "text/xml";
    if (strcasecmp(ext, ".swf") == 0)
        return "application/x-shockwave-flash";
    if (strcasecmp(ext, ".pdf") == 0)
        return "application/pdf";
    if (strcasecmp(ext, ".html") == 0 || strcasecmp(ext, ".htm") == 0)
        return "text/html";
    if (strcasecmp(ext, ".css") == 0)
        return "text/css";
    return "application/octet-stream";
}
const char *get_mime_type(const char *filename)
{
    char *dot = strrchr(filename, '.');
    return (get_mime_type_from_ext(dot));
}
int get_attachment_type(const char *filename)
{
    const char * mime_type = get_mime_type(filename);
    if (strncmp(mime_type, "image/", strlen("image/")) == 0) {
        return ATTACH_IMG;
    }
    if (strcmp(mime_type, "application/x-shockwave-flash") == 0) {
        return ATTACH_FLASH;
    }
    return ATTACH_OTHERS;
}
int get_attachment_type_from_ext(const char *ext)
{
    const char * mime_type = get_mime_type_from_ext(ext);
    if (strncmp(mime_type, "image/", strlen("image/")) == 0) {
        return ATTACH_IMG;
    }
    if (strcmp(mime_type, "application/x-shockwave-flash") == 0) {
        return ATTACH_FLASH;
    }
    return ATTACH_OTHERS;
}


char *checkattach(char *buf, long size, long *len, char **attachptr)
{
    char *ptr;
    int att_size;
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
    memcpy(&att_size, buf, sizeof(int));
    *len = ntohl(att_size);
    if (*len > size)
        *len = size;
    *attachptr = buf + sizeof(int);
    return ptr;
}

/**
 * 一个能检测attach的fgets
 * 文件尾返回0,否则返回1
 */
int skip_attach_fgets(char *s, int size, FILE * stream)
{
    int matchpos = 0;
    int ch;
    char *ptr;
#ifdef ENABLE_LIKE
    int like_match=0;
#endif

    ptr = s;
    while ((ch = fgetc(stream)) != EOF) {
#ifdef ENABLE_LIKE
        if (ch == LIKE_PAD[like_match]) {
            like_match++;
            if(like_match==LIKE_PAD_SIZE) {
                while((ch=fgetc(stream))!=EOF) {
                    if(ch!='\n') {
                        fseek(stream, sizeof(struct like)-1, SEEK_CUR);
                    } else {
                        break; // LIKE完毕
                    }
                }
                like_match=0;
            }
            continue;
        }
#endif
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
#ifdef ENABLE_LIKE
    int like_match=0;
#endif

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
#ifdef ENABLE_LIKE
        if (ch==LIKE_PAD[like_match]) {
            like_match++;
            if(like_match==LIKE_PAD_SIZE) {
                int count=LIKE_PAD_SIZE+1; // LIKE_PAD + ... + \n
                while((ch=fgetc(stream))!=EOF) {
                    if(ch!='\n') {
                        count+=sizeof(struct like);
                        fseek(stream, sizeof(struct like)-1, SEEK_CUR);
                    } else {
                        break; // LIKE完毕
                    }
                }
                fseek(stream, -count, SEEK_CUR);
                return -count;
            }
            continue;
            /*
             * FIXME 有可能存在隐患的地方 windinsn
             * 这里有2个假设
             * 1. LIKE部分在附件之前，如果是extra.c -> add_user_like 加的可以保证
             * 2. LIKE之前的部分，即文章正文是不会出现LIKE_PAD内的字符，即 \034 \035 \036 的
             * 如果是LIKE部分，返回后与附件处理方式一致，即直接复制内容
             */
        }
#endif
        
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

    j = safe_mmapfile(ffn, O_RDONLY, PROT_READ, MAP_SHARED, &p, &fsize, NULL);
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
            if (j) { //为了计数和以前一致,姑且用j做判断bool了,想像它是eff_done_flag吧...
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
    } else
        return 0;
    end_mmapfile((void *) op, fsize, -1);
    return abssize;
}

int get_effsize(char *ffn)
{
    return get_effsize_attach(ffn, NULL);
}

/* 文章是否置顶 */
int is_top(struct fileheader *fh, const char *boardname)
{
    char filename[STRLEN];
    int i, bid;
    struct BoardStatus *bs;
    sprintf(filename, "%s", fh->filename);
    POSTFILE_BASENAME(filename)[0] = 'Z';
    bid = getbid(boardname, NULL);
    bs = getbstatus(bid);
    for (i=bs->toptitle;i>0;i--) {
        if (cmpname(&(bs->topfh[i-1]), filename))
            break;
    }
    return i;
}

/* 增加置顶文章*/
int add_top(struct fileheader *fileinfo, const char *boardname, int flag)
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
    if (get_num_records(dirpath, sizeof(top)) >= MAX_DING) {
        return 4;
    }
    if (is_top(fileinfo, boardname) > 0)
        return 5;
    link(newpath, path);
    append_record(dirpath, &top, sizeof(top));
    board_update_toptitle(getbid(boardname, NULL), true);
#ifdef BOARD_SECURITY_LOG
    board_security_report(NULL, getCurrentUser(), "置顶", boardname, fileinfo);
#endif
    return 0;
}

/*增加文摘*/
static int add_digest(struct fileheader *fileinfo, const char *boardname)
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
    if (get_num_records(dirpath, sizeof(digest)) >= MAX_DIGEST) {
        return 4;
    }
    link(newpath, path);
    append_record(dirpath, &digest, sizeof(digest));    /* 文摘目录下添加 .DIR */
    fileinfo->accessed[0] = fileinfo->accessed[0] | FILE_DIGEST;
    return 0;
}

/*删除文摘*/
static int dele_digest(char *dname, const char *boardname)
{                               /* 删除文摘内一篇POST, dname=post文件名,boardname版面名字 */
    char digest_name[STRLEN];
    char new_dir[STRLEN];
    char buf[STRLEN];
    struct fileheader fh;
    int pos;

    strcpy(digest_name, dname);
    setbdir(DIR_MODE_DIGEST, new_dir, boardname);

    POSTFILE_BASENAME(digest_name)[0] = 'G';
    //TODO: 这里也有个同步问题
    pos = search_record(new_dir, &fh, sizeof(fh), (RECORD_FUNC_ARG) cmpname, digest_name);      /* 文摘目录下 .DIR中 搜索 该POST */
    if (pos <= 0) {
        return 2;
    }
    delete_record(new_dir, sizeof(struct fileheader), pos, (RECORD_FUNC_ARG) cmpname, digest_name);
    setbfile(buf, boardname, digest_name);
    my_unlink(buf);
    return 0;
}

#ifdef FILTER
static int pass_filter(struct fileheader *fileinfo, const struct boardheader *board, session_t* session)
{
    int bid;
    const struct boardheader *bh=NULL;
    char boardname[STRLEN];
    int to_censor=0;
#ifdef NEWSMTH
    const struct boardheader *filter_bh=NULL;

    if ((!strcmp(board->filename, FILTER_BOARD)) || (board->flag&BOARD_CENSOR_FILTER))
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
            
            bid=fileinfo->o_bid;
            bh=getboard(bid);
            
            if (!bh)
                return -1;
#ifdef NEWSMTH
            if (!strcmp(board->filename, FILTER_BOARD) && bh->flag&BOARD_CENSOR) {
                /* 若来源是先审后发的版面，经过Filter审核后进入版面编审环节 windinsn, Sep 13, 2012 */
                sprintf(boardname, "%sFilter", bh->filename);
                bid=getbid(boardname, &filter_bh);
        
                if (bid<=0 || !filter_bh || !(filter_bh->flag&BOARD_CENSOR_FILTER))
                    return -1;
                boardname[0]='\0';
                strcpy(boardname, filter_bh->filename);
                
                to_censor=1;
            } else
#endif
            strcpy(boardname, bh->filename);
            
            if (!to_censor)
                fileinfo->accessed[1] |= FILE_CENSOR;
                
            setbfile(oldpath, board->filename, fileinfo->filename);
            setbfile(newpath, boardname, fileinfo->filename);
            f_cp(oldpath, newpath, 0);

            setbfile(dirpath, boardname, DOT_DIR);
            if ((filedes = open(dirpath, O_WRONLY | O_CREAT, 0664)) == -1) {
                return -1;
            }
            newfh = *fileinfo;
            writew_lock(filedes, 0, SEEK_SET, 0);
            nowid = get_nextid_bid(bid);
            newfh.id = nowid;
            if (fileinfo->o_id == fileinfo->o_groupid || to_censor)
                newfh.groupid = newfh.reid = newfh.id;
            else {
                newfh.groupid = fileinfo->o_groupid;
                newfh.reid = fileinfo->o_reid;
            }
            
            lseek(filedes, 0, SEEK_END);
            if (safewrite(filedes, &newfh, sizeof(fileheader)) == -1) {
                bbslog("user", "apprec write err! %s", newfh.filename);
            }
            un_lock(filedes, 0, SEEK_SET, 0);
            close(filedes);

            updatelastpost(boardname);
#if 0 /* 这个看起来是错误的 - atppp 20051228 */
#ifdef HAVE_BRC_CONTROL
            brc_add_read(newfh.id, session);
#endif
#endif
            if (newfh.id == newfh.groupid)
                setboardorigin(boardname, 1);
            setboardtitle(boardname, 1);
            if (newfh.accessed[0] & FILE_MARKED)
                setboardmark(boardname, 1);
                
            if (to_censor)
                fileinfo->accessed[1] |= FILE_CENSOR;    
#ifdef ENABLE_REFER
            else {
                send_refer_msg(boardname, &newfh, NULL, newpath);
            }
#endif
            
        }
    }
    return 0;
}
#endif                          /* FILTER */

/**
  设置fileheader的属性
  @param dirarg 需要操作的.DIR
  @param currmode 当前dir的模式
  @param board 当前版面
  @param fileinfo 文章结构
  @param flag 要操作的标志
  @param data 进行的操作数据。
  @param bmlog 是否做版主操作记录
  TODO: 检查调用这个函数的地方必须都检查版主权限
  @return 0 成功
              1 不能做操作
              2 找不到原文
              3 操作已完成
              4 文摘区(置顶区)满
              -1 文件打开错误
  */
int change_post_flag(struct write_dir_arg *dirarg, int currmode, const struct boardheader *board, struct fileheader *fileinfo, int flag, struct fileheader *data, bool dobmlog, session_t* session)
{
    char buf[MAXPATH];
    struct fileheader *originFh;
    int ret = 0;

    if (fileinfo && POSTFILE_BASENAME(fileinfo->filename)[0] == 'Z' && !(flag&FILE_NOREPLY_FLAG) && !(flag&FILE_ATTACHPOS_FLAG) && !(flag&FILE_EFFSIZE_FLAG) && !(flag&FILE_MODTITLE_FLAG)
#if defined(NEWSMTH) && !defined(SECONDSITE)
            && !(flag&FILE_EDIT_FLAG)
#endif
            )
        /*
         * 置顶的文章不能做操作
         * 可进行置底文章的标题修改、attachpos及eff_size的更新，此时 mode 为 DIR_MODE_ZHIDING
         * 记录置顶文章修改时间
         */
        return 1;

    /*
     * 在文摘区不能做文摘操作
     */
    if ((flag == FILE_DIGEST_FLAG) && (currmode == DIR_MODE_DIGEST))
        return 1;
    /*
     * if ((flag == FILE_DIGEST_FLAG) && (currmode != DIR_MODE_NORMAL))
     * return 1;
     */

    if (currmode == DIR_MODE_DELETED || currmode == DIR_MODE_JUNK || currmode == DIR_MODE_SELF
#ifdef BOARD_SECURITY_LOG
        /* 版面安全记录区不允许操作 */
            || currmode == DIR_MODE_BOARD
#endif
#ifdef HAVE_USERSCORE
        /* 版面积分变更记录区不允许操作 */
            || currmode == DIR_MODE_SCORE
#endif
            )
        /*
         * 在删除区，自删区不能做操作
         */
        return 1;

    if ((flag == FILE_MARK_FLAG || flag == FILE_DELETE_FLAG) && (!strcmp(board->filename, "syssecurity")
            || !strcmp(board->filename, FILTER_BOARD)))
        /*
         * 在过滤板，系统记录版面不能做mark,标记删除操作
         */
        return 1;               /* Leeward 98.03.29 */

    if (flag == FILE_TITLE_FLAG && currmode != DIR_MODE_NORMAL)
        /*
         * 在普通模式下才能修改主体
         */
        return 1;

#ifdef COMMEND_ARTICLE
    if (flag == FILE_COMMEND_FLAG && currmode != DIR_MODE_NORMAL)
        /*
         * 在普通模式下才能推荐
         */
        return 1;
#endif
    if (prepare_write_dir(dirarg, fileinfo, currmode) != 0)
        return 2;

    originFh = dirarg->fileptr + (dirarg->ent - 1);     /*新的fileheader */
    setbfile(buf, board->filename, originFh->filename);

    /*
     * add fen 处理
     */
#ifdef NEWSMTH
    if (flag & FILE_FEN_FLAG) {
        if (data->accessed[1] & FILE_FEN) {
            originFh->accessed[1] |= FILE_FEN;
            if (dobmlog)
                bmlog(session->currentuser->userid, board->filename, 15, 1);
        } else {
            originFh->accessed[1] &= ~FILE_FEN;
            if (dobmlog)
                bmlog(session->currentuser->userid, board->filename, 16, 1);
        }
    }
#endif /* NEWSMTH */

#if defined(NEWSMTH) && !defined(SECONDSITE)
    /* 记录文章修改时间 */
    if (flag & FILE_EDIT_FLAG) {
        originFh->edittime = time(0);
    }
#endif /* NEWSMTH */

    /*
     * mark 处理
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
     * 不可回复 处理
     */
    if (flag & FILE_NOREPLY_FLAG) {
        if (!strcmp(board->filename, SYSMAIL_BOARD)) {
            char ans[STRLEN];

            snprintf(ans, STRLEN, "〖%s〗 处理: %s", session->currentuser->userid, fileinfo->title);
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
        /*else
            originFh->accessed[1] &= ~FILE_COMMEND;*/
    }
#endif

    /*
     * 标记 处理
     */
    if (flag & FILE_SIGN_FLAG) {
        if (data->accessed[0] & FILE_SIGN) {
            originFh->accessed[0] |= FILE_SIGN;
            if (dobmlog)
                bmlog(session -> currentuser -> userid, board -> filename, 15, 1);
        } else {
            originFh->accessed[0] &= ~FILE_SIGN;
            if (dobmlog)
                bmlog(session -> currentuser -> userid, board -> filename, 16, 1);
        }
    }
    if (flag & FILE_PERCENT_FLAG) {
        if (data->accessed[0] & FILE_PERCENT) {
            originFh->accessed[0] |= FILE_PERCENT;
            if (dobmlog)
                bmlog(session -> currentuser -> userid, board -> filename, 15, 1);
        } else {
            originFh->accessed[0] &= ~FILE_PERCENT;
            if (dobmlog)
                bmlog(session -> currentuser -> userid, board -> filename, 16, 1);
        }
    }
    /*
     * 标记删除 处理
     */
    if (flag & FILE_DELETE_FLAG) {
        if (data->accessed[1] & FILE_DEL) {
            originFh->accessed[1] |= FILE_DEL;
            if (dobmlog)
                bmlog(session -> currentuser -> userid, board -> filename, 17, 1);
        } else {
            originFh->accessed[1] &= ~FILE_DEL;
            if (dobmlog)
                bmlog(session -> currentuser -> userid, board -> filename, 18, 1);
        }
    }

    /*
     * 收入文摘处理
     */
    if (flag & FILE_DIGEST_FLAG) {
        if (data->accessed[0] & FILE_DIGEST) {  /*设置DIGEST */
            if (dobmlog) {
                bmlog(session->currentuser->userid, board->filename, 3, 1);
                ret = add_digest(originFh, board->filename);
            } else {            /*其实这时候只需要改一下标志就够了 */
                originFh->accessed[0] |= FILE_DIGEST;
            }
        } else {                /* 如果已经是文摘的话，则从文摘中删除该post */
            originFh->accessed[0] = (originFh->accessed[0] & ~FILE_DIGEST);
            if (dobmlog) {
                bmlog(session->currentuser->userid, board->filename, 4, 1);
                ret = dele_digest(originFh->filename, board->filename);
            }
        }
    }
    /*
     * 修改标题
     */
    if (flag & FILE_MODTITLE_FLAG) {
        strcpy(buf, data->title);
        if (*buf != 0)
            strcpy(originFh->title, buf);
    }
    if (flag & FILE_MODMISC_FLAG) {
        if (data->accessed[1] & FILE_MAILBACK)
            originFh->accessed[1] |= FILE_MAILBACK;
        else
            originFh->accessed[1] &= ~FILE_MAILBACK;
        if (data->accessed[1] & FILE_TEX)
            originFh->accessed[1] |= FILE_TEX;
        else
            originFh->accessed[1] &= ~FILE_TEX;
        if (!memcmp(data->innflag, "SS", 2))
            originFh->innflag[0] = originFh->innflag[1] = 'S';
        else
            originFh->innflag[0] = originFh->innflag[1] = 'L';
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

            if (!ret && (!strcmp(board->filename, FILTER_BOARD)
#ifdef NEWSMTH
                        ||board->flag&BOARD_CENSOR_FILTER
#endif
                        )) {
                char ans[STRLEN];
                snprintf(ans, STRLEN, "〖%s〗处理: %s", session->currentuser->userid, fileinfo->title);
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
         * 需要更新.DIR文件
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
        un_lock(dirarg->fd, 0, SEEK_SET, 0);
#ifdef BOARD_SECURITY_LOG
    /* DIR_MODE_NORMAL中，记录下列标记操作 */
    if (currmode == DIR_MODE_NORMAL && (flag & FILE_MARK_FLAG || flag & FILE_DIGEST_FLAG || flag & FILE_NOREPLY_FLAG
            || flag & FILE_SIGN_FLAG || flag & FILE_DELETE_FLAG || flag & FILE_PERCENT_FLAG
#ifdef NEWSMTH
            || flag & FILE_FEN_FLAG
#endif
            ))
    {
        if (flag & FILE_MARK_FLAG) {
            if (data->accessed[0] & FILE_MARKED)
                sprintf(buf, "标m");
            else
                sprintf(buf, "去m");
        } else if (flag & FILE_DIGEST_FLAG) {
            if (data->accessed[0] & FILE_DIGEST)
                sprintf(buf, "标g");
            else
                sprintf(buf, "去g");
        } else if (flag & FILE_NOREPLY_FLAG) {
            if (data->accessed[1] & FILE_READ)
                sprintf(buf, "标;");
            else
                sprintf(buf, "去;");
        } else if (flag & FILE_SIGN_FLAG) {
            if (data->accessed[0] & FILE_SIGN)
                sprintf(buf, "标#");
            else
                sprintf(buf, "去#");
        } else if (flag & FILE_PERCENT_FLAG) {
            if (data->accessed[0] & FILE_PERCENT)
                sprintf(buf, "标%%");
            else
                sprintf(buf, "去%%");
        } else if (flag & FILE_DELETE_FLAG) {
            if (data->accessed[1] & FILE_DEL)
                sprintf(buf, "标X");
            else
                sprintf(buf, "去X");
        }
#ifdef NEWSMTH
        else if (flag & FILE_FEN_FLAG) {
            if (data->accessed[1] & FILE_FEN)
                sprintf(buf,"摘十大");
            else
                sprintf(buf,"恢复十大");
        }
#endif
        board_security_report(NULL, getCurrentUser(), buf, board->filename, originFh);
    }
#endif
    return ret;
}

/* etnlegend - 修改附件核心 */
long ea_dump(int fd_src,int fd_dst,long offset)
{
    char buf[2048];
    long length,ret,len;
    void *p;
    if (fd_src==fd_dst||offset<0)
        return -1;
    if (ftruncate(fd_dst,offset)==-1)
        return -1;
    lseek(fd_src,offset,SEEK_SET);
    lseek(fd_dst,offset,SEEK_SET);
    length=0;
    do {                                            //复制文件
        if ((ret=read(fd_src,buf,2048*sizeof(char)))>0)
            for (p=buf,len=ret;len>0&&ret!=-1;vpm(p,ret),len-=ret)
                length+=(ret=write(fd_dst,p,len));
    } while (ret>0);
    if (ret==-1)
        return -1;
    return length;
}
static long ea_parse(int fd,struct ea_attach_info *ai)
{
    char buf[8],c;
    int count,n;
    long ret,length,end;
    unsigned int size;
    if (!ai)
        return -2;
    bzero(ai,MAXATTACHMENTCOUNT*sizeof(struct ea_attach_info));
    count=0;length=lseek(fd,0,SEEK_CUR);
    end=lseek(fd,0,SEEK_END);lseek(fd,length,SEEK_SET);
    do {
        if ((ret=read(fd,buf,ATTACHMENT_SIZE*sizeof(char)))>0) {
            if (ret==ATTACHMENT_SIZE*sizeof(char)) {
                if (!memcmp(buf,ATTACHMENT_PAD,ATTACHMENT_SIZE*sizeof(char))) {
                    ai[count].offset=length;        //当前附件段起始位置
                    n=0;
                    do {                            //当前附件文件名称
                        if ((ret=read(fd,&c,sizeof(char)))>0) {
                            if (ret==sizeof(char)) {
                                ai[count].name[n++]=c;
                                if (!c||n>60)       //结束或达到附件文件名称长度限制 /* TODO: filenamename length - atppp*/
                                    break;
                            } else {
                                if (lseek(fd,0,SEEK_CUR)==end)
                                    break;
                                else
                                    lseek(fd,-ret,SEEK_CUR);
                            }
                        }
                    } while (ret>0);
                    if (ret==-1||!ret)
                        break;
                    do {                            //当前附件文件长度
                        if ((ret=read(fd,&size,sizeof(unsigned int)))>0) {
                            if (ret==sizeof(unsigned int)) {
                                ai[count].size=ntohl(size);
                                ai[count].length=((ATTACHMENT_SIZE+n)*sizeof(char)+
                                                  sizeof(unsigned int)+ai[count].size);
                                break;
                            } else {
                                if (lseek(fd,0,SEEK_CUR)==end)
                                    break;
                                else
                                    lseek(fd,-ret,SEEK_CUR);
                            }
                        }
                    } while (ret>0);
                    if (ret==-1||(length+ai[count].length)>end)
                        break;
                    length=lseek(fd,ai[count].size,SEEK_CUR);count++;
                    if (!(count<MAXATTACHMENTCOUNT))
                        break;
                } else
                    break;
            } else {
                if (lseek(fd,0,SEEK_CUR)==end)
                    break;
                else
                    lseek(fd,-ret,SEEK_CUR);
            }
        }
    } while (ret>0);
    if (ret==-1)
        return -1;
    return length;
}
long ea_locate(int fd,struct ea_attach_info *ai)
{
    char c;
    int n;
    long ret,offset,end;
    end=lseek(fd,0,SEEK_END);
    n=0;lseek(fd,0,SEEK_SET);
    do {                                            //定位附件标识 ATTACHMENT_PAD
        if ((ret=read(fd,&c,sizeof(char)))>0) {
            if (ret==sizeof(char)) {
                if (c==ATTACHMENT_PAD[n])
                    n++;
                else
                    n=0;
            } else {
                if (lseek(fd,0,SEEK_CUR)==end)
                    break;
                else
                    lseek(fd,-ret,SEEK_CUR);
            }
        }
    } while (ret>0&&n<ATTACHMENT_SIZE*sizeof(char));
    if (ret==-1)
        return -1;
    offset=lseek(fd,-(n*sizeof(char)),SEEK_CUR);
    ret=ea_parse(fd,ai);
    if (ret==-1)
        return -1;
    if (!(ret<0)&&ret!=end&&ftruncate(fd,ret)==-1)
        return -1;
    return offset;
}
static long ea_append_helper(int fd,struct ea_attach_info *ai,const char *fn,const char *original_filename)
{
    char buf[2048];
    const char *pos1, *pos2;
    int fd_recv,count;
    unsigned int size;
    long ret,len,end;
    void *p;
    if (!ai)
        return -1;
    for (count=0;count<MAXATTACHMENTCOUNT&&ai[count].name[0];count++)
        continue;
    if (count==MAXATTACHMENTCOUNT)                        //已达到数量上限
        return -1;
    if ((fd_recv=open(fn,O_RDONLY,0644))==-1)
        return -1;
    readw_lock(fd_recv, 0, SEEK_SET, 0);


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
        return -1;
    if (len > 60){
        original_filename += (len-60);
        if (original_filename[-1] & 0x80){
            original_filename++;
        }
    }
    strcpy(ai[count].name,original_filename);
    filter_upload_filename(ai[count].name);

    end=lseek(fd_recv,0,SEEK_END);ai[count].size=(unsigned int)end;
    ai[count].length=((ATTACHMENT_SIZE+strlen(ai[count].name)+1)*sizeof(char)
                      +sizeof(unsigned int)+ai[count].size);
    lseek(fd_recv,0,SEEK_SET);ai[count].offset=lseek(fd,0,SEEK_END);
    ret=0;
    for (p=ATTACHMENT_PAD,len=ATTACHMENT_SIZE*sizeof(char);len>0&&ret!=-1;vpm(p,ret),len-=ret)
        ret=write(fd,p,len);                        //写入附件标识 ATTACHMENT_PAD
    for (p=ai[count].name,len=(strlen(ai[count].name)+1)*sizeof(char);len>0&&ret!=-1;vpm(p,ret),len-=ret)
        ret=write(fd,p,len);                        //写入附件文件名称
    for (size=htonl(end),p=&size,len=sizeof(unsigned int);len>0&&ret!=-1;vpm(p,ret),len-=ret)
        ret=write(fd,p,len);                        //写入附件文件大小(网络字节序)
    while (ret>0) {                                 //写入附件文件内容
        if ((ret=read(fd_recv,buf,2048*sizeof(char)))>0)
            for (p=buf,len=ret;len>0&&ret!=-1;vpm(p,ret),len-=ret)
                ret=write(fd,p,len);
    }
    len=lseek(fd_recv,0,SEEK_CUR);
    un_lock(fd_recv, 0, SEEK_SET, 0);
    close(fd_recv);
    if (ret==-1||len!=end) {
        bzero(&ai[count],sizeof(struct ea_attach_info));
        if (ftruncate(fd,ai[count].offset)==-1)
            return -2;
        return -1;
    }
    return end;
}
long ea_append(int fd,struct ea_attach_info *ai,const char *fn,const char *original_filename)
{
    long ret = ea_append_helper(fd,ai,fn,original_filename);
    unlink(fn);
    return(ret);
}
long ea_delete(int fd,struct ea_attach_info *ai,int pos)
{
    int count,n;
    long ret,end;
    void *p;
    if (!ai)
        return -1;
    for (count=0;count<MAXATTACHMENTCOUNT&&ai[count].name[0];count++)
        continue;
    if (!count)                                     //无附件
        return -1;
    if (!(pos>0)||pos>count)                        //参数错误
        return -1;
    ret=ai[pos-1].size;
    if (pos==count) {                               //最后位置的附件
        if (ftruncate(fd,ai[pos-1].offset)==-1)
            return -1;
        bzero(&ai[pos-1],sizeof(struct ea_attach_info));
    } else {                                         //其它位置的附件
        end=lseek(fd,0,SEEK_END);
        p=mmap(NULL,end,PROT_READ|PROT_WRITE,MAP_SHARED,fd,0);
        if (p==(void*)-1)
            return -1;
        memmove(vpo(p,ai[pos-1].offset),vpo(p,ai[pos].offset),(end-ai[pos].offset));
        munmap(p,end);
        if (ftruncate(fd,end-ai[pos-1].length)==-1)
            return -2;
        for (n=pos;n<count;n++)
            ai[n].offset-=ai[pos-1].length;
        memmove(&ai[pos-1],&ai[pos],(count-pos)*sizeof(struct ea_attach_info));
        bzero(&ai[count-1],sizeof(struct ea_attach_info));
    }
    return ret;
}
/* 修改附件核心结束 */




int getattachtmppath(char *buf, size_t buf_len, session_t *session)
{
#if ! defined(FREE)
    /* setcachehomefile() 不接受 buf_len 参数，先直接这么写吧 */
    snprintf(buf,buf_len,"%s/home/%c/%s/%d/upload",TMPFSROOT,toupper(session->currentuser->userid[0]),
             session->currentuser->userid, session->utmpent);
#else
    snprintf(buf,buf_len,"%s/%s_%d",ATTACHTMPPATH,session->currentuser->userid,  session->utmpent);
#endif
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
                post_file->attachment = ftell(fp);
            }
            fwrite(ATTACHMENT_PAD, ATTACHMENT_SIZE, 1, fp);
            fwrite(name, strlen(name) + 1, 1, fp);
            BBS_TRY {
                if (safe_mmapfile_handle(fd,  PROT_READ, MAP_SHARED, &ptr, & size) == 0) {
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
            BBS_END;
            end_mmapfile((void *) ptr, size, -1);

            close(fd);
        }
        fclose(fp2);
    }
    f_rm(attachdir);
    return n;
}

int upload_read_fileinfo(struct ea_attach_info *ai, session_t *session)
{
    char buf[256];
    char attachdir[MAXPATH], attachfile[MAXPATH];
    FILE *fp2;
    int n=0;
    struct stat stat_buf;

    bzero(ai,MAXATTACHMENTCOUNT*sizeof(struct ea_attach_info));
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

int upload_del_file(const char *original_file, session_t *session)
{
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

static int upload_add_file_helper(const char *filename, char *original_filename, session_t *session)
{
    struct ea_attach_info ai[MAXATTACHMENTCOUNT];
    char attachdir[MAXPATH], attachfile[MAXPATH];
    FILE *fp;
    char buf[256];
    int i, n, len;
    int totalsize=0;
    char *pos1, *pos2;
    struct stat stat_buf;

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
    if (len > 60) {
        original_filename += (len-60);
        if (original_filename[-1] & 0x80) {
            original_filename++;
        }
    }
    filter_upload_filename(original_filename);

    for (i=0;i<n;i++) {
        if (strcmp(ai[i].name, original_filename) == 0) return -4;
        totalsize+=ai[i].size;
    }
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

int upload_add_file(const char *filename, char *original_filename, session_t *session)
{
    int ret = upload_add_file_helper(filename, original_filename, session);
    if (ret) unlink(filename);
    return(ret);
}

/* etnlegend, 2006.04.16, 区段删除核心 */

#define DRBP_MODE       0644
#define DRBP_LEN        _POSIX_PATH_MAX

int cancel_inn(const char *board,const struct fileheader *file)
{
    FILE *fp,*fp_bntp;
    char buf[DRBP_LEN],nick[DRBP_LEN],*p;
    int len;
    setbfile(buf,board,file->filename);
    if (!(fp=fopen(buf,"r")))
        return 1;
    if (!(fp_bntp=fopen("innd/cancel.bntp","a"))) {
        fclose(fp);
        return 2;
    }
    nick[0]=0;
    while (skip_attach_fgets(buf,DRBP_LEN,fp)) {
        buf[len=(strlen(buf)-1)]=0;
        if (len<8)
            break;
        if (!strncmp(buf,"发信人: ",8)) {
            if (!(p=strrchr(buf,')')))
                break;
            *p=0;
            if (!(p=strchr(buf,'(')))
                break;
            snprintf(nick,DRBP_LEN,"%s",&p[1]);
            break;
        }
    }
    snprintf(buf,DRBP_LEN,"%s\t%s\t%s\t%s\t%s\n",board,file->filename,file->owner,nick,file->title);
    fputs(buf,fp_bntp);
    fclose(fp);
    fclose(fp_bntp);
    return 0;
}

static inline int delete_range_cancel_post_mv(const char *board,struct fileheader *file)
{
    char buf[DRBP_LEN],obuf[DRBP_LEN],nbuf[DRBP_LEN];
    time_t currtime;
    setbfile(obuf,board,file->filename);
    !(file->filename[1]=='/')?(file->filename[0]='D'):(file->filename[2]='D');
    setbfile(nbuf,board,file->filename);
    currtime=time(NULL);
    if (file->innflag[0]=='S'&&file->innflag[1]=='S'&&(get_posttime(file)>(currtime-(14*86400))))
        cancel_inn(board,file);
    file->accessed[sizeof(((const struct fileheader*)NULL)->accessed)-1]=((currtime/86400)%100);
    if (getCurrentUser()) {
        snprintf(buf,ARTICLE_TITLE_LEN,"%-32.32s - %s",file->title,getCurrentUser()->userid);
        snprintf(file->title,ARTICLE_TITLE_LEN,"%s",buf);
    }
    return f_mv(obuf,nbuf);
}

static inline int delete_range_cancel_post_del(const char *board,struct fileheader *file)
{
    char buf[DRBP_LEN];
    setbfile(buf,board,file->filename);
    if (file->innflag[0]=='S'&&file->innflag[1]=='S'&&(get_posttime(file)>(time(NULL)-(14*86400))))
        cancel_inn(board,file);
    return unlink(buf);
}

static inline int delete_range_cancel_mail_mv(const char *user,struct fileheader *file)
{
    return 0;
}

static inline int delete_range_cancel_mail_del(const char *user,struct fileheader *file)
{
    struct stat st;
    char buf[DRBP_LEN];
    int ret;
    setmailfile(buf,user,file->filename);
    if (lstat(buf,&st)==-1||!S_ISREG(st.st_mode)||st.st_nlink>1)
        ret=0;
    else
        ret=(int)st.st_size;
    unlink(buf);
    return ret;
}

int delete_range_base(
    const char *videntity,              /* 操作对象标识, 即版面名称或用户名称 */
    const char *vdir_src,               /* 源 DIR 名称, 为 NULL 时默认为 DOT_DIR 宏设置的值 */
    const char *vdir_dst,               /* 目的 DIR 名称, 为 NULL 时直接删除文件 */
    int vid_from,                       /* 区段首文章或信件位置 */
    int vid_to,                         /* 区段尾文章或信件位置 */
    int vmode,                          /* 操作模式, DELETE_RANGE_BASE_MODE_ 系列宏的位或 */
    int (*func)(
        const char*,
        struct fileheader*
    ),                                  /* 对需要操作的文章执行的附加操作, 为 NULL 时执行默认操作 */
    const struct stat *vst_src          /* 用于校验文件修改的源 DIR 的文件状态描述, 为 NULL 时不进行校验 */
)
{
#define DRBP_CSRC       ((vmode&DELETE_RANGE_BASE_MODE_CHECK)&&vst_src)
#define DRBP_MAIL       (vmode&DELETE_RANGE_BASE_MODE_MAIL)
#define DRBP_DST        (vdir_dst&&(*vdir_dst)&&!(vmode&(DELETE_RANGE_BASE_MODE_MPDEL|DELETE_RANGE_BASE_MODE_CLEAR)))
#define DRBP_RSRC       do{munmap(pm,st_src.st_size);fcntl(fd_src,F_SETLKW,&lck_clr);close(fd_src);}while(0)
#define DRBP_RDST       do{fcntl(fd_dst,F_SETLKW,&lck_clr);close(fd_dst);}while(0)
#ifndef DELETE_RANGE_RESERVE_DIGEST
#define DRBP_MARKS(f)   ((f)->accessed[0]&(FILE_MARKED|FILE_PERCENT))
#else /* DELETE_RANGE_RESERVE_DIGEST */
#define DRBP_MARKS(f)   ((f)->accessed[0]&(FILE_MARKED|FILE_PERCENT|FILE_DIGEST))
#endif /* DELETE_RANGE_RESERVE_DIGEST */
#define DRBP_TOKEN(f)   ((f)->accessed[1]&FILE_DEL)
#define DRBP_CHK_T      (vmode&DELETE_RANGE_BASE_MODE_TOKEN)
#define DRBP_CHK_F      (DELETE_RANGE_BASE_MODE_TOKEN|DELETE_RANGE_BASE_MODE_MPDEL)
#define DRBP_CHK_M      (!((vmode&DELETE_RANGE_BASE_MODE_OVERM)&&(vmode&DRBP_CHK_F)))
#define DRBP_UNDEL(f)   ((DRBP_CHK_T&&!DRBP_TOKEN(f))||(DRBP_CHK_M&&DRBP_MARKS(f)))
#define DRBP_TSET(n)    do{tab[((n)>>3)]|=(1<<((n)&0x07));}while(0)
#define DRBP_TGET(n)    (tab[((n)>>3)]&(1<<((n)&0x07)))
#define DRBP_GSET(n)    do{gab[((n)>>3)]|=(1<<((n)&0x07));}while(0)
#define DRBP_GGET(n)    (gab[((n)>>3)]&(1<<((n)&0x07)))
    static const struct flock lck_set={.l_type=F_WRLCK,.l_whence=SEEK_SET,.l_start=0,.l_len=0,.l_pid=0};
    static const struct flock lck_clr={.l_type=F_UNLCK,.l_whence=SEEK_SET,.l_start=0,.l_len=0,.l_pid=0};
    struct userec *user;
    struct fileheader *src,*dst;
    struct stat st_src,st_dst;
    char path_src[DRBP_LEN],path_dst[DRBP_LEN];
    unsigned char *tab, *gab;
    int fd_src,fd_dst,count,total,reserved,id_from,id_to,i,j,n_src,n_dst,ret,len;
    void *pm,*p;
#ifdef BOARD_SECURITY_LOG
    struct fileheader *r_src;
#endif
    /* 合法性检测 */
    if (!videntity||!*videntity)
        return 0x10;
    if (!vdir_src||!*vdir_src)
        vdir_src=DOT_DIR;
    if (!(vid_from>0))
        return 0x11;
    /* 处理源 DIR 文件 */
    !DRBP_MAIL?setbfile(path_src,videntity,vdir_src):setmailfile(path_src,videntity,vdir_src);
    if (stat(path_src,&st_src)==-1||!S_ISREG(st_src.st_mode))
        return 0x20;
    if (DRBP_CSRC&&(st_src.st_mtime>vst_src->st_mtime))     /* 检测源 DIR 文件内容更新 */
        return 0x21;
    if ((fd_src=open(path_src,O_RDWR,DRBP_MODE))==-1)
        return 0x22;
    if (fcntl(fd_src,F_SETLKW,&lck_set)==-1) {
        close(fd_src);
        return 0x23;
    }
    if ((pm=mmap(NULL,st_src.st_size,PROT_READ|PROT_WRITE,MAP_SHARED,fd_src,0))==MAP_FAILED) {
        fcntl(fd_src,F_SETLKW,&lck_clr);
        close(fd_src);
        return 0x24;
    }
    /* 计算源参数 */
    total=st_src.st_size/sizeof(struct fileheader);
    id_from=(vid_from-1);
    id_to=((!(vid_to>total)?vid_to:total)-1);
    if (!((count=((id_to-id_from)+1))>0)) {
        DRBP_RSRC;
        return 0x30;
    }
    reserved=((total-count)-id_from);
    src=(struct fileheader*)pm;
    src+=id_from;
    /* 处理目的 DIR 文件 */
    if (DRBP_DST) {
        !DRBP_MAIL?setbfile(path_dst,videntity,vdir_dst):setmailfile(path_dst,videntity,vdir_dst);
        if (stat(path_dst,&st_dst)==-1) {
            if (errno==ENOENT)
                st_dst.st_size=0;
            else {
                DRBP_RSRC;
                return 0x40;
            }
        } else {
            if (!S_ISREG(st_dst.st_mode)) {
                DRBP_RSRC;
                return 0x41;
            }
        }
        if ((fd_dst=open(path_dst,O_WRONLY|O_CREAT|O_APPEND,DRBP_MODE))==-1) {
            DRBP_RSRC;
            return 0x42;
        }
        if (fcntl(fd_dst,F_SETLKW,&lck_set)==-1) {
            DRBP_RSRC;
            close(fd_dst);
            return 0x43;
        }
    } else
        fd_dst=-1;
    /* 处理区段操作 */
    switch (vmode&DELETE_RANGE_BASE_MODE_OPMASK) {
        case DELETE_RANGE_BASE_MODE_TOKEN:                  /* 删除拟删文章 */
        case DELETE_RANGE_BASE_MODE_RANGE:                  /* 常规区段删除 */
            /* 创建辅助表 */
            if (!(tab=(unsigned char*)malloc(((count>>3)+1)*sizeof(unsigned char))) || !(gab=(unsigned char*)malloc(((count>>3)+1)*sizeof(unsigned char)))) {
                DRBP_RDST;
                DRBP_RSRC;
                return 0x80;
            }
            memset(tab,0,((count>>3)+1)*sizeof(unsigned char));
            memset(gab,0,((count>>3)+1)*sizeof(unsigned char));
            /* 创建目的 DIR 缓存 */
            if (DRBP_DST) {
                if (!(dst=(struct fileheader*)malloc(count*sizeof(struct fileheader)))) {
                    DRBP_RDST;
                    DRBP_RSRC;
                    free(tab);
                    free(gab);
                    return 0x81;
                }
            } else
                dst=NULL;
#ifdef BOARD_SECURITY_LOG
            if (!DRBP_MAIL) {
                r_src = (struct fileheader *)malloc(count * sizeof(struct fileheader));
                memcpy(r_src, src, count * sizeof(struct fileheader));
            }
#endif
            /* 依据条件进行标记 */
            if (!func) {
                if (!DRBP_MAIL) {
                    if (DRBP_DST)
                        for (i=0;i<count;i++) {
                            if (DRBP_UNDEL(&src[i]))
                                continue;
                            delete_range_cancel_post_mv(videntity,&src[i]);
                            DRBP_TSET(i);
                            DRBP_GSET(i);
                        }
                    else
                        for (i=0;i<count;i++) {
                            if (DRBP_UNDEL(&src[i]))
                                continue;
                            delete_range_cancel_post_del(videntity,&src[i]);
                            DRBP_TSET(i);
                            DRBP_GSET(i);
                        }
                } else {
                    if (DRBP_DST)
                        for (i=0;i<count;i++) {
                            if (DRBP_UNDEL(&src[i]))
                                continue;
                            delete_range_cancel_mail_mv(videntity,&src[i]);
                            DRBP_TSET(i);
                            DRBP_GSET(i);
                        }
                    else {
                        for (ret=0,i=0;i<count;i++) {
                            if (DRBP_UNDEL(&src[i]))
                                continue;
                            ret+=delete_range_cancel_mail_del(videntity,&src[i]);
                            DRBP_TSET(i);
                            DRBP_GSET(i);
                        }
                        if (getuser(videntity,&user)) {
                            if (!(ret>user->usedspace))
                                user->usedspace-=ret;
                            else
                                get_mailusedspace(user,1);
                        }
                    }
                }
            } else
                for (i=0;i<count;i++) {
                    if (!func(videntity,&src[i]))
                        continue;
                    DRBP_TSET(i);
                }
            /* 处理目的 DIR 文件写入 */
            if (DRBP_DST) {
                /* 优化复制次数的内容复制 */
                for (n_dst=0,i=0,j=0;i<count;i++) {
                    if (DRBP_TGET(i)) {
                        j++;
                        continue;
                    }
                    if (j) {
                        memcpy(&dst[n_dst],&src[i-j],j*sizeof(struct fileheader));
                        n_dst+=j;
                        j=0;
                    }
                }
                if (j) {                                    /* 最后一块需要复制的数据 */
                    memcpy(&dst[n_dst],&src[count-j],j*sizeof(struct fileheader));
                    n_dst+=j;
                }
                /* 写入数据 */
                for (p=dst,len=n_dst*sizeof(struct fileheader),ret=0;len>0&&ret!=-1;vpm(p,ret),len-=ret)
                    ret=write(fd_dst,p,len);
                if (ret==-1) {
                    ftruncate(fd_dst,st_dst.st_size);
                    DRBP_RDST;
                    DRBP_RSRC;
                    free(tab);
                    free(gab);
                    free(dst);
#ifdef BOARD_SECURITY_LOG
                    if (!DRBP_MAIL)
                        free(r_src);
#endif
                    return 0x60;
                }
            }
            DRBP_RDST;
            /* 处理文摘区区段对应的原文 */
            if (strcmp(vdir_src,".DIGEST")==0) {
                struct write_dir_arg dirarg;
                struct fileheader xfh;
                const struct boardheader *bh;
                char buf[STRLEN];

                bh = getbcache(videntity);
                init_write_dir_arg(&dirarg);
                setbdir(DIR_MODE_NORMAL, buf, videntity);
                dirarg.filename = buf;
                for (n_src=0,i=0;i<count;i++) {
                    if (DRBP_GGET(i)) {
                        memcpy(&xfh, &src[i], sizeof(struct fileheader));
                        POSTFILE_BASENAME(xfh.filename)[0]='M';
                        change_post_flag(&dirarg, DIR_MODE_NORMAL, bh, &xfh, FILE_DIGEST_FLAG, &xfh, 0, getSession());
                    }
                }
                free_write_dir_arg(&dirarg);
            }
            /* 处理源 DIR 文件写入 */
            for (n_src=0,i=0,j=0;i<count;i++) {
                if (!DRBP_TGET(i)) {
                    j++;
                    src[i].accessed[1]&=~FILE_DEL;
                    continue;
                }
                if (j) {
                    memmove(&src[n_src],&src[i-j],j*sizeof(struct fileheader));
                    n_src+=j;
                    j=0;
                }
            }
            if (j) {
                memmove(&src[n_src],&src[count-j],j*sizeof(struct fileheader));
                n_src+=j;
            }
            memmove(&src[n_src],&src[count],reserved*sizeof(struct fileheader));
            /* 确定文件长度并同步映像数据 */
            if (ftruncate(fd_src,(st_src.st_size-(count-n_src)*sizeof(struct fileheader)))==-1) {
                DRBP_RSRC;
                free(tab);
                free(gab);
                free(dst);
#ifdef BOARD_SECURITY_LOG
                if (!DRBP_MAIL)
                    free(r_src);
#endif
                return 0x61;
            }
            if (msync(pm,(st_src.st_size-(count-n_src)*sizeof(struct fileheader)),MS_SYNC)==-1) {
                DRBP_RSRC;
                free(tab);
                free(gab);
                free(dst);
#ifdef BOARD_SECURITY_LOG
                if (!DRBP_MAIL)
                    free(r_src);
#endif
                return 0x62;
            }
#ifdef BOARD_SECURITY_LOG
            if (!DRBP_MAIL) {
                char filename[STRLEN], title[STRLEN], date[8];
                FILE *fn;
                gettmpfilename(filename, "range_delete");
                if ((fn=fopen(filename, "w"))!=NULL){
                    fprintf(fn, "\033[45;33m%s文章列表\033[K\033[m\n",
                            (vmode&DELETE_RANGE_BASE_MODE_OPMASK)==DELETE_RANGE_BASE_MODE_RANGE?"常规区段删除":"区段删除拟删");
                    fprintf(fn, "\033[44m文章ID号 作者         日期    标题\033[K\033[m\n");
                    for (i=0;i<count;i++) {
                        if (DRBP_TGET(i)) {
                            strncpy(date, ctime((time_t *)&r_src[i].posttime) + 4, 6);
                            date[6] = '\0';
                            fprintf(fn, "%8d %-12s %6s  %s%s\n", r_src[i].id, r_src[i].owner, date, r_src[i].id==r_src[i].groupid?"● ":"", r_src[i].title);
                        }
                    }
                    fclose(fn);
                }
                sprintf(title, "%s%s文章: [%d - %d]", (vmode&DELETE_RANGE_BASE_MODE_OPMASK)==DELETE_RANGE_BASE_MODE_RANGE?"常规区段删除":"区段删除拟删",
                        strcmp(vdir_src, ".DIR")==0?"版面":"文摘区", id_from+1, id_to+1);
                board_security_report(filename, getCurrentUser(), title, videntity, NULL);
                unlink(filename);
                free(r_src);
            }
#endif
            DRBP_RSRC;
            free(tab);
            free(gab);
            free(dst);
            return 0x00;
        case DELETE_RANGE_BASE_MODE_FORCE:                  /* 强制区段删除 */
#ifdef BOARD_SECURITY_LOG
            if (!DRBP_MAIL) {
                r_src = (struct fileheader *)malloc(count * sizeof(struct fileheader));
                memcpy(r_src, src, count * sizeof(struct fileheader));
            }
#endif
            if (!func) {
                if (!DRBP_MAIL) {
                    if (DRBP_DST)
                        for (i=0;i<count;i++)
                            delete_range_cancel_post_mv(videntity,&src[i]);
                    else
                        for (i=0;i<count;i++)
                            delete_range_cancel_post_del(videntity,&src[i]);
                } else {
                    if (DRBP_DST)
                        for (i=0;i<count;i++)
                            delete_range_cancel_mail_mv(videntity,&src[i]);
                    else {
                        for (ret=0,i=0;i<count;i++)
                            ret+=delete_range_cancel_mail_del(videntity,&src[i]);
                        if (getuser(videntity,&user)) {
                            if (!(ret>user->usedspace))
                                user->usedspace-=ret;
                            else
                                get_mailusedspace(user,1);
                        }
                    }
                }
            } else
                for (i=0;i<count;i++)
                    func(videntity,&src[i]);
            if (DRBP_DST) {
                for (p=src,len=count*sizeof(struct fileheader),ret=0;len>0&&ret!=-1;vpm(p,ret),len-=ret)
                    ret=write(fd_dst,p,len);
                if (ret==-1) {
                    ftruncate(fd_dst,st_dst.st_size);
                    DRBP_RDST;
                    DRBP_RSRC;
#ifdef BOARD_SECURITY_LOG
                    if (!DRBP_MAIL)
                        free(r_src);
#endif
                    return 0x70;
                }
            }
            DRBP_RDST;
            /* 处理文摘区区段对应的原文 */
            if (strcmp(vdir_src,".DIGEST")==0) {
                struct write_dir_arg dirarg;
                struct fileheader xfh;
                const struct boardheader *bh;
                char buf[STRLEN];

                bh = getbcache(videntity);
                init_write_dir_arg(&dirarg);
                setbdir(DIR_MODE_NORMAL, buf, videntity);
                dirarg.filename = buf;
                for (n_src=0,i=0;i<count;i++) {
                    memcpy(&xfh, &src[i], sizeof(struct fileheader));
                    POSTFILE_BASENAME(xfh.filename)[0]='M';
                    change_post_flag(&dirarg, DIR_MODE_NORMAL, bh, &xfh, FILE_DIGEST_FLAG, &xfh, 0, getSession());
                }
                free_write_dir_arg(&dirarg);
            }
            memmove(src,&src[count],reserved*sizeof(struct fileheader));
            if (ftruncate(fd_src,(st_src.st_size-count*sizeof(struct fileheader)))==-1) {
                DRBP_RSRC;
#ifdef BOARD_SECURITY_LOG
                if (!DRBP_MAIL)
                    free(r_src);
#endif
                return 0x71;
            }
            if (msync(pm,(st_src.st_size-count*sizeof(struct fileheader)),MS_SYNC)==-1) {
                DRBP_RSRC;
#ifdef BOARD_SECURITY_LOG
                if (!DRBP_MAIL)
                    free(r_src);
#endif
                return 0x72;
            }
#ifdef BOARD_SECURITY_LOG
            if (!DRBP_MAIL) {
                char filename[STRLEN], title[STRLEN], date[8];
                FILE *fn;
                gettmpfilename(filename, "range_delete");
                if ((fn=fopen(filename, "w"))!=NULL){
                    fprintf(fn, "\033[45;33m强制区段删除文章列表\033[K\033[m\n");
                    fprintf(fn, "\033[44m文章ID号 作者         日期    标题\033[K\033[m\n");
                    for (i=0;i<count;i++) {
                        strncpy(date, ctime((time_t *)&r_src[i].posttime) + 4, 6);
                        date[6] = '\0';
                        fprintf(fn, "%8d %-12s %6s  %s%s\n", r_src[i].id, r_src[i].owner, date, r_src[i].id==r_src[i].groupid?"● ":"", r_src[i].title);
                    }
                    fclose(fn);
                }
                sprintf(title, "强制区段删除%s文章: [%d - %d]", strcmp(vdir_src, ".DIR")==0?"版面":"文摘区", id_from+1, id_to+1);
                board_security_report(filename, getCurrentUser(), title, videntity, NULL);
                unlink(filename);
                free(r_src);
            }
#endif
            DRBP_RSRC;
            return 0x00;
        case DELETE_RANGE_BASE_MODE_MPDEL:                  /* 区段标记拟删 */
            for (i=0;i<count;i++)
                !DRBP_UNDEL(&src[i])?(src[i].accessed[1]|=FILE_DEL):(src[i].accessed[1]&=~FILE_DEL);
#ifdef BOARD_SECURITY_LOG
            if (!DRBP_MAIL) {
                char filename[STRLEN], title[STRLEN], date[8];
                FILE *fn;
                gettmpfilename(filename, "range_delete");
                if ((fn=fopen(filename, "w"))!=NULL){
                    fprintf(fn, "\033[45;33m区段标记拟删文章列表\033[K\033[m\n");
                    fprintf(fn, "\033[44m文章ID号 作者         日期    标题\033[K\033[m\n");
                    for (i=0;i<count;i++) {
                        if (!DRBP_UNDEL(&src[i])) {
                            strncpy(date, ctime((time_t *)&src[i].posttime) + 4, 6);
                            date[6] = '\0';
                            fprintf(fn, "%8d %-12s %6s  %s%s\n", src[i].id, src[i].owner, date, src[i].id==src[i].groupid?"● ":"", src[i].title);
                        }
                    }
                    fclose(fn);
                }
                sprintf(title, "区段标记%s拟删文章: [%d - %d]", strcmp(vdir_src, ".DIR")==0?"版面":"文摘区", id_from+1, id_to+1);
                board_security_report(filename, getCurrentUser(), title, videntity, NULL);
                unlink(filename);
            }
#endif
            DRBP_RDST;
            DRBP_RSRC;
            return 0x00;
        case DELETE_RANGE_BASE_MODE_CLEAR:                  /* 区段清除拟删 */
            for (i=0;i<count;i++)
                src[i].accessed[1]&=~FILE_DEL;
#ifdef BOARD_SECURITY_LOG
            if (!DRBP_MAIL) {
                char filename[STRLEN], title[STRLEN], date[8];
                FILE *fn;
                gettmpfilename(filename, "range_delete");
                if ((fn=fopen(filename, "w"))!=NULL){
                    fprintf(fn, "\033[45;33m区段清除拟删文章列表\033[K\033[m\n");
                    fprintf(fn, "\033[44m文章ID号 作者         日期    标题\033[K\033[m\n");
                    for (i=0;i<count;i++) {
                        strncpy(date, ctime((time_t *)&src[i].posttime) + 4, 6);
                        date[6] = '\0';
                        fprintf(fn, "%8d %-12s %6s  %s%s\n", src[i].id, src[i].owner, date, src[i].id==src[i].groupid?"● ":"", src[i].title);
                    }
                    fclose(fn);
                }
                sprintf(title, "区段清除%s拟删文章: [%d - %d]", strcmp(vdir_src, ".DIR")==0?"版面":"文摘区", id_from+1, id_to+1);
                board_security_report(filename, getCurrentUser(), title, videntity, NULL);
                unlink(filename);
            }
#endif
            DRBP_RDST;
            DRBP_RSRC;
            return 0x00;
        default:
            DRBP_RDST;
            DRBP_RSRC;
            return 0x90;
    }
#undef DRBP_CSRC
#undef DRBP_MAIL
#undef DRBP_DST
#undef DRBP_RSRC
#undef DRBP_RDST
#undef DRBP_MARKS
#undef DRBP_TOKEN
#undef DRBP_CHK_T
#undef DRBP_CHK_F
#undef DRBP_CHK_M
#undef DRBP_UNDEL
#undef DRBP_TSET
#undef DRBP_TGET
}

#undef DRBP_MODE
#undef DRBP_LEN

/* --END--, etnlegend, 2006.04.19, 区段删除核心 */

#ifdef BATCHRECOVERY
/*-------------------------------------区段恢复-------------------------------------------*/

/* 区段恢复主要操作。  benogy@bupt 20080807 */

/* 部分参数说明：
 * src:  .DELETED 或 .JUNK  因为区段只有版主或站务才能执行
 * mode: 恢复模式  1 是常规  2是强制
 * vst_src: 源文件的状态，用来检验是否有两个或以上的人同时操作，为NULL时不校验
 * 返回值说明：
 * 0 成功   1 打开文件失败   2  源文件变动  3 其他错误
 * */

int undelete_range_base(char* board, const char* src,int from,int to,int mode,struct stat *vst_src)
{
#define RELES_FILE  do{fcntl(fd_src,F_SETLKW,&lck_clr);fcntl(fd_dst,F_SETLKW,&lck_clr);close(fd_src);close(fd_dst);}while(0)

   static const struct flock lck_set={.l_type=F_WRLCK,.l_whence=SEEK_SET,.l_start=0,.l_len=0,.l_pid=0};
   static const struct flock lck_clr={.l_type=F_UNLCK,.l_whence=SEEK_SET,.l_start=0,.l_len=0,.l_pid=0};
   struct fileheader *fh_src,tmpfile;
   struct stat st_src,st_dst;
   char path_src[256],path_dst[256];
   int fd_src,fd_dst,total_dst,num,i;
   void *pm_src,*pm_dst;
#ifdef BOARD_SECURITY_LOG
   char filename[STRLEN], title[STRLEN], date[8];
   FILE *fn;
   gettmpfilename(filename, "range_undelete");
#endif

   if(mode!=1 && mode!=2)
      return 3;

   sprintf(path_src,"boards/%s/%s",board,src);
   sprintf(path_dst,"boards/%s/.DIR",board);/* 默认恢复的目的地是.DIR */

   if(stat(path_src,&st_src)==-1 || !S_ISREG(st_src.st_mode) ||
         stat(path_dst,&st_dst)==-1 || !S_ISREG(st_dst.st_mode))
      return 1;/* 不存在该文件 */

   if(vst_src!=NULL && st_src.st_mtime > vst_src->st_mtime)
      return 2;/* 源文件有更新 */

   fd_src=open(path_src,O_RDWR | O_CREAT,0644);
   fd_dst=open(path_dst,O_RDWR | O_CREAT,0644);

   if(fd_src==-1 || fd_dst==-1)
      return 1;/* 打开文件失败 */

   total_dst=st_dst.st_size/sizeof(struct fileheader);
   --from;
   --to;
   num=to-from+1;/* 操作文章的数目 */

   if(from<0 || from>to || to>=(st_src.st_size/sizeof(struct fileheader))){
      close(fd_src);
      close(fd_dst);
      return 3;/* 其它错误 */
   }

   if(fcntl(fd_src,F_SETLKW,&lck_set)==-1){
      close(fd_src);
      return 1;
   }
   if(fcntl(fd_dst,F_SETLKW,&lck_set)==-1){
      fcntl(fd_src,F_SETLKW,&lck_clr);
      close(fd_dst);
      return 1;
   }

   if((pm_src=mmap(NULL,st_src.st_size,PROT_READ|PROT_WRITE,MAP_SHARED,fd_src,0))==MAP_FAILED){
      RELES_FILE;
      return 3;
   }

   if(ftruncate(fd_dst,st_dst.st_size+num*sizeof(struct fileheader))==-1){/* .DIR扩容 */
      munmap(pm_src,st_src.st_size);
      RELES_FILE;
      return 3;
   }
   stat(path_dst,&st_dst);/* 重新获取信息 */
   if((pm_dst=mmap(NULL,st_dst.st_size,PROT_READ|PROT_WRITE,MAP_SHARED,fd_dst,0))==MAP_FAILED){
      ftruncate(fd_dst,st_dst.st_size-num*sizeof(struct fileheader));
      munmap(pm_src,st_src.st_size);
      RELES_FILE;
      return 3;
   }

   fh_src=(struct fileheader*)pm_src+from;/* 要恢复的第一篇文章 */

#ifdef BOARD_SECURITY_LOG
   fn = fopen(filename, "w");
   if (fn) {
       fprintf(fn, "\033[45;33m%s区段恢复文章列表\033[K\033[m\n", mode==1?"常规":"强制");
       fprintf(fn, "\033[44m文章ID号 作者         日期    标题\033[K\033[m\n");
   }
#endif
   if(mode == 1) /* 常规区段恢复 */
      for(i=num;i>0;--i,++fh_src){
         tmpfile=*fh_src;
         if((!(tmpfile.accessed[1]&FILE_DEL)) && (undelete_change_file_attr(board,&tmpfile)==0)){
            undelete_insert_file(&tmpfile,(struct fileheader*)pm_dst,&total_dst);
            fh_src->filename[0]='\0';
#ifdef BOARD_SECURITY_LOG
            if (fn) {
                strncpy(date, ctime((time_t *)&tmpfile.posttime) + 4, 6);
                date[6] = '\0';
                fprintf(fn, "%8d %-12s %6s  %s%s\n", tmpfile.id, tmpfile.owner, date, tmpfile.id==tmpfile.groupid?"● ":"", tmpfile.title);
            }
#endif
         }
      }

   else /* 强制区段恢复 */
      for(i=num;i>0;--i,++fh_src){
         tmpfile=*fh_src;
         if(undelete_change_file_attr(board,&tmpfile)==0){
            undelete_insert_file(&tmpfile,(struct fileheader*)pm_dst,&total_dst);
            fh_src->filename[0]='\0';
#ifdef BOARD_SECURITY_LOG
            if (fn) {
                strncpy(date, ctime((time_t *)&tmpfile.posttime) + 4, 6);
                date[6] = '\0';
                fprintf(fn, "%8d %-12s %6s  %s%s\n", tmpfile.id, tmpfile.owner, date, tmpfile.id==tmpfile.groupid?"● ":"", tmpfile.title);
            }
#endif
         }
      }

#ifdef BOARD_SECURITY_LOG
   if (fn)
       fclose(fn);
#endif
   if(ftruncate(fd_dst,total_dst*sizeof(struct fileheader))==-1){
      munmap(pm_dst,st_dst.st_size);
      munmap(pm_src,st_src.st_size);
      RELES_FILE;
#ifdef BOARD_SECURITY_LOG
      unlink(filename);
#endif
      return 3;
   }
   if(msync(pm_dst,total_dst*sizeof(struct fileheader),MS_SYNC)==-1){
      munmap(pm_dst,st_dst.st_size);
      munmap(pm_src,st_src.st_size);
      RELES_FILE;
#ifdef BOARD_SECURITY_LOG
      unlink(filename);
#endif
      return 3;
   }

   if(msync(pm_src,st_src.st_size/sizeof(struct fileheader),MS_SYNC)==-1){
      munmap(pm_src,st_src.st_size);
      munmap(pm_dst,st_dst.st_size);
      RELES_FILE;
#ifdef BOARD_SECURITY_LOG
      unlink(filename);
#endif
      return 3;
   }

   munmap(pm_src,st_src.st_size);
   munmap(pm_dst,st_dst.st_size);
   RELES_FILE;

#ifdef BOARD_SECURITY_LOG
   sprintf(title, "%s区段恢复删除区文章: [%d - %d]", mode==1?"常规":"强制", from+1, to+1);
   board_security_report(filename, getCurrentUser(), title, board, NULL);
   unlink(filename);
#endif
   return 0;

#undef RELES_FILE
}

/*----------------------------------------end  区段恢复------------------------------------*/

#endif /*BATCHRECOVERY*/
/* 主题回复数统计，pig2532 */


/* 主题回复数统计，pig2532 */

#ifdef HAVE_REPLY_COUNT

struct modify_reply_arg {
    int value;
    int mode;
    struct fileheader *lastpost;
};

static int update_reply_count(int fd, fileheader_t* base, int ent, int total, bool match, void* arg)
{
    if (match) {
        struct modify_reply_arg *marg;
        struct fileheader *fh;
        marg = (struct modify_reply_arg *)arg;
        fh = &base[ent - 1];
        if (marg->mode == 0)
            fh->replycount += marg->value;
        else
            fh->replycount = marg->value;
        if (marg->lastpost) {
            strcpy(fh->last_owner, marg->lastpost->owner);
            fh->last_posttime = get_posttime(marg->lastpost);
        }
    }
    return 0;
}

int modify_reply_count(const char* bname, int gid, int value, int mode, struct fileheader* lastpost)
{
    char dirpath[PATHLEN];
    int fd;
    struct fileheader fh;
    struct modify_reply_arg arg;

    setbdir(DIR_MODE_NORMAL, dirpath, bname);
    fd = open(dirpath, O_RDWR, 0644);
    if (fd < 0)
        return 0;

    fh.id = gid;
    arg.value = value;
    arg.mode = mode;
    arg.lastpost = lastpost;
    mmap_dir_search(fd, &fh, update_reply_count, &arg);
    close(fd);

    setbdir(DIR_MODE_ORIGIN, dirpath, bname);
    fd = open(dirpath, O_RDWR, 0644);
    if (fd < 0)
        return 0;

    mmap_dir_search(fd, &fh, update_reply_count, &arg);
    close(fd);

    return 0;
}

int refresh_reply_count(const char* bname, int gid)
{
    int fd, count, i, replycount;
    struct stat buf;
    struct fileheader *ptr, *lastpost, lastfh;
    char *head, dirpath[PATHLEN];

    setbdir(DIR_MODE_NORMAL, dirpath, bname);
    fd = open(dirpath, O_RDWR, 0644);
    if (fd < 0)
        return 0;

    replycount = -1;
    lastpost = NULL;
    BBS_TRY {
        if (!safe_mmapfile_handle(fd, PROT_READ | PROT_WRITE, MAP_SHARED, &head, &buf.st_size)) {
            close(fd);
            BBS_RETURN(0);
        }
        count = buf.st_size / sizeof(struct fileheader);
        ptr = (struct fileheader *)head;
        replycount = 0;
        for (i=0; i<count; i++) {
            if (ptr->groupid == gid) {
                replycount++;
                lastpost = ptr;
            }
            ptr++;
        }
    }
    BBS_CATCH {
    }
    BBS_END;

    if (lastpost)
        memcpy(&lastfh, lastpost, sizeof(struct fileheader));

    end_mmapfile((void *)head, buf.st_size, -1);
    close(fd);

    if (replycount >= 0)
        modify_reply_count(bname, gid, replycount, 1, &lastfh);

    return 0;
}

#endif /* HAVE_REPLY_COUNT */

