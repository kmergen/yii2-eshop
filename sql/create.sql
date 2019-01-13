CREATE TABLE IF NOT EXISTS `eshop_address` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`firstname` VARCHAR(255) NULL DEFAULT NULL,
	`lastname` VARCHAR(255) NULL DEFAULT NULL,
	`company` VARCHAR(255) NULL DEFAULT NULL,
	`street` VARCHAR(255) NULL DEFAULT NULL,
	`city` VARCHAR(255) NULL DEFAULT NULL,
	`province` VARCHAR(255) NULL DEFAULT NULL,
	`country_code` VARCHAR(255) NULL DEFAULT NULL,
	`phone1` VARCHAR(255) NULL DEFAULT NULL,
	`phone2` VARCHAR(255) NULL DEFAULT NULL,
	`created_at` DATETIME NOT NULL,
	`updated_at` DATETIME NOT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

CREATE TABLE IF NOT EXISTS `eshop_customer` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`address_id` INT(11) NOT NULL,
	`email` VARCHAR(255) NOT NULL,
	`user_id` INT(11) NULL DEFAULT NULL COMMENT 'The user id from the yii application',
	`birthday` DATE NULL DEFAULT NULL,
	`gender` VARCHAR(1) NULL DEFAULT 'u',
	`created_at` DATETIME NOT NULL,
	`updated_at` DATETIME NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `email` (`email`),
	INDEX `FK_customer_eshop_address` (`address_id`),
	INDEX `user_id` (`user_id`),
	CONSTRAINT `FK_customer_eshop_address` FOREIGN KEY (`address_id`) REFERENCES `eshop_address` (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `eshop_article_category` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(100) NOT NULL,
	`parent` INT(11) NOT NULL DEFAULT '0',
	`shipping` TINYINT(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `eshop_article` (
	`id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'The product id',
	`sku` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'SKU or model number.',
	`category_id` INT(11) NOT NULL COMMENT 'FK The category id from table eshop_product_category ',
	`title` VARCHAR(150) NOT NULL DEFAULT '' COMMENT 'The title of the product',
	`description` LONGTEXT NOT NULL COMMENT 'The description of the product',
	`sell_price` DECIMAL(15,4) NOT NULL,
	`default_qty` SMALLINT(5) NOT NULL DEFAULT '1',
	`active` TINYINT(2) NOT NULL DEFAULT '1',
	`media_album_id` INT(11) NULL DEFAULT NULL,
	`created_at` DATETIME NOT NULL,
	`updated_at` DATETIME NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `sku` (`sku`),
	INDEX `category_id` (`category_id`),
	INDEX `FK_eshop_article_media_album` (`media_album_id`),
	CONSTRAINT `FK_eshop_article_eshop_article_category` FOREIGN KEY (`category_id`) REFERENCES `eshop_article_category` (`id`) ON UPDATE CASCADE,
	CONSTRAINT `FK_eshop_article_media_album` FOREIGN KEY (`media_album_id`) REFERENCES `media_album` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;



CREATE TABLE `eshop_order` (
	`id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary key: the order ID.',
	`customer_id` INT(11) NOT NULL,
	`status` VARCHAR(32) NULL DEFAULT NULL COMMENT 'The order status.',
	`total` DECIMAL(15,4) NOT NULL,
	`invoice_address_id` INT(11) NULL DEFAULT NULL,
	`shipping_address_id` INT(11) NULL DEFAULT NULL,
	`data` TEXT NULL DEFAULT NULL COMMENT 'A serialized array of extra data.',
	`ip` VARCHAR(255) NULL DEFAULT '' COMMENT 'Host IP address of the person paying for the order.',
	`comment` TEXT NULL DEFAULT NULL COMMENT 'Order comment',
	`created_at` DATETIME NOT NULL,
	`updated_at` DATETIME NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `uid` (`customer_id`),
	INDEX `FK_eshop_order_eshop_address` (`invoice_address_id`),
	INDEX `FK_eshop_order_eshop_address_2` (`shipping_address_id`),
	CONSTRAINT `FK_eshop_order_customer` FOREIGN KEY (`customer_id`) REFERENCES `eshop_customer` (`id`),
	CONSTRAINT `FK_eshop_order_eshop_address` FOREIGN KEY (`invoice_address_id`) REFERENCES `eshop_address` (`id`),
	CONSTRAINT `FK_eshop_order_eshop_address_2` FOREIGN KEY (`shipping_address_id`) REFERENCES `eshop_address` (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS `eshop_order_item` (
	`order_id` INT(11)  NOT NULL COMMENT 'The sc_order.order_id.',
	`article_id` INT(11)  NOT NULL COMMENT 'The product id from table product',
	`title` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'The product title, from node.title.',
	`sku` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'The product model/SKU, from sc_products.model.',
	`qty` SMALLINT(5)  NOT NULL,
	`sell_price` DECIMAL(15,4) NOT NULL,
	`data` TEXT NULL DEFAULT NULL COMMENT 'A serialized array of extra data.',
	PRIMARY KEY (`order_id`, `article_id`),
	CONSTRAINT `FK_eshop_order_item_eshop_order` FOREIGN KEY (`order_id`) REFERENCES `eshop_order` (`id`) ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `eshop_payment` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`order_id` INT(11) NOT NULL,
	`transaction_id` VARCHAR(64) NOT NULL DEFAULT '',
	`status` VARCHAR(64) NOT NULL DEFAULT '',
	`payment_method` VARCHAR(64) NOT NULL DEFAULT '',
	`created_at` DATETIME NOT NULL,
	`updated_at` DATETIME NOT NULL,
	`data` BLOB NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `FK_eshop_payment_eshop_order` (`order_id`),
	CONSTRAINT `FK_eshop_payment_eshop_order` FOREIGN KEY (`order_id`) REFERENCES `eshop_order` (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
