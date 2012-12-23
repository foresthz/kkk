/*
 * bm的操作函数
 */

#include "bbs.h"

/* 获取txt文件的内容，并返回字符串数组
 * 要确保result数组不大于maxcount，调用完成后caller需要free(*ptr)
 * return:
 *        0 文件不存在或错误
 *        count 字符串数
 */
int get_textfile_string(const char *file, char **ptr, char *result[], int maxcount)
{
    int fd;
    int count, size;
    struct stat st;
    char *p, *q;
    
    if ((fd=open(file, O_RDONLY))==-1)
        return 0;
    if (fstat(fd, &st)!=0 || st.st_size==0) {
        close(fd);
        return 0;
    }
    *ptr = (char *)malloc(st.st_size);
    read(fd, *ptr, st.st_size);
    count = 0;
    q = p = *ptr;
    if (*p!='#' && *p!='\n') /* 跳过注释行和长度为0的行 */
        result[count++] = p;
    size = st.st_size;
    while (size>0 && (p=(char *)memmem(p, size, "\n", 1))!=NULL) {
        *p = '\0';
        p++;
        if (*p == '\r')
            p++;
        size -= p-q;
        if (size<=0 || count>=maxcount)
            break;
        q = p;
        if (*p!='#' && *p!='\n') /* 跳过注释行和长度为0的行 */
            result[count++] = p;
    }
    /*
    for (i=1;i<st.st_size;i++) {
        if ((*ptr)[i] == '\n') {
            (*ptr)[i] = '\0';
            i++;
            count++;
            if (count>=maxcount)
                break;
            if (i==st.st_size)
                break;
            if ((*ptr)[i] == '\r') {
                i++;
                if (i==st.st_size)
                    break;
                result[count] = &((*ptr)[i]);
            } else {
                result[count] = &((*ptr)[i]);
            }
        }
    }
    */
    close(fd);
    return count;
}

/*
 * 获得封禁理由列表，denyreason在调用时定义
 * board为空时，获得系统封禁理由列表 
 */
int get_deny_reason(const char *board, char denyreason[][STRLEN], int max)
{
    char filename[STRLEN];
    char *ptr, *reason[MAXDENYREASON];
    int count, i;

    max = (max>MAXDENYREASON) ? MAXDENYREASON : max;
    if (board)
        setvfile(filename, board, "deny_reason");
    else
        sprintf(filename, "etc/deny_reason");
    count = get_textfile_string(filename, &ptr, reason, max);
    if (count > 0) {
        for (i=0;i<count;i++) {
            remove_blank_ctrlchar(reason[i], denyreason[i], true, true, true);
            denyreason[i][30] = '\0';
            //strcpy(denyreason[i], reason[i]);
        }
        free(ptr);
    }
    return count;
}

int save_deny_reason(const char *board, char denyreason[][STRLEN], int count)
{
    char filename[STRLEN];
    int i;
    FILE *fn;

    setvfile(filename, board, "deny_reason");

#ifdef BOARD_SECURITY_LOG
    char oldfilename[STRLEN];
    gettmpfilename(oldfilename, "old_deny_reason");
    f_cp(filename, oldfilename, 0); 
#endif

    if ((fn=fopen(filename, "w"))!=NULL) {
        for (i=0;i<count;i++)
            fprintf(fn, "%s\n", denyreason[i]);
        fclose(fn);
#ifdef BOARD_SECURITY_LOG
        board_file_report(board, "修改 <版面封禁理由>", oldfilename, filename);
        unlink(oldfilename);
#endif
    }
    return 0;
}

/* 从封禁文件读取的字符串中获得封禁理由, 与文件内容格式关系紧密 */
int get_denied_reason(const char *buf, char *reason)
{
    char genbuf[STRLEN], *p;

    strcpy(genbuf, buf);
    p = genbuf + IDLEN;
    genbuf[42] = '\0';
    remove_blank_ctrlchar(p, reason, true, true, false);
    return 0;
}

/* 从封禁文件读取的字符串中获得封禁操作ID, 与文件内容格式关系紧密 */
int get_denied_operator(const char *buf, char *opt)
{
    char genbuf[STRLEN], *p;

    strcpy(genbuf, buf);
    p = genbuf+43;
    p[IDLEN] = '\0';
    remove_blank_ctrlchar(p, opt, true, true, false);
    return 0;
}

#ifdef MANUAL_DENY
/* 从封禁文件读取的字符串中获得自动解封/手动解封选项，与文件内容格式关系紧密 */
int get_denied_freetype(const char *buf)
{
    return (!strncmp(buf+64, "解", 2));    
}
#endif

