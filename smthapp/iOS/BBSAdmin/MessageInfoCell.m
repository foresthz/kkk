//
//  MessageInfoCell.m
//  BBSAdmin
//
//  Created by HE BIAO on 1/24/14.
//  Copyright (c) 2014 newsmth. All rights reserved.
//

#import "MessageInfoCell.h"

@implementation MessageInfoCell
@synthesize label_count,label_name,imageview_dir;

- (id)initWithStyle:(UITableViewCellStyle)style reuseIdentifier:(NSString *)reuseIdentifier
{
    self = [super initWithStyle:style reuseIdentifier:reuseIdentifier];
    if (self) {
        // Initialization code
    }
    return self;
}

- (void)setSelected:(BOOL)selected animated:(BOOL)animated
{
    [super setSelected:selected animated:animated];

    // Configure the view for the selected state
}

-(void)setContentInfo:(NSString*)name :(int)unread :(UIImage *)image
{
    [label_name setText:name];

    if(unread == 0){
        [label_count setText:@""];
    }else{
        [label_count setText:[NSString stringWithFormat:@"%d条未读",unread]];
        label_count.textColor = [UIColor redColor];
    }

    if(image) {
        imageview_dir.image = image;
    }
}

@end
