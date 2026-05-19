-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 10, 2025 at 01:31 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `wildlife_categorization`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `admin_ID` int(11) NOT NULL,
  `users_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`admin_ID`, `users_ID`) VALUES
(1, 1),
(2, 4);

-- --------------------------------------------------------

--
-- Table structure for table `approval`
--

CREATE TABLE `approval` (
  `approval_ID` int(11) NOT NULL,
  `species_ID` int(11) NOT NULL,
  `admin_ID` int(11) NOT NULL,
  `approval_date` datetime DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `approval`
--

INSERT INTO `approval` (`approval_ID`, `species_ID`, `admin_ID`, `approval_date`, `status`) VALUES
(1, 18, 2, '2025-05-10 19:27:23', 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `Category_ID` int(11) NOT NULL,
  `Category_Name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`Category_ID`, `Category_Name`) VALUES
(1, 'Carnivore'),
(2, 'Herbivore'),
(3, 'Omnivore');

-- --------------------------------------------------------

--
-- Table structure for table `habitat`
--

CREATE TABLE `habitat` (
  `Habitat_ID` int(11) NOT NULL,
  `Habitat_Name` varchar(60) DEFAULT NULL,
  `Habitat_Location` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `habitat`
--

INSERT INTO `habitat` (`Habitat_ID`, `Habitat_Name`, `Habitat_Location`) VALUES
(1, 'Tropical Rainforest', 'Amazon, Congo, Southeast Asia'),
(2, 'Savanna', 'African Plains, Australia'),
(3, 'Desert', 'Sahara, Gobi, Arabian'),
(4, 'Tundra', 'Arctic, Northern Canada, Alaska'),
(5, 'Temperate Forest', 'Europe, Eastern USA, East Asia'),
(6, 'Grassland', 'Prairies (USA), Pampas (Argentina)'),
(7, 'Wetlands', 'Everglades (USA), Pantanal (Brazil)'),
(8, 'Mountain', 'Himalayas, Andes, Rockies'),
(9, 'Coastal', 'Mangroves, Coral Reefs, Beaches'),
(10, 'Freshwater', 'Lakes, Rivers, Ponds'),
(11, 'Marine/Ocean', 'Pacific Ocean, Coral Reefs'),
(12, 'Mangrove Forest', 'Sundarbans, Southeast Asia');

-- --------------------------------------------------------

--
-- Table structure for table `species`
--

CREATE TABLE `species` (
  `Species_ID` int(11) NOT NULL,
  `Category_ID` int(11) DEFAULT NULL,
  `Species_Name` varchar(40) DEFAULT NULL,
  `Species_Sci_Name` varchar(60) DEFAULT NULL,
  `is_endangered` tinyint(1) DEFAULT NULL,
  `Habitat_ID` int(11) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `uploader_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `species`
--

INSERT INTO `species` (`Species_ID`, `Category_ID`, `Species_Name`, `Species_Sci_Name`, `is_endangered`, `Habitat_ID`, `image_url`, `uploader_ID`) VALUES
(1, 3, 'Philippine Tarsier', 'Carlito syrichta', 1, 1, NULL, NULL),
(2, 2, 'Tamaraw', 'Bubalus mindorensis', 1, 1, NULL, NULL),
(3, 1, 'Philippine Eagle', 'Pithecophaga jefferyi', 1, 1, NULL, NULL),
(4, 1, 'Philippine Cobra', 'Naja philippinensis', 1, 1, NULL, NULL),
(5, 2, 'Philippine Deer', 'Rusa marianna', 1, 1, NULL, NULL),
(6, 1, 'Saltwater Crocodile', 'Crocodylus porosus', 1, 9, NULL, NULL),
(7, 1, 'Philippine pygmy woodpecker', 'Yungipicus maculatus', 0, 5, NULL, NULL),
(8, 1, 'Philippine bush warbler', 'Horornis seebohmi', 0, 5, NULL, NULL),
(9, 1, 'Bengal Tiger', 'Panthera tigris tigris', 1, 1, NULL, NULL),
(10, 1, 'African Elephant', 'Loxodonta africana', 1, 2, NULL, NULL),
(11, 1, 'Giraffe', 'Giraffa camelopardalis', 0, 2, NULL, NULL),
(12, 1, 'Dromedary Camel', 'Camelus dromedarius', 0, 3, NULL, NULL),
(13, 1, 'Polar Bear', 'Ursus maritimus', 1, 4, NULL, NULL),
(14, 1, 'Grey Wolf', 'Canis lupus', 0, 5, NULL, NULL),
(15, 1, 'Snow Leopard', 'Panthera uncia', 1, 8, NULL, NULL),
(16, 1, 'Red Fox', 'Vulpes vulpes', 0, 5, NULL, NULL),
(17, 1, 'Giant Panda', 'Ailuropoda melanoleuca', 1, 5, NULL, NULL),
(18, 1, 'Lion', 'Panthera leo', 1, 2, NULL, 1),
(19, 1, 'Cheetah', 'Acinonyx jubatus', 1, 2, NULL, NULL),
(20, 1, 'Jaguar', 'Panthera onca', 1, 1, NULL, NULL),
(21, 1, 'American Bison', 'Bison bison', 0, 6, NULL, NULL),
(22, 1, 'Asian Elephant', 'Elephas maximus', 1, 1, NULL, NULL),
(23, 1, 'Koala', 'Phascolarctos cinereus', 1, 5, NULL, NULL),
(24, 1, 'Wolverine', 'Gulo gulo', 1, 4, NULL, NULL),
(25, 1, 'Peregrine Falcon', 'Falco peregrinus', 0, 8, NULL, NULL),
(26, 1, 'Osprey', 'Pandion haliaetus', 0, 9, NULL, NULL),
(27, 1, 'Sea Turtle', 'Cheloniidae', 1, 11, NULL, NULL),
(28, 1, 'Emperor Penguin', 'Aptenodytes forsteri', 1, 4, NULL, NULL),
(29, 1, 'American Alligator', 'Alligator mississippiensis', 1, 10, NULL, NULL),
(30, 1, 'Bald Eagle', 'Haliaeetus leucocephalus', 0, 9, NULL, NULL),
(31, 1, 'Blue Whale', 'Balaenoptera musculus', 1, 11, NULL, NULL),
(32, 1, 'Great White Shark', 'Carcharodon carcharias', 1, 11, NULL, NULL),
(33, 1, 'Kangaroo', 'Macropus rufus', 0, 2, NULL, NULL),
(34, 1, 'Wallaby', 'Macropus', 0, 2, NULL, NULL),
(35, 1, 'Emu', 'Dromaius novaehollandiae', 0, 2, NULL, NULL),
(36, 1, 'Orangutan', 'Pongo', 1, 1, NULL, NULL),
(37, 1, 'Sumatran Rhino', 'Dicerorhinus sumatrensis', 1, 1, NULL, NULL),
(38, 1, 'Komodo Dragon', 'Varanus komodoensis', 1, 9, NULL, NULL),
(39, 1, 'Sloth', 'Folivora', 0, 1, NULL, NULL),
(40, 1, 'Pygmy Hippo', 'Choeropsis liberiensis', 1, 10, NULL, NULL),
(41, 1, 'Red Kangaroo', 'Macropus rufus', 0, 2, NULL, NULL),
(42, 1, 'Tasmanian Tiger', 'Thylacinus cynocephalus', 1, 2, NULL, NULL),
(43, 1, 'Mantis Shrimp', 'Stomatopoda', 0, 11, NULL, NULL),
(44, 1, 'Clownfish', 'Amphiprioninae', 0, 11, NULL, NULL),
(45, 1, 'Moose', 'Alces alces', 0, 5, NULL, NULL),
(46, 1, 'Albatross', 'Diomedea', 0, 11, NULL, NULL),
(47, 1, 'Bald Ibis', 'Geronticus eremita', 0, 9, NULL, NULL),
(48, 1, 'Seagull', 'Larus', 0, 9, NULL, NULL),
(49, 1, 'Harpy Eagle', 'Harpia harpyja', 1, 1, NULL, NULL),
(50, 1, 'Crocodile Monitor', 'Varanus salvadorii', 1, 9, NULL, NULL),
(58, 1, 'edres', 'dfsfasd', 1, 1, '', NULL),
(61, 1, 'Tiger', 'Panthera tigris', 1, 2, NULL, 3),
(62, 1, 'Tiger', 'Panthera tigris', 1, 2, NULL, 6),
(63, 1, 'Leopard', 'Panthera pardus', 1, 2, NULL, 4);

-- --------------------------------------------------------

--
-- Table structure for table `uploader_users`
--

CREATE TABLE `uploader_users` (
  `uploader_id` int(11) NOT NULL,
  `users_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `uploader_users`
--

INSERT INTO `uploader_users` (`uploader_id`, `users_ID`) VALUES
(1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `users_ID` int(11) NOT NULL,
  `username` varchar(60) DEFAULT NULL,
  `PASSWORD` varchar(60) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`users_ID`, `username`, `PASSWORD`) VALUES
(1, 'admin', 'password123'),
(3, 'uploader_jane', 'password123'),
(4, 'admin_mike', 'adminpass');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`admin_ID`),
  ADD KEY `users_ID` (`users_ID`);

--
-- Indexes for table `approval`
--
ALTER TABLE `approval`
  ADD PRIMARY KEY (`approval_ID`),
  ADD KEY `fk_species` (`species_ID`),
  ADD KEY `fk_admin` (`admin_ID`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`Category_ID`);

--
-- Indexes for table `habitat`
--
ALTER TABLE `habitat`
  ADD PRIMARY KEY (`Habitat_ID`);

--
-- Indexes for table `species`
--
ALTER TABLE `species`
  ADD PRIMARY KEY (`Species_ID`),
  ADD KEY `fk_uploader` (`uploader_ID`),
  ADD KEY `Habitat_ID` (`Habitat_ID`),
  ADD KEY `Category_ID` (`Category_ID`);

--
-- Indexes for table `uploader_users`
--
ALTER TABLE `uploader_users`
  ADD PRIMARY KEY (`uploader_id`),
  ADD KEY `users_ID` (`users_ID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`users_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `admin_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `approval`
--
ALTER TABLE `approval`
  MODIFY `approval_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `Category_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `habitat`
--
ALTER TABLE `habitat`
  MODIFY `Habitat_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `species`
--
ALTER TABLE `species`
  MODIFY `Species_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `uploader_users`
--
ALTER TABLE `uploader_users`
  MODIFY `uploader_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `users_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD CONSTRAINT `admin_users_ibfk_1` FOREIGN KEY (`users_ID`) REFERENCES `users` (`users_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `approval`
--
ALTER TABLE `approval`
  ADD CONSTRAINT `fk_admin` FOREIGN KEY (`admin_ID`) REFERENCES `admin_users` (`admin_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_species` FOREIGN KEY (`species_ID`) REFERENCES `species` (`Species_ID`) ON DELETE CASCADE;

--
-- Constraints for table `species`
--
ALTER TABLE `species`
  ADD CONSTRAINT `fk_species_habitat` FOREIGN KEY (`Habitat_ID`) REFERENCES `habitat` (`Habitat_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `species_ibfk_1` FOREIGN KEY (`Habitat_ID`) REFERENCES `habitat` (`Habitat_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `species_ibfk_2` FOREIGN KEY (`Category_ID`) REFERENCES `category` (`Category_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `uploader_users`
--
ALTER TABLE `uploader_users`
  ADD CONSTRAINT `uploader_users_ibfk_1` FOREIGN KEY (`users_ID`) REFERENCES `users` (`users_ID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
