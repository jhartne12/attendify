-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 14, 2025 at 05:53 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `attendify`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `adminID` int(3) NOT NULL,
  `Name` varchar(40) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(40) NOT NULL,
  `securityQ` varchar(60) NOT NULL,
  `securityA` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendee`
--

CREATE TABLE `attendee` (
  `attendeeID` int(3) NOT NULL,
  `Name` varchar(40) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(40) NOT NULL,
  `securityQ` varchar(60) NOT NULL,
  `securityA` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `categoryID` int(3) NOT NULL,
  `name` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`categoryID`, `name`) VALUES
(1, 'Conference'),
(2, 'Workshop'),
(3, 'Seminar'),
(4, 'Meetup'),
(5, 'Webinar'),
(6, 'Social');

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE `event` (
  `eventID` int(55) NOT NULL,
  `organizerID` int(3) NOT NULL,
  `Name` varchar(40) NOT NULL,
  `date` datetime NOT NULL,
  `address` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `categoryID` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_attendee`
--

CREATE TABLE `event_attendee` (
  `event_attendeeID` int(3) NOT NULL,
  `eventID` int(55) NOT NULL,
  `attendeeID` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notificationID` int(11) NOT NULL,
  `attendeeID` int(3) DEFAULT NULL,
  `organizerID` int(3) DEFAULT NULL,
  `message` varchar(255) NOT NULL,
  `isRead` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `organizer`
--

CREATE TABLE `organizer` (
  `organizerID` int(3) NOT NULL,
  `Name` varchar(40) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(40) NOT NULL,
  `securityQ` varchar(60) NOT NULL,
  `securityA` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`adminID`);

--
-- Indexes for table `attendee`
--
ALTER TABLE `attendee`
  ADD PRIMARY KEY (`attendeeID`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`categoryID`);

--
-- Indexes for table `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`eventID`),
  ADD KEY `organizer` (`organizerID`),
  ADD KEY `event_category_fk` (`categoryID`);

--
-- Indexes for table `event_attendee`
--
ALTER TABLE `event_attendee`
  ADD PRIMARY KEY (`event_attendeeID`),
  ADD KEY `event_attendee_event_fk` (`eventID`),
  ADD KEY `event_attendee_attendee_fk` (`attendeeID`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notificationID`),
  ADD KEY `attendeeID` (`attendeeID`),
  ADD KEY `organizerID` (`organizerID`);

--
-- Indexes for table `organizer`
--
ALTER TABLE `organizer`
  ADD PRIMARY KEY (`organizerID`) USING BTREE;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `adminID` int(3) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendee`
--
ALTER TABLE `attendee`
  MODIFY `attendeeID` int(3) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `categoryID` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `event`
--
ALTER TABLE `event`
  MODIFY `eventID` int(55) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_attendee`
--
ALTER TABLE `event_attendee`
  MODIFY `event_attendeeID` int(3) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notificationID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `organizer`
--
ALTER TABLE `organizer`
  MODIFY `organizerID` int(3) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `event`
--
ALTER TABLE `event`
  ADD CONSTRAINT `event_category_fk` FOREIGN KEY (`categoryID`) REFERENCES `category` (`categoryID`),
  ADD CONSTRAINT `event_ibfk_1` FOREIGN KEY (`organizerID`) REFERENCES `organizer` (`organizerID`);

--
-- Constraints for table `event_attendee`
--
ALTER TABLE `event_attendee`
  ADD CONSTRAINT `event_attendee_attendee_fk` FOREIGN KEY (`attendeeID`) REFERENCES `attendee` (`attendeeID`),
  ADD CONSTRAINT `event_attendee_event_fk` FOREIGN KEY (`eventID`) REFERENCES `event` (`eventID`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`attendeeID`) REFERENCES `attendee` (`attendeeID`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`organizerID`) REFERENCES `organizer` (`organizerID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
