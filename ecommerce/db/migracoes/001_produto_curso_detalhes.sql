-- Produto passa a guardar o curso (cor/filtro do site) e os itens do "Ver detalhes".
-- Antes isso ficava fixo no HTML. Rodar uma vez no banco.
ALTER TABLE produto ADD COLUMN IF NOT EXISTS curso varchar(20);
ALTER TABLE produto ADD COLUMN IF NOT EXISTS detalhes varchar(500);

-- valores que estavam no HTML (front/produtos.html) para os 3 produtos atuais
UPDATE produto SET curso = 'informatica',
    detalhes = 'Conceitos de programação e lógica|Redes e protocolos|Hardware e sistemas operacionais|Coleção exclusiva do curso de Informática do CTI'
    WHERE id_produto = 5 AND curso IS NULL;
UPDATE produto SET curso = 'mecanica',
    detalhes = 'Motores e transmissões|Usinagem e processos de fabricação|Máquinas e ferramentas|Coleção exclusiva do curso de Mecânica do CTI'
    WHERE id_produto = 6 AND curso IS NULL;
UPDATE produto SET curso = 'eletronica',
    detalhes = 'Circuitos e componentes|Automação e controle|Leitura de esquemas elétricos|Coleção exclusiva do curso de Eletrônica do CTI'
    WHERE id_produto = 7 AND curso IS NULL;