/* 从封禁文件读取的字符串中获取封禁时间 */
time_t get_denied_time(const char *buf)
{
    time_t denytime;
    char genbuf[STRLEN], *p;

    strcpy(genbuf, buf);
    if (NULL != (p = strrchr(genbuf, '[')))
        sscanf(p + 1, "%lu", &denytime);
    else
        denytime = time(0) + 1;
    return denytime;
}

/* 使用模板发布封禁公告
 * mode:
 *      0:添加
 *      1:修改
 */
#ifdef RECORD_DENY_FILE
int deny_announce(char *uident, const struct boardheader *bh, char *reason, int day, struct userec *operator, time_t time, int mode, const struct fileheader *fh, int filtermode)
#else
int deny_announce(char *uident, const struct boardheader *bh, char *reason, int day, struct userec *operator, time_t time, int mode)
#endif
{
    struct userec *lookupuser;
    char tmplfile[STRLEN], postfile[STRLEN], title[STRLEN], title1[STRLEN], timebuf[STRLEN];
    int sysop;
#ifdef MEMBER_MANAGER
	int core_member=0;
#endif	

    gettmpfilename(postfile, "ann_deny.%d", getpid());
    sprintf(tmplfile, "etc/denypost_template");

    if ((HAS_PERM(operator, PERM_SYSOP) || HAS_PERM(operator, PERM_OBOARDS))
            && !chk_BM_instr(bh->BM, operator->userid))
        sysop = 1;
    else {
        sysop = 0;
#ifdef MEMBER_MANAGER
		if (!HAS_PERM(operator, PERM_SYSOP)&&!chk_currBM(bh->BM, operator))
			core_member=1;
#endif		
	}
	
    getuser(uident, &lookupuser);
    if (mode==0) {
        sprintf(title, "%s被取消在%s版的发文权限", uident, bh->filename);
        if (PERM_BOARDS & lookupuser->userlevel)
            sprintf(title1, "%s 封某版" NAME_BM " %s 在 %s", operator->userid, uident, bh->filename);
        else
            sprintf(title1, "%s 封 %s 在 %s", operator->userid, uident, bh->filename);
    } else {
        sprintf(title, "修改%s在%s版的发文权限封禁", uident, bh->filename);
        if (PERM_BOARDS & lookupuser->userlevel)
            sprintf(title1, "%s 修改某版" NAME_BM " %s 在 %s 的封禁", operator->userid, uident, bh->filename);
        else
            sprintf(title1, "%s 修改 %s 在 %s 的封禁", operator->userid, uident, bh->filename);
    }
    if (dashf(tmplfile)) {
        char daystr[4], opbuf[STRLEN];
        sprintf(daystr, "%d", day);
        if (sysop)
            sprintf(opbuf, "%s" NAME_SYSOP_GROUP DENY_NAME_SYSOP "：\x1b[4m%s\x1b[m",  NAME_BBS_CHINESE, operator->userid);
#ifdef MEMBER_MANAGER
		else if (core_member)
			sprintf(opbuf, NAME_CORE_MEMBER ":\x1b[4m%s\x1b[m", operator->userid);
#endif
		else
            sprintf(opbuf, NAME_BM ":\x1b[4m%s\x1b[m", operator->userid);
        if (write_formatted_file(tmplfile, postfile, "ssssss",
                    uident, bh->filename, reason, daystr, opbuf, ctime_r(&time, timebuf))<0)
            return -1;
    } else {
        FILE *fn;
        fn = fopen(postfile, "w+");
        fprintf(fn, "由于 \x1b[4m%s\x1b[m 在 \x1b[4m%s\x1b[m 版的 \x1b[4m%s\x1b[m 行为，\n", uident, bh->filename, reason);
        fprintf(fn, DENY_BOARD_AUTOFREE " \x1b[4m%d\x1b[m 天。\n", day);
        /* day不允许0天，有需要的自行改造 */
        /*
        if (day)
            fprintf(fn, DENY_BOARD_AUTOFREE " \x1b[4m%d\x1b[m 天。\n", day);
        else
            fprintf(fn, DENY_BOARD_NOAUTOFREE "\n");
        */
#ifdef ZIXIA
        GetDenyPic(fn, DENYPIC, ndenypic, dpcount);
#endif 
        if (sysop) {
            fprintf(fn, "                            %s" NAME_SYSOP_GROUP DENY_NAME_SYSOP "：\x1b[4m%s\x1b[m\n", NAME_BBS_CHINESE, operator->userid);
        }
#ifdef MEMBER_MANAGER
		else if (core_member) {
			fprintf(fn, "                              " NAME_CORE_MEMBER ":\x1b[4m%s\x1b[m\n", operator->userid);
		}
#endif		
		else {
            fprintf(fn, "                              " NAME_BM ":\x1b[4m%s\x1b[m\n", operator->userid);
        }
        fprintf(fn, "                              %s\n", ctime_r(&time, timebuf));
        fclose(fn);
    }
#ifdef NEWSMTH
	post_file(operator, "", postfile, bh->filename, title, 0, 1 /*core_member?2:1*/, getSession());
#else
    post_file(operator, "", postfile, bh->filename, title, 0, 2, getSession());
#endif
#ifdef RECORD_DENY_FILE
    if (fh) {
        char bname[STRLEN], filebuf[STRLEN], filestr[256];
        FILE *fn, *fn2;
        int size;
        
        fn = fopen(postfile, "r+");
        fseek(fn, 0, SEEK_END);
        if (filtermode==0)
            sprintf(bname, "%s", bh->filename);
        else if (filtermode==1)
            sprintf(bname, "%s", "Filter");
        else
            sprintf(bname, "%sFilter", bh->filename);
        setbfile(filebuf, bname, fh->filename);
        if (!dashf(filebuf)) {
            int i;
            char prefix[4]="MDJ", *p, ch;
            p = strrchr(filebuf, '/') + 1;
            ch = *p;
            for (i=0;i<3;i++) {
                if (ch == prefix[i])
                    continue;
                *p = prefix[i];
                if (dashf(filebuf))
                    break;
            }
        }
        if ((fn2=fopen(filebuf, "r"))==NULL) {
            fprintf(fn, "\033[1;31;45m系统问题, 无法显示全文, 请联系技术站务. \033[K\033[m\n");
        } else {
            fprintf(fn, "\033[1;33;45m以下是被封文章全文");
            if (filtermode)
                fprintf(fn, "(来源于\033[32m%s\033[33m过滤区)", filtermode==1?"系统":bh->filename);
            fprintf(fn, ":\033[K\033[m\n");
            while ((size=-attach_fgets(filestr,256,fn2))) {
                if (size<0)
                    fprintf(fn,"%s",filestr);
                else
                    put_attach(fn2,fn,size);
            }
            fclose(fn2);
            fprintf(fn, "\033[1;33;45m全文结束.\033[K\033[m\n");
        }
        fclose(fn);
    }
#endif
    post_file(operator, "", postfile, "denypost", title1, 0, -1, getSession());
#ifdef BOARD_SECURITY_LOG
    FILE *fn;
    fn = fopen(postfile, "w");
    fprintf(fn, "\033[33m封禁用户: \033[4;32m%s\033[m\n", uident);
    fprintf(fn, "\033[33m封禁原因: \033[4;32m%s\033[m\n", reason);
    fprintf(fn, "\033[33m封禁天数: \033[4;32m %d 天\033[m\n", day);
    fclose(fn);
    sprintf(title, "%s %s 在本版的发文权限", mode==0?"取消":"修改", uident);
#ifdef RECORD_DENY_FILE
    board_security_report(postfile, operator, title, bh->filename, fh);
#else
    board_security_report(postfile, operator, title, bh->filename, NULL);
#endif
#endif
    unlink(postfile);

    return 0;
}

