-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 06/02/2026 às 19:20
-- Versão do servidor: 10.4.28-MariaDB
-- Versão do PHP: 8.0.28

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
  `nome_cliente` varchar(150) DEFAULT NULL,
  `caminho_pdf_final` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `valor_total` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `historico_gerado`
--

INSERT INTO `historico_gerado` (`id`, `template_id`, `nome_cliente`, `caminho_pdf_final`, `created_at`, `valor_total`) VALUES
(23, 2, 'ORÇAMENTO PARA PHILIP MORRIS BRASIL', 'arquivos/gerados/doc_1770132895.pdf', '2026-02-03 15:34:55', 0.00),
(24, 2, 'Criciuma SC x Taubate', 'arquivos/gerados/doc_1770133157.pdf', '2026-02-03 15:39:17', 0.00),
(25, 2, 'ORÇAMENTO PARA PHILIP MORRIS BRASIL', 'arquivos/gerados/doc_1770134332.pdf', '2026-02-03 15:58:52', 0.00),
(26, 2, 'ORÇAMENTO PARA PHILIP MORRIS BRASIL', 'arquivos/gerados/doc_1770134676.pdf', '2026-02-03 16:04:36', 1000.00),
(27, 4, 'ORÇAMENTO PARA PHILIP MORRIS BRASIL', 'arquivos/gerados/doc_1770134929.pdf', '2026-02-03 16:08:49', 1000.00);

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

--
-- Índices para tabelas despejadas
--

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
  ADD KEY `template_id` (`template_id`);

--
-- Índices de tabela `templates`
--
ALTER TABLE `templates`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `coordenadas_template`
--
ALTER TABLE `coordenadas_template`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de tabela `historico_gerado`
--
ALTER TABLE `historico_gerado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de tabela `templates`
--
ALTER TABLE `templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `coordenadas_template`
--
ALTER TABLE `coordenadas_template`
  ADD CONSTRAINT `coordenadas_template_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `historico_gerado`
--
ALTER TABLE `historico_gerado`
  ADD CONSTRAINT `historico_gerado_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
