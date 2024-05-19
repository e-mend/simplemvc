<?php

namespace App\enum;

enum AclRole: string
{
    case CAN_READ_POST = 'can_read_post';
    case CAN_CREATE_POST = 'can_create_post';
    case CAN_UPDATE_POST = 'can_update_post';
    case CAN_DELETE_POST = 'can_delete_post';
    case CAN_SEE_DELETE_POSTS = 'can_see_delete_posts';
    case POST_1 = 'post_1';
    case POST_2 = 'post_2';
    case POST_3 = 'post_3';
    case CAN_READ_INVENTORY = 'can_read_inventory';
    case CAN_CREATE_INVENTORY = 'can_create_inventory';
    case CAN_UPDATE_INVENTORY = 'can_update_inventory';
    case CAN_DELETE_INVENTORY = 'can_delete_inventory';
    case CAN_SEE_DELETE_INVENTORY = 'can_see_delete_inventory';
    case ADMIN = 'admin';
    case SUPER_ADMIN = 'super_admin';
    case DEVELOPER = 'developer';
    case USER = 'user';
}