-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Počítač: 127.0.0.1
-- Vytvořeno: Pát 10. úno 2023, 22:15
-- Verze serveru: 10.4.24-MariaDB
-- Verze PHP: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

USE f157080;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `f157080`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `accounting_settings`
--

CREATE TABLE `accounting_settings` (
  `settings_id` int(11) NOT NULL,
  `valid_from` date NOT NULL,
  `valid_to` date NOT NULL,
  `vat` int(11) NOT NULL,
  `accountant_detail_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `accounting_settings`
--

INSERT INTO `accounting_settings` (`settings_id`, `valid_from`, `valid_to`, `vat`, `accountant_detail_id`, `seller_id`) VALUES
(1, '2019-01-01', '2099-12-31', 21, 1, 1);

-- --------------------------------------------------------

--
-- Struktura tabulky `address`
--

CREATE TABLE `address` (
  `address_id` int(11) NOT NULL,
  `street` varchar(30) COLLATE utf8_czech_ci NOT NULL,
  `registry_number` int(11) NOT NULL,
  `house_number` int(11) NOT NULL DEFAULT 0,
  `city` varchar(30) COLLATE utf8_czech_ci NOT NULL,
  `zip` varchar(10) COLLATE utf8_czech_ci NOT NULL,
  `country_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `address`
--

INSERT INTO `address` (`address_id`, `street`, `registry_number`, `house_number`, `city`, `zip`, `country_id`) VALUES
(1, 'Šikmá', 128, 5, 'Praha', '10000', 1),
(2, 'Rovná', 6, 0, 'Brno', '30000', 1),
(3, 'Na drahách', 29, 0, 'Stará Ves n.O', '739 23', 1);

-- --------------------------------------------------------

--
-- Struktura tabulky `article`
--

CREATE TABLE `article` (
  `article_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `content` text COLLATE utf8_czech_ci DEFAULT NULL,
  `url` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `controller` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `article`
--

INSERT INTO `article` (`article_id`, `title`, `content`, `url`, `description`, `controller`) VALUES
(1, 'Uvod', NULL, 'uvod', 'Úvodní článek na webu v MVC v PHP', 'CoreModule\\System\\Controllers\\Uvod'),
(2, 'Přihlášení', NULL, 'prihlaseni', 'Přihlášení do uživatelského účtu.', 'CoreModule\\Users\\Controllers\\Login'),
(3, 'Stránka nebyla nalezena', '<p>Litujeme, ale požadovaná stránka nebyla nalezena. Zkontrolujte prosím URL adresu.</p>', 'chyba', 'Stránka nebyla nalezena.', 'CoreModule\\System\\Controllers\\Error'),
(4, 'Kontakt', NULL, 'kontakt', 'Kontaktní formulář', 'CoreModule\\System\\Controllers\\Contact'),
(5, 'Administrace webu', NULL, 'administrace', 'Administrace webu', 'CoreModule\\Users\\Controllers\\Administration'),
(7, 'Editor', NULL, 'editor', 'Editor článku', 'CoreModule\\Articles\\Controllers\\Editor'),
(8, 'Výpis článků', NULL, 'seznam-clanku', 'Výpis všech článků', 'CoreModule\\Articles\\Controllers\\ArticleList'),
(9, 'Produkty', '', 'produkty', 'Produkty', 'EshopModule\\Products\\Controllers\\Product'),
(10, 'Účetní nastavení', '', 'nastaveni', 'Účetní nastavení pro časová období.', 'EshopModule\\Accounting\\Controllers\\Settings'),
(11, 'Objednávka', '', 'objednavka', 'Objednávka', 'EshopModule\\Products\\Controllers\\Order'),
(12, 'Správa osoby', '', 'osoby', 'Správa osoby', 'EshopModule\\Persons\\Controllers\\Person'),
(13, 'Správa kategorií', '', 'sprava-kategorii', 'Správa kategorií', 'EshopModule\\Products\\Controllers\\Category'),
(14, 'Správa objednávek', '', 'sprava-objednavek', 'Správa objednávek', 'EshopModule\\Products\\Controllers\\OrderManagement'),
(15, 'Dešťovka', NULL, 'destovka', 'destovka', 'CoreModule\\System\\Controllers\\Destovka'),
(16, 'Servis', NULL, 'servis', 'Servis', 'CoreModule\\System\\Controllers\\Servis'),
(17, 'Úprava vody', NULL, 'uprava-vody', 'Úprava vody', 'CoreModule\\System\\Controllers\\UpravaVody'),
(18, 'Čištění vrtaných studní', NULL, 'cisteni-vrtu', 'Čištění vrtaných studní', 'CoreModule\\System\\Controllers\\CisteniVrtu'),
(19, 'Čištění studní', NULL, 'cisteni-studni', 'Čištění studni', 'CoreModule\\System\\Controllers\\CisteniStudni'),
(20, 'Co děláme?', NULL, 'codelame', 'Co děláme?', 'CoreModule\\System\\Controllers\\Codelame'),
(21, 'Programy', NULL, 'programy', 'Programy', 'CoreModule\\System\\Controllers\\Programy');

-- --------------------------------------------------------

--
-- Struktura tabulky `bank_account`
--

CREATE TABLE `bank_account` (
  `bank_account_id` int(11) NOT NULL,
  `bank_code` varchar(4) COLLATE utf8_czech_ci NOT NULL,
  `account_number` varchar(20) COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `bank_account`
--

INSERT INTO `bank_account` (`bank_account_id`, `bank_code`, `account_number`) VALUES
(1, '0800', '1345987014');

-- --------------------------------------------------------

--
-- Struktura tabulky `bank_code`
--

CREATE TABLE `bank_code` (
  `bank_code_id` int(11) NOT NULL,
  `bank_code` int(4) NOT NULL,
  `bank_name` varchar(80) NOT NULL,
  `swift` varchar(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Vypisuji data pro tabulku `bank_code`
--

INSERT INTO `bank_code` (`bank_code_id`, `bank_code`, `bank_name`, `swift`) VALUES
(1, 100, 'Komerční banka, a.s.', 'KOMBCZPP'),
(2, 300, 'Československá obchodní banka, a.s.', 'CEKOCZPP'),
(3, 600, 'GE Money Bank, a.s.', 'AGBACZPP'),
(4, 710, 'Česká národní banka', 'CNBACZPP'),
(5, 800, 'Česká spořitelna, a.s.', 'GIBACZPX'),
(6, 2010, 'Fio banka, a.s.', 'FIOBCZPP'),
(7, 2020, 'Bank of Tokyo-Mitsubishi UFJ (Holland) N.V. Prague Branch, organizační složka', 'BOTKCZPP'),
(8, 2030, 'AKCENTA, spořitelní a úvěrní družstvo', NULL),
(9, 2050, 'WPB Capital, spořitelní družstvo', NULL),
(10, 2060, 'Citfin, spořitelní družstvo', 'CITFCZPP'),
(11, 2070, 'Moravský Peněžní Ústav – spořitelní družstvo', 'MPUBCZPP'),
(12, 2100, 'Hypoteční banka, a.s.', NULL),
(13, 2200, 'Peněžní dům, spořitelní družstvo', NULL),
(14, 2210, 'Evropsko-ruská banka, a.s.', 'FICHCZPP'),
(15, 2220, 'Artesa, spořitelní družstvo', 'ARTTCZPP'),
(16, 2240, 'Poštová banka, a.s., pobočka Česká republika', 'POBNCZPP'),
(17, 2250, 'Záložna CREDITAS, spořitelní družstvo', 'CTASCZ22'),
(18, 2310, 'ZUNO BANK AG, organizační složka', 'ZUNOCZPP'),
(19, 2600, 'Citibank Europe plc, organizační složka', 'CITICZPX'),
(20, 2700, 'UniCredit Bank Czech Republic and Slovakia, a.s.', 'BACXCZPP'),
(21, 3020, 'MEINL BANK Aktiengesellschaft,pobočka Praha', NULL),
(22, 3030, 'Air Bank a.s.', 'AIRACZP1'),
(23, 3500, 'ING Bank N.V.', 'INGBCZPP'),
(24, 4000, 'LBBW Bank CZ a.s.', 'SOLACZPP'),
(25, 4300, 'Českomoravská záruční a rozvojová banka, a.s.', 'CMZRCZP1'),
(26, 5400, 'The Royal Bank of Scotland plc, organizační složka', 'ABNACZPP'),
(27, 5500, 'Raiffeisenbank a.s.', 'RZBCCZPP'),
(28, 5800, 'J & T Banka, a.s.', 'JTBPCZPP'),
(29, 6000, 'PPF banka a.s.', 'PMBPCZPP'),
(30, 6100, 'Equa bank a.s.', 'EQBKCZPP'),
(31, 6200, 'COMMERZBANK Aktiengesellschaft, pobočka Praha', 'COBACZPX'),
(32, 6210, 'mBank S.A., organizační složka', 'BREXCZPP'),
(33, 6300, 'BNP Paribas Fortis SA/NV, pobočka Česká republika', 'GEBACZPP'),
(34, 6700, 'Všeobecná úverová banka a.s., pobočka Praha', 'SUBACZPP'),
(35, 6800, 'Sberbank CZ, a.s.', 'VBOECZ2X'),
(36, 7910, 'Deutsche Bank A.G. Filiale Prag', 'DEUTCZPX'),
(37, 7940, 'Waldviertler Sparkasse Bank AG', 'SPWTCZ21'),
(38, 7950, 'Raiffeisen stavební spořitelna a.s.', NULL),
(39, 7960, 'Českomoravská stavební spořitelna, a.s.', NULL),
(40, 7970, 'Wüstenrot-stavební spořitelna a.s.', NULL),
(41, 7980, 'Wüstenrot hypoteční banka a.s.', NULL),
(42, 7990, 'Modrá pyramida stavební spořitelna, a.s.', NULL),
(43, 8030, 'Raiffeisenbank im Stiftland eG pobočka Cheb, odštěpný závod', 'GENOCZ21'),
(44, 8040, 'Oberbank AG pobočka Česká republika', 'OBKLCZ2X'),
(45, 8060, 'Stavební spořitelna České spořitelny, a.s.', NULL),
(46, 8090, 'Česká exportní banka, a.s.', 'CZEECZPP'),
(47, 8150, 'HSBC Bank plc - pobočka Praha', 'MIDLCZPP'),
(48, 8200, 'PRIVAT BANK AG der Raiffeisenlandesbank Oberösterreich v České republice', NULL);

-- --------------------------------------------------------

--
-- Struktura tabulky `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `url` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `order_no` int(11) NOT NULL,
  `hidden` tinyint(4) NOT NULL DEFAULT 0,
  `parent_category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `category`
--

INSERT INTO `category` (`category_id`, `url`, `title`, `order_no`, `hidden`, `parent_category_id`) VALUES
(1, '', 'Vše', 2, 0, NULL),
(5, 'cerpadla', 'Čerpadla', 3, 0, NULL),
(8, 'nadrze', 'Nádrže', 6, 0, NULL),
(19, 'nezarazeno', 'Nezařazeno', 12, 1, NULL),
(20, 'zpusoby-dopravy', 'Způsoby dopravy', 13, 1, NULL),
(23, 'kalova', 'Kalová', 4, 0, 5),
(24, 'do-studny', 'Do studny', 5, 0, 5),
(25, 'monolitni', 'Monolitní', 7, 0, 8),
(26, 'svarene', 'Svařené', 8, 0, 8),
(27, 'filtry', 'Filtry', 9, 0, NULL),
(28, 'jednokomorove', 'Jednokomorové', 10, 0, 27),
(29, 'dvoukomorove', 'Dvoukomorové', 11, 0, 27);

-- --------------------------------------------------------

--
-- Struktura tabulky `country`
--

CREATE TABLE `country` (
  `country_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `country`
--

INSERT INTO `country` (`country_id`, `title`) VALUES
(1, 'Česká republika'),
(2, 'Slovenská republika');

-- --------------------------------------------------------

--
-- Struktura tabulky `e_order`
--

CREATE TABLE `e_order` (
  `e_order_id` int(11) NOT NULL,
  `token` char(32) COLLATE utf8_czech_ci NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `buyer_id` int(11) DEFAULT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `seller_address_id` int(11) DEFAULT NULL,
  `buyer_address_id` int(11) DEFAULT NULL,
  `seller_person_detail_id` int(11) DEFAULT NULL,
  `buyer_person_detail_id` int(11) DEFAULT NULL,
  `accountant_detail_id` int(11) DEFAULT NULL,
  `number` int(11) DEFAULT NULL,
  `issued` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `state` enum('created','completed','accepted','sent','suspended','canceled') COLLATE utf8_czech_ci NOT NULL DEFAULT 'created',
  `buyer_delivery_address_id` int(11) DEFAULT NULL,
  `delivery_product_id` int(11) DEFAULT NULL,
  `seller_bank_account_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `e_order`
--

INSERT INTO `e_order` (`e_order_id`, `token`, `created`, `buyer_id`, `seller_id`, `seller_address_id`, `buyer_address_id`, `seller_person_detail_id`, `buyer_person_detail_id`, `accountant_detail_id`, `number`, `issued`, `due_date`, `state`, `buyer_delivery_address_id`, `delivery_product_id`, `seller_bank_account_id`) VALUES
(10, '2020f3989d5a91b5f8814fb6c616a3fa', '2023-01-21 01:26:27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'created', NULL, NULL, NULL),
(11, '083475a463a6e915dea6e81626432e10', '2023-01-21 11:44:08', 3, 1, 1, 3, 1, 3, 1, 2023021001, '2023-02-10', '2023-02-24', 'sent', 3, 101, 1),
(12, '7a1fa0095c0925aa9bb930982de7bf89', '2023-01-21 11:44:34', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'created', NULL, NULL, NULL),
(13, '9178eff8332ff19e2340710721c21295', '2023-01-21 13:17:55', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'created', NULL, NULL, NULL),
(14, '6def4028711229e756af9086c2c96933', '2023-01-24 15:53:31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'created', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktura tabulky `person`
--

CREATE TABLE `person` (
  `person_id` int(11) NOT NULL,
  `person_detail_id` int(11) NOT NULL,
  `address_id` int(11) NOT NULL,
  `delivery_address_id` int(11) DEFAULT NULL,
  `bank_account_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `person`
--

INSERT INTO `person` (`person_id`, `person_detail_id`, `address_id`, `delivery_address_id`, `bank_account_id`, `user_id`) VALUES
(1, 1, 1, NULL, 1, 1),
(2, 2, 2, NULL, NULL, 2),
(3, 3, 3, NULL, NULL, 4);

-- --------------------------------------------------------

--
-- Struktura tabulky `person_detail`
--

CREATE TABLE `person_detail` (
  `person_detail_id` int(11) NOT NULL,
  `first_name` varchar(30) COLLATE utf8_czech_ci DEFAULT NULL,
  `last_name` varchar(30) COLLATE utf8_czech_ci DEFAULT NULL,
  `company_name` varchar(50) COLLATE utf8_czech_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8_czech_ci NOT NULL,
  `fax` varchar(20) COLLATE utf8_czech_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `tax_number` varchar(20) COLLATE utf8_czech_ci DEFAULT NULL,
  `identification_number` int(15) DEFAULT NULL,
  `registry_entry` varchar(100) COLLATE utf8_czech_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `person_detail`
--

INSERT INTO `person_detail` (`person_detail_id`, `first_name`, `last_name`, `company_name`, `phone`, `fax`, `email`, `tax_number`, `identification_number`, `registry_entry`) VALUES
(1, NULL, NULL, 'Localeshop s.r.o.', '+420 731 256 987', NULL, 'admin@localeshop.cz', 'CZ4369875214', 43581425, 'Firma je zapsaná do obchodního rejstříku vedeného krajským soudem v Praze, oddíl B, vložka 745982.'),
(2, 'Petr', 'Nový', '', '728143695', '', 'petr@novy.cz', '', NULL, ''),
(3, 'Lukáš', 'Kříž', '', '+420721838789', '', 'lukikriz@centrum.cz', '', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktura tabulky `product`
--

CREATE TABLE `product` (
  `product_id` int(11) NOT NULL,
  `code` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `url` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `short_description` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `description` text COLLATE utf8_czech_ci NOT NULL,
  `price` decimal(10,1) NOT NULL,
  `old_price` decimal(10,1) DEFAULT NULL,
  `rating_sum` int(11) NOT NULL DEFAULT 0,
  `ratings` int(11) NOT NULL DEFAULT 0,
  `stock` int(11) NOT NULL DEFAULT 0,
  `images_count` int(11) NOT NULL DEFAULT 0,
  `hidden` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `product`
--

INSERT INTO `product` (`product_id`, `code`, `url`, `title`, `short_description`, `description`, `price`, `old_price`, `rating_sum`, `ratings`, `stock`, `images_count`, `hidden`) VALUES
(101, '10000001', 'ceska-posta-dobirka', 'Česká pošta - Dobírka', 'Česká pošta - Dobírka', 'Česká pošta - Dobírka', '130.0', NULL, 0, 0, -1, 0, 1),
(102, '10000002', 'ppl-dobirka', 'PPL - Dobírka', 'PPL - Dobírka', 'PPL - Dobírka', '100.0', NULL, 0, 0, 0, 0, 1),
(120, 'Yt9Vtfl', 'filterek', 'Filterek', 'Filterek', '<p>Filterek</p>', '2500.0', NULL, 0, 0, 0, 2, 0),
(124, 'ABC123456', 'cerpadlo-einhell', 'Čerpadlo Einhell E-506', 'Čerpadlo má výtlak až 70 m a s výkonem 50 W je na trhu mezi tím TOP!', '<p>V&yacute;tlak:&nbsp; <strong>až 70m!</strong></p>\r\n<p>V&yacute;kon:&nbsp; <strong>50W.</strong></p>', '2300.0', '2800.0', 5, 1, 2, 2, 0),
(125, 'ABC123457', 'cerpadlo-einhell2', 'Čerpadlo Einhell E-321', 'Výtlak 50m! Výkon 50W', '<p>V&yacute;tlak 50m!</p>\r\n<p>V&yacute;kon 50W</p>', '12000.0', '12400.0', 0, 0, 0, 1, 0);

-- --------------------------------------------------------

--
-- Struktura tabulky `product_category`
--

CREATE TABLE `product_category` (
  `product_category_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `product_category`
--

INSERT INTO `product_category` (`product_category_id`, `product_id`, `category_id`) VALUES
(109, 101, 20),
(110, 102, 20),
(120, 120, 8),
(124, 124, 23),
(125, 125, 24);

-- --------------------------------------------------------

--
-- Struktura tabulky `product_e_order`
--

CREATE TABLE `product_e_order` (
  `product_e_order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `e_order_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `review`
--

CREATE TABLE `review` (
  `review_id` int(11) NOT NULL,
  `content` text COLLATE utf8_czech_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `sent` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `review`
--

INSERT INTO `review` (`review_id`, `content`, `user_id`, `product_id`, `rating`, `sent`) VALUES
(1, 'Topik', 4, 124, 5, '2023-01-21 14:04:29');

-- --------------------------------------------------------

--
-- Struktura tabulky `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `admin` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `user`
--

INSERT INTO `user` (`user_id`, `name`, `password`, `admin`) VALUES
(1, 'admin', '$2y$10$fYd.rr4CbtDuGwPrRtQU..fQXqwypG/EM2f/R2ntf6KvJWLLboPxC', 1),
(2, NULL, '$2y$10$1h4vTURtNwiEhZpACkr1ROLQ.yUFjqacH3MmDz.coqfuQANIkKK/K', 0),
(3, NULL, '$2y$10$AIg8VZN1L.dbMoZSkoUQ0eEMymnToX2C.Zq56vr6ikLLQU2IdM7k2', 0),
(4, NULL, '$2y$10$oFJ3v57qvs2E5dzEkRIQn.1vxYJlb8NhZLui3nsoucCxNTSrVxF.i', 1);

--
-- Indexy pro exportované tabulky
--

--
-- Indexy pro tabulku `accounting_settings`
--
ALTER TABLE `accounting_settings`
  ADD PRIMARY KEY (`settings_id`),
  ADD KEY `accountant_detail_id` (`accountant_detail_id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexy pro tabulku `address`
--
ALTER TABLE `address`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `country_id` (`country_id`);

--
-- Indexy pro tabulku `article`
--
ALTER TABLE `article`
  ADD PRIMARY KEY (`article_id`),
  ADD UNIQUE KEY `url` (`url`);

--
-- Indexy pro tabulku `bank_account`
--
ALTER TABLE `bank_account`
  ADD PRIMARY KEY (`bank_account_id`);

--
-- Indexy pro tabulku `bank_code`
--
ALTER TABLE `bank_code`
  ADD PRIMARY KEY (`bank_code_id`),
  ADD KEY `bank_code` (`bank_code`);

--
-- Indexy pro tabulku `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `url` (`url`),
  ADD KEY `parent_category_id` (`parent_category_id`);

--
-- Indexy pro tabulku `country`
--
ALTER TABLE `country`
  ADD PRIMARY KEY (`country_id`);

--
-- Indexy pro tabulku `e_order`
--
ALTER TABLE `e_order`
  ADD PRIMARY KEY (`e_order_id`),
  ADD KEY `seller_bank_account_id` (`seller_bank_account_id`),
  ADD KEY `delivery_product_id` (`delivery_product_id`),
  ADD KEY `buyer_delivery_address_id` (`buyer_delivery_address_id`),
  ADD KEY `accountant_detail_id` (`accountant_detail_id`),
  ADD KEY `buyer_person_detail_id` (`buyer_person_detail_id`),
  ADD KEY `seller_person_detail_id` (`seller_person_detail_id`),
  ADD KEY `buyer_address_id` (`buyer_address_id`),
  ADD KEY `seller_address_id` (`seller_address_id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexy pro tabulku `person`
--
ALTER TABLE `person`
  ADD PRIMARY KEY (`person_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `bank_account_id` (`bank_account_id`),
  ADD KEY `delivery_address_id` (`delivery_address_id`),
  ADD KEY `address_id` (`address_id`),
  ADD KEY `person_detail_id` (`person_detail_id`);

--
-- Indexy pro tabulku `person_detail`
--
ALTER TABLE `person_detail`
  ADD PRIMARY KEY (`person_detail_id`);

--
-- Indexy pro tabulku `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`);
ALTER TABLE `product` ADD FULLTEXT KEY `title` (`title`);
ALTER TABLE `product` ADD FULLTEXT KEY `fulltext_2` (`title`,`short_description`);

--
-- Indexy pro tabulku `product_category`
--
ALTER TABLE `product_category`
  ADD PRIMARY KEY (`product_category_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexy pro tabulku `product_e_order`
--
ALTER TABLE `product_e_order`
  ADD PRIMARY KEY (`product_e_order_id`),
  ADD KEY `e_order_id` (`e_order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexy pro tabulku `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `unique_product_user` (`product_id`,`user_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexy pro tabulku `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `jmeno` (`name`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `accounting_settings`
--
ALTER TABLE `accounting_settings`
  MODIFY `settings_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pro tabulku `address`
--
ALTER TABLE `address`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pro tabulku `article`
--
ALTER TABLE `article`
  MODIFY `article_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT pro tabulku `bank_account`
--
ALTER TABLE `bank_account`
  MODIFY `bank_account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pro tabulku `bank_code`
--
ALTER TABLE `bank_code`
  MODIFY `bank_code_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT pro tabulku `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT pro tabulku `country`
--
ALTER TABLE `country`
  MODIFY `country_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pro tabulku `e_order`
--
ALTER TABLE `e_order`
  MODIFY `e_order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pro tabulku `person`
--
ALTER TABLE `person`
  MODIFY `person_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pro tabulku `person_detail`
--
ALTER TABLE `person_detail`
  MODIFY `person_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pro tabulku `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT pro tabulku `product_category`
--
ALTER TABLE `product_category`
  MODIFY `product_category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT pro tabulku `product_e_order`
--
ALTER TABLE `product_e_order`
  MODIFY `product_e_order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT pro tabulku `review`
--
ALTER TABLE `review`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pro tabulku `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `accounting_settings`
--
ALTER TABLE `accounting_settings`
  ADD CONSTRAINT `accounting_settings_ibfk_1` FOREIGN KEY (`accountant_detail_id`) REFERENCES `person_detail` (`person_detail_id`),
  ADD CONSTRAINT `accounting_settings_ibfk_2` FOREIGN KEY (`seller_id`) REFERENCES `person` (`person_id`);

--
-- Omezení pro tabulku `address`
--
ALTER TABLE `address`
  ADD CONSTRAINT `address_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `country` (`country_id`);

--
-- Omezení pro tabulku `category`
--
ALTER TABLE `category`
  ADD CONSTRAINT `category_ibfk_1` FOREIGN KEY (`parent_category_id`) REFERENCES `category` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Omezení pro tabulku `e_order`
--
ALTER TABLE `e_order`
  ADD CONSTRAINT `e_order_ibfk_1` FOREIGN KEY (`seller_address_id`) REFERENCES `address` (`address_id`),
  ADD CONSTRAINT `e_order_ibfk_10` FOREIGN KEY (`seller_id`) REFERENCES `person` (`person_id`),
  ADD CONSTRAINT `e_order_ibfk_2` FOREIGN KEY (`buyer_address_id`) REFERENCES `address` (`address_id`),
  ADD CONSTRAINT `e_order_ibfk_3` FOREIGN KEY (`seller_person_detail_id`) REFERENCES `person_detail` (`person_detail_id`),
  ADD CONSTRAINT `e_order_ibfk_4` FOREIGN KEY (`buyer_person_detail_id`) REFERENCES `person_detail` (`person_detail_id`),
  ADD CONSTRAINT `e_order_ibfk_5` FOREIGN KEY (`buyer_delivery_address_id`) REFERENCES `address` (`address_id`),
  ADD CONSTRAINT `e_order_ibfk_6` FOREIGN KEY (`delivery_product_id`) REFERENCES `product` (`product_id`),
  ADD CONSTRAINT `e_order_ibfk_7` FOREIGN KEY (`seller_bank_account_id`) REFERENCES `bank_account` (`bank_account_id`),
  ADD CONSTRAINT `e_order_ibfk_8` FOREIGN KEY (`accountant_detail_id`) REFERENCES `person_detail` (`person_detail_id`),
  ADD CONSTRAINT `e_order_ibfk_9` FOREIGN KEY (`buyer_id`) REFERENCES `person` (`person_id`);

--
-- Omezení pro tabulku `person`
--
ALTER TABLE `person`
  ADD CONSTRAINT `person_ibfk_1` FOREIGN KEY (`person_detail_id`) REFERENCES `person_detail` (`person_detail_id`),
  ADD CONSTRAINT `person_ibfk_2` FOREIGN KEY (`address_id`) REFERENCES `address` (`address_id`),
  ADD CONSTRAINT `person_ibfk_3` FOREIGN KEY (`delivery_address_id`) REFERENCES `address` (`address_id`),
  ADD CONSTRAINT `person_ibfk_4` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_account` (`bank_account_id`),
  ADD CONSTRAINT `person_ibfk_5` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Omezení pro tabulku `product_category`
--
ALTER TABLE `product_category`
  ADD CONSTRAINT `product_category_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `product_category_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Omezení pro tabulku `product_e_order`
--
ALTER TABLE `product_e_order`
  ADD CONSTRAINT `product_e_order_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`),
  ADD CONSTRAINT `product_e_order_ibfk_2` FOREIGN KEY (`e_order_id`) REFERENCES `e_order` (`e_order_id`);

--
-- Omezení pro tabulku `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `review_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
