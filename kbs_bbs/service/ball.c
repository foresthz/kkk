#include "bbs.h"
#include "service.h"

#define MAX_ROW  9
#define MAX_COL  9
#define MAX_BALL 81
#define IBC      5  // initial ball count
#define CPG      3  // ball count per group

struct point {
    int row;
    int col;
};

struct ball {
    struct point p;
    int key;  // start��end�м�¼״̬��ballpath�м�¼ǰ��
};

int balls[MAX_ROW][MAX_COL];
int ballvisited[MAX_ROW][MAX_COL];
int head, tail;

struct ball start, end;
struct ball ballpath[MAX_BALL];
int ngtype[CPG];
int score=0, hole;

void ball_help()
{
    move(3, 40);
    prints("����˵��: ");
    move(5, 42);
    prints("������ƶ���꣬�ո�ѡ��");
    move(7, 42);
    prints("Home/A��End/D  �ƶ���������ס���β");
    move(9, 42);
    prints("PgUp/W��PgDn/S �ƶ���������ס���β");
}

void show_frame()
{
    int i;
    move(0, 0);
    clrtobot();
    prints("�����ө��ө��ө��ө��ө��ө��ө��ө���\n");
    for (i=1;i<MAX_COL;i++){
        prints("��  ��  ��  ��  ��  ��  ��  ��  ��  ��\n");
        prints("�ĩ��੤�੤�੤�੤�੤�੤�੤�੤��\n");
    }
    prints("��  ��  ��  ��  ��  ��  ��  ��  ��  ��\n");
    prints("�����۩��۩��۩��۩��۩��۩��۩��۩���\n");
    move(20, 0);
    prints("������ɫ:            �÷�:");
    ball_help();
}

void show_balls()
{
    int i, j, btype;
    char buf[STRLEN];
    for (i=0;i<MAX_ROW;i++){
        for (j=0;j<MAX_COL;j++) {
            move(i*2+1, j*4+2);
            btype=balls[i][j];
            if (btype>0) {
                sprintf(buf, "\033[1;3%dm��\033[m", btype);
            } else
                sprintf(buf, "  ");
            prints(buf);
        }
    }
    move(20, 10);
    for (i=0;i<CPG;i++) {
        sprintf(buf, "\033[1;3%dm��\033[m", ngtype[i]);
        prints(buf);
    }
    move(20, 26);
    prints("%d", score);

    return;
}
/* ����һ���㣬��ռ���ѷ��ʷ���-1��ΪĿ�ĵ�ʱ����1 */
int visit_point(struct point p, int dir)
{
    struct point dp[4] = {{1, 0}, {0, 1}, {-1, 0}, {0, -1}};
    p.row += dp[dir].row;
    p.col += dp[dir].col;
    if (p.row<0 || p.row>=MAX_ROW || p.col<0 || p.col>=MAX_COL)
        return -1;
    if (balls[p.row][p.col] || ballvisited[p.row][p.col])
        return -1;
    ballvisited[p.row][p.col] = 1;
    ballpath[tail].p = p;
    ballpath[tail].key = head-1;
    tail++;
    if (p.row==end.p.row && p.col==end.p.col)
        return 1;
    return 0;
}

int get_path()
{
    int i, found=0;
    struct point p;

    head = 0;
    tail = 1;
    bzero(ballvisited, MAX_BALL * sizeof(int));
    bzero(ballpath, sizeof(ballpath));
    ballpath[0] = start;
    ballpath[0].key = -1;
    while (head<tail) {
        p = ballpath[head].p;
        head++;
        for (i=0;i<4;i++) {
            if (visit_point(p, i)==1) {
                found = 1;
                break;
            }
        }
        if (found)
            break;
    }
    return found;
}

void show_point(struct point p[], int btype, int count)
{
    int i;
    char buf[STRLEN];
    for (i=count;i>0;i--) {
        move(p[i-1].row*2+1, p[i-1].col*4+2);
        sprintf(buf, "\033[1;3%d;47m��\033[m", btype);
        prints(buf);
    }
}

void show_path()
{
    int count=0, btype=balls[start.p.row][start.p.col];
    struct point ballpath2[MAX_BALL];
    struct ball b = ballpath[tail-1];

    do {
        ballpath2[count++] = b.p;
        b = ballpath[b.key];
    } while (b.key!=-1);

    show_point(ballpath2, btype, count);
    refresh();
}

int create_ball()
{
    return rand()%7+1;
}

/* ����һ��ball */
void deposit_ball(int btype)
{
    int row, col;
    while (1) {
        row = rand()%MAX_ROW;
        col = rand()%MAX_COL;
        if (balls[row][col]==0) {
            balls[row][col] = btype;
            break;
        }
    }
}

/* ����5��ball */
void init_ball()
{
    int i, btype;
    for (i=0;i<IBC;i++) {
        btype = create_ball();
        deposit_ball(btype);
    }
    hole = MAX_BALL - IBC;
}

/* ����һ�飨3����ball */
void create_group()
{
    int i;
    if (hole<=0)
        return;
    for (i=0;i<CPG;i++)
        ngtype[i] = create_ball();
    return;
}

