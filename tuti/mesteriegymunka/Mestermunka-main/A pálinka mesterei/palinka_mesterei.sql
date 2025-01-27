-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2025. Jan 16. 12:01
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

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `palinka`
--

CREATE TABLE `palinka` (  
  `PalinkaID` int(11) NOT NULL,
  `Nev` varchar(255) NOT NULL,
  `AlkoholTartalom` decimal(5,2) NOT NULL,
  `Ar` decimal(10,2) NOT NULL,
  `Kategoria` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

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
-- Indexek a kiírt táblákhoz
--

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
-- A tábla indexei `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `palinka`
--
ALTER TABLE `palinka`
  MODIFY `PalinkaID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `rendeles`
--
ALTER TABLE `rendeles`
  MODIFY `RendelesID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `user`
--
ALTER TABLE `user`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Megkötések a kiírt táblákhoz
--

--
-- Megkötések a táblához `rendeles`
--
ALTER TABLE `rendeles`
  ADD CONSTRAINT `rendeles_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`),
  ADD CONSTRAINT `rendeles_ibfk_2` FOREIGN KEY (`PalinkaID`) REFERENCES `palinka` (`PalinkaID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