/* 使用模板发布封禁邮件
 * mode:
 *      0:添加
 *      1:修改
 */
int deny_mailuser(char *uident, const struct boardheader *bh, char *reason, int day, struct userec *operator, time_t time, int mode, int autofree)
{
    struct userec *lookupuser;
    char tmplfile[STRLEN], mailfile[STRLEN], title[STRLEN], timebuf[STRLEN];
    int sysop;

#ifdef MEMBER_MANAGER
	int core_member=0;
#endif
	
    gettmpfilename(mailfile, "mail_deny.%d", getpid());
    sprintf(tmplfile, "etc/denymail_template");

    if ((HAS_PERM(operator, PERM_SYSOP) || HAS_PERM(operator, PERM_OBOARDS))
            && !chk_BM_instr(bh->BM, operator->userid))
        sysop = 1;
    else {
        sysop = 0;
#ifdef MEMBER_MANAGER
		if (!HAS_PERM(operator, PERM_SYSOP)&&!chk_currBM(bh->BM, operator))
			core_member=1;
#endif	
	}
	
    getuser(uident, &lookupuser);
    if (mode==0) {
        sprintf(title, "%s被取消在%s版的发文权限", uident, bh->filename);
    } else {
        sprintf(title, "修改%s在%s版的发文权限封禁", uident, bh->filename);
    }
    if (dashf(tmplfile)) {
        char sender[STRLEN], sitename[STRLEN], opfrom[STRLEN], daystr[16], opbuf[STRLEN], replyhint[STRLEN];
        sprintf(daystr, "\x1b[4m%d\x1b[m 天", day);
        if (!autofree)
            sprintf(replyhint, "，到期后请回复\n此信申请恢复权限");
        else
            replyhint[0] = '\0';
        if (sysop) {
            sprintf(sender, "SYSOP (%s) ", NAME_SYSOP);
            sprintf(sitename, "%s (%24.24s)", BBS_FULL_NAME, ctime_r(&time, timebuf));
            sprintf(opfrom, "%s", NAME_BBS_ENGLISH);
            sprintf(opbuf, "%s" NAME_SYSOP_GROUP DENY_NAME_SYSOP "：\x1b[4m%s\x1b[m",NAME_BBS_CHINESE, operator->userid);
        } else {
            sprintf(sender, "%s ", operator->userid);
            sprintf(sitename, "%s (%24.24s)", BBS_FULL_NAME, ctime_r(&time, timebuf));
            sprintf(opfrom, "%s", SHOW_USERIP(operator, getSession()->fromhost));
#ifdef MEMBER_MANAGER
			if (core_member)
			sprintf(opbuf, NAME_CORE_MEMBER ":\x1b[4m%s\x1b[m", operator->userid);
			else
#endif			
            sprintf(opbuf, NAME_BM ":\x1b[4m%s\x1b[m", operator->userid);
        }
        if (write_formatted_file(tmplfile, mailfile, "ssssssssss",
                    sender, title, sitename, opfrom, bh->filename, reason, daystr, replyhint, opbuf, ctime_r(&time, timebuf))<0)
            return -1;
    } else {
        FILE *fn;
        fn = fopen(mailfile, "w+");
        if (sysop) {
            fprintf(fn, "寄信人: SYSOP (%s) \n", NAME_SYSOP);
            fprintf(fn, "标  题: %s\n", title);
            fprintf(fn, "发信站: %s (%24.24s)\n", BBS_FULL_NAME, ctime_r(&time, timebuf));
            fprintf(fn, "来  源: %s\n", NAME_BBS_ENGLISH);
            fprintf(fn, "\n");
            fprintf(fn, "由于您在 \x1b[4m%s\x1b[m 版 \x1b[4m%s\x1b[m，我很遗憾地通知您， \n", bh->filename, reason);
            fprintf(fn, DENY_DESC_AUTOFREE " \x1b[4m%d\x1b[m 天", day);
            /* day不允许0天，有需要的自行改造 */
            /* if (day)
                fprintf(fn, DENY_DESC_AUTOFREE " \x1b[4m%d\x1b[m 天", day);
            else
                fprintf(fn, DENY_DESC_NOAUTOFREE);
            */
            if (!autofree)
                fprintf(fn, "，到期后请回复\n此信申请恢复权限。\n");
            fprintf(fn, "\n");
            fprintf(fn, "                            %s" NAME_SYSOP_GROUP DENY_NAME_SYSOP "：\x1b[4m%s\x1b[m\n", NAME_BBS_CHINESE, operator->userid);
            fprintf(fn, "                              %s\n", ctime_r(&time, timebuf));
        } else {
            fprintf(fn, "寄信人: %s \n", operator->userid);
            fprintf(fn, "标  题: %s\n", title);
            fprintf(fn, "发信站: %s (%24.24s)\n", BBS_FULL_NAME, ctime_r(&time, timebuf));
            fprintf(fn, "来  源: %s \n", SHOW_USERIP(operator, getSession()->fromhost));
            fprintf(fn, "\n");
            fprintf(fn, "由于您在 \x1b[4m%s\x1b[m 版 \x1b[4m%s\x1b[m，我很遗憾地通知您， \n", bh->filename, reason);
            fprintf(fn, DENY_DESC_AUTOFREE " \x1b[4m%d\x1b[m 天", day);
            /* day不允许0天，有需要的自行改造 */
            /* if (day)
                fprintf(fn, DENY_DESC_AUTOFREE " \x1b[4m%d\x1b[m 天", day);
            else
                fprintf(fn, DENY_DESC_NOAUTOFREE);
            */
            if (!autofree)
                fprintf(fn, "，到期后请回复\n此信申请恢复权限。\n");
#ifdef ZIXIA
            GetDenyPic(fn, DENYPIC, ndenypic, dpcount);
#endif 
            fprintf(fn, "\n");
#ifdef MEMBER_MANAGER
			if (core_member)
			fprintf(fn, "                              " NAME_CORE_MEMBER ":\x1b[4m%s\x1b[m\n", operator->userid);
			else
#endif				
            fprintf(fn, "                              " NAME_BM ":\x1b[4m%s\x1b[m\n", operator->userid);
            fprintf(fn, "                              %s\n", ctime_r(&time, timebuf));
        }
        fclose(fn);
    }
    if (lookupuser)
#ifdef NEWSMTH
        mail_file(DELIVER /*core_member?operator->userid:DELIVER*/, mailfile, uident, title, 0, NULL);
#else
        mail_file(operator->userid, mailfile, uident, title, 0, NULL);
#endif
    unlink(mailfile);

    return 0;
}

