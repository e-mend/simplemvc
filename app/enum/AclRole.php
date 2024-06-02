<?php

namespace App\enum;

enum AclRole: string
{
    case CAN_READ_POST = 'can_read_post';
    case CAN_CREATE_POST = 'can_create_post';
    case CAN_UPDATE_POST = 'can_update_post';
    case CAN_DISABLE_POST = 'can_disable_post';
    case CAN_SEE_DISABLED_POST = 'can_see_disabled_post';
    case POST_1 = 'post_1';
    case POST_2 = 'post_2';
    case POST_3 = 'post_3';
    case CAN_READ_INVENTORY = 'can_read_inventory';
    case CAN_CREATE_INVENTORY = 'can_create_inventory';
    case CAN_UPDATE_INVENTORY = 'can_update_inventory';
    case CAN_DISABLE_INVENTORY = 'can_disable_inventory';
    case CAN_SEE_DISABLED_INVENTORY = 'can_see_disabled_inventory';
    case ADMIN = 'admin';
    case SUPER_ADMIN = 'super_admin';
    case DEVELOPER = 'developer';
    case USER = 'user';
}