-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Tempo de geração: 22/03/2026 às 16:25
-- Versão do servidor: 10.4.28-MariaDB
-- Versão do PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `cabral_remocoes`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telefone` varchar(30) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `clientes`
--

INSERT INTO `clientes` (`id`, `nome`, `email`, `telefone`, `created_at`, `updated_at`) VALUES
(1, 'Arthur Ferreira Fernandes', 'arthur@123.com', '(11) 98659-9562', '2026-02-08 03:48:59', '2026-02-08 03:48:59'),
(2, 'Sarah Alves Moya Ferreira', 'sarah@123.com', '11998844335', '2026-02-08 03:55:57', '2026-02-08 03:55:57'),
(4, 'Marina', 'marina@123.com', '(11) 97558-3176', '2026-02-08 04:18:10', '2026-02-08 13:04:35'),
(5, 'Arthur', 'arthur@123.com', '11986599562', '2026-03-08 15:04:49', '2026-03-08 15:04:49');

-- --------------------------------------------------------

--
-- Estrutura para tabela `contratos_campos`
--

CREATE TABLE `contratos_campos` (
  `id` int(11) NOT NULL,
  `contrato_id` int(11) NOT NULL,
  `variavel` varchar(50) NOT NULL,
  `label` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `contratos_campos`
--

INSERT INTO `contratos_campos` (`id`, `contrato_id`, `variavel`, `label`) VALUES
(21, 5, 'nome_cliente', 'Nome do Cliente'),
(22, 5, 'Rg', 'Numero RG'),
(23, 5, 'cpf', 'Numero Cpf'),
(24, 5, 'Endereco', 'Endereço completo'),
(26, 7, 'nome_cliente', 'Nome do Cliente'),
(27, 7, 'Nome_Financeiro', 'Nome do Responsavel Financeiro'),
(28, 7, 'Cpf_Financeiro', 'CPF do Responsavel Financeiro'),
(29, 7, 'Telefone_Financeiro', 'Telefone do Responsavel Financeiro'),
(30, 7, 'Endereco_Financeiro', 'Endereço do Responsavel Financeiro');

-- --------------------------------------------------------

--
-- Estrutura para tabela `contratos_templates`
--

CREATE TABLE `contratos_templates` (
  `id` int(11) NOT NULL,
  `nome_modelo` varchar(100) NOT NULL,
  `caminho_arquivo` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `contratos_templates`
--

INSERT INTO `contratos_templates` (`id`, `nome_modelo`, `caminho_arquivo`, `created_at`) VALUES
(5, 'Contrato', 'arquivos/contratos_base/contrato_edit_698808fc55dc3.docx', '2026-02-08 03:46:31'),
(7, 'Teste', 'arquivos/contratos_base/contrato_69ad8fda9a00b.docx', '2026-03-08 15:03:54');

-- --------------------------------------------------------

--
-- Estrutura para tabela `coordenadas_template`
--

CREATE TABLE `coordenadas_template` (
  `id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL,
  `campo_chave` varchar(50) NOT NULL,
  `pos_x` float NOT NULL,
  `pos_y` float NOT NULL,
  `largura` float DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `coordenadas_template`
--

INSERT INTO `coordenadas_template` (`id`, `template_id`, `campo_chave`, `pos_x`, `pos_y`, `largura`) VALUES
(8, 2, 'ITEM', 10, 115, 0),
(9, 2, 'DESCRIÇÃO', 25, 115, 0),
(10, 2, 'QUANTIDADE', 115, 115, 0),
(11, 2, 'UNIDADE', 138, 115, 0),
(12, 2, 'PREÇO', 158, 115, 0),
(13, 2, 'SUBTOTAL', 182, 115, 0),
(14, 2, 'tabela_inicio', 0, 115, 0),
(15, 4, 'ITEM', 10, 115, 0),
(16, 4, 'DESCRIÇÃO', 25, 115, 0),
(17, 4, 'QUANTIDADE', 115, 115, 0),
(18, 4, 'UNIDADE', 138, 115, 0),
(19, 4, 'PREÇO', 158, 115, 0),
(20, 4, 'SUBTOTAL', 182, 115, 0),
(21, 4, 'tabela_inicio', 0, 115, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `historico_gerado`
--

CREATE TABLE `historico_gerado` (
  `id` int(11) NOT NULL,
  `template_id` int(11) DEFAULT NULL,
  `contrato_id` int(11) DEFAULT NULL,
  `nome_cliente` varchar(150) DEFAULT NULL,
  `caminho_pdf_final` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `valor_total` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `historico_gerado`
--

INSERT INTO `historico_gerado` (`id`, `template_id`, `contrato_id`, `nome_cliente`, `caminho_pdf_final`, `created_at`, `valor_total`) VALUES
(36, NULL, 7, 'Arthur', 'arquivos/gerados/Contrato_20260308_160648_69ad90880ced7.pdf', '2026-03-08 15:06:48', 0.00),
(37, 4, NULL, 'Teste', 'arquivos/gerados/doc_1772982473.pdf', '2026-03-08 15:07:53', 1000.00);

-- --------------------------------------------------------

--
-- Estrutura para tabela `templates`
--

CREATE TABLE `templates` (
  `id` int(11) NOT NULL,
  `nome_modelo` varchar(100) NOT NULL,
  `caminho_arquivo` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `templates`
--

INSERT INTO `templates` (`id`, `nome_modelo`, `caminho_arquivo`, `created_at`) VALUES
(2, 'Teste', 'uploads/template_698209e28f81b.pdf', '2026-02-03 14:44:50'),
(4, 'Modelo 1', 'arquivos/templates/template_69821d7d725c9.pdf', '2026-02-03 16:08:29');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `created_at`) VALUES
(1, 'Administrador', 'admin@cabral.com.br', '$2y$10$BgPa7hqrndG1sUAxBKzWK.1ORA4x5pwhkItzCdSrlfSOjsj0rx9dC', '2026-02-08 04:37:26');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Índices de tabela `contratos_campos`
--
ALTER TABLE `contratos_campos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contrato_id` (`contrato_id`);

--
-- Índices de tabela `contratos_templates`
--
ALTER TABLE `contratos_templates`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `coordenadas_template`
--
ALTER TABLE `coordenadas_template`
  ADD PRIMARY KEY (`id`),
  ADD KEY `template_id` (`template_id`);

--
-- Índices de tabela `historico_gerado`
--
ALTER TABLE `historico_gerado`
  ADD PRIMARY KEY (`id`),
  ADD KEY `template_id` (`template_id`),
  ADD KEY `fk_hist_contrato` (`contrato_id`);

--
-- Índices de tabela `templates`
--
ALTER TABLE `templates`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `contratos_campos`
--
ALTER TABLE `contratos_campos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de tabela `contratos_templates`
--
ALTER TABLE `contratos_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `coordenadas_template`
--
ALTER TABLE `coordenadas_template`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de tabela `historico_gerado`
--
ALTER TABLE `historico_gerado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de tabela `templates`
--
ALTER TABLE `templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `contratos_campos`
--
ALTER TABLE `contratos_campos`
  ADD CONSTRAINT `contratos_campos_ibfk_1` FOREIGN KEY (`contrato_id`) REFERENCES `contratos_templates` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `coordenadas_template`
--
ALTER TABLE `coordenadas_template`
  ADD CONSTRAINT `coordenadas_template_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `historico_gerado`
--
ALTER TABLE `historico_gerado`
  ADD CONSTRAINT `fk_hist_contrato` FOREIGN KEY (`contrato_id`) REFERENCES `contratos_templates` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `historico_gerado_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