/* ����һ�飨3����ball��û�п�λ�Ͳ����� */
int deposit_group()
{
    int i;
    for (i=0;i<CPG;i++) {
        if (hole<=0)
            return -1;
        deposit_ball(ngtype[i]);
        hole--;
    }
    return 0;
}

int move_ball()
{
    if (get_path()) {
        show_path();
        balls[end.p.row][end.p.col] = balls[start.p.row][start.p.col];
        balls[start.p.row][start.p.col] = 0;
        return 1;
    }
    return 0;
}

/* �������ӳɹ���ball */
int erase_ball()
{
    struct point dp[4] = {{0, 1}, {1, 0}, {1, 1}, {1, -1}};
    int dir, v, count, totalcount=1, row, col;

    v = balls[end.p.row][end.p.col];
    for (dir=0;dir<4;dir++) {
        count = 1;
        /* ��С�ķ����� */
        row = end.p.row;
        col = end.p.col;
        while (1) {
            row = row - dp[dir].row;
            col = col - dp[dir].col;
            if (row<0 || row>=MAX_ROW || col<0 || col>=MAX_COL || balls[row][col]!=v)
                break;
            count++;
        }
        /* �ش�ķ����� */
        row = end.p.row;
        col = end.p.col;
        while (1) {
            row = row + dp[dir].row;
            col = col + dp[dir].col;
            if (row<0 || row>=MAX_ROW || col<0 || col>=MAX_COL || balls[row][col]!=v)
                break;
            count++;
        }
        if (count>4) {
            totalcount = totalcount + count - 1;
            while (count>0) {
                row = row - dp[dir].row;
                col = col - dp[dir].col;
                balls[row][col] = 0;
                count--;
            }
        }
    }
    return totalcount>1 ? totalcount : 0;
}

int ball_main()
{
    int i, row, col, moveball, erase;
    char buf[STRLEN];
    srand(time(0));

    row=col=0;
    show_frame();
    init_ball();
    create_group();
    while(1) {
        moveball = 0;
        bzero(&start, sizeof(struct ball));
        bzero(&end, sizeof(struct ball));
        show_balls();
        while(!moveball) {
            if (start.key) {    //��ʾѡ��ball
                move(start.p.row*2+1, start.p.col*4+2);
                sprintf(buf, "\033[1;3%d;47m��\033[m", balls[start.p.row][start.p.col]);
                prints(buf);
            }
            row = (row+MAX_ROW)%MAX_ROW;
            col = (col+MAX_COL)%MAX_COL;
            move(row*2+1, col*4+2);
            i = igetkey();
            switch(toupper(i)) {
                case KEY_UP:
                    row--;
                    break;
                case KEY_DOWN:
                    row++;
                    break;
                case KEY_LEFT:
                    col--;
                    break;
                case KEY_RIGHT:
                    col++;
                    break;
                case KEY_HOME:
                case 'A':
                    col=0;
                    break;
                case KEY_END:
                case 'D':
                    col=MAX_COL-1;
                    break;
                case KEY_PGUP:
                case 'W':
                    row=0;
                    break;
                case KEY_PGDN:
                case 'S':
                    row=MAX_ROW-1;
                    break;
                case ' ':
                    if (balls[row][col]) { //ѡ�е�����ball�ĵ�
                        if (start.key) { //ȥ��ԭ��ѡ��ball����ʾ
                            move(start.p.row*2+1, start.p.col*4+2);
                            sprintf(buf, "\033[1;3%dm��\033[m", balls[start.p.row][start.p.col]);
                            prints(buf);
                        }
                        start.p.row = row;
                        start.p.col = col;
                        start.key = 1;
                    } else { //ѡ�е���û��ball�ĵ�
                        if (!start.key) //��ûѡ���
                            continue;
                        else {
                            end.p.row = row;
                            end.p.col = col;
                            end.key = 1;
                            /* ��ʼ�ƶ� */
                            if (move_ball()==0) {
                                move(22, 10);
                                prints("�޷�����Ŀ�ĵ�");
                                pressreturn();
                                move(start.p.row*2+1, start.p.col*4+2);
                                sprintf(buf, "\033[1;3%dm��\033[m", balls[start.p.row][start.p.col]);
                                prints(buf);
                                move(22, 10);clrtoeol();
                            } else {
                                /* �ƶ���� */
                                show_balls();
                                usleep(200);
                                refresh();
                                /* �������ӵĳɹ�balls */
                                erase = erase_ball();
                                if (erase) {
                                    hole += erase;
                                    score += (erase - 4) * 10;
                                }
                                moveball = 1;
                            }
                            start.key = end.key = 0;
                        }
                    }
                    break;
                case Ctrl('C'):
                    return 0;
                default:
                    break;
            }
        }
        if (i==Ctrl('C'))
            return 0;
        if (!erase) {  // �ϴ��ƶ�δ��������ballʱ�����þɵģ������µ�
            if (deposit_group()==-1) {
                show_balls();
                move(22, 10);
                prints("��Ϸ����");
                pressreturn();
                return 0;
            }
            create_group();
        }
    }
    return 0;
}
