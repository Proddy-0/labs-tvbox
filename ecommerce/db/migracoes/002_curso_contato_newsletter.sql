-- Cursos saem do código e viram tabela (antes: lista repetida no util.php, no script.js e nos filtros do HTML).
CREATE TABLE IF NOT EXISTS curso (
    id_curso serial PRIMARY KEY,
    nome varchar(60) NOT NULL,
    cor char(7) NOT NULL DEFAULT '#14537D',   -- cor do card/filtro (hex)
    classe varchar(20),                        -- classe CSS pronta do site (is-info, is-mec, is-eletro); vazio = usa a cor
    excluido boolean DEFAULT false,
    data_exclusao timestamp
);

INSERT INTO curso (nome, cor, classe)
SELECT v.nome, v.cor, v.classe
FROM (VALUES ('Informática', '#1D51C1', 'is-info'),
             ('Mecânica',    '#BA1313', 'is-mec'),
             ('Eletrônica',  '#EB9F1C', 'is-eletro')) AS v(nome, cor, classe)
WHERE NOT EXISTS (SELECT 1 FROM curso);

ALTER TABLE produto ADD COLUMN IF NOT EXISTS fk_curso integer REFERENCES curso(id_curso);

-- produto.curso (texto, criado na 001) vira fk_curso
DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = 'produto' AND column_name = 'curso') THEN
        UPDATE produto SET fk_curso = curso.id_curso
        FROM curso
        WHERE produto.fk_curso IS NULL
          AND curso.classe = CASE produto.curso
                                 WHEN 'informatica' THEN 'is-info'
                                 WHEN 'mecanica' THEN 'is-mec'
                                 WHEN 'eletronica' THEN 'is-eletro'
                             END;
        ALTER TABLE produto DROP COLUMN curso;
    END IF;
END $$;

-- Mensagens do formulário de contato (front/contato.html)
CREATE TABLE IF NOT EXISTS contato (
    id_contato serial PRIMARY KEY,
    nome varchar(100) NOT NULL,
    email varchar(150) NOT NULL,
    assunto varchar(50) NOT NULL,
    mensagem varchar(2000) NOT NULL,
    data timestamp DEFAULT CURRENT_TIMESTAMP,
    lida boolean DEFAULT false,
    fk_usuario integer REFERENCES usuario(id_usuario)  -- preenchido se estava logado
);

-- Inscrições da newsletter (front/index.html)
CREATE TABLE IF NOT EXISTS newsletter (
    id_newsletter serial PRIMARY KEY,
    email varchar(150) NOT NULL UNIQUE,
    data timestamp DEFAULT CURRENT_TIMESTAMP
);
