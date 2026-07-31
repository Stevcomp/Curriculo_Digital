-- ---------------------------------------------------
-- Tabela 1: dados_pessoais (tabela principal / mãe)
-- ---------------------------------------------------
CREATE TABLE dados_pessoais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    cargo VARCHAR(150) NOT NULL,
	resumo varchar(500),
    foto_url VARCHAR(255),               
    cidade VARCHAR(100),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- Tabela 2: contatos
-- A pessoa pode ter mais de um meio de ccontato
-- ---------------------------------------------------
CREATE TABLE contatos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dados_pessoais_id INT NOT NULL,
    tipo ENUM('email', 'telefone', 'linkedin', 'github', 'site', 'outro') NOT NULL,
    valor VARCHAR(200) NOT NULL,          

    CONSTRAINT fk_contatos_dados_pessoais
        FOREIGN KEY (dados_pessoais_id)
        REFERENCES dados_pessoais(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- Tabela 3: experiencias
-- Histórico profissional da pessoa
-- ---------------------------------------------------
CREATE TABLE experiencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dados_pessoais_id INT NOT NULL,
    empresa VARCHAR(150) NOT NULL,
    funcao VARCHAR(150) NOT NULL,
    periodo VARCHAR(100) NOT NULL,        
    descricao TEXT,

    CONSTRAINT fk_experiencias_dados_pessoais
        FOREIGN KEY (dados_pessoais_id)
        REFERENCES dados_pessoais(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- Tabela 4: formacao
-- Histórico acadêmico da pessoa
-- ---------------------------------------------------
CREATE TABLE formacao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dados_pessoais_id INT NOT NULL,
    instituicao VARCHAR(150) NOT NULL,
    curso VARCHAR(150) NOT NULL,
    periodo VARCHAR(100) NOT NULL,

    CONSTRAINT fk_formacao_dados_pessoais
        FOREIGN KEY (dados_pessoais_id)
        REFERENCES dados_pessoais(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

