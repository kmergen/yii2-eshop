CREATE TABLE IF NOT EXISTS `eshop_article_category` (
	`id` INT(11) NOT NULL,
	`name` VARCHAR(100) NOT NULL,
	`parent` INT(11) NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `eshop_article` (
	`id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'The product id',
	`sku` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'SKU or model number.',
	`category_id` INT(11)  NOT NULL COMMENT 'FK The category id from table eshop_product_category ',
	`title` VARCHAR(150) NOT NULL DEFAULT '' COMMENT 'The title of the product',
	`description` LONGTEXT NOT NULL COMMENT 'The description of the product',
	`sell_price` DECIMAL(15,4) NOT NULL,
	`default_qty` SMALLINT(5)  NOT NULL DEFAULT 1,
	`selectable` TINYINT(2) NOT NULL,
	`ordering` TINYINT(4) NOT NULL DEFAULT 1,
	`article_type` TINYINT(2) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `sku` (`sku`),
	INDEX `category_id` (`category_id`),
	CONSTRAINT `FK_eshop_product_eshop_product_category` FOREIGN KEY (`category_id`) REFERENCES `eshop_article_category` (`id`)
)
COMMENT='Product information for nodes.'
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `eshop_order` (
	`id` INT(11)  NOT NULL AUTO_INCREMENT COMMENT 'Primary key: the order ID.',
	`uid` INT(11)  NOT NULL,
	`status` VARCHAR(32) NULL DEFAULT NULL COMMENT 'The order status.',
	`total` DECIMAL(15,4) NOT NULL,
	`phone` VARCHAR(255) NULL DEFAULT '' COMMENT 'The phone number.',
	`billing_firstname` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'The first name of the person paying for the order.',
	`billing_lastname` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'The last name of the person paying for the order.',
	`billing_company` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'The company of the billing address.',
	`billing_street1` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'The street address where the bill will be sent.',
	`billing_street2` VARCHAR(255) NULL DEFAULT '' COMMENT 'The second line of the street address.',
	`billing_zone` VARCHAR(255) NULL DEFAULT '' COMMENT 'The billing zone e.g. Rheinland-Pfalz',
	`billing_postcode` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'The postal code where the bill will be sent.',
	`billing_city` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'The city where the bill will be sent.',
	`billing_country` VARCHAR(2) NOT NULL DEFAULT '' COMMENT 'The country ID of the delivery location.',
	`data` TEXT NULL DEFAULT NULL COMMENT 'A serialized array of extra data.',
	`created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`modified` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`host` VARCHAR(255) NULL DEFAULT '' COMMENT 'Host IP address of the person paying for the order.',
	`comment` TEXT NULL DEFAULT NULL COMMENT 'Order comment',
	PRIMARY KEY (`id`),
	INDEX `uid` (`uid`)
)
COMMENT='Stores Simplecart order information.'
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
COMMENT='The products that have been ordered.'
COLLATE='utf8_general_ci'
ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS `eshop_payment_status` (
	`tid` VARCHAR(64) NOT NULL DEFAULT '',
	`order_id` INT(11)  NOT NULL,
	`paygate` VARCHAR(64) NOT NULL DEFAULT '',
	`status` VARCHAR(64) NOT NULL DEFAULT '',
	`payment_method` VARCHAR(64) NOT NULL DEFAULT '',
	`created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`tid`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
