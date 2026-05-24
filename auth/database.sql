-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 02, 2025 at 02:24 PM
-- Server version: 10.6.23-MariaDB
-- PHP Version: 8.3.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `clcxkjhp_Paymentgateway1432`
--

-- --------------------------------------------------------

--
-- Table structure for table `amazon_token`
--

CREATE TABLE `amazon_token` (
  `id` int(11) NOT NULL,
  `phoneNumber` varchar(20) DEFAULT NULL,
  `failCount` int(3) DEFAULT 0,
  `user_token` varchar(2555) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `date` timestamp NULL DEFAULT current_timestamp(),
  `ubid_acbin` mediumtext DEFAULT NULL,
  `at_acbin` mediumtext DEFAULT NULL,
  `x_acbin` mediumtext DEFAULT NULL,
  `upi_id` varchar(50) DEFAULT NULL,
  `cookies` varchar(2555) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `api_settings`
--

CREATE TABLE `api_settings` (
  `id` int(11) NOT NULL,
  `whatsapp_api_url` varchar(255) NOT NULL,
  `sender_id` varchar(50) NOT NULL,
  `api_key` varchar(100) NOT NULL,
  `sender_email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `api_settings`
--

INSERT INTO `api_settings` (`id`, `whatsapp_api_url`, `sender_id`, `api_key`, `sender_email`) VALUES
(1, 'https://fast..in/send-message?api_key=GxtcnAHodm3okM36s7kNqqRyNlpAiC&sender=9876543210&number={MOBOLE}&message={MSG}&footer=Team APNAGATEWAY', '9876543210', 'XIsLeipb3l8JJJYH94YCgkG3oOKLNt', 'dvdcv.business@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `bharatpe_tokens`
--

CREATE TABLE `bharatpe_tokens` (
  `id` int(11) NOT NULL,
  `user_token` longtext DEFAULT NULL,
  `phoneNumber` varchar(255) DEFAULT NULL,
  `failCount` int(3) DEFAULT 0,
  `token` varchar(255) DEFAULT NULL,
  `cookie` text DEFAULT NULL,
  `merchantId` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` varchar(255) DEFAULT 'Deactive',
  `Upiid` varchar(255) DEFAULT NULL,
  `user_id` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `callback_report`
--

CREATE TABLE `callback_report` (
  `id` int(11) NOT NULL,
  `order_id` varchar(255) NOT NULL,
  `request_url` longtext NOT NULL,
  `response` longtext NOT NULL,
  `user_token` varchar(255) NOT NULL,
  `mobile` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `earnings`
--

CREATE TABLE `earnings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `freecharge_token`
--

CREATE TABLE `freecharge_token` (
  `id` int(11) NOT NULL,
  `phoneNumber` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `failCount` int(3) DEFAULT 0,
  `user_token` varchar(150) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `app_fc` text CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `Upiid` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `status` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `user_id` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `googlepay_tokens`
--

CREATE TABLE `googlepay_tokens` (
  `id` int(12) NOT NULL,
  `user_token` longtext DEFAULT NULL,
  `phoneNumber` varchar(255) DEFAULT NULL,
  `failCount` int(3) DEFAULT NULL,
  `Instance_Id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` varchar(255) DEFAULT 'Deactive',
  `Upiid` varchar(255) DEFAULT NULL,
  `user_id` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `googlepay_transactions`
--

CREATE TABLE `googlepay_transactions` (
  `id` int(11) NOT NULL,
  `user_token` longtext DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `utr` bigint(20) DEFAULT NULL,
  `user_id` int(12) DEFAULT NULL,
  `paymentTimestamp` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hdfc`
--

CREATE TABLE `hdfc` (
  `id` int(11) NOT NULL,
  `number` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `failCount` int(3) DEFAULT 0,
  `seassion` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `device_id` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `user_token` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `pin` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Request_Upi` tinyint(1) DEFAULT 1,
  `upi_hdfc` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `UPI` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tidlist` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  `status` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `mobile` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `user_id` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `manual_token`
--

CREATE TABLE `manual_token` (
  `id` int(11) NOT NULL,
  `date` timestamp NULL DEFAULT current_timestamp(),
  `phoneNumber` varchar(20) DEFAULT NULL,
  `failCount` int(3) DEFAULT NULL,
  `upi_id` varchar(100) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `bank_ac_number` varchar(50) DEFAULT NULL,
  `ifsc_code` varchar(20) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_token` varchar(255) DEFAULT NULL,
  `status` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `merchants`
--

CREATE TABLE `merchants` (
  `id` int(11) NOT NULL,
  `merchantname` varchar(255) NOT NULL,
  `DisplayName` varchar(25) DEFAULT NULL,
  `tablename` varchar(255) NOT NULL,
  `foldername` varchar(255) NOT NULL,
  `user_update_column` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `merchants`
--

INSERT INTO `merchants` (`id`, `merchantname`, `DisplayName`, `tablename`, `foldername`, `user_update_column`, `status`) VALUES
(1, 'hdfc', 'HDFC Vyapar', 'hdfc', 'payment', 'hdfc_connected', 1),
(2, 'phonepe', 'Phonepe Business', 'phonepe_tokens', 'payment2', 'phonepe_connected', 1),
(3, 'paytm', 'PayTm Business', 'paytm_tokens', 'payment3', 'paytm_connected', 1),
(4, 'bharatpe', 'Bharatpe Business', 'bharatpe_tokens', 'payment4', 'bharatpe_connected', 1),
(5, 'googlepay', 'Gpay Business', 'googlepay_tokens', 'payment5', 'googlepay_connected', 0),
(6, 'freecharge', 'FreeCharge UPI', 'freecharge_token', 'payment6', 'freecharge_connected', 1),
(7, 'sbi', 'SBI Yono Merchant', 'sbi_token', 'payment7', 'sbi_connected', 1),
(8, 'mobikwik', 'Mobiquik Pocket UPI', 'mobikwik_token', 'payment8', 'mobikwik_connected', 1),
(9, 'manual', 'Manual UPI', 'manual_token', 'payment9', 'manual_connected', 1),
(10, 'amazon', 'Amazon UPI', 'amazon_token', 'payment92', 'amazon_connected', 1),
(11, 'paynearby', 'PayNearBy Retailer UPI', 'paynearby_token', 'payment93', 'paynearby_connected', 1),
(12, 'bajaj', 'BajajFinserv Business', 'bajaj_token', 'payment94', 'bajaj_connected', 0),
(13, 'quintuspay', 'quintuspay', 'quintuspay_token', 'payment95', 'quintuspay_connected', 1);

-- --------------------------------------------------------

--
-- Table structure for table `mobikwik_token`
--

CREATE TABLE `mobikwik_token` (
  `id` int(11) NOT NULL,
  `user_token` longtext CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `phoneNumber` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `failCount` int(3) DEFAULT 0,
  `Authorization` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `merchant_upi` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `date` timestamp NULL DEFAULT current_timestamp(),
  `status` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT 'Deactive',
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `offers`
--

CREATE TABLE `offers` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `validity` date NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_id` mediumtext NOT NULL,
  `user_token` longtext NOT NULL,
  `status` text NOT NULL,
  `amount` int(11) NOT NULL,
  `utr` longtext NOT NULL,
  `remark` longtext DEFAULT NULL,
  `customer_name` longtext DEFAULT NULL,
  `customer_mobile` longtext NOT NULL,
  `payerUpi` varchar(64) DEFAULT NULL,
  `redirect_url` longtext NOT NULL,
  `remark1` longtext NOT NULL,
  `remark2` longtext NOT NULL,
  `gateway_txn` longtext NOT NULL,
  `method` text NOT NULL,
  `merchentMobile` longtext DEFAULT NULL,
  `HDFC_TXNID` mediumtext DEFAULT NULL,
  `upiLink` mediumtext DEFAULT NULL,
  `description` mediumtext DEFAULT NULL,
  `byteTransactionId` varchar(255) DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  `paytm_txn_ref` varchar(255) DEFAULT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `webhook_sent` varchar(25) DEFAULT 'no',
  `notifi_send` varchar(3) DEFAULT 'no'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_links`
--

CREATE TABLE `payment_links` (
  `id` int(11) NOT NULL,
  `link_token` varchar(255) NOT NULL,
  `order_id` varchar(255) NOT NULL,
  `user_token` varchar(255) DEFAULT NULL,
  `merchentMobile` varchar(10) DEFAULT NULL,
  `payee_vpa` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `paynearby_token`
--

CREATE TABLE `paynearby_token` (
  `id` int(11) NOT NULL,
  `phoneNumber` varchar(15) NOT NULL,
  `failCount` int(11) DEFAULT 0,
  `user_token` text DEFAULT NULL,
  `status` enum('Active','Deactive') DEFAULT 'Deactive',
  `user_id` int(11) DEFAULT NULL,
  `upi_id` varchar(100) DEFAULT NULL,
  `agent_ref_id` varchar(10) DEFAULT NULL,
  `authorization` varchar(512) DEFAULT NULL,
  `deviceid` varchar(64) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `paytm_tokens`
--

CREATE TABLE `paytm_tokens` (
  `id` int(11) NOT NULL,
  `user_token` longtext NOT NULL,
  `phoneNumber` varchar(255) DEFAULT NULL,
  `failCount` int(3) DEFAULT 0,
  `MID` varchar(255) DEFAULT NULL,
  `Upiid` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(255) DEFAULT 'Deactive',
  `user_id` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `phonepe_tokens`
--

CREATE TABLE `phonepe_tokens` (
  `sl` int(11) NOT NULL,
  `user_token` longtext NOT NULL,
  `phoneNumber` longtext NOT NULL,
  `failCount` int(3) NOT NULL DEFAULT 0,
  `merchant_upi` varchar(150) DEFAULT NULL,
  `userId` longtext NOT NULL,
  `token` longtext NOT NULL,
  `refreshToken` longtext NOT NULL,
  `name` text NOT NULL,
  `device_data` longtext NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Deactive',
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `planorders`
--

CREATE TABLE `planorders` (
  `id` int(11) NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `userid` int(11) NOT NULL,
  `planid` int(11) NOT NULL,
  `amount` varchar(10) DEFAULT NULL,
  `payment_date` datetime NOT NULL,
  `expiry_date` datetime DEFAULT NULL,
  `utr` varchar(50) DEFAULT NULL,
  `status` enum('PENDING','SUCCESS','FAILURE') NOT NULL DEFAULT 'PENDING',
  `remark` varchar(255) DEFAULT NULL,
  `userMobile` varchar(15) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quintuspay_token`
--

CREATE TABLE `quintuspay_token` (
  `id` int(11) NOT NULL,
  `phoneNumber` varchar(15) NOT NULL,
  `failCount` int(11) DEFAULT 0,
  `user_token` varchar(255) NOT NULL,
  `token` longtext DEFAULT NULL,
  `status` enum('Active','Deactive') DEFAULT 'Active',
  `user_id` int(11) NOT NULL,
  `upi_id` varchar(100) DEFAULT NULL,
  `date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `refer_income_slabs`
--

CREATE TABLE `refer_income_slabs` (
  `id` int(11) NOT NULL,
  `title` varchar(50) NOT NULL,
  `required_referrals` int(11) NOT NULL,
  `commission_percent` decimal(5,2) NOT NULL,
  `color_class` varchar(20) DEFAULT 'primary',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `refer_income_slabs`
--

INSERT INTO `refer_income_slabs` (`id`, `title`, `required_referrals`, `commission_percent`, `color_class`, `created_at`) VALUES
(22, 'Beginner', 5, 5.00, 'success', '2025-05-12 16:58:50'),
(23, 'Growth', 10, 10.00, 'primary', '2025-05-12 16:58:50'),
(24, 'Pro', 50, 20.00, 'warning', '2025-05-12 16:58:50'),
(25, 'Elite', 100, 40.00, 'danger', '2025-05-12 16:58:50');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `transactionId` mediumtext DEFAULT NULL,
  `status` mediumtext DEFAULT NULL,
  `order_id` mediumtext DEFAULT NULL,
  `vpa` mediumtext DEFAULT NULL,
  `paymentApp` mediumtext DEFAULT NULL,
  `amount` mediumtext DEFAULT NULL,
  `user_token` mediumtext DEFAULT NULL,
  `UTR` mediumtext DEFAULT NULL,
  `description` mediumtext DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  `mobile` varchar(255) DEFAULT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `merchantTransactionId` varchar(255) DEFAULT NULL,
  `transactionNote` varchar(255) DEFAULT NULL,
  `user_id` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sbi_token`
--

CREATE TABLE `sbi_token` (
  `id` int(11) NOT NULL,
  `phoneNumber` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `failCount` int(3) NOT NULL DEFAULT 0,
  `merchant_username` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `merchant_session` text CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `merchant_csrftoken` text CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `merchant_token` varchar(150) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `merchant_upi` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `status` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `user_token` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `user_id` varchar(150) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settlement`
--

CREATE TABLE `settlement` (
  `id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `utr_no` varchar(50) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `remark` text DEFAULT NULL,
  `date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `brand_name` varchar(255) NOT NULL,
  `logo_url` varchar(255) NOT NULL,
  `site_link` varchar(255) NOT NULL,
  `whatsapp_number` varchar(20) NOT NULL,
  `copyright_text` varchar(255) NOT NULL,
  `refer_system_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Refer system ON (1) or OFF (0)',
  `bonus_refer` int(3) NOT NULL DEFAULT 0 COMMENT 'Income for the referred person',
  `income_sponser` int(3) NOT NULL DEFAULT 0 COMMENT 'Income for the Sponser',
  `vip_plan` int(3) NOT NULL COMMENT 'montly plan amount'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `brand_name`, `logo_url`, `site_link`, `whatsapp_number`, `copyright_text`, `refer_system_status`, `bonus_refer`, `income_sponser`, `vip_plan`) VALUES
(1, 'FastGateway', 'https://static.vecteezy.com/system/resources/previews/041/270/510/non_2x/pay-letter-logo-design-inspiration-for-a-unique-identity-modern-elegance-and-creative-design-watermark-your-success-with-the-striking-this-logo-vector.jpg', 'https://upi.lhrce.in', '9876543210', 'FastGateway™', 1, 10, 99, 99);

-- --------------------------------------------------------

--
-- Table structure for table `store_id`
--

CREATE TABLE `store_id` (
  `sl` int(11) NOT NULL,
  `user_token` longtext NOT NULL,
  `unitId` longtext NOT NULL,
  `roleName` longtext NOT NULL,
  `groupValue` longtext NOT NULL,
  `groupId` longtext NOT NULL,
  `user_id` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_plan`
--

CREATE TABLE `subscription_plan` (
  `id` int(11) NOT NULL,
  `plan_name` varchar(100) DEFAULT NULL,
  `amount` varchar(50) DEFAULT NULL,
  `expiry` varchar(50) DEFAULT NULL COMMENT 'In Month',
  `hitLimit` int(5) NOT NULL,
  `singleMerchantLimit` int(3) NOT NULL,
  `totalMerchantLimit` int(3) NOT NULL,
  `status` varchar(10) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscription_plan`
--

INSERT INTO `subscription_plan` (`id`, `plan_name`, `amount`, `expiry`, `hitLimit`, `singleMerchantLimit`, `totalMerchantLimit`, `status`, `date`) VALUES
(1, 'Starter', '199', '1', 5000, 1, 1, 'active', '2024-10-11 18:20:31'),
(5, 'Trial', '0.00', '1', 100, 3, 10, 'inactive', '2025-03-04 06:49:20'),
(6, 'Business', '299', '1', 10000, 1, 3, 'active', '2025-03-04 09:52:27'),
(7, 'Infinity', '499', '1', 15000, 3, 5, 'active', '2025-03-04 09:55:46'),
(8, 'Ultimate', '999', '1', 25000, 5, 10, 'active', '2025-03-04 09:57:03'),
(9, 'Starter Yearly', '1999', '12', 50000, 1, 1, 'active', '2025-03-04 14:02:23'),
(10, 'Business Yearly', '2999', '12', 100000, 1, 3, 'active', '2025-03-04 14:03:43'),
(11, 'Infinity Yearly', '4999', '12', 150000, 3, 5, 'active', '2025-03-04 14:04:28'),
(12, 'Ultimate Yearly', '9999', '12', 300000, 5, 10, 'active', '2025-03-04 14:05:29');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `mobile` varchar(255) NOT NULL,
  `merchant_id` varchar(50) DEFAULT NULL,
  `role` enum('User','Admin','Developer','') NOT NULL DEFAULT 'User',
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `password` varchar(255) NOT NULL,
  `is_otp` enum('YES','NO') NOT NULL,
  `otp` varchar(6) NOT NULL,
  `otp_expiry` datetime NOT NULL,
  `whatsapp_alert` enum('YES','NO') NOT NULL,
  `email_alert` enum('YES','NO') NOT NULL,
  `email` varchar(255) NOT NULL,
  `company` varchar(255) NOT NULL,
  `pin` varchar(255) NOT NULL,
  `pan` varchar(255) NOT NULL,
  `aadhaar` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `user_token` varchar(255) NOT NULL,
  `expiry` date NOT NULL,
  `vip_expiry` date NOT NULL DEFAULT '2025-01-01',
  `tranjection_Count` int(6) NOT NULL DEFAULT 0,
  `planId` int(11) DEFAULT NULL,
  `callback_url` longtext NOT NULL,
  `bptoken` longtext NOT NULL,
  `upiid` longtext NOT NULL,
  `acc_lock` int(11) NOT NULL DEFAULT 0,
  `acc_ban` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'off',
  `upi_id` mediumtext DEFAULT NULL COMMENT 'This is the UPI ID for PhonePe',
  `referral_code` varchar(25) DEFAULT NULL,
  `referred_by` int(11) DEFAULT NULL,
  `Intent_unable` tinyint(1) DEFAULT 1,
  `merchentRouting` tinyint(1) DEFAULT 0,
  `telegram_subscribed` varchar(3) DEFAULT 'off',
  `telegram_chat_id` varchar(25) DEFAULT NULL,
  `telegram_username` varchar(25) DEFAULT NULL,
  `phonepe_connected` varchar(3) DEFAULT 'No',
  `hdfc_connected` varchar(3) DEFAULT 'No',
  `paytm_connected` varchar(3) DEFAULT 'No',
  `bharatpe_connected` varchar(3) DEFAULT 'No',
  `googlepay_connected` varchar(3) DEFAULT 'No',
  `mobikwik_connected` enum('Yes','No') NOT NULL DEFAULT 'No',
  `sbi_connected` enum('Yes','No') NOT NULL DEFAULT 'No',
  `freecharge_connected` enum('Yes','No') NOT NULL DEFAULT 'No',
  `amazon_connected` varchar(3) DEFAULT 'No',
  `manual_connected` enum('Yes','No') DEFAULT NULL,
  `paynearby_connected` varchar(3) NOT NULL DEFAULT 'No',
  `quintuspay_connected` varchar(3) DEFAULT 'No',
  `instance_id` varchar(255) DEFAULT NULL,
  `instance_secret` varchar(255) DEFAULT NULL,
  `fixed_navbar` varchar(50) DEFAULT NULL,
  `fixed_layout` varchar(50) DEFAULT NULL,
  `sidebar_layout` varchar(50) DEFAULT NULL,
  `box_style` varchar(50) DEFAULT NULL,
  `theme_color` varchar(50) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `login_token` varchar(255) DEFAULT NULL,
  `create_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `mobile`, `merchant_id`, `role`, `balance`, `password`, `is_otp`, `otp`, `otp_expiry`, `whatsapp_alert`, `email_alert`, `email`, `company`, `pin`, `pan`, `aadhaar`, `location`, `user_token`, `expiry`, `vip_expiry`, `tranjection_Count`, `planId`, `callback_url`, `bptoken`, `upiid`, `acc_lock`, `acc_ban`, `upi_id`, `referral_code`, `referred_by`, `Intent_unable`, `merchentRouting`, `telegram_subscribed`, `telegram_chat_id`, `telegram_username`, `phonepe_connected`, `hdfc_connected`, `paytm_connected`, `bharatpe_connected`, `googlepay_connected`, `mobikwik_connected`, `sbi_connected`, `freecharge_connected`, `amazon_connected`, `manual_connected`, `paynearby_connected`, `quintuspay_connected`, `instance_id`, `instance_secret`, `fixed_navbar`, `fixed_layout`, `sidebar_layout`, `box_style`, `theme_color`, `logo`, `login_token`, `create_date`) VALUES
(162, 'fastpay', '8984473230', 'MS5Y51BPZ6Q61729109450', 'Admin', 13.00, '$2b$12$F0N0o3LFp.4OIYI0BvW8d.O3TSERm/d9/oIgqnoQbksaVflhQwIBi', 'NO', '847390', '2025-08-31 06:20:25', 'YES', 'NO', 'admin@ggmail.com', 'fastgateway', '234557', 'GYBPS63DGDFG', '9859859859', 'DCGVBFDXGBDF', '3b5a65c28184fb285ab2751307c8908c', '2039-07-01', '2039-07-01', 2072, 12, 'https://web.whatsapp.com/', '', '', 0, 'off', NULL, '9CC8FDCD5AB9', NULL, 0, 1, 'on', '5983288467', 'FGBNHFDH', 'No', 'Yes', 'No', 'Yes', 'No', 'No', 'No', 'No', 'Yes', 'No', 'No', 'No', 'ISFPZhiUZ9721689543', NULL, '1', '1', '', '', 'default', 'https://previews.123rf.com/images/keath/keath1609/keath160900267/63404714-payment-icon-money-and-payment-red-button-badge-illustration.jpg', NULL, '2025-08-06 20:45:18'),
(257, 'USER', '8984473231', 'MS3091CAABBE1734511050', 'User', 0.00, '$2b$12$F0N0o3LFp.4OIYI0BvW8d.O3TSERm/d9/oIgqnoQbksaVflhQwIBi', 'NO', '256741', '2025-08-09 12:13:55', 'YES', 'YES', 'dfghdf@g.nb', 'FastPay', '410502', 'ASNPK1902Q', '8445556558454', 'PUNE ', '253658775914f93c7dfe36802e541d58', '2035-08-04', '2025-02-07', 702, 12, '', '', '', 0, 'off', NULL, '275B3AF6830C', NULL, 1, 1, 'off', NULL, NULL, 'No', 'No', 'Yes', 'No', 'No', 'No', 'No', 'Yes', 'No', NULL, 'No', 'No', 'Iv0C1TBjZF404721900', NULL, '1', '1', '', NULL, 'blue', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQIf4R5qPKHPNMyAqV-FjS_OTBB8pfUV29Phg&s', '8ad27b097222396cca21db7d4caf4f357efe3534d67a11595c246dfd8b24b47e', '2025-08-06 20:45:18');

-- --------------------------------------------------------

--
-- Table structure for table `user_checkout_settings`
--

CREATE TABLE `user_checkout_settings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `theme` int(2) NOT NULL DEFAULT 1,
  `show_qr` tinyint(1) DEFAULT 1,
  `show_paytmButton` tinyint(1) DEFAULT 1,
  `Show_GpayButton` tinyint(4) NOT NULL DEFAULT 1,
  `show_phonepe` tinyint(1) NOT NULL DEFAULT 1,
  `show_help` tinyint(1) DEFAULT 1,
  `remove_branding` tinyint(1) DEFAULT 0,
  `show_payment_logos` tinyint(1) DEFAULT 1,
  `Show_IntentButton` tinyint(1) DEFAULT 0,
  `show_upiRequest` tinyint(1) DEFAULT 1,
  `show_download_qr` tinyint(1) DEFAULT 1,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `headerColor` varchar(10) NOT NULL DEFAULT 'blue',
  `bodyColor` varchar(10) NOT NULL DEFAULT 'white',
  `display_header_footer` tinyint(1) DEFAULT 1,
  `display_loading_screen` tinyint(1) DEFAULT 1,
  `news` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_checkout_settings`
--

INSERT INTO `user_checkout_settings` (`id`, `user_id`, `theme`, `show_qr`, `show_paytmButton`, `Show_GpayButton`, `show_phonepe`, `show_help`, `remove_branding`, `show_payment_logos`, `Show_IntentButton`, `show_upiRequest`, `show_download_qr`, `updated_at`, `headerColor`, `bodyColor`, `display_header_footer`, `display_loading_screen`, `news`) VALUES
(4, 374, 1, 1, 1, 1, 1, 1, 0, 0, 0, 1, 1, '2025-08-07 05:15:21', '#ff47a0', '#ffffff', 1, 1, 'Do Not go back if you made any payment on qr or buttons     Important : click on Cancel/go back button if you want to Cancel this transaction.'),
(5, 459, 1, 1, 1, 1, 1, 1, 0, 1, 0, 1, 1, '2025-08-07 05:15:21', '#ff47a0', '#000000', 1, 1, 'Do Not go back if you made any payment on qr or buttons     Important : click on Cancel/go back button if you want to Cancel this transaction.'),
(6, 303, 2, 1, 1, 1, 1, 0, 1, 0, 0, 0, 1, '2025-08-11 09:26:23', '#bff6fd', '#fbfddd', 1, 1, '   Welcome to RB RECHARGE. Do Not recived any amount in this QR from Unknown Person. Any Issue in add wallet Please call 011-6926-1955'),
(7, 305, 2, 1, 1, 1, 1, 0, 1, 0, 0, 0, 1, '2025-08-11 09:25:50', '#ff47a0', '#f8f8f1', 1, 1, ' Welcome to RB NEXT.Do Not recived any amount in this QR from Unknown Person. Any Issue in add wallet Please call 011-6926-1955'),
(8, 362, 1, 1, 1, 1, 0, 0, 1, 0, 1, 0, 1, '2025-08-07 05:15:21', '#fcff47', '#000000', 1, 1, 'Do Not go back if you made any payment on qr or buttons     Important : click on Cancel/go back button if you want to Cancel this transaction.'),
(9, 339, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 1, '2025-08-07 05:15:21', '#ff47a0', '#ffffff', 1, 1, 'Do Not go back if you made any payment on qr or buttons     Important : click on Cancel/go back button if you want to Cancel this transaction.'),
(10, 276, 2, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, '2025-08-07 05:15:21', '#ff47a0', '#fb7f60', 1, 1, 'Do Not go back if you made any payment on qr or buttons     Important : click on Cancel/go back button if you want to Cancel this transaction.'),
(11, 363, 1, 1, 1, 1, 1, 1, 0, 0, 0, 1, 1, '2025-08-07 05:15:21', '#ff47a0', 'white', 1, 1, 'Do Not go back if you made any payment on qr or buttons     Important : click on Cancel/go back button if you want to Cancel this transaction.'),
(12, 462, 1, 1, 1, 1, 1, 1, 0, 0, 0, 1, 1, '2025-08-07 05:15:21', '#ff47a0', '#ffffff', 1, 1, 'Do Not go back if you made any payment on qr or buttons     Important : click on Cancel/go back button if you want to Cancel this transaction.'),
(13, 270, 2, 1, 1, 0, 0, 1, 0, 1, 0, 0, 1, '2025-08-07 05:15:21', '#ff47a0', '#000000', 1, 1, 'Do Not go back if you made any payment on qr or buttons     Important : click on Cancel/go back button if you want to Cancel this transaction.'),
(14, 304, 1, 1, 1, 1, 1, 0, 1, 0, 0, 0, 1, '2025-08-07 05:15:21', '#ff47a0', '#000000', 1, 0, 'Do Not go back if you made any payment on qr or buttons     Important : click on Cancel/go back button if you want to Cancel this transaction.'),
(15, 310, 1, 1, 1, 1, 1, 1, 0, 1, 1, 1, 1, '2025-08-07 05:15:21', '#ff47a0', '#000000', 1, 1, 'Do Not go back if you made any payment on qr or buttons     Important : click on Cancel/go back button if you want to Cancel this transaction.'),
(16, 398, 1, 1, 0, 0, 1, 1, 0, 0, 0, 0, 1, '2025-08-07 05:15:21', '#00ff00', '#000000', 1, 1, 'Do Not go back if you made any payment on qr or buttons     Important : click on Cancel/go back button if you want to Cancel this transaction.'),
(17, 337, 1, 1, 1, 1, 1, 1, 0, 0, 0, 1, 1, '2025-08-07 05:15:21', '#ff47a0', '#000000', 1, 1, 'Do Not go back if you made any payment on qr or buttons     Important : click on Cancel/go back button if you want to Cancel this transaction.'),
(18, 438, 2, 1, 0, 0, 0, 1, 0, 1, 0, 0, 1, '2025-08-07 05:15:21', '#ff47a0', '#000000', 1, 1, 'Do Not go back if you made any payment on qr or buttons     Important : click on Cancel/go back button if you want to Cancel this transaction.'),
(232, 257, 1, 1, 1, 1, 1, 1, 0, 1, 0, 1, 1, '2025-09-02 08:08:37', '#c800b2', '#ffffff', 1, 1, '');

-- --------------------------------------------------------

--
-- Table structure for table `user_ips`
--

CREATE TABLE `user_ips` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ip` varchar(45) NOT NULL,
  `domain` varchar(100) DEFAULT NULL,
  `type` varchar(50) DEFAULT 'Other',
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_ips`
--

INSERT INTO `user_ips` (`id`, `ip`, `domain`, `type`, `user_id`, `status`, `created_at`) VALUES
(6, '94.130.14.121', NULL, 'Other', 303, 1, '2025-04-05 04:55:16'),
(7, '119.18.55.170', NULL, 'Other', 305, 1, '2025-04-05 05:11:43'),
(8, '103.49.124.30', NULL, 'Other', 368, 1, '2025-04-05 13:21:15'),
(9, '198.38.88.206', NULL, 'Other', 162, 1, '2025-04-05 15:38:57'),
(10, '216.48.189.185', NULL, 'Other', 429, 1, '2025-04-05 15:59:40'),
(11, '139.5.188.148', NULL, 'Other', 404, 1, '2025-04-06 02:17:36'),
(12, '139.5.188.226', NULL, 'Other', 292, 1, '2025-04-06 02:51:47'),
(13, '101.53.146.111', NULL, 'Other', 451, 1, '2025-04-06 03:10:50'),
(14, '45.142.237.220', NULL, 'Other', 451, 1, '2025-04-06 03:11:08'),
(15, '164.52.219.126', NULL, 'Other', 307, 1, '2025-04-06 03:57:09'),
(16, '135.181.60.238', NULL, 'Other', 270, 1, '2025-04-06 04:10:05'),
(17, '54.38.84.17', NULL, 'Other', 257, 1, '2025-04-06 04:10:59'),
(18, '54.38.84.17', NULL, 'Other', 162, 1, '2025-04-06 05:54:01');

-- --------------------------------------------------------

--
-- Table structure for table `wallet_transactions`
--

CREATE TABLE `wallet_transactions` (
  `transaction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` float NOT NULL,
  `type` enum('credit','debit') NOT NULL,
  `timestamp` timestamp NULL DEFAULT current_timestamp(),
  `opening_balance` float NOT NULL,
  `closing_balance` float NOT NULL,
  `utr` varchar(25) DEFAULT NULL,
  `Remark` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `amazon_token`
--
ALTER TABLE `amazon_token`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `api_settings`
--
ALTER TABLE `api_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bharatpe_tokens`
--
ALTER TABLE `bharatpe_tokens`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `callback_report`
--
ALTER TABLE `callback_report`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `earnings`
--
ALTER TABLE `earnings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `freecharge_token`
--
ALTER TABLE `freecharge_token`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `googlepay_tokens`
--
ALTER TABLE `googlepay_tokens`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `googlepay_transactions`
--
ALTER TABLE `googlepay_transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hdfc`
--
ALTER TABLE `hdfc`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `manual_token`
--
ALTER TABLE `manual_token`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `merchants`
--
ALTER TABLE `merchants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mobikwik_token`
--
ALTER TABLE `mobikwik_token`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `offers`
--
ALTER TABLE `offers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_links`
--
ALTER TABLE `payment_links`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `paynearby_token`
--
ALTER TABLE `paynearby_token`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `paytm_tokens`
--
ALTER TABLE `paytm_tokens`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `phonepe_tokens`
--
ALTER TABLE `phonepe_tokens`
  ADD PRIMARY KEY (`sl`);

--
-- Indexes for table `planorders`
--
ALTER TABLE `planorders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`);

--
-- Indexes for table `quintuspay_token`
--
ALTER TABLE `quintuspay_token`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `refer_income_slabs`
--
ALTER TABLE `refer_income_slabs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `merchantTransactionId` (`merchantTransactionId`);

--
-- Indexes for table `sbi_token`
--
ALTER TABLE `sbi_token`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settlement`
--
ALTER TABLE `settlement`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userid` (`userid`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `store_id`
--
ALTER TABLE `store_id`
  ADD PRIMARY KEY (`sl`);

--
-- Indexes for table `subscription_plan`
--
ALTER TABLE `subscription_plan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `planId` (`planId`);

--
-- Indexes for table `user_checkout_settings`
--
ALTER TABLE `user_checkout_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_ips`
--
ALTER TABLE `user_ips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_type` (`type`);

--
-- Indexes for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `amazon_token`
--
ALTER TABLE `amazon_token`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT for table `api_settings`
--
ALTER TABLE `api_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bharatpe_tokens`
--
ALTER TABLE `bharatpe_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=180;

--
-- AUTO_INCREMENT for table `callback_report`
--
ALTER TABLE `callback_report`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `earnings`
--
ALTER TABLE `earnings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `freecharge_token`
--
ALTER TABLE `freecharge_token`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=241;

--
-- AUTO_INCREMENT for table `googlepay_tokens`
--
ALTER TABLE `googlepay_tokens`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `googlepay_transactions`
--
ALTER TABLE `googlepay_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hdfc`
--
ALTER TABLE `hdfc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=774;

--
-- AUTO_INCREMENT for table `manual_token`
--
ALTER TABLE `manual_token`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=178;

--
-- AUTO_INCREMENT for table `merchants`
--
ALTER TABLE `merchants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `mobikwik_token`
--
ALTER TABLE `mobikwik_token`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `offers`
--
ALTER TABLE `offers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=239560;

--
-- AUTO_INCREMENT for table `payment_links`
--
ALTER TABLE `payment_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=202999;

--
-- AUTO_INCREMENT for table `paynearby_token`
--
ALTER TABLE `paynearby_token`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `paytm_tokens`
--
ALTER TABLE `paytm_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=380;

--
-- AUTO_INCREMENT for table `phonepe_tokens`
--
ALTER TABLE `phonepe_tokens`
  MODIFY `sl` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=801;

--
-- AUTO_INCREMENT for table `planorders`
--
ALTER TABLE `planorders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=715;

--
-- AUTO_INCREMENT for table `quintuspay_token`
--
ALTER TABLE `quintuspay_token`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `refer_income_slabs`
--
ALTER TABLE `refer_income_slabs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=154247;

--
-- AUTO_INCREMENT for table `sbi_token`
--
ALTER TABLE `sbi_token`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=156;

--
-- AUTO_INCREMENT for table `settlement`
--
ALTER TABLE `settlement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `store_id`
--
ALTER TABLE `store_id`
  MODIFY `sl` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=324;

--
-- AUTO_INCREMENT for table `subscription_plan`
--
ALTER TABLE `subscription_plan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=707;

--
-- AUTO_INCREMENT for table `user_checkout_settings`
--
ALTER TABLE `user_checkout_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=233;

--
-- AUTO_INCREMENT for table `user_ips`
--
ALTER TABLE `user_ips`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=211;

--
-- AUTO_INCREMENT for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=216;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
