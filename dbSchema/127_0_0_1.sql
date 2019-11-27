-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: 27-Nov-2019 às 01:55
-- Versão do servidor: 5.7.26
-- versão do PHP: 7.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cto`
--
DROP DATABASE IF EXISTS `cto`;
CREATE DATABASE IF NOT EXISTS `cto` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `cto`;

-- --------------------------------------------------------

--
-- Estrutura da tabela `bairro`
--

DROP TABLE IF EXISTS `bairro`;
CREATE TABLE IF NOT EXISTS `bairro` (
  `idBairro` int(11) NOT NULL AUTO_INCREMENT,
  `descricao` varchar(65) NOT NULL,
  PRIMARY KEY (`idBairro`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `bairro`
--

INSERT INTO `bairro` (`idBairro`, `descricao`) VALUES
(1, 'limoeiro'),
(2, 'Abner Afonso');

-- --------------------------------------------------------

--
-- Estrutura da tabela `caixaatendimento`
--

DROP TABLE IF EXISTS `caixaatendimento`;
CREATE TABLE IF NOT EXISTS `caixaatendimento` (
  `idCaixa` int(11) NOT NULL AUTO_INCREMENT,
  `latitude` varchar(65) NOT NULL,
  `longitude` varchar(65) NOT NULL,
  `descricao` varchar(50) NOT NULL,
  `idSpliter` tinyint(4) NOT NULL,
  `idBairro` int(11) NOT NULL,
  `portasUsadas` tinyint(4) NOT NULL,
  PRIMARY KEY (`idCaixa`),
  KEY `bairro_caixaatendimento_fk` (`idBairro`),
  KEY `spliter_caixaatendimento_fk` (`idSpliter`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `caixaatendimento`
--

INSERT INTO `caixaatendimento` (`idCaixa`, `latitude`, `longitude`, `descricao`, `idSpliter`, `idBairro`, `portasUsadas`) VALUES
(1, '-18.575990', '-46.505584', 'caixa01', 1, 1, 10),
(2, '-17.218555', '-46.873330', 'Caixa 02', 1, 1, 0);

-- --------------------------------------------------------

--
-- Estrutura da tabela `cliente`
--

DROP TABLE IF EXISTS `cliente`;
CREATE TABLE IF NOT EXISTS `cliente` (
  `IdPessoaCliente` int(11) NOT NULL,
  `rua` varchar(65) NOT NULL,
  `numero` smallint(6) NOT NULL,
  `complemento` varchar(65) DEFAULT NULL,
  `idBairro` int(11) NOT NULL,
  PRIMARY KEY (`IdPessoaCliente`),
  KEY `bairro_cliente_fk` (`idBairro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `cliente`
--

INSERT INTO `cliente` (`IdPessoaCliente`, `rua`, `numero`, `complemento`, `idBairro`) VALUES
(2, 'guanabara', 12, 'casa de esquina', 1),
(5, 'Zeca Preto', 68, 'Casa', 2);

-- --------------------------------------------------------

--
-- Estrutura da tabela `funcionario`
--

