-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2025. Feb 12. 10:31
-- Kiszolgáló verziója: 10.4.28-MariaDB
-- PHP verzió: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Adatbázis: `palinka_mesterei`
--
CREATE DATABASE palinka_mesterei;
USE palinka_mesterei;
SET NAMES 'utf8mb4' COLLATE 'utf8mb4_hungarian_ci';


-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `kepek`
--

CREATE TABLE `kepek` (
  `KepID` int(11) NOT NULL,
  `PalinkaID` int(11) NOT NULL,
  `KepNev` varchar(255) NOT NULL,
  `KepURL` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `kepek`
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
-- Tábla szerkezet ehhez a táblához `palinka`
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
-- A tábla adatainak kiíratása `palinka`
--

INSERT INTO `palinka` (`PalinkaID`, `Nev`, `AlkoholTartalom`, `Ar`, `Kategoria`, `DB_szam`) VALUES
(1, 'Málna Pálinka', 44.00, 5000.00, 'Gyümölcs', 10),
(2, 'Eper Pálinka', 40.00, 7300.00, 'Gyümölcs', 0),
(3, 'Füge Pálinka', 46.00, 9400.00, 'Gyümölcs', 93),
(4, 'Cseresznye Pálinka', 38.00, 5000.00, 'Gyümölcs', 99),
(6, 'Kajszi Pálinka', 49.00, 6600.00, 'Gyümölcs', 99),
(7, 'Tök Pálinka', 43.00, 8400.00, 'Zöldség', 100),
(8, 'Dió Pálinka', 47.00, 5500.00, 'Magvas', 100),
(9, 'Fekete Ribizli Pálinka', 45.00, 6300.00, 'Gyümölcs', 100),
(10, 'Muskotály Pálinka', 45.00, 4500.00, 'Virág', 98);

-- --------------------------------------------------------

--
-- A nézet helyettes szerkezete `ranking`
-- (Lásd alább az aktuális nézetet)
--
CREATE TABLE `ranking` (
`player_id` int(11)
,`username` varchar(255)
,`total_score` decimal(32,0)
,`rank_position` bigint(21)
);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `rendeles`
--

CREATE TABLE `rendeles` (
  `RendelesID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `PalinkaID` int(11) NOT NULL,
  `Darab` int(11) NOT NULL,
  `ArTotal` decimal(10,2) NOT NULL,
  `RendelesDatum` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `scores`
--

CREATE TABLE `scores` (
  `score_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `points` int(11) NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `scores`
--

INSERT INTO `scores` (`score_id`, `player_id`, `points`, `date`) VALUES
(1, 9, 16, '2025-02-12 10:07:58');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `user`
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
-- A tábla adatainak kiíratása `user`
--

INSERT INTO `user` (`UserID`, `Nev`, `Email`, `Jelszo`, `RegisztracioDatum`, `Eletkor`, `Szerepkor`) VALUES
(9, 'Dozsa', 'levirozsa11@gmail.com', '$2y$10$TeIQWCliKsfI2JKfuEBg4uNG9wA1.rwPmw21ln46EBOOZbUvXqPFC', '2025-02-06', NULL, 'felhasználó'),
(10, 'Dozsa1', 'rozlev404@hengersor.hu', '$2y$10$cwUVQdxTR9Ka.kRwugqTAuOiLH5WHjBaddd2lIO3wtE7YZntKSuBe', '2025-02-06', NULL, 'felhasználó'),
(11, 'Dozsa3213', 'levirozsa11@gmail.com2', '$2y$10$/wwy71rL.PRPPyKNHkTiKO7gYrdCT3ImrqcwH6Gi8iDGhoFjTmi2.', '2025-02-06', NULL, 'felhasználó');

-- --------------------------------------------------------

--
-- Nézet szerkezete `ranking`
--
DROP TABLE IF EXISTS `ranking`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `ranking`  AS SELECT `u`.`UserID` AS `player_id`, `u`.`Nev` AS `username`, coalesce(sum(`s`.`points`),0) AS `total_score`, rank() over ( order by sum(`s`.`points`) desc) AS `rank_position` FROM (`user` `u` left join `scores` `s` on(`u`.`UserID` = `s`.`player_id`)) GROUP BY `u`.`UserID`, `u`.`Nev` ;

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `kepek`
--
ALTER TABLE `kepek`
  ADD PRIMARY KEY (`KepID`),
  ADD KEY `kepek_palinka_fk` (`PalinkaID`);

--
-- A tábla indexei `palinka`
--
ALTER TABLE `palinka`
  ADD PRIMARY KEY (`PalinkaID`);

--
-- A tábla indexei `rendeles`
--
ALTER TABLE `rendeles`
  ADD PRIMARY KEY (`RendelesID`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `PalinkaID` (`PalinkaID`);

--
-- A tábla indexei `scores`
--
ALTER TABLE `scores`
  ADD PRIMARY KEY (`score_id`),
  ADD KEY `player_id` (`player_id`);

--
-- A tábla indexei `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `kepek`
--
ALTER TABLE `kepek`
  MODIFY `KepID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT a táblához `palinka`
--
ALTER TABLE `palinka`
  MODIFY `PalinkaID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT a táblához `rendeles`
--
ALTER TABLE `rendeles`
  MODIFY `RendelesID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `scores`
--
ALTER TABLE `scores`
  MODIFY `score_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT a táblához `user`
--
ALTER TABLE `user`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Megkötések a kiírt táblákhoz
--

--
-- Megkötések a táblához `kepek`
--
ALTER TABLE `kepek`
  ADD CONSTRAINT `kepek_palinka_fk` FOREIGN KEY (`PalinkaID`) REFERENCES `palinka` (`PalinkaID`);

--
-- Megkötések a táblához `rendeles`
--
ALTER TABLE `rendeles`
  ADD CONSTRAINT `rendeles_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`),
  ADD CONSTRAINT `rendeles_ibfk_2` FOREIGN KEY (`PalinkaID`) REFERENCES `palinka` (`PalinkaID`);

--
-- Megkötések a táblához `scores`
--
ALTER TABLE `scores`
  ADD CONSTRAINT `scores_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `user` (`UserID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
