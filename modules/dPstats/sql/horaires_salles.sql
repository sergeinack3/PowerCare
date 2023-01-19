-- Table des entrées/sorties
CREATE TEMPORARY TABLE operations_salle_date (
  op_id INT(11) UNSIGNED,
  entree TIME,
  sortie TIME,
  salle_id INT(11) UNSIGNED,
  `date` DATE
);

-- Opérations affectées aux salles
INSERT INTO operations_salle_date (op_id, entree, sortie, salle_id, `date`)
SELECT operation_id, entree_salle, sortie_salle, `salle_id` , `date`
FROM `operations`
WHERE `salle_id` IS NOT NULL
AND `date` IS NOT NULL;

-- Opérations affectées aux plages opératoire
INSERT INTO operations_salle_date (op_id, entree, sortie, salle_id, `date`)
SELECT operation_id, entree_salle, sortie_salle, `operations` . `salle_id` , `plagesop` . `date`
FROM `operations`, `plagesop` 
WHERE `operations` . `salle_id` IS NOT NULL
AND `plagesop` . plageop_id = operations.plageop_id;

-- Table des ouvertures et fermetures de salle chaque couple salle/jour
CREATE TEMPORARY TABLE es_minmax_salle_date (
  nb_op INT(11) UNSIGNED,
  entree TIME,
  sortie TIME,
  salle_id INT(11) UNSIGNED,
  `date` DATE
);

-- Calcul des ouvertures et fermutures
INSERT INTO es_minmax_salle_date (nb_op, entree, sortie, salle_id, `date`)
SELECT COUNT(op_id), MIN(entree), MAX(sortie), `salle_id`, `date`
FROM operations_salle_date
GROUP BY  `date`, `salle_id`;

-- Calcul des moyenne d'ouvertures et fermetures
SELECT 
  salle_id AS Salle,
  SUM(nb_op) AS total_op, 
  FORMAT(AVG(nb_op), 1) AS moyenne_op, 
  sec_to_time(AVG(time_to_sec(entree))) AS moyenne_entree, 
  sec_to_time(SQRT(VARIANCE(time_to_sec(entree)))) AS ecart_entree, 
  sec_to_time(AVG(time_to_sec(sortie))) AS moyenne_sortie,
  sec_to_time(SQRT(VARIANCE(time_to_sec(sortie)))) AS ecart_sortie
FROM es_minmax_salle_date
GROUP BY salle_id;