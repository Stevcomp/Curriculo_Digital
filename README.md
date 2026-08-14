# 🎓 Currículo Digital SENAI

Um sistema web completo, moderno e responsivo desenvolvido em **PHP, MySQL, HTML5, CSS3 e JavaScript**, projetado para criação, gerenciamento e publicação de currículos digitais profissionais.

---

## 🌟 Principais Funcionalidades

### 📄 Exportação para PDF / Impressão em 1 Clique
- Botão dedicado **"Imprimir / Salvar PDF"** integrado na barra superior da página inicial.
- Regras de impressão avançadas em CSS (`@media print`) que ocultam elementos administrativos, ajustam fontes e cores e garantem um layout A4 impecável.

### ✏️ Edição Dinâmica de Informações (Modais em Tempo Real)
- Painel administrativo com suporte a **edição completa** de todos os dados cadastrados.
- Janelas modais dinâmicas em JavaScript que permitem alterar registros de contatos, experiências, formações e certificados sem sair da página.

### 📜 Seção de Certificados e Cursos Complementares
- Gestão completa de certificações técnicas e cursos.
- **Seletor do Explorador de Arquivos**: O usuário pode selecionar arquivos em PDF, fotos (PNG, JPG, WebP) ou documentos (DOC, DOCX) diretamente do seu computador.
- Suporte a links web externos ou arquivos anexados localmente.

### 📁 Upload de Anexos do Currículo
- Possibilidade de anexar um arquivo PDF/Doc do currículo completo na seção de dados pessoais, disponibilizando um botão de download direto na barra superior pública.

### 🌓 Alternador de Tema Dark / Light Mode
- Suporte a **Modo Claro e Modo Escuro** na interface pública e no painel administrativo.
- Armazenamento da preferência do usuário no `localStorage` do navegador.

### 💼 Linha do Tempo (Timeline)
- Exibição de **Experiências Profissionais** e **Formações Acadêmicas** em formato de linha do tempo vertical interativa.

### 📇 Badges de Contato Inteligentes
- Mapeamento automático de ícones do FontAwesome para diferentes tipos de contato:
  - 📧 E-mail (`mailto:`)
  - 📱 Telefone / WhatsApp (`tel:`)
  - 💼 LinkedIn
  - 💻 GitHub
  - 🌐 Site / Portfólio

### 🖼️ Upload de Foto de Perfil
- Upload de imagens nos formatos JPG, PNG e WebP com geração de nomes únicos e atualização instantânea do perfil.

---

## 🛠️ Tecnologias Utilizadas

| Tecnologia | Descrição |
| :--- | :--- |
| **PHP 8.x (PDO)** | Linguagem server-side para regras de negócio e consultas preparadas contra SQL Injection. |
| **MySQL / MariaDB** | Banco de dados relacional com integridade referencial (`ON DELETE CASCADE`). |
| **CSS3 Avançado** | Variáveis CSS (`:root`), Grid, Flexbox, Glassmorphism, Micro-animações e `@media print`. |
| **JavaScript (ES6+)** | Manipulação do DOM, controle de modais e persistência do tema. |
| **FontAwesome 6.5.1** | Biblioteca de ícones vetoriais. |
| **Plus Jakarta Sans** | Tipografia moderna importada via Google Fonts. |

---

## 📁 Estrutura de Arquivos

```text
Curriculo_Digital_Senai/
├── index.php           # Página principal de exibição do currículo digital
├── admin.php           # Painel administrativo estilo Dashboard para gerenciar o currículo
├── style.css           # Estilos da página pública, temas claro/escuro e regras de impressão PDF
├── style1.css          # Estilos do painel de administração e modais de edição
├── conexao.php         # Conexão PDO com o MySQL + Auto-migração da tabela de certificados
├── crud.php            # Funções utilitárias reutilizáveis para Create, Read, Update e Delete
├── processa_foto.php   # Script responsável por receber e validar o upload de fotos
├── curriculo.sql        # Script SQL completo com o esquema de tabelas do banco de dados
├── uploads/            # Pasta destino das fotos de perfil dos usuários
└── README.md           # Documentação oficial do projeto
```

---

## 🚀 Como Executar o Projeto

### Pré-requisitos
- Servidor Web com suporte a PHP e MySQL (ex: **XAMPP**, WampServer, Laragon ou Apache nativo).

### Passo a Passo

1. **Clonar / Copiar os Arquivos**:
   - Coloque a pasta do projeto dentro do diretório `htdocs` do seu XAMPP:
     ```bash
     C:\xampp\htdocs\Curriculo_Digital_Senai
     ```

2. **Configurar o Banco de Dados**:
   - Abra o **phpMyAdmin** (`http://localhost/phpmyadmin`).
   - Crie um banco de dados chamado `curriculo_db`.
   - Importe o arquivo `curriculo.sql` para criar a estrutura inicial de tabelas.
   - *Nota: O sistema possui auto-criação de tabelas complementares via `conexao.php`.*

3. **Acessar a Aplicação**:
   - Visualização do Currículo: [http://localhost/Curriculo_Digital_Senai/index.php](http://localhost/Curriculo_Digital_Senai/index.php)
   - Painel Administrativo: [http://localhost/Curriculo_Digital_Senai/admin.php](http://localhost/Curriculo_Digital_Senai/admin.php)

---

## 📝 Licença

Projeto desenvolvido para fins educacionais e profissionais no âmbito do **SENAI**. Livre para uso, modificação e aprimoramento.