#ifdef TITLEKEYWORD
/*
 * 获得标题关键字列表，titlekey在调用时定义
 * board为空时，获得系统标题关键字
 */
int get_title_key(const char *board, char titlekey[][8], int max)
{
    char filename[STRLEN];
    char *ptr, *key[MAXTITLEKEY];
    int count, i;

    max = (max>MAXTITLEKEY) ? MAXTITLEKEY : max;
    if (board)
        setvfile(filename, board, "title_keyword");
    else
        sprintf(filename, "etc/title_keyword");
    count = get_textfile_string(filename, &ptr, key, max);
    if (count > 0) {
        for (i=0;i<count;i++) {
            remove_blank_ctrlchar(key[i], titlekey[i], true, true, true);
            titlekey[i][6] = '\0';
        }   
        free(ptr);
    }   
    return count;
}   

int save_title_key(const char *board, char titlekey[][8], int count)
{
    char filename[STRLEN];
    int i;
    FILE *fn;
    
    setvfile(filename, board, "title_keyword");

#ifdef BOARD_SECURITY_LOG
    char oldfilename[STRLEN];
    gettmpfilename(oldfilename, "old_title_keyword");
    f_cp(filename, oldfilename, 0);
#endif

    if ((fn=fopen(filename, "w"))!=NULL) {
        for (i=0;i<count;i++)
            fprintf(fn, "%s\n", titlekey[i]);
        fclose(fn);
        load_title_key(0, getbid(board, NULL), board);
#ifdef BOARD_SECURITY_LOG
        board_file_report(board, "修改 <标题关键字>", oldfilename, filename);
        unlink(oldfilename);
#endif
    }
    return 0;
}
#endif

