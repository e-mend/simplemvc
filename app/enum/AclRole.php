<?php

namespace App\enum;

class AclRole
{
    const CAN_READ_SAFE = 'can_read_safe';
    const CAN_CREATE_SAFE = 'can_create_safe';
    const CAN_UPDATE_SAFE = 'can_update_safe';
    const CAN_DISABLE_SAFE = 'can_disable_safe';
    const CAN_SEE_DISABLED_SAFE = 'can_see_disabled_safe';
    const SAFE_1 = 'safe_1';
    const SAFE_2 = 'safe_2';
    const SAFE_3 = 'safe_3';
    const CAN_READ_INVENTORY = 'can_read_inventory';
    const CAN_CREATE_INVENTORY = 'can_create_inventory';
    const CAN_UPDATE_INVENTORY = 'can_update_inventory';
    const CAN_DISABLE_INVENTORY = 'can_disable_inventory';
    const CAN_SEE_DISABLED_INVENTORY = 'can_see_disabled_inventory';
    const ADMIN = 'admin';
    const SUPER_ADMIN = 'super_admin';
    const DEVELOPER = 'developer';
    const USER = 'user';
}