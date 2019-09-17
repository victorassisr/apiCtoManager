-- phpMyAdmin SQL Dump
-- version 4.6.6
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: 31-Ago-2019 às 16:29
-- Versão do servidor: 5.7.17-log
-- PHP Version: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cto`
--
CREATE DATABASE IF NOT EXISTS `cto` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `cto`;

-- --------------------------------------------------------

--
-- Estrutura da tabela `bairro`
--

CREATE TABLE `bairro` (
  `idBairro` int(11) NOT NULL auto_increment,
  `descricao` varchar(65) NOT NULL,
  primary key (`idBairro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `caixaatendimento`
--

CREATE TABLE `caixaatendimento` (
  `idCaixa` int(11) NOT NULL auto_increment,
  `latitude` varchar(65) NOT NULL,
  `longitude` varchar(65) NOT NULL,
  `descricao` varchar(50) NOT NULL,
  `idSpliter` tinyint(4) NOT NULL,
  `idBairro` int(11) NOT NULL,
  `portasUsadas` tinyint(4) NOT NULL,
  primary key(`idCaixa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `cliente`
--

CREATE TABLE `cliente` (
  `IdPessoaCliente` int(11) NOT NULL,
  `rua` varchar(65) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `numero` smallint(6) NOT NULL,
  `complemento` varchar(65) DEFAULT NULL,
  `idBairro` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `funcionario`
--

CREATE TABLE `funcionario` (
  `IdPessoaFuncionario` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `senha` varchar(50) NOT NULL,
  `idTipo` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `instalacao`
--

CREATE TABLE `instalacao` (
  `Porta` tinyint(4) NOT NULL,
  `dataInstalacao` date NOT NULL,
  `idCaixa` int(11) NOT NULL,
  `dataLiberacaoPorta` date NOT NULL,
  `IdPessoaFuncionario` int(11) NOT NULL,
  `IdPessoaCliente` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `pessoa`
--

CREATE TABLE `pessoa` (
  `IdPessoa` int(11) NOT NULL auto_increment,
  `nome` varchar(30) NOT NULL,
  `sobrenome` varchar(100) NOT NULL,
  primary key(`IdPessoa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `spliter`
--

CREATE TABLE `spliter` (
  `idSpliter` tinyint(4) NOT NULL auto_increment,
  `saidas` smallint(6) NOT NULL,
  `descricao` varchar(50) NOT NULL,
  primary key(`idSpliter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tipousuario`
--

CREATE TABLE `tipousuario` (
  `idTipo` tinyint(4) NOT NULL auto_increment,
  `descricao` varchar(30) NOT NULL,
  primary key(`idTipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bairro`
--
-- ALTER TABLE `bairro`
--  ADD PRIMARY KEY (`idBairro`);
--
-- Indexes for table `caixaatendimento`
--
ALTER TABLE `caixaatendimento`
  ADD KEY `bairro_caixaatendimento_fk` (`idBairro`),
  ADD KEY `spliter_caixaatendimento_fk` (`idSpliter`);

--
-- Indexes for table `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`IdPessoaCliente`),
  ADD KEY `bairro_cliente_fk` (`idBairro`);

--
-- Indexes for table `funcionario`
--
ALTER TABLE `funcionario`
  ADD PRIMARY KEY (`IdPessoaFuncionario`),
  ADD KEY `tipousuario_funcionario_fk` (`idTipo`);

--
-- Indexes for table `instalacao`
--
ALTER TABLE `instalacao`
  ADD PRIMARY KEY (`Porta`,`dataInstalacao`,`idCaixa`),
  ADD KEY `funcionario_instalacao_fk` (`IdPessoaFuncionario`),
  ADD KEY `caixaatendimento_instalacao_fk` (`idCaixa`),
  ADD KEY `cliente_instalacao_fk` (`IdPessoaCliente`);

--
-- Indexes for table `pessoa`
--
-- ALTER TABLE `pessoa`
--  ADD PRIMARY KEY (`IdPessoa`);

--
-- Indexes for table `spliter`
--
-- ALTER TABLE `spliter`
--  ADD PRIMARY KEY (`idSpliter`);

--
-- Indexes for table `tipousuario`
--
-- ALTER TABLE `tipousuario`
--  ADD PRIMARY KEY (`idTipo`);

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

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