#ifdef BOARD_SECURITY_LOG
struct post_report_arg {
    char *file;
    int id;
    int count;
    int bm;
    time_t tm;
};

/* 生成文章对应的操作记录索引，使用apply_record回调 */
int make_post_report(struct fileheader *fh, int idx, struct post_report_arg *pa){
    if (get_posttime(fh)<pa->tm)
        return QUIT;
    if (fh->o_id == pa->id) {
        /* 非版主不能查看除m/g之外的操作 */
        if (!pa->bm &&
                strncmp(fh->title, "标m ", 4) && strncmp(fh->title, "标g ", 4) && strncmp(fh->title, "去m ", 4) && strncmp(fh->title, "去g ", 4))
            return 0;
        append_record(pa->file, fh, sizeof(struct fileheader));
        pa->count++;
        return 1;
    }
    return 0;
}

int make_post_report_dir(char *index, struct boardheader *bh, struct fileheader *fh, int bm) {
    char index_s[STRLEN];
    struct stat st;
    struct post_report_arg pa;
    
    setbdir(DIR_MODE_BOARD, index_s, bh->filename);
    if (!dashf(index_s) || stat(index_s, &st)==-1 || st.st_size==0)
        return 0;
    
    sprintf(index, "%s.%d[%d]", index_s, fh->id, getpid());
    bzero(&pa, sizeof(struct post_report_arg));
    pa.file = index;
    pa.id = fh->id;
    pa.bm = bm;
    pa.tm = get_posttime(fh);
    
    apply_record(index_s, (APPLY_FUNC_ARG)make_post_report, sizeof(struct fileheader), &pa, 0, 1);
    reverse_record(index, sizeof(struct fileheader));
    return pa.count;
}

/* 从删除区找到对应id的有效帖子，用于apply_record回调 */
int get_report_deleted_ent_by_id(struct fileheader *fh, int idx, struct post_report_arg *pa){
    if (fh->id == pa->id) {
        if (fh->filename[0])
            pa->count = idx;
        return QUIT;
    }
    return 0;
}