DROP TABLE IF EXISTS `funcionario`;
CREATE TABLE IF NOT EXISTS `funcionario` (
  `IdPessoaFuncionario` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `senha` varchar(50) NOT NULL,
  `idTipo` tinyint(4) NOT NULL,
  PRIMARY KEY (`IdPessoaFuncionario`),
  KEY `tipousuario_funcionario_fk` (`idTipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `funcionario`
--

INSERT INTO `funcionario` (`IdPessoaFuncionario`, `usuario`, `senha`, `idTipo`) VALUES
(1, 'admin', 'admin', 1),
(6, 'victor', '88fa846e5f8aa198848be76e1abdcb7d7a42d292', 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `instalacao`
--

DROP TABLE IF EXISTS `instalacao`;
CREATE TABLE IF NOT EXISTS `instalacao` (
  `Porta` tinyint(4) NOT NULL,
  `dataInstalacao` date NOT NULL,
  `idCaixa` int(11) NOT NULL,
  `dataLiberacaoPorta` date DEFAULT NULL,
  `IdPessoaFuncionario` int(11) NOT NULL,
  `IdPessoaCliente` int(11) NOT NULL,
  PRIMARY KEY (`Porta`,`dataInstalacao`,`idCaixa`),
  KEY `funcionario_instalacao_fk` (`IdPessoaFuncionario`),
  KEY `caixaatendimento_instalacao_fk` (`idCaixa`),
  KEY `cliente_instalacao_fk` (`IdPessoaCliente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `instalacao`
--

INSERT INTO `instalacao` (`Porta`, `dataInstalacao`, `idCaixa`, `dataLiberacaoPorta`, `IdPessoaFuncionario`, `IdPessoaCliente`) VALUES
(5, '2019-11-26', 2, NULL, 1, 2),
(6, '2019-11-19', 1, NULL, 1, 2);

-- --------------------------------------------------------

--
-- Estrutura da tabela `pessoa`
--

DROP TABLE IF EXISTS `pessoa`;
CREATE TABLE IF NOT EXISTS `pessoa` (
  `IdPessoa` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(30) NOT NULL,
  `sobrenome` varchar(100) NOT NULL,
  PRIMARY KEY (`IdPessoa`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `pessoa`
--

INSERT INTO `pessoa` (`IdPessoa`, `nome`, `sobrenome`) VALUES
(1, 'cueio', 'wellington'),
(2, 'edinon', 'jr'),
(3, 'Victor', 'Assis'),
(4, 'Victor', 'Assis'),
(5, 'Victor', 'Assis'),
(6, 'Victor', 'Assis');

-- --------------------------------------------------------

--
-- Estrutura da tabela `spliter`
--

DROP TABLE IF EXISTS `spliter`;
CREATE TABLE IF NOT EXISTS `spliter` (
  `idSpliter` tinyint(4) NOT NULL AUTO_INCREMENT,
  `saidas` smallint(6) NOT NULL,
  `descricao` varchar(50) NOT NULL,
  PRIMARY KEY (`idSpliter`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `spliter`
--

INSERT INTO `spliter` (`idSpliter`, `saidas`, `descricao`) VALUES
(1, 8, '1/8');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tipousuario`
--

DROP TABLE IF EXISTS `tipousuario`;
CREATE TABLE IF NOT EXISTS `tipousuario` (
  `idTipo` tinyint(4) NOT NULL AUTO_INCREMENT,
  `descricao` varchar(30) NOT NULL,
  PRIMARY KEY (`idTipo`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `tipousuario`
--

INSERT INTO `tipousuario` (`idTipo`, `descricao`) VALUES
(1, 'admin'),
(2, 'Usuario'),
(3, 'Noob');

--
-- Constraints for dumped tables
--

--
-- Limitadores para a tabela `caixaatendimento`
--
ALTER TABLE `caixaatendimento`
  ADD CONSTRAINT `bairro_caixaatendimento_fk` FOREIGN KEY (`idBairro`) REFERENCES `bairro` (`idBairro`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `spliter_caixaatendimento_fk` FOREIGN KEY (`idSpliter`) REFERENCES `spliter` (`idSpliter`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `bairro_cliente_fk` FOREIGN KEY (`idBairro`) REFERENCES `bairro` (`idBairro`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `pessoa_cliente_fk` FOREIGN KEY (`IdPessoaCliente`) REFERENCES `pessoa` (`IdPessoa`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `funcionario`
--
ALTER TABLE `funcionario`
  ADD CONSTRAINT `pessoa_funcionario_fk` FOREIGN KEY (`IdPessoaFuncionario`) REFERENCES `pessoa` (`IdPessoa`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `tipousuario_funcionario_fk` FOREIGN KEY (`idTipo`) REFERENCES `tipousuario` (`idTipo`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `instalacao`
--
ALTER TABLE `instalacao`
  ADD CONSTRAINT `caixaatendimento_instalacao_fk` FOREIGN KEY (`idCaixa`) REFERENCES `caixaatendimento` (`idCaixa`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `cliente_instalacao_fk` FOREIGN KEY (`IdPessoaCliente`) REFERENCES `cliente` (`IdPessoaCliente`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `funcionario_instalacao_fk` FOREIGN KEY (`IdPessoaFuncionario`) REFERENCES `funcionario` (`IdPessoaFuncionario`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
