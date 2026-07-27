CREATE TABLE `praticien_justificatif` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `praticien_id` int(11) NOT NULL,
  `pratique` varchar(255) NOT NULL,
  `justificatif` longblob NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`praticien_id`) REFERENCES `praticien`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