/* 根据操作记录原文ID号在删除区找原文 */
int get_report_deleted_ent(struct fileheader *fh, struct boardheader *bh) {
    char dir[STRLEN];
    struct post_report_arg pa;
    bzero(&pa, sizeof(struct post_report_arg));
    pa.id = fh->o_id;
    setbdir(DIR_MODE_DELETED, dir, bh->filename);
    /* 逆序从删除区找到最近一篇删除文章 */
    apply_record(dir, (APPLY_FUNC_ARG)get_report_deleted_ent_by_id, sizeof(struct fileheader), &pa, 0, 1);
    return pa.count;
}

/* 版面配置文件修改记录, 通过diff获得新旧文件差异
 */
void board_file_report(const char *board, char *title, char *oldfile, char *newfile)
{       
    FILE *fn, *fn2;
    char filename[STRLEN], buf[256];

    gettmpfilename(filename, "board_report_file", getpid());
    if((fn=fopen(filename, "w"))!=NULL){
        if(strstr(title, "删除")){
            fprintf(fn, "\033[1;33;45m删除文件信息备份\033[K\033[m\n");
    
            if((fn2=fopen(oldfile, "r"))!=NULL){
                while(fgets(buf, 256, fn2)!=NULL)
                    fputs(buf, fn);
                fclose(fn2);
                fprintf(fn, "\n");
            }
            fclose(fn);
        } else {
            char genbuf[STRLEN*2], filediff[STRLEN];
            gettmpfilename(filediff, "filediff", getpid());
            if(!dashf(oldfile)){
                f_touch(oldfile);
            }
            sprintf(genbuf, "diff -u %s %s > %s", oldfile, newfile, filediff);
            system(genbuf);
            fprintf(fn, "\033[1;33;45m修改文件信息备份\033[K\033[m\n");
        
            if((fn2=fopen(filediff, "r"))!=NULL){
                /* 跳过前2行 */
                fgets(buf, 256, fn2);fgets(buf, 256, fn2);
                while(fgets(buf, 256, fn2)!=NULL){
                    if(buf[0]=='-')
                        fprintf(fn, "\033[1;35m%s\033[m", buf);
                    else if(buf[0]=='+')
                        fprintf(fn, "\033[1;36m%s\033[m", buf);
                    else
                        fputs(buf, fn);
                }
                fclose(fn2);
                fprintf(fn, "\n");
            }
            fclose(fn);
            unlink(filediff);
        }
        board_security_report(filename, getCurrentUser(), title, board, NULL);
        unlink(filename);
    }
}

/* 获得版面安全记录的最新id */
int board_last_log_id(const char *board)
{
    struct fileheader fh;
    char filename[STRLEN * 2];
    int count;

    setbdir(DIR_MODE_BOARD, filename, board);
    memset(&fh, 0, sizeof(struct fileheader));
    count = get_num_records(filename, sizeof(struct fileheader));
    if (count <= 0)
        return 0;
    get_record(filename, &fh, sizeof(struct fileheader), count);

    if (fh.id != 0)
        return fh.id;
    return 0;
}

