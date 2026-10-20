-- produto.imagem passa a ser o caminho a partir da RAIZ do site, igual ao SGE
-- (SGE grava "<pasta>/produto<id>.<ext>" e mostra em eq.projetoscti.com.br/<equipe>/<imagem>).
-- Antes guardávamos relativo à pasta front/ ("img/cards/x.png").
UPDATE produto
SET imagem = 'front/' || imagem
WHERE imagem LIKE 'img/%';
