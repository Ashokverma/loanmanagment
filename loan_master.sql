-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 13, 2019 at 08:52 PM
-- Server version: 10.1.36-MariaDB
-- PHP Version: 7.2.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `loan_master`
--

-- --------------------------------------------------------

--
-- Table structure for table `lm_customer`
--

CREATE TABLE `lm_customer` (
  `C_ID` varchar(10) NOT NULL,
  `C_NAME` varchar(50) NOT NULL,
  `C_ADHAAR` varchar(16) NOT NULL,
  `C_MOBILENO` int(10) NOT NULL,
  `C_TIMESTAMP` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `C_DOC_ADHAAR` text,
  `C_DOC_PAN` text,
  `C_DOC_OTHERDOC` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `lm_customer`
--

INSERT INTO `lm_customer` (`C_ID`, `C_NAME`, `C_ADHAAR`, `C_MOBILENO`, `C_TIMESTAMP`, `C_DOC_ADHAAR`, `C_DOC_PAN`, `C_DOC_OTHERDOC`) VALUES
('9VSYA2PDPU', 'Ashok', '99999999999999', 2147483647, '2018-11-09 20:38:21', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lm_emi`
--

CREATE TABLE `lm_emi` (
  `EMI_AMOUNT` int(11) NOT NULL,
  `EMI_DATE` date NOT NULL,
  `LOAN_ID` varchar(15) NOT NULL,
  `EMI_STATUSTEMP` tinyint(1) NOT NULL DEFAULT '0',
  `EMI_STATUSCONFIRM` tinyint(1) NOT NULL DEFAULT '0',
  `EMI_AMOUNT_PAID` int(11) NOT NULL DEFAULT '0',
  `EMI_PAYMENT_DATE` date NOT NULL,
  `PLENTY_STATUS` tinyint(1) NOT NULL DEFAULT '1',
  `EMI_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `lm_emi`
--

INSERT INTO `lm_emi` (`EMI_AMOUNT`, `EMI_DATE`, `LOAN_ID`, `EMI_STATUSTEMP`, `EMI_STATUSCONFIRM`, `EMI_AMOUNT_PAID`, `EMI_PAYMENT_DATE`, `PLENTY_STATUS`, `EMI_ID`) VALUES
(30000, '2018-10-20', 'LOAN_LVGtgncY4q', 1, 1, 1000, '2018-10-20', 1, 6),
(30000, '2018-11-20', 'LOAN_LVGtgncY4q', 0, 0, 0, '0000-00-00', 1, 7),
(30000, '2018-12-20', 'LOAN_LVGtgncY4q', 0, 0, 0, '0000-00-00', 1, 8),
(30000, '2019-01-20', 'LOAN_LVGtgncY4q', 0, 0, 0, '0000-00-00', 1, 9);

-- --------------------------------------------------------

--
-- Table structure for table `lm_loan`
--

CREATE TABLE `lm_loan` (
  `C_ID` varchar(10) NOT NULL,
  `LOAN_ID` varchar(15) NOT NULL,
  `LOAN_AMOUNT` int(11) NOT NULL,
  `LOAN_DURATION` int(11) NOT NULL,
  `LOAN_TYPE` enum('MONTHLY','WEEKLY') NOT NULL,
  `LOAN_STARTDATE` date NOT NULL,
  `LOAN_CREATORID` varchar(20) NOT NULL,
  `LOAN_TIMESTAMP` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LOAN_PLENTY` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `lm_loan`
--

INSERT INTO `lm_loan` (`C_ID`, `LOAN_ID`, `LOAN_AMOUNT`, `LOAN_DURATION`, `LOAN_TYPE`, `LOAN_STARTDATE`, `LOAN_CREATORID`, `LOAN_TIMESTAMP`, `LOAN_PLENTY`) VALUES
('9VSYA2PDPU', 'LOAN_LVGtgncY4q', 100000, 4, 'MONTHLY', '2018-09-20', 'test', '2018-11-09 20:59:18', 0);

-- --------------------------------------------------------

--
-- Table structure for table `lm_master`
--

CREATE TABLE `lm_master` (
  `LM_USERNAME` varchar(20) NOT NULL,
  `LM_PASSWORD` varchar(100) NOT NULL,
  `LAST_LOGIN` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `AUTH` enum('ADMIN','OPERA','DEBUG') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `lm_master`
--

INSERT INTO `lm_master` (`LM_USERNAME`, `LM_PASSWORD`, `LAST_LOGIN`, `AUTH`) VALUES
('test', 'ce9e15551dcef8f03d22b9f331b0417f', '2018-11-09 21:13:25', 'OPERA'),
('xxx', 'f0a4058fd33489695d53df156b77c724', '2018-11-09 21:16:15', 'OPERA');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `lm_customer`
--
ALTER TABLE `lm_customer`
  ADD PRIMARY KEY (`C_ID`),
  ADD UNIQUE KEY `C_ADHAAR` (`C_ADHAAR`);

--
-- Indexes for table `lm_emi`
--
ALTER TABLE `lm_emi`
  ADD PRIMARY KEY (`EMI_ID`);

--
-- Indexes for table `lm_loan`
--
ALTER TABLE `lm_loan`
  ADD PRIMARY KEY (`LOAN_ID`),
  ADD KEY `C_ID` (`C_ID`);

--
-- Indexes for table `lm_master`
--
ALTER TABLE `lm_master`
  ADD PRIMARY KEY (`LM_USERNAME`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `lm_emi`
--
ALTER TABLE `lm_emi`
  MODIFY `EMI_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `lm_loan`
--
ALTER TABLE `lm_loan`
  ADD CONSTRAINT `lm_loan_ibfk_1` FOREIGN KEY (`C_ID`) REFERENCES `lm_customer` (`C_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