/* 版面安全记录，在DIR_MODE_BOARD中添加文章, jiangjun, 20100610 */
int board_security_report(const char *filename, struct userec *user, const char *title, const char *bname, const struct fileheader *xfh)
{       
    struct fileheader fh;
    struct boardheader bh;
    FILE *fin, *fout;
    char buf[STRLEN], bufcp[READ_BUFFER_SIZE], timebuf[STRLEN];
    int mode, fd;
    time_t now;
    size_t size;
            
    bzero(&fh, sizeof(struct fileheader));
    setbpath(buf, bname);
    if (!getboardnum(bname, &bh))
        return 1;
    if (GET_POSTFILENAME(fh.filename, buf))
        return 2;
    POSTFILE_BASENAME(fh.filename)[0] = 'B';
    set_posttime(&fh);
    
    mode = 0;
    if (user == NULL)
        mode = 1;  //自动发信
    if (mode)
        memcpy(fh.owner, DELIVER, OWNER_LEN);
    else
        memcpy(fh.owner, user -> userid, OWNER_LEN);
    fh.owner[OWNER_LEN - 1] = 0;
    strnzhcpy(fh.title, title, ARTICLE_TITLE_LEN);
    setbfile(buf, bname, fh.filename);
    if (!(fout = fopen(buf, "w")))
        return 3;

    if (mode) {
        now = time(NULL);
        fprintf(fout, "发信人: "DELIVER" (自动发信系统), 信区: %s安全记录\n", bname);
        fprintf(fout, "标  题: %s\n", fh.title);
        fprintf(fout, "发信站: %s自动发信系统 (%24.24s)\n\n", BBS_FULL_NAME, ctime_r(&now, timebuf));
    } else {
        now = time(NULL);
        fprintf(fout, "发信人: %s (%s), 信区: %s安全记录\n", user->userid, user->username, bname);
        fprintf(fout, "标  题: %s\n", fh.title);
        fprintf(fout, "发信站: %s (%24.24s)\n\n", BBS_FULL_NAME, ctime_r(&now, timebuf));
    }
    fprintf(fout, "\033[36m版面管理安全记录\033[m\n");
    fprintf(fout, "\033[33m记录原因: \033[32m%s %s\033[m\n", (mode)?"":user->userid, title);
    if (!mode)
        fprintf(fout, "\033[33m用户来源: \033[32m%s\033[m\n", getSession()->fromhost);
    if (filename != NULL) {
        if (!(fin = fopen(filename, "r"))) {
            fprintf(fout, "\n\033[45;31m系统错误，无法记录相关详细信息\033[K\033[m\n");
        } else {
            fprintf(fout, "\n\033[36m本次操作附加信息\033[m\n");
            while (true) {
                size = fread(bufcp, 1, READ_BUFFER_SIZE, fin);
                if (size == 0)
                    break;
                fwrite(bufcp, size, 1, fout);
            }
            fclose(fin);
        }
    }
    if (xfh) {
        time_t t=get_posttime(xfh);
        fprintf(fout, "\n\033[36m本次操作对应文章信息\033[m\n");
        fprintf(fout, "\033[33m文章标题: \033[4;32m%s\033[m\n", xfh->title);
        fprintf(fout, "\033[33m文章作者: \033[4;32m%s\033[m\n", xfh->owner);
        fprintf(fout, "\033[33m文章ID号: \033[4;32m%d\033[m\n", xfh->id);
        fprintf(fout, "\033[33m发表时间: \033[4;32m%s\033[m\n", ctime(&t));
    }
    fclose(fout);
    fh.eff_size = get_effsize_attach(buf, &fh.attachment);
    setbdir(DIR_MODE_BOARD, buf, bname);
    if ((fd = open(buf, O_WRONLY|O_CREAT, S_IRUSR | S_IWUSR | S_IRGRP | S_IWGRP | S_IROTH)) == -1) {
        return 5;
    }
    writew_lock(fd, 0, SEEK_SET, 0);
    fh.id = board_last_log_id(bname) + 1;
    fh.groupid = fh.id;
    fh.reid = fh.id;
    if (xfh)
        fh.o_id = xfh->id;
    else
        fh.o_id = 0;
    lseek(fd, 0, SEEK_END);
    if (safewrite(fd, &fh, sizeof(struct fileheader)) == -1) {
        un_lock(fd, 0, SEEK_SET, 0);
        close(fd);
        setbfile(buf, bname, fh.filename);
        unlink(buf);
        return 6;
    }
    un_lock(fd, 0, SEEK_SET, 0);
    close(fd);
    return 0;
}
#endif

#ifdef HAVE_USERSCORE
/* 获取文章对应的积分奖励记录文件 */
void setsfile(char *file, struct boardheader *bh, struct fileheader *fh)
{
    char buf[STRLEN];

    strcpy(buf, fh->filename);
    POSTFILE_BASENAME(buf)[0]='A';
    setbfile(file, bh->filename, buf);
}

/* 查询符合条件的积分奖励记录，使用apply_record回调 */
int get_award_score(struct score_award_arg *pa, int idx, struct score_award_arg *sa)
{
    if (sa->bm) {
        if (pa->bm)
            sa->score += pa->score;
    } else {
        if (!pa->bm && !strcmp(pa->userid, sa->userid))
            sa->score += pa->score;
    }
    return 0;
}

/* 最多可奖励的积分 */
int max_award_score(struct boardheader *bh, struct userec *user, struct fileheader *fh, int bm)
{
    int max;
    struct score_award_arg sa;
    char file[STRLEN];

    max = bm?MAX_BOARD_AWARD_SCORE:MAX_USER_AWARD_SCORE;

    bzero(&sa, sizeof(struct score_award_arg));
    strcpy(sa.userid, user->userid);
    sa.bm = bm;

    setsfile(file, bh, fh);
    apply_record(file, (APPLY_FUNC_ARG)get_award_score, sizeof(struct score_award_arg), &sa, 0, 0);
    max = max - sa.score;

    return max;
}

/* 查询积分奖励总数，使用apply_record回调 */
int get_all_award_score(struct score_award_arg *pa, int idx, struct score_award_arg *sa)
{
    sa->score += pa->score;
    return 0;
}

