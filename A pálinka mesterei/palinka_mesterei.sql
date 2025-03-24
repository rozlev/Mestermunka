-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 24, 2025 at 01:08 PM
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
-- Database: `palinka_mesterei`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `FillKuponok` ()   BEGIN
    DECLARE i INT DEFAULT 0;
    DECLARE current_count INT;
    
    -- Ellenőrizzük, hány kupon van már a táblában
    SELECT COUNT(*) INTO current_count FROM `kuponok`;
    
    -- Ha kevesebb, mint 100, akkor töltjük fel
    WHILE current_count + i < 100 DO
        INSERT INTO `kuponok` (`KuponKod`)
        VALUES (GenerateKuponKod());
        SET i = i + 1;
    END WHILE;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ProcessKuponTorles` ()   BEGIN
    DECLARE torolt_id INT;
    DECLARE done INT DEFAULT 0;
    DECLARE torles_cursor CURSOR FOR 
        SELECT `TorlesID` FROM `kupon_torles_naplo` WHERE `Feldolgozva` = 0;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
    
    -- Tranzakció indítása
    START TRANSACTION;
    
    -- Kurzor megnyitása a feldolgozatlan naplóbejegyzések feldolgozására
    OPEN torles_cursor;
    
    read_loop: LOOP
        FETCH torles_cursor INTO torolt_id;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Ellenőrizzük, hogy a kuponok tábla nem nőtt-e 100 fölé
        IF (SELECT COUNT(*) FROM `kuponok`) < 100 THEN
            -- Új kupon generálása
            INSERT INTO `kuponok` (`KuponKod`)
            VALUES (GenerateKuponKod());
        END IF;
        
        -- Naplóbejegyzés jelölése feldolgozottként
        UPDATE `kupon_torles_naplo`
        SET `Feldolgozva` = 1
        WHERE `TorlesID` = torolt_id;
    END LOOP;
    
    CLOSE torles_cursor;
    
    -- Tranzakció véglegesítése
    COMMIT;
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `GenerateKuponKod` () RETURNS VARCHAR(10) CHARSET utf8mb4 COLLATE utf8mb4_general_ci DETERMINISTIC BEGIN
    RETURN CONCAT(
        SUBSTRING('ABCDEFGHIJKLMNOPQRSTUVWXYZ', FLOOR(RAND() * 26) + 1, 1),
        SUBSTRING('ABCDEFGHIJKLMNOPQRSTUVWXYZ', FLOOR(RAND() * 26) + 1, 1),
        SUBSTRING('ABCDEFGHIJKLMNOPQRSTUVWXYZ', FLOOR(RAND() * 26) + 1, 1),
        LPAD(FLOOR(RAND() * 10000), 4, '0')
    );
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `kepek`
--

CREATE TABLE `kepek` (
  `KepID` int(11) NOT NULL,
  `PalinkaID` int(11) NOT NULL,
  `KepNev` varchar(255) NOT NULL,
  `KepURL` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- Dumping data for table `kepek`
--

INSERT INTO `kepek` (`KepID`, `PalinkaID`, `KepNev`, `KepURL`) VALUES
(37, 1, 'Málna Pálinka', 'https://zimekpalinka.hu/wp-content/uploads/2020/12/0004_466.jpg-1024x1024.png'),
(38, 2, 'Eper Pálinka', 'https://eshop.tavlisa.cz/files/prod_images/temp_big/-ef161b6f-34f8-499f-8223-89a58dc6432d-_v.jpg'),
(39, 3, 'Füge Pálinka', 'https://th.bing.com/th/id/R.62717920c5bab88f47f4e8f611e0cc40?rik=6iREIdAACbMYJg&riu=http%3a%2f%2fwww.natura-antunovic.com%2fwp-content%2fuploads%2f2014%2f11%2fsmokva-boca.jpg&ehk=%2ff%2b2OBgmqLZq9QIV5h1yRnIgpqfDIjJBcouEny2iyyE%3d&risl=&pid=ImgRaw&r=0'),
(40, 4, 'Cseresznye Pálinka', 'https://th.bing.com/th/id/OIP.rJUNFtRoMJPpOWSZb8ZouAHaHa?rs=1&pid=ImgDetMain'),
(41, 6, 'Kajszibarack Pálinka', 'https://th.bing.com/th/id/R.b2df27ac5d4bb7da6d16a63e9904e768?rik=bXCN8PNWX9rFcA&pid=ImgRaw&r=0'),
(42, 7, 'Tök Pálinka', 'https://szicsek.hu/45-large_default/suetotoek-parlat.jpg'),
(43, 8, 'Dió Pálinka', 'https://italkereso.hu/media/item/palinka/panyolai/panyolai-mezes-zold-dio-likor-0.5l-xxl.jpeg'),
(44, 9, 'Fekete Ribizli Pálinka', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSTkWavMk2uqp-Iv3WoMG-I8i_TdVeiEG0MXw&s'),
(45, 10, 'Muskotály Pálinka', 'https://www.palinka.com/images/gallery/hu/big/Palotas-Ottonel-Muskotaly-Szolo-Palinka-035liter.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `kosar`
--

CREATE TABLE `kosar` (
  `KosarID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `PalinkaID` int(11) NOT NULL,
  `Darab` int(11) NOT NULL,
  `Datum` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `kosar_view`
