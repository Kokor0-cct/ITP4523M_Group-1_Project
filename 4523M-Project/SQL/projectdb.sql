SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS `projectdb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `projectdb`;

DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `cid` int(11) NOT NULL,
  `cname` varchar(255) NOT NULL,
  `cpassword` varchar(255) NOT NULL,
  `ctel` varchar(20) NOT NULL,
  `caddr` varchar(255) NOT NULL,
  `company` varchar(255) DEFAULT NULL,
  `cBudget` int(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `customers` (`cid`, `cname`, `cpassword`, `ctel`, `caddr`, `company`, `cBudget`) VALUES
(1, 'taiman', 'cust123', '12312333', 'Flat A, 12/F, Sunshine Building, Mong Kok, Kowloon', 'ABC Trading Ltd.', 8700),
(2, 'siuming', 'cust456', '98765432', 'Room 8, 3/F, Harbour View Court, Tsuen Wan, New Territories', NULL, 0);

DROP TABLE IF EXISTS `furniturematerials`;
CREATE TABLE `furniturematerials` (
  `fid` int(11) NOT NULL,
  `mid` int(11) NOT NULL,
  `pmqty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `furniturematerials` (`fid`, `mid`, `pmqty`) VALUES
(1, 1, 2),
(2, 1, 10),
(3, 1, 5),
(3, 3, 10),
(3, 4, 3),
(4, 1, 15),
(5, 1, 4),
(5, 2, 6),
(6, 1, 12);

DROP TABLE IF EXISTS `furnitures`;
CREATE TABLE `furnitures` (
  `fid` int(11) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `fdesc` varchar(255) NOT NULL,
  `fSize` varchar(120) NOT NULL,
  `fprice` decimal(10,2) NOT NULL,
  `fStock` int(11) NOT NULL,
  `fImgPath` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `furnitures` (`fid`, `fname`, `fdesc`, `fSize`, `fprice`, `fStock`, `fImgPath`) VALUES
(1, 'Oak Dining Chair', 'Classic style dining chair made of solid oak.', '85cm*85cm*90cm', 450.00, 314, '../img/Oak_Dining_Chair.png'),
(2, 'Large Dining Table', '6-seater dining table, perfect for families.', '16cm*90cm*75', 2500.00, 253, '../img/Large Dining Table.png'),
(3, '3-Seater Fabric Sofa', 'Comfortable grey fabric sofa with foam filling.', '185cm*76cm* 81cm', 3800.00, 0, '../img/3-Seater Fabric Sofa.png'),
(4, 'Wooden Wardrobe', 'Double door wardrobe with hanging space.', '100cm*55cm*195cm', 1800.00, 222, '../img/Wooden Wardrobe.png'),
(5, 'Industrial Bookshelf', 'Modern style bookshelf with steel frame.', '83cm*34cm*180cm', 1200.00, 125, '../img/Industrial Bookshelf.png'),
(6, 'Queen Size Bed Frame', 'Sturdy bed frame for queen size mattress.', '205cm*155cm*35cm', 2200.00, 182, '../img/Queen Size Bed Frame.png');

DROP TABLE IF EXISTS `materials`;
CREATE TABLE `materials` (
  `mid` int(11) NOT NULL,
  `mname` varchar(255) NOT NULL,
  `mqty` int(11) NOT NULL DEFAULT 0,
  `munit` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `materials` (`mid`, `mname`, `mqty`, `munit`) VALUES
(1, 'Oak Wood Plank', 500, 'pcs'),
(2, 'Steel Tube', 200, 'meter'),
(3, 'Fabric Cloth', 100, 'meter'),
(4, 'High Density Foam', 50, 'block');

DROP TABLE IF EXISTS `orderfurnitures`;
CREATE TABLE `orderfurnitures` (
  `oid` int(11) NOT NULL,
  `fid` int(11) NOT NULL,
  `oqty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `orderfurnitures` (`oid`, `fid`, `oqty`) VALUES
(1, 1, 1),
(3, 1, 6),
(3, 2, 2),
(3, 5, 3),
(2, 6, 1);

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `oid` int(11) NOT NULL,
  `odate` datetime NOT NULL DEFAULT current_timestamp(),
  `ototalamount` decimal(10,2) NOT NULL,
  `cid` int(11) NOT NULL,
  `odeliverydate` datetime NOT NULL,
  `odeliveraddress` text NOT NULL,
  `ostatus` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `orders` (`oid`, `odate`, `ototalamount`, `cid`, `odeliverydate`, `odeliveraddress`, `ostatus`) VALUES
(1, '2026-06-19 16:10:56', 450.00, 1, '2026-04-10 14:00:00', 'Flat A, 12/F, Sunshine Building, Mong Kok, Kowloon', 1),
(2, '2026-06-19 16:10:56', 2200.00, 1, '2026-04-12 10:00:00', 'Flat A, 12/F, Sunshine Building, Mong Kok, Kowloon', 1),
(3, '2026-06-23 09:14:16', 11300.00, 1, '2026-06-28 15:14:00', 'Flat A, 12/F, Sunshine Building, Mong Kok, Kowloon', 1);

DROP TABLE IF EXISTS `staffs`;
CREATE TABLE `staffs` (
  `sid` int(11) NOT NULL,
  `spassword` varchar(255) NOT NULL,
  `sname` varchar(255) NOT NULL,
  `srole` varchar(50) NOT NULL,
  `stel` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `staffs` (`sid`, `spassword`, `sname`, `srole`, `stel`) VALUES
(1, 'admin', 'Admin', 'Administrator', '12345678');


ALTER TABLE `customers`
  ADD PRIMARY KEY (`cid`);

ALTER TABLE `furniturematerials`
  ADD PRIMARY KEY (`fid`,`mid`),
  ADD KEY `mid` (`mid`);

ALTER TABLE `furnitures`
  ADD PRIMARY KEY (`fid`);

ALTER TABLE `materials`
  ADD PRIMARY KEY (`mid`);

ALTER TABLE `orderfurnitures`
  ADD PRIMARY KEY (`fid`,`oid`),
  ADD KEY `oid` (`oid`);

ALTER TABLE `orders`
  ADD PRIMARY KEY (`oid`),
  ADD KEY `cid` (`cid`);

ALTER TABLE `staffs`
  ADD PRIMARY KEY (`sid`);


ALTER TABLE `customers`
  MODIFY `cid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `furnitures`
  MODIFY `fid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

ALTER TABLE `materials`
  MODIFY `mid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `orders`
  MODIFY `oid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

ALTER TABLE `staffs`
  MODIFY `sid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;


ALTER TABLE `furniturematerials`
  ADD CONSTRAINT `furniturematerials_ibfk_1` FOREIGN KEY (`fid`) REFERENCES `furnitures` (`fid`),
  ADD CONSTRAINT `furniturematerials_ibfk_2` FOREIGN KEY (`mid`) REFERENCES `materials` (`mid`);

ALTER TABLE `orderfurnitures`
  ADD CONSTRAINT `orderfurnitures_ibfk_1` FOREIGN KEY (`fid`) REFERENCES `furnitures` (`fid`),
  ADD CONSTRAINT `orderfurnitures_ibfk_2` FOREIGN KEY (`oid`) REFERENCES `orders` (`oid`);

ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`cid`) REFERENCES `customers` (`cid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