/* 获得积分奖励总数 */
int all_award_score(struct boardheader *bh, struct fileheader *fh)
{
    struct score_award_arg sa;
    char file[STRLEN];

    bzero(&sa, sizeof(struct score_award_arg));
    setsfile(file, bh, fh);
    apply_record(file, (APPLY_FUNC_ARG)get_all_award_score, sizeof(struct score_award_arg), &sa, 0, 0);

    return sa.score;
}

/* 版面积分奖励/扣除记录 */
int add_award_record(struct boardheader *bh, struct userec *opt, struct fileheader *fh, int score, int bm)
{
    struct score_award_arg sa;
    char file[STRLEN];

    bzero(&sa, sizeof(struct score_award_arg));
    setsfile(file, bh, fh);
    strcpy(sa.userid, opt->userid);
    sa.score = score;
    sa.t = time(0);
    sa.bm = bm;

    append_record(file, &sa, sizeof(struct score_award_arg));
    return 0;
}

void bcache_setreadonly(int readonly);

/* 奖励用户个人积分 */
int award_score_from_user(struct boardheader *bh, struct userec *from, struct userec *user, struct fileheader *fh, int score)
{
    char buf[STRLEN];

    if ((int)(from->score_user) < score)
        return -1;

    user->score_user += score;
    from->score_user -= score;

    sprintf(buf, "%s 版奖励积分 <%s>", bh->filename, fh->title);
    score_change_mail(user, user->score_user-score, user->score_user, 0, 0, buf);

    sprintf(buf, "奖励 %s 版 %s 积分 <%s>", bh->filename, user->userid, fh->title);
    score_change_mail(user, from->score_user+score, from->score_user, 0, 0, buf);

    add_award_record(bh, from, fh, score, 0);
    return 0;
}

/* 奖励用户版面积分 */
int award_score_from_board(struct boardheader *bh, struct userec *opt, struct userec *user, struct fileheader *fh, int score)
{
    char buf[STRLEN];

    if ((int)(bh->score) < score)
        return -1;

    user->score_user += score;
    bcache_setreadonly(0);
    bh->score -= score;
    bcache_setreadonly(1);

    sprintf(buf, "%s 版%s积分 <%s>", bh->filename, score>0?"奖励":"扣除", fh->title);
    score_change_mail(user, user->score_user-score, user->score_user, 0, 0, buf);

#ifdef BOARD_SECURITY_LOG
    char tmpfile[STRLEN];
    FILE *fn;
    gettmpfilename(tmpfile, "award_score");
    if ((fn = fopen(tmpfile, "w"))!=NULL) {
        fprintf(fn, "  版面积分: \033[33m%8d\033[m -> \033[32m%-8d\t\t%s%d\033[m\n", bh->score+score, bh->score, score<0?"\033[31m↑":"\033[36m↓", abs(score));
        fclose(fn);
    }
    sprintf(buf, "%s %s %d 积分", score>0?"奖励":"扣除", user->userid, abs(score));
    board_security_report(tmpfile, opt, buf, bh->filename, fh);
    unlink(tmpfile);
#endif

    add_award_record(bh, opt, fh, score, 1);
    return 0;
}

int add_award_mark(struct boardheader *bh, struct fileheader *fh)
{
    FILE *fin, *fout;
    char buf[256], fname[STRLEN], outfile[STRLEN];
    int score, asize, added=0;

    setbfile(fname, bh->filename, fh->filename);
    if ((fin = fopen(fname, "rb")) == NULL)
        return 0;
    gettmpfilename(outfile, "awardmark", getpid());
    if ((fout = fopen(outfile, "w")) == NULL) {
        fclose(fin);
        return 0;
    }
    while ((asize = -attach_fgets(buf, 256, fin)) != 0) {
        if (asize<0) {
            if (!added && !strncmp(buf, "发信站: ", 8)) {
                char *p;
                if ((p=strstr(buf, "  \033[31m[累计积分奖励:"))!=NULL)
                    *p = '\0';
                else if (buf[strlen(buf)-1] == '\n')
                    buf[strlen(buf)-1] = '\0';
                score = all_award_score(bh, fh);
                fprintf(fout, "%s  \033[31m[累计积分奖励: %d]\033[m\n", buf, score);
                added = 1;
            } else
                fputs(buf, fout);
        } else
            put_attach(fin, fout, asize);
    }

    fclose(fin);
    fclose(fout);

    f_cp(outfile, fname, O_TRUNC);
    unlink(outfile);
    
    return 1;
}
#endif
