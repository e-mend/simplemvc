# Part 1

CREATE SCHEMA `InventoryApp`;

# Part 2

CREATE TABLE `InventoryApp`.`user`(
    `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `option` JSON NOT NULL,
    `first_name` VARCHAR(255) NOT NULL,
    `last_name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `image` JSON,
    `created_at` DATETIME NOT NULL,
    `created_by` INT,
    `updated_by` INT,
    `updated_at` DATETIME,
    `disabled_at` DATETIME,
    `disabled_by` INT,
    `is_disabled` BOOLEAN NOT NULL DEFAULT 0,
    `favorite` BOOLEAN NOT NULL DEFAULT 0,
    FOREIGN KEY (disabled_by) REFERENCES user(id),
    FOREIGN KEY (created_by) REFERENCES user(id),
    FOREIGN KEY (updated_by) REFERENCES user(id)
);

CREATE TABLE `InventoryApp`.`kill_switch`(
    `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `type` VARCHAR(255) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES user(id)
);

CREATE TABLE `InventoryApp`.`inventory`(
    `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `quantity` INT NOT NULL,
    `description` VARCHAR(255),
    `price` FLOAT NOT NULL,
    `created_at` DATETIME NOT NULL,
    `created_by` INT,
    `is_disabled` BOOLEAN NOT NULL DEFAULT 0,
    `disabled_by` INT,
    `disabled_at` DATETIME,
    `favorite` BOOLEAN NOT NULL DEFAULT 0,
    `image` JSON,
    `updated_at` DATETIME,
    `updated_by` INT,
    FOREIGN KEY (updated_by) REFERENCES user(id),
    FOREIGN KEY (created_by) REFERENCES user(id),
    FOREIGN KEY (disabled_by) REFERENCES user(id)
);

CREATE TABLE `InventoryApp`.`throttle`(
    `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `ip` VARCHAR(255) NOT NULL,
    `device` VARCHAR(255) NOT NULL,
    `created_at` DATETIME NOT NULL,
    `success` BOOLEAN NOT NULL,
    `details` JSON
);

CREATE TABLE `InventoryApp`.`safe`(
    `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `body` VARCHAR(255) NOT NULL,
    `created_at` DATETIME NOT NULL,
    `created_by` INT,
    `disabled_by` INT,
    `disabled_at` DATETIME,
    `favorite` BOOLEAN NOT NULL DEFAULT 0,
    `is_disabled` BOOLEAN NOT NULL DEFAULT 0,
    FOREIGN KEY (disabled_by) REFERENCES user(id),
    FOREIGN KEY (created_by) REFERENCES user(id)
);

CREATE TABLE `InventoryApp`.`comment`(
    `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `body` VARCHAR(255) NOT NULL,
    `created_at` DATETIME NOT NULL,
    `created_by` INT,
    `post_id` INT,
    FOREIGN KEY (post_id) REFERENCES post(id),
    FOREIGN KEY (created_by) REFERENCES user(id)
);

CREATE TABLE `InventoryApp`.`temp`(
    `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `link` VARCHAR(255) NOT NULL,
    `option` JSON,
    `type` VARCHAR(255) NOT NULL,
    `created_by` INT,
    `created_at` DATETIME NOT NULL,
    `is_disabled` BOOLEAN NOT NULL DEFAULT 0,
    `disabled_by` INT,
    `disabled_at` DATETIME,
    FOREIGN KEY (disabled_by) REFERENCES user(id),
    FOREIGN KEY (created_by) REFERENCES user(id)
);

CREATE TABLE `InventoryApp`.`log`(
    `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `action` VARCHAR(255) NOT NULL,
    `created_at` DATETIME NOT NULL,
    `created_by` INT,
    `old_data` JSON,
    `new_data` JSON,
    FOREIGN KEY (created_by) REFERENCES user(id)
);

# Part 3

INSERT INTO
    `InventoryApp`.`user` (username, password, option, created_at, email, first_name, last_name)
VALUES
    ('admin', 'Padrao@123', 
'{
  "permission": {
    "CAN_READ_SAFE": true,
    "can_create_safe": true,
    "can_update_safe": true,
    "can_disable_safe": true,
    "can_see_disabled_safe": true,
    "safe_1": true,
    "safe_2": true,
    "safe_3": true,
    "can_read_inventory": true,
    "can_create_inventory": true,
    "can_update_inventory": true,
    "can_disable_inventory": true,
    "can_see_disabled_inventory": true,
    "user": true,
    "admin": true,
    "super_admin": true
  },
  "email": ""
}', NOW(), 'pleasechangeinapp@mail.com',
'Rafael', 'Camargo Silva');

INSERT INTO
    `InventoryApp`.`user` (username, password, option, created_at, email, first_name, last_name)
VALUES
    ('MadAdmin', 'Padrao@123',
'{
    "permission": {
        "CAN_READ_SAFE": true,
        "can_create_safe": true,
        "can_update_safe": true,
        "can_disable_safe": true,
        "can_see_disabled_safe": true,
        "safe_1": true,
        "safe_2": true,
        "safe_3": true,
        "can_read_inventory": true,
        "can_create_inventory": true,
        "can_update_inventory": true,
        "can_disable_inventory": true,
        "can_see_disabled_inventory": true,
        "user": true,
        "admin": true,
        "super_admin": true,
        "developer": true
    }
}', NOW(), 'paiva.gabriel911@gmail.com',
'Gabriel', 'Camargo de Paiva');
