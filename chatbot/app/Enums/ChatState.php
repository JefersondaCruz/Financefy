<?php

namespace App\Enums;

enum ChatState: string
{
    // Auth Flow
    case IDLE = 'idle';
    case AWAITING_LOGIN_EMAIL = 'awaiting_login_email';
    case AWAITING_LOGIN_PASSWORD = 'awaiting_login_password';
    case AWAITING_REGISTER_NAME = 'awaiting_register_name';
    case AWAITING_REGISTER_EMAIL = 'awaiting_register_email';
    case AWAITING_REGISTER_PASSWORD = 'awaiting_register_password';

    // Product Flow
    case AWAITING_PRODUCT_NAME = 'awaiting_product_name';
    case AWAITING_PRODUCT_VALUE = 'awaiting_product_value';
    case AWAITING_PRODUCT_CATEGORY = 'awaiting_product_category';
    case AWAITING_PRODUCT_CONFIRMATION = 'awaiting_product_confirmation';

    // Menu
    case MAIN_MENU = 'main_menu';
}

