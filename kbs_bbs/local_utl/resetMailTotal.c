/*
 * ������������⣬������ԭ���ǣ������� 0xffff ֮�����û���������ʱ��ͻ��Զ�����ɨ��
 * ���е��ż� reset usedspace�����ǣ��п������Զ�ɨ��֮ǰ usedspace �ͷ����˱仯�ȷ��յ���
 * ���ż�����������Ҫ����������� usedspace���� 0xffff �ĳ� get_mailusedspace(user,1)��
 * ��������һ��������������ʱ��ͻ���ӳ�������ʲô����� usedspace �᲻��ȷ���ٺ٣�
 * ��Ҫ������ˮľ�ĳ��������⣬�϶�����Ϊ�����������˲���ʧ����ɾ���ļ���- atppp...���������ָ����
 */
#include "bbs.h"

char *curuserid;

int query_BM(struct userec *user, char *arg)
{
    curuserid = user->userid;
    user->usedspace = 0xffff;
    return 1;
}

static void 
usage()
{
	fprintf(stderr, "Usage: resetMailTotal <-a|-u userid>\n\n");
	fprintf(stderr, "    If -a parameter is provided, this program will reset all userids' mail space,\n");
	fprintf(stderr, "    else only reset the specified userid's mail space.\n");
}

int 
main(int argc, char ** argv)
{
	struct userec *user = NULL;

    chdir(BBSHOME);
    resolve_ucache();
    resolve_boards();
	if (argc == 2 && !strcmp(argv[1], "-a"))
    	apply_users(query_BM, NULL);
	else if (argc == 3 && !strcmp(argv[1], "-u"))
	{
		getuser(argv[2], &user);
		if (user == NULL)
		{
			fprintf(stderr, "User %s not found.\n", argv[1]);
			return -1;
		}
		user->usedspace = 0xFFFF;
	}
	else
		usage();

	return 0;
}
