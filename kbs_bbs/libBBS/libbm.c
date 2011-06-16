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
    int i, count;
    struct stat st;
    
    if ((fd=open(file, O_RDONLY))==-1)
        return 0;
    if (fstat(fd, &st)!=0) {
        close(fd);
        return 0;
    }
    *ptr = (char *)malloc(st.st_size);
    read(fd, *ptr, st.st_size);
    count = 0;
    result[0] = *ptr;
    for (i=1;i<st.st_size;i++) {
        if ((*ptr)[i] == '\n') {
            (*ptr)[i] = '\0';
            i++;
            count++;
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
            if (count>=maxcount)
                break;
        }
    }
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

    if (board)
        setvfile(filename, board, "deny_reason");
    else
        sprintf(filename, "etc/deny_reason");
    if ((fn=fopen(filename, "w"))!=NULL) {
        for (i=0;i<count;i++)
            fprintf(fn, "%s\n", denyreason[i]);
        fclose(fn);
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
int deny_announce(char *uident, const struct boardheader *bh, char *reason, int day, struct userec *operator, time_t time, int mode)
{
    struct userec *lookupuser;
    char tmplfile[STRLEN], postfile[STRLEN], title[STRLEN], title1[STRLEN], timebuf[STRLEN];
    int sysop;

    gettmpfilename(postfile, "ann_deny.%d", getpid());
    sprintf(tmplfile, "etc/denypost_template");

    if ((HAS_PERM(operator, PERM_SYSOP) || HAS_PERM(operator, PERM_OBOARDS))
            && !chk_BM_instr(bh->BM, operator->userid))
        sysop = 1;
    else
        sysop = 0;
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
            sprintf(opbuf, NAME_BBS_CHINESE NAME_SYSOP_GROUP DENY_NAME_SYSOP "：\x1b[4m%s\x1b[m", operator->userid);
        else
            sprintf(opbuf, NAME_BM ":\x1b[4m%s\x1b[m", operator->userid);
        if (write_formatted_file(tmplfile, postfile, "ssssss",
                    uident, bh->filename, reason, daystr, opbuf, ctime_r(&time, timebuf))<0)
            return -1;
    } else {
        FILE *fn;
        fn = fopen(postfile, "w+");
        fprintf(fn, "由于 \x1b[4m%s\x1b[m 在 \x1b[4m%s\x1b[m 版的 \x1b[4m%s\x1b[m 行为，\n", uident, bh->filename, reason);
        if (day)
            fprintf(fn, DENY_BOARD_AUTOFREE " \x1b[4m%d\x1b[m 天。\n", day);
        else
            fprintf(fn, DENY_BOARD_NOAUTOFREE "\n");
        if (sysop) {
            fprintf(fn, "                            %s" NAME_SYSOP_GROUP DENY_NAME_SYSOP "：\x1b[4m%s\x1b[m\n", NAME_BBS_CHINESE, operator->userid);
        } else {
            fprintf(fn, "                              " NAME_BM ":\x1b[4m%s\x1b[m\n", operator->userid);
        }
        fprintf(fn, "                              %s\n", ctime_r(&time, timebuf));
        fclose(fn);
    }
#ifdef NEWSMTH
    post_file(operator, "", postfile, bh->filename, title, 0, 1, getSession());
#else
    post_file(operator, "", postfile, bh->filename, title, 0, 2, getSession());
#endif
    post_file(operator, "", postfile, "denypost", title1, 0, -1, getSession());
    unlink(postfile);

    return 0;
}

/* 使用模板发布封禁邮件
 * mode:
 *      0:添加
 *      1:修改
 */
int deny_mailuser(char *uident, const struct boardheader *bh, char *reason, int day, struct userec *operator, time_t time, int mode)
{
    struct userec *lookupuser;
    char tmplfile[STRLEN], mailfile[STRLEN], title[STRLEN], timebuf[STRLEN];
    int sysop;

    gettmpfilename(mailfile, "mail_deny.%d", getpid());
    sprintf(tmplfile, "etc/denymail_template");

    if ((HAS_PERM(operator, PERM_SYSOP) || HAS_PERM(operator, PERM_OBOARDS))
            && !chk_BM_instr(bh->BM, operator->userid))
        sysop = 1;
    else
        sysop = 0;
    getuser(uident, &lookupuser);
    if (mode==0) {
        sprintf(title, "%s被取消在%s版的发文权限", uident, bh->filename);
    } else {
        sprintf(title, "修改%s在%s版的发文权限封禁", uident, bh->filename);
    }
    if (dashf(tmplfile)) {
        char sender[STRLEN], sitename[STRLEN], opfrom[STRLEN], daystr[4], opbuf[STRLEN];
        sprintf(daystr, "%d", day);
        if (sysop) {
            sprintf(sender, "SYSOP (%s) ", NAME_SYSOP);
            sprintf(sitename, "%s (%24.24s)", BBS_FULL_NAME, ctime_r(&time, timebuf));
            sprintf(opfrom, "%s", NAME_BBS_ENGLISH);
            sprintf(opbuf, NAME_BBS_CHINESE NAME_SYSOP_GROUP DENY_NAME_SYSOP "：\x1b[4m%s\x1b[m", operator->userid);
        } else {
            sprintf(sender, "%s ", operator->userid);
            sprintf(sitename, "%s (%24.24s)", BBS_FULL_NAME, ctime_r(&time, timebuf));
            sprintf(opfrom, "%s", SHOW_USERIP(operator, getSession()->fromhost));
            sprintf(opbuf, NAME_BM ":\x1b[4m%s\x1b[m", operator->userid);
        }
        if (write_formatted_file(tmplfile, mailfile, "sssssssss",
                    sender, title, sitename, opfrom, bh->filename, reason, daystr, opbuf, ctime_r(&time, timebuf))<0)
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
            if (day)
                fprintf(fn, DENY_DESC_AUTOFREE " \x1b[4m%d\x1b[m 天", day);
            else
                fprintf(fn, DENY_DESC_NOAUTOFREE);
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
            if (day)
                fprintf(fn, DENY_DESC_AUTOFREE " \x1b[4m%d\x1b[m 天", day);
            else
                fprintf(fn, DENY_DESC_NOAUTOFREE);
            fprintf(fn, "\n");
            fprintf(fn, "                              " NAME_BM ":\x1b[4m%s\x1b[m\n", operator->userid);
            fprintf(fn, "                              %s\n", ctime_r(&time, timebuf));
        }
        fclose(fn);
    }
    if (lookupuser)
#ifdef NEWSMTH
        mail_file(DELIVER, mailfile, uident, title, 0, NULL);
#else
        mail_file(operator->userid, mailfile, uident, title, 0, NULL);
#endif
    unlink(mailfile);

    return 0;
}
