/*
 * $Id$
 */
#include "bbslib.h"

int main()
{
    FILE *fp;
    struct userec x;
    int logins = 0, posts = 0, stays = 0, lifes = 0, total = 0;

    initwww_all();
    if (!loginok)
        http_fatal("匆匆过客不加入排名");
    fp = fopen(".PASSWDS", "r");
    while (1) {
        if (fread(&x, sizeof(x), 1, fp) <= 0)
            break;
        if (x.userid[0] < 'A')
            continue;
        if (x.userlevel == 0)
            continue;
        if (x.numposts >= getCurrentUser()->numposts)
            posts++;
        if (x.numlogins >= getCurrentUser()->numlogins)
            logins++;
        if (x.stay >= getCurrentUser()->stay)
            stays++;
        if (x.firstlogin <= getCurrentUser()->firstlogin)
            lifes++;
        total++;
    }
    fclose(fp);
    printf("<center>%s -- 个人排名统计 [使用者: %s]<hr color=green>\n", BBSNAME, getCurrentUser()->userid);
    printf("<table width=320><tr><td>项目<td>数值<td>全站排名<td>相对比例\n");
    printf("<tr><td>本站网龄<td>%d天<td>%d<td>TOP %5.2f%%", (time(0) - getCurrentUser()->firstlogin) / 86400, lifes, (lifes * 100.) / total);
    printf("<tr><td>上站次数<td>%d次<td>%d<td>TOP %5.2f%%", getCurrentUser()->numlogins, logins, logins * 100. / total);
    printf("<tr><td>发表文章<td>%d次<td>%d<td>TOP %5.2f%%", getCurrentUser()->numposts, posts, posts * 100. / total);
    printf("<tr><td>在线时间<td>%d分<td>%d<td>TOP %5.2f%%", getCurrentUser()->stay / 60, stays, stays * 100. / total);
    printf("</table><br>总用户数: %d", total);
}
