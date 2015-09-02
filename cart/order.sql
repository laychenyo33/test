-- phpMyAdmin SQL Dump
-- version 3.4.10.1
-- http://www.phpmyadmin.net
--
-- 主機: localhost
-- 產生日期: 2014 年 02 月 14 日 17:25
-- 伺服器版本: 5.0.22
-- PHP 版本: 5.2.17

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 資料庫: `amg_potson`
--

-- --------------------------------------------------------

--
-- 表的結構 `eng_order`
--

CREATE TABLE IF NOT EXISTS `eng_order` (
  `m_id` int(8) NOT NULL,
  `o_id` varchar(20) NOT NULL,
  `o_status` tinyint(1) NOT NULL DEFAULT '1',
  `o_paid` tinyint(4) NOT NULL DEFAULT '0',
  `o_createdate` datetime NOT NULL,
  `o_modifydate` datetime NOT NULL,
  `o_account` varchar(100) NOT NULL,
  `o_company_name` varchar(100) NOT NULL,
  `o_contact_s` tinyint(4) NOT NULL,
  `o_name` varchar(100) NOT NULL,
  `o_zip` int(5) NOT NULL DEFAULT '0',
  `o_country` varchar(50) NOT NULL,
  `o_address` varchar(255) NOT NULL,
  `o_tel` varchar(50) NOT NULL,
  `o_fax` varchar(50) NOT NULL,
  `o_cellphone` varchar(50) NOT NULL,
  `o_email` varchar(255) NOT NULL,
  `o_fee_price` int(8) NOT NULL DEFAULT '0',
  `o_ship_price` int(8) NOT NULL,
  `o_subtotal_price` int(8) NOT NULL,
  `o_total_price` int(8) NOT NULL,
  `o_content` text NOT NULL,
  `o_payment_type` varchar(100) NOT NULL,
  `o_shipment_type` int(11) NOT NULL,
  `o_add_name` varchar(255) NOT NULL,
  `o_add_tel` varchar(50) NOT NULL,
  `o_add_cellphone` varchar(50) NOT NULL,
  `o_add_address` text NOT NULL,
  `o_add_city` varchar(255) NOT NULL,
  `o_add_area` varchar(255) NOT NULL,
  `o_add_zip` varchar(255) NOT NULL,
  `o_add_mail` text NOT NULL,
  `o_invoice_type` tinyint(1) NOT NULL,
  `o_invoice_name` varchar(255) NOT NULL,
  `o_invoice_vat` varchar(50) NOT NULL,
  `o_invoice_text` text NOT NULL,
  `o_shipping_time` date NOT NULL,
  `o_arrival_time` date NOT NULL,
  `del` tinyint(4) NOT NULL DEFAULT '0',
  `o_atm_last5` char(5) NOT NULL,
  `rid` VARCHAR( 255 ) NOT NULL,
  PRIMARY KEY  (`o_id`),
  KEY `mc_id` (`m_id`),
  KEY `o_payed` (`o_paid`),
  KEY `o_shipment_type` (`o_shipment_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
