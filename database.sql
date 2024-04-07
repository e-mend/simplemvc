# Part 1

CREATE SCHEMA `InventoryApp`;

# Part 2

CREATE TABLE `InventoryApp`.`user`(
    `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `permission` JSON NOT NULL,
    `created_at` DATETIME NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `is_deleted` BOOLEAN NOT NULL DEFAULT 0,
    `image` VARCHAR(255)
);

CREATE TABLE `InventoryApp`.`permission`(
    `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL
);

CREATE TABLE `InventoryApp`.`inventory`(
    `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `quantity` INT NOT NULL,
    `description` VARCHAR(255),
    `price` VARCHAR(255) NOT NULL,
    `created_at` DATETIME NOT NULL,
    `is_deleted` BOOLEAN NOT NULL DEFAULT 0,
    `deleted_by` INT,
    `deleted_at` DATETIME,
    `created_by` INT,
    `favorite` BOOLEAN NOT NULL DEFAULT 0,
    FOREIGN KEY (created_by) REFERENCES user(id),
    FOREIGN KEY (deleted_by) REFERENCES user(id)
);

CREATE TABLE `InventoryApp`.`throttle`(
    `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `ip` VARCHAR(255) NOT NULL,
    `device` VARCHAR(255) NOT NULL,
    `created_at` DATETIME NOT NULL,
    `success` BOOLEAN NOT NULL
);

CREATE TABLE `InventoryApp`.`post`(
    `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `body` VARCHAR(255) NOT NULL,
    `created_at` DATETIME NOT NULL,
    `is_deleted` BOOLEAN NOT NULL DEFAULT 0,
    `created_by` INT,
    `deleted_by` INT,
    `deleted_at` DATETIME,
    `favorite` BOOLEAN NOT NULL DEFAULT 0,
    FOREIGN KEY (deleted_by) REFERENCES user(id),
    FOREIGN KEY (created_by) REFERENCES user(id)
);

CREATE TABLE `InventoryApp`.`temp`(
    `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `link` VARCHAR(255) NOT NULL,
    `created_at` DATETIME NOT NULL,
    `permission` JSON NOT NULL,
    `is_deleted` BOOLEAN NOT NULL DEFAULT 0,
    `created_by` INT,
    `deleted_by` INT,
    `deleted_at` DATETIME,
    FOREIGN KEY (deleted_by) REFERENCES user(id),
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
    `InventoryApp`.`permission` (name) 
VALUES 
    ('can_read_post'), ('can_create_post'), ('can_update_post'), ('can_delete_post'), ('can_see_deleted_posts'),
    ('post_1'), ('post_2'), ('post_3'), 
    ('can_read_inventory'), ('can_create_inventory'), ('can_update_inventory'), ('can_delete_inventory'),
    ('developer'), ('admin'), ('normal_user');

INSERT INTO
    `InventoryApp`.`user` (username, password, permission, created_at, email)
VALUES
    ('admin', 'padrao@123', 
'{
    "permissions": [
    "can_read_post",
    "can_create_post",
    "can_update_post",
    "can_delete_post",
    "can_see_deleted_posts",
    "post_1",
    "post_2",
    "post_3",
    "can_read_inventory",
    "can_create_inventory",
    "can_update_inventory",
    "can_delete_inventory",
    "admin"
  ]
}', NOW(), 'pleasechangeinapp@mail.com');