-- (See below for the actual view)
--
CREATE TABLE `kosar_view` (
`KosarID` int(11)
,`UserID` int(11)
,`Felhasznalo` varchar(255)
,`PalinkaID` int(11)
,`Palinka` varchar(255)
,`Egysegar` decimal(10,2)
,`Darab` int(11)
,`Osszeg` decimal(20,2)
,`Datum` timestamp
);

-- --------------------------------------------------------

--
-- Table structure for table `kuponok`
--

CREATE TABLE `kuponok` (
  `KuponID` int(11) NOT NULL,
  `KuponKod` varchar(10) NOT NULL,
  `LetrehozasDatum` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- Dumping data for table `kuponok`
--

INSERT INTO `kuponok` (`KuponID`, `KuponKod`, `LetrehozasDatum`) VALUES
(2, 'NFP3017', '2025-03-24 11:59:21'),
(3, 'STO5081', '2025-03-24 11:59:21'),
(4, 'XXY8713', '2025-03-24 11:59:21'),
(5, 'PHS7188', '2025-03-24 11:59:21'),
(6, 'LAV9793', '2025-03-24 11:59:21'),
(7, 'MKO6794', '2025-03-24 11:59:21'),
(8, 'RJT7221', '2025-03-24 11:59:21'),
(9, 'IML6745', '2025-03-24 11:59:21'),
(10, 'CMB8784', '2025-03-24 11:59:21'),
(11, 'FLP6669', '2025-03-24 11:59:21'),
(12, 'NPM6161', '2025-03-24 11:59:21'),
(13, 'PFE3028', '2025-03-24 11:59:21'),
(14, 'ZYT9872', '2025-03-24 11:59:21'),
(15, 'RJV0951', '2025-03-24 11:59:21'),
(16, 'YNQ6605', '2025-03-24 11:59:21'),
(17, 'KBZ8522', '2025-03-24 11:59:21'),
(18, 'HVG8305', '2025-03-24 11:59:21'),
(19, 'KLA8375', '2025-03-24 11:59:21'),
(20, 'CZP0192', '2025-03-24 11:59:21'),
(21, 'IOX7413', '2025-03-24 11:59:21'),
(22, 'AXM7436', '2025-03-24 11:59:21'),
(23, 'FWT0268', '2025-03-24 11:59:21'),
(24, 'YPD9567', '2025-03-24 11:59:21'),
(25, 'JWI9931', '2025-03-24 11:59:21'),
(26, 'ZAA1622', '2025-03-24 11:59:21'),
(27, 'SAB2152', '2025-03-24 11:59:21'),
(28, 'WUF7009', '2025-03-24 11:59:21'),
(29, 'XJF9822', '2025-03-24 11:59:21'),
(30, 'GFJ1350', '2025-03-24 11:59:21'),
(31, 'QRO7493', '2025-03-24 11:59:21'),
(32, 'BCH0807', '2025-03-24 11:59:21'),
(33, 'PRP0187', '2025-03-24 11:59:21'),
(34, 'HIU9020', '2025-03-24 11:59:21'),
(35, 'FGT8926', '2025-03-24 11:59:21'),
(36, 'GPE1236', '2025-03-24 11:59:21'),
(37, 'CBB0919', '2025-03-24 11:59:21'),
(38, 'HEA5183', '2025-03-24 11:59:21'),
(39, 'OGQ3590', '2025-03-24 11:59:21'),
(40, 'XLN1812', '2025-03-24 11:59:21'),
(41, 'KLC0774', '2025-03-24 11:59:21'),
(42, 'DIJ8686', '2025-03-24 11:59:21'),
(43, 'FKJ6885', '2025-03-24 11:59:21'),
(44, 'INR8102', '2025-03-24 11:59:21'),
(45, 'AQF1260', '2025-03-24 11:59:21'),
(46, 'ZNQ6176', '2025-03-24 11:59:21'),
(47, 'FDZ6392', '2025-03-24 11:59:21'),
(48, 'FCX2380', '2025-03-24 11:59:21'),
(49, 'LPN8713', '2025-03-24 11:59:21'),
(50, 'UII5946', '2025-03-24 11:59:21'),
(51, 'AGF3875', '2025-03-24 11:59:21'),
(52, 'GDV7465', '2025-03-24 11:59:21'),
(53, 'HDV6933', '2025-03-24 11:59:21'),
(54, 'AAC3578', '2025-03-24 11:59:21'),
(55, 'NNA5847', '2025-03-24 11:59:21'),
(56, 'VMU5982', '2025-03-24 11:59:21'),
(57, 'PEB8340', '2025-03-24 11:59:21'),
(58, 'ZIS6260', '2025-03-24 11:59:21'),
(59, 'ZZA1909', '2025-03-24 11:59:21'),
(60, 'WSZ7911', '2025-03-24 11:59:21'),
(61, 'AQE8976', '2025-03-24 11:59:21'),
(62, 'AJU8110', '2025-03-24 11:59:21'),
(63, 'SDP4723', '2025-03-24 11:59:21'),
(64, 'QRL3088', '2025-03-24 11:59:21'),
(65, 'EXC6831', '2025-03-24 11:59:21'),
(66, 'DSC2861', '2025-03-24 11:59:21'),
(67, 'EBU8157', '2025-03-24 11:59:21'),
(68, 'RVF6289', '2025-03-24 11:59:21'),
(69, 'LKO6592', '2025-03-24 11:59:21'),
(70, 'PBM1825', '2025-03-24 11:59:21'),
(71, 'MYF2109', '2025-03-24 11:59:21'),
(72, 'LOL5529', '2025-03-24 11:59:21'),
(73, 'LOL5784', '2025-03-24 11:59:21'),
(74, 'OZI6354', '2025-03-24 11:59:21'),
(75, 'FDC1036', '2025-03-24 11:59:21'),
(76, 'EQP0738', '2025-03-24 11:59:21'),
(77, 'PSV9447', '2025-03-24 11:59:21'),
(78, 'GNS0782', '2025-03-24 11:59:21'),
(79, 'GYA2974', '2025-03-24 11:59:21'),
(80, 'KDK6997', '2025-03-24 11:59:21'),
(81, 'GEC9336', '2025-03-24 11:59:21'),
(82, 'KFV5495', '2025-03-24 11:59:21'),
(83, 'GOD8886', '2025-03-24 11:59:21'),
(84, 'CTN2931', '2025-03-24 11:59:21'),
(85, 'YUF5776', '2025-03-24 11:59:21'),
(86, 'HTY3865', '2025-03-24 11:59:21'),
(87, 'DLV7977', '2025-03-24 11:59:21'),
(88, 'NGP3282', '2025-03-24 11:59:21'),
(89, 'VCB9855', '2025-03-24 11:59:21'),
(90, 'TUU4519', '2025-03-24 11:59:21'),
(91, 'YIV2046', '2025-03-24 11:59:21'),
(92, 'NXB5928', '2025-03-24 11:59:21'),
(93, 'TAT7833', '2025-03-24 11:59:21'),
(94, 'QVG6765', '2025-03-24 11:59:21'),
(95, 'RKZ5978', '2025-03-24 11:59:21'),
(96, 'CSH2791', '2025-03-24 11:59:21'),
(97, 'NVO3158', '2025-03-24 11:59:21'),
(98, 'XNW7228', '2025-03-24 11:59:21'),
(99, 'BFU3402', '2025-03-24 11:59:21'),
(100, 'IQF1751', '2025-03-24 11:59:21'),
(101, 'ONV6417', '2025-03-24 11:59:56');

--
-- Triggers `kuponok`
--
DELIMITER $$
CREATE TRIGGER `kupon_torles_elott` BEFORE DELETE ON `kuponok` FOR EACH ROW BEGIN
    INSERT INTO `kupon_torles_naplo` (`ToroltKuponID`, `ToroltKuponKod`)
    VALUES (OLD.`KuponID`, OLD.`KuponKod`);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `kupon_torles_naplo`
--

CREATE TABLE `kupon_torles_naplo` (
  `TorlesID` int(11) NOT NULL,
  `ToroltKuponID` int(11) NOT NULL,
  `ToroltKuponKod` varchar(10) NOT NULL,
  `Feldolgozva` tinyint(1) NOT NULL DEFAULT 0,
  `TorlesDatum` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- Dumping data for table `kupon_torles_naplo`
--

INSERT INTO `kupon_torles_naplo` (`TorlesID`, `ToroltKuponID`, `ToroltKuponKod`, `Feldolgozva`, `TorlesDatum`) VALUES
(1, 1, 'SGC7652', 1, '2025-03-24 11:59:55');

-- --------------------------------------------------------

--
-- Table structure for table `palinka`
--

CREATE TABLE `palinka` (
  `PalinkaID` int(11) NOT NULL,
  `Nev` varchar(255) NOT NULL,
  `AlkoholTartalom` decimal(5,2) NOT NULL,
  `Ar` decimal(10,2) NOT NULL,
  `Kategoria` varchar(50) NOT NULL,
  `DB_szam` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- Dumping data for table `palinka`
--

INSERT INTO `palinka` (`PalinkaID`, `Nev`, `AlkoholTartalom`, `Ar`, `Kategoria`, `DB_szam`) VALUES
(1, 'Málna Pálinka', 44.00, 5000.00, 'Gyümölcs', 31),
(2, 'Eper Pálinka', 40.00, 7300.00, 'Gyümölcs', 41),
(3, 'Füge Pálinka', 46.00, 9400.00, 'Gyümölcs', 77),
(4, 'Cseresznye Pálinka', 38.00, 5000.00, 'Gyümölcs', 98),
(6, 'Kajszi Pálinka', 49.00, 6600.00, 'Gyümölcs', 92),
(7, 'Tök Pálinka', 43.00, 8400.00, 'Zöldség', 95),
(8, 'Dió Pálinka', 47.00, 5500.00, 'Magvas', 99),
(9, 'Fekete Ribizli Pálinka', 45.00, 6300.00, 'Gyümölcs', 99),
(10, 'Muskotály Pálinka', 45.00, 4500.00, 'Virág', 97);

-- --------------------------------------------------------

--
-- Stand-in structure for view `ranking`
-- (See below for the actual view)
--
CREATE TABLE `ranking` (
`player_id` int(11)
,`username` varchar(255)
,`total_score` decimal(32,0)
,`rank_position` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `rendeles`
--

CREATE TABLE `rendeles` (
  `RendelesID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `PalinkaID` int(11) NOT NULL,
  `Darab` int(11) NOT NULL,
  `ArTotal` decimal(10,2) NOT NULL,
  `RendelesDatum` date NOT NULL,
  `RendelesCsoportID` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- Dumping data for table `rendeles`
--

INSERT INTO `rendeles` (`RendelesID`, `UserID`, `PalinkaID`, `Darab`, `ArTotal`, `RendelesDatum`, `RendelesCsoportID`) VALUES
(4, 10, 3, 1, 9400.00, '2025-02-20', 'ORDER_67b7086e68f9a'),
(5, 10, 1, 2, 10000.00, '2025-02-20', 'ORDER_67b7086e68f9a'),
(6, 10, 7, 3, 25200.00, '2025-02-20', 'ORDER_67b7086e68f9a'),
(7, 10, 3, 1, 9400.00, '2025-02-20', 'ORDER_67b708a93c5b5'),
(8, 10, 1, 1, 5000.00, '2025-02-20', 'ORDER_67b708a93c5b5'),
(9, 10, 1, 2, 10000.00, '2025-02-20', 'ORDER_67b709719df30'),
(10, 10, 3, 1, 9400.00, '2025-02-20', 'ORDER_67b709719df30'),
(11, 10, 1, 1, 5000.00, '2025-03-13', 'ORDER_67d2ac825f5c1'),
(12, 10, 3, 1, 9400.00, '2025-03-13', 'ORDER_67d2ac825f5c1'),
(13, 13, 3, 2, 18800.00, '2025-03-17', 'ORDER_67d7eaa8936b2'),
(14, 13, 3, 1, 9400.00, '2025-03-17', 'ORDER_67d7eb8352ad7'),
(15, 13, 3, 1, 9400.00, '2025-03-17', 'ORDER_67d7ec114811f'),
(16, 9, 3, 1, 9400.00, '2025-03-20', 'ORDER_67dbe52634bb7'),
(17, 9, 3, 1, 9400.00, '2025-03-20', 'ORDER_67dbe52f575eb'),
(18, 10, 3, 1, 9400.00, '2025-03-20', 'ORDER_67dbeb36ed895'),
(19, 10, 1, 1, 5000.00, '2025-03-20', 'ORDER_67dbed432b61e'),
(20, 9, 1, 1, 5000.00, '2025-03-20', 'ORDER_67dbf2938d248'),
(21, 9, 2, 1, 7300.00, '2025-03-20', 'ORDER_67dbf2938d248');

-- --------------------------------------------------------

--
-- Table structure for table `scores`
--

CREATE TABLE `scores` (
  `score_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `points` int(11) NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- Dumping data for table `scores`
--

INSERT INTO `scores` (`score_id`, `player_id`, `points`, `date`) VALUES
(1, 9, 16, '2025-02-12 10:07:58'),
(2, 10, 17, '2025-02-20 10:51:48'),
(3, 10, 4, '2025-03-13 10:59:49');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `UserID` int(11) NOT NULL,
  `Nev` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Jelszo` varchar(255) NOT NULL,
  `RegisztracioDatum` date NOT NULL,
  `Eletkor` int(11) DEFAULT NULL,
  `Szerepkor` enum('admin','felhasználó') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`UserID`, `Nev`, `Email`, `Jelszo`, `RegisztracioDatum`, `Eletkor`, `Szerepkor`) VALUES
(9, 'Dozsa', 'levirozsa11@gmail.com', '$2y$10$TeIQWCliKsfI2JKfuEBg4uNG9wA1.rwPmw21ln46EBOOZbUvXqPFC', '2025-02-06', NULL, 'admin'),
(10, 'Dozsa1', 'rozlev404@hengersor.hu', '$2y$10$cwUVQdxTR9Ka.kRwugqTAuOiLH5WHjBaddd2lIO3wtE7YZntKSuBe', '2025-02-06', NULL, 'felhasználó'),
(11, 'Dozsa3213', 'levirozsa11@gmail.com2', '$2y$10$/wwy71rL.PRPPyKNHkTiKO7gYrdCT3ImrqcwH6Gi8iDGhoFjTmi2.', '2025-02-06', NULL, 'felhasználó'),
(12, 'dozsaa', 'tari.tamas.mark@gmail.com', '$2y$10$WPYQZjnVeh228vIRro0GgupVx143JPCaelXAlIWRPM.pPRavZH4Bq', '2025-02-20', NULL, 'felhasználó'),
(13, 'asd1233', 'nemben431@hengersor.hu', '$2y$10$8rY6kxGSkheQCTuKrwPgFOOcjHZYsb66TCwEgginFQr15BueoVdmC', '2025-03-17', 19, 'felhasználó');

-- --------------------------------------------------------

--
-- Structure for view `kosar_view`
--
DROP TABLE IF EXISTS `kosar_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `kosar_view`  AS SELECT `k`.`KosarID` AS `KosarID`, `k`.`UserID` AS `UserID`, `u`.`Nev` AS `Felhasznalo`, `k`.`PalinkaID` AS `PalinkaID`, `p`.`Nev` AS `Palinka`, `p`.`Ar` AS `Egysegar`, `k`.`Darab` AS `Darab`, `p`.`Ar`* `k`.`Darab` AS `Osszeg`, `k`.`Datum` AS `Datum` FROM ((`kosar` `k` join `user` `u` on(`k`.`UserID` = `u`.`UserID`)) join `palinka` `p` on(`k`.`PalinkaID` = `p`.`PalinkaID`)) ;

-- --------------------------------------------------------

--
-- Structure for view `ranking`
--
DROP TABLE IF EXISTS `ranking`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `ranking`  AS SELECT `u`.`UserID` AS `player_id`, `u`.`Nev` AS `username`, coalesce(sum(`s`.`points`),0) AS `total_score`, rank() over ( order by sum(`s`.`points`) desc) AS `rank_position` FROM (`user` `u` left join `scores` `s` on(`u`.`UserID` = `s`.`player_id`)) GROUP BY `u`.`UserID`, `u`.`Nev` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `kepek`
--
ALTER TABLE `kepek`
  ADD PRIMARY KEY (`KepID`),
  ADD KEY `kepek_palinka_fk` (`PalinkaID`);

--
-- Indexes for table `kosar`
--
ALTER TABLE `kosar`
  ADD PRIMARY KEY (`KosarID`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `PalinkaID` (`PalinkaID`);

--
-- Indexes for table `kuponok`
--
ALTER TABLE `kuponok`
  ADD PRIMARY KEY (`KuponID`),
  ADD UNIQUE KEY `KuponKod` (`KuponKod`);

--
-- Indexes for table `kupon_torles_naplo`
--
ALTER TABLE `kupon_torles_naplo`
  ADD PRIMARY KEY (`TorlesID`);

--
-- Indexes for table `palinka`
--
ALTER TABLE `palinka`
  ADD PRIMARY KEY (`PalinkaID`);

--
-- Indexes for table `rendeles`
--
ALTER TABLE `rendeles`
  ADD PRIMARY KEY (`RendelesID`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `PalinkaID` (`PalinkaID`);

--
-- Indexes for table `scores`
--
ALTER TABLE `scores`
  ADD PRIMARY KEY (`score_id`),
  ADD KEY `player_id` (`player_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `kepek`
--
ALTER TABLE `kepek`
  MODIFY `KepID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `kosar`
--
ALTER TABLE `kosar`
  MODIFY `KosarID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kuponok`
--
ALTER TABLE `kuponok`
  MODIFY `KuponID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT for table `kupon_torles_naplo`
--
ALTER TABLE `kupon_torles_naplo`
  MODIFY `TorlesID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `palinka`
--
ALTER TABLE `palinka`
  MODIFY `PalinkaID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `rendeles`
--
ALTER TABLE `rendeles`
  MODIFY `RendelesID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `scores`
--
ALTER TABLE `scores`
  MODIFY `score_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `kepek`
--
ALTER TABLE `kepek`
  ADD CONSTRAINT `kepek_palinka_fk` FOREIGN KEY (`PalinkaID`) REFERENCES `palinka` (`PalinkaID`);

--
-- Constraints for table `kosar`
--
ALTER TABLE `kosar`
  ADD CONSTRAINT `kosar_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE,
  ADD CONSTRAINT `kosar_ibfk_2` FOREIGN KEY (`PalinkaID`) REFERENCES `palinka` (`PalinkaID`) ON DELETE CASCADE;

--
-- Constraints for table `rendeles`
--
ALTER TABLE `rendeles`
  ADD CONSTRAINT `rendeles_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`),
  ADD CONSTRAINT `rendeles_ibfk_2` FOREIGN KEY (`PalinkaID`) REFERENCES `palinka` (`PalinkaID`);

--
-- Constraints for table `scores`
--
ALTER TABLE `scores`
  ADD CONSTRAINT `scores_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `user` (`UserID`) ON DELETE CASCADE;

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`localhost` EVENT `CheckKuponTorles` ON SCHEDULE EVERY 1 SECOND STARTS '2025-03-24 12:59:21' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    CALL `ProcessKuponTorles`();
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
