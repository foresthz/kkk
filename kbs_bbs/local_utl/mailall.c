#include "bbs.h"
int mailuser(struct userec* user,char* arg)
{
    mail_file("SYSOP", "boards/Announce/M.1041329439.00", user->userid, "ף��������������", BBSPOST_LINK, NULL);
    return 0;
};

main()
{
    chdir(BBSHOME);
    resolve_ucache();
    resolve_boards();
    resolve_utmp();
    apply_users(mailuser,NULL);
}

