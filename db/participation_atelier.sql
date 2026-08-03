CREATE TABLE `participation_atelier` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `atelier_id` int(10) UNSIGNED NOT NULL,
  `praticien_id` int(11) NOT NULL,
  `state` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_atelier_candidate` (`atelier_id`),
  KEY `fk_praticien_candidate` (`praticien_id`),
  CONSTRAINT `fk_atelier_candidate` FOREIGN KEY (`atelier_id`) REFERENCES `atelier` (`id`) ON DELETE CASCADE
  CONSTRAINT `fk_praticien_candidate` FOREIGN KEY (`praticien_id`) REFERENCES `praticien` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
