-- phpMyAdmin SQL Dump
-- version 4.6.6
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: 13-Jul-2019 às 02:53
-- Versão do servidor: 5.7.17-log
-- PHP Version: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sis_cto`
--
CREATE DATABASE IF NOT EXISTS `sis_cto` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `sis_cto`;

-- --------------------------------------------------------

--
-- Estrutura da tabela `caixaatendimento`
--

DROP TABLE IF EXISTS `caixaatendimento`;
CREATE TABLE `caixaatendimento` (
  `idCaixa` int(11) NOT NULL,
  `localizacaox` varchar(65) DEFAULT NULL,
  `localizacaoy` varchar(65) DEFAULT NULL,
  `idSpliter` int(11) NOT NULL,
  `identificacaoCaixa` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `cliente`
--

DROP TABLE IF EXISTS `cliente`;
CREATE TABLE `cliente` (
  `idCliente` int(11) NOT NULL,
  `nome` varchar(65) NOT NULL,
  `rua` varchar(65) NOT NULL,
  `numero` smallint(6) NOT NULL,
  `bairro` varchar(65) NOT NULL,
  `complemento` varchar(65) DEFAULT NULL,
  `idCaixa` int(11) NOT NULL,
  `porta` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `spliter`
--

DROP TABLE IF EXISTS `spliter`;
CREATE TABLE `spliter` (
  `idSpliter` int(11) NOT NULL,
  `quantidadePortas` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tipousuario`
--

DROP TABLE IF EXISTS `tipousuario`;
CREATE TABLE `tipousuario` (
  `idTipo` smallint(6) NOT NULL,
  `descricao` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `idUser` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `senha` varchar(50) NOT NULL,
  `tipoUser` smallint(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `caixaatendimento`
--
ALTER TABLE `caixaatendimento`
  ADD PRIMARY KEY (`idCaixa`),
  ADD KEY `idPortaFK` (`idSpliter`);

--
-- Indexes for table `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`idCliente`),
  ADD KEY `idCaixaFK` (`idCaixa`);

--
-- Indexes for table `spliter`
--
ALTER TABLE `spliter`
  ADD PRIMARY KEY (`idSpliter`);

--
-- Indexes for table `tipousuario`
--
ALTER TABLE `tipousuario`
  ADD PRIMARY KEY (`idTipo`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`idUser`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `spliter`
--
ALTER TABLE `spliter`
  MODIFY `idSpliter` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `tipousuario`
--
ALTER TABLE `tipousuario`
  MODIFY `idTipo` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `idUser` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- Constraints for dumped tables
--

--
-- Limitadores para a tabela `caixaatendimento`
--
ALTER TABLE `caixaatendimento`
  ADD CONSTRAINT `PortaFK` FOREIGN KEY (`idSpliter`) REFERENCES `spliter` (`idSpliter`),
  ADD CONSTRAINT `idPortaFK` FOREIGN KEY (`idSpliter`) REFERENCES `spliter` (`idSpliter`);

--
-- Limitadores para a tabela `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `idCaixaFK` FOREIGN KEY (`idCaixa`) REFERENCES `caixaatendimento` (`idCaixa`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
