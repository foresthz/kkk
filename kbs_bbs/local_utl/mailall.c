#include "bbs.h"
int mailuser(struct userec* user,char* arg)
{
    mail_file("Arbitrator", "tekan/JuryMail01", user->userid, "[����]BBS ˮľ�廪վ�ٲ�ίԱ�Ἧ������", BBSPOST_LINK, NULL);
    return 0;
};

main()
{
    chdir(BBSHOME);
    resolve_ucache();
    resolve_boards();
    resolve_utmp();
//    mail_file("Arbitrator", "tekan/JuryMail01", "KCN", "[����]BBS ˮľ�廪վ�ٲ�ίԱ�Ἧ������", BBSPOST_LINK, NULL);
    apply_users(mailuser,NULL);
}

