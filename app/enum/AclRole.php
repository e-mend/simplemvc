<?php

namespace App\enum;

enum AclRole: string
{
    case CAN_READ_SAFE = 'can_read_safe';
    case CAN_CREATE_SAFE = 'can_create_safe';
    case CAN_UPDATE_SAFE = 'can_update_safe';
    case CAN_DISABLE_SAFE = 'can_disable_safe';
    case CAN_SEE_DISABLED_SAFE = 'can_see_disabled_safe';
    case SAFE_1 = 'safe_1';
    case SAFE_2 = 'safe_2';
    case SAFE_3 = 'safe_3';
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