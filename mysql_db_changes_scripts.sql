
/**** Note: Use following queries to setup the product table and insert the products ****/

/*. 1. To create product table */

CREATE TABLE `test`.`products` (
  `product_id` INT NOT NULL AUTO_INCREMENT COMMENT '',
  `product_code` VARCHAR(15) NOT NULL COMMENT '',
  `product_name` VARCHAR(75) NOT NULL COMMENT '',
  `available_qty` INT NOT NULL DEFAULT 0 COMMENT '',
  `ordered_qty` INT NOT NULL DEFAULT 0 COMMENT '',
  `returned_qty` INT NOT NULL DEFAULT 0 COMMENT '',
  `is_active` BIT NOT NULL DEFAULT 1 COMMENT '',
  `created_ts` DATETIME NOT NULL COMMENT '',
  PRIMARY KEY (`product_id`)  COMMENT '')
ENGINE = InnoDB
AUTO_INCREMENT = 1000;

/* 2. To insert products (A, B , C, D & E) with qty 5 each */

INSERT INTO `test`.`products` (`product_code`, `product_name`, `available_qty`, `ordered_qty`, `returned_qty`, `is_active`, `created_ts`) VALUES ('A', 'Product A', '5', '0', '0', 1, '2017-06-23');
INSERT INTO `test`.`products` (`product_code`, `product_name`, `available_qty`, `ordered_qty`, `returned_qty`, `is_active`, `created_ts`) VALUES ('B', 'Product B', '5', '0', '0', 1, '2017-06-23');
INSERT INTO `test`.`products` (`product_code`, `product_name`, `available_qty`, `ordered_qty`, `returned_qty`, `is_active`, `created_ts`) VALUES ('C', 'Product C', '5', '0', '0', 1, '2017-06-23');
INSERT INTO `test`.`products` (`product_code`, `product_name`, `available_qty`, `ordered_qty`, `returned_qty`, `is_active`, `created_ts`) VALUES ('D', 'Product D', '5', '0', '0', 1, '2017-06-23');
INSERT INTO `test`.`products` (`product_code`, `product_name`, `available_qty`, `ordered_qty`, `returned_qty`, `is_active`, `created_ts`) VALUES ('E', 'Product E', '5', '0', '0', 1, '2017-06-23');
