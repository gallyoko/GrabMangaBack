<?php

namespace GrabMangaBundle\Services;

class BddService {
    private $em;
	
	public function __construct($doctrine) {
        $this->em = $doctrine->getManager();
	}

    public function dropSaveTables() {
        try {
            $queryDrop = "DROP TABLE IF EXISTS save_manga_ebook ;";
            $stmt = $this->em->getConnection()->prepare($queryDrop);
            $stmt->execute();
            $queryDrop = "DROP TABLE IF EXISTS save_manga_chapter ;";
            $stmt = $this->em->getConnection()->prepare($queryDrop);
            $stmt->execute();
            $queryDrop = "DROP TABLE IF EXISTS save_manga_tome ;";
            $stmt = $this->em->getConnection()->prepare($queryDrop);
            $stmt->execute();
            $queryDrop = "DROP TABLE IF EXISTS save_manga ;";
            $stmt = $this->em->getConnection()->prepare($queryDrop);
            $stmt->execute();
            return true;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de suppression des éventuelles tables de sauvegarde : ". $ex->getMessage(), $ex->getCode());
        }
    }

    public function saveTableManga() {
        try {
            $queryCreate = "CREATE TABLE IF NOT EXISTS `save_manga` (
                              `id` INT(11) NOT NULL AUTO_INCREMENT,
                              `title` VARCHAR(255) NOT NULL,
                              `url` VARCHAR(255) NOT NULL,
                              `synopsis` TEXT NULL DEFAULT NULL,
                              PRIMARY KEY (`id`))
                            ENGINE = InnoDB
                            AUTO_INCREMENT = 1
                            DEFAULT CHARACTER SET = utf8;";
            $stmt = $this->em->getConnection()->prepare($queryCreate);
            $create = $stmt->execute();
            if ($create === false) {
                throw new \Exception("Erreur lors de la creation de la table de sauvegarde.");
            }
            $queryInsert = "INSERT INTO save_manga (SELECT * FROM manga);";
            $stmt = $this->em->getConnection()->prepare($queryInsert);
            $insert = $stmt->execute();
            if ($insert === false) {
                throw new \Exception("Erreur lors de l'insertion des donnees a sauvegarder.");
            }
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors de la sauvegarde de la table <manga> : ". $ex->getMessage(), $ex->getCode());
        }
    }

    public function saveTableMangaTome() {
        try {
            $queryCreate = "CREATE TABLE IF NOT EXISTS `save_manga_tome` (
                              `id` INT(11) NOT NULL AUTO_INCREMENT,
                              `manga_id` INT(11) NOT NULL,
                              `title` VARCHAR(255) NOT NULL,
                              PRIMARY KEY (`id`),
                              INDEX `fk_save_manga_tome_manga1_idx` (`manga_id` ASC),
                              CONSTRAINT `fk_save_manga_tome_manga1`
                                FOREIGN KEY (`manga_id`)
                                REFERENCES `save_manga` (`id`)
                                ON DELETE NO ACTION
                                ON UPDATE NO ACTION)
                            ENGINE = InnoDB
                            AUTO_INCREMENT = 1
                            DEFAULT CHARACTER SET = utf8;";
            $stmt = $this->em->getConnection()->prepare($queryCreate);
            $create = $stmt->execute();
            if ($create === false) {
                throw new \Exception("Erreur lors de la creation de la table de sauvegarde.");
            }
            $queryInsert = "INSERT INTO save_manga_tome (SELECT * FROM manga_tome);";
            $stmt = $this->em->getConnection()->prepare($queryInsert);
            $insert = $stmt->execute();
            if ($insert === false) {
                throw new \Exception("Erreur lors de l'insertion des donnees a sauvegarder.");
            }
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors de la sauvegarde de la table <manga_tome> : ". $ex->getMessage(), $ex->getCode());
        }
    }

    public function saveTableMangaChapter() {
        try {
            $queryCreate = "CREATE TABLE IF NOT EXISTS `save_manga_chapter` (
                              `id` INT(11) NOT NULL AUTO_INCREMENT,
                              `manga_id` INT(11) NOT NULL,
                              `manga_tome_id` INT(11) NULL,
                              `title` VARCHAR(255) NOT NULL,
                              `url` VARCHAR(255) NOT NULL,
                              PRIMARY KEY (`id`),
                              INDEX `fk_save_manga_chapter_mangas1_idx` (`manga_id` ASC),
                              INDEX `fk_save_manga_chapter_manga_tome1_idx` (`manga_tome_id` ASC),
                              CONSTRAINT `fk_save_manga_chapter_mangas1`
                                FOREIGN KEY (`manga_id`)
                                REFERENCES `save_manga` (`id`)
                                ON DELETE NO ACTION
                                ON UPDATE NO ACTION,
                              CONSTRAINT `fk_save_manga_chapter_manga_tome1`
                                FOREIGN KEY (`manga_tome_id`)
                                REFERENCES `save_manga_tome` (`id`)
                                ON DELETE NO ACTION
                                ON UPDATE NO ACTION)
                            ENGINE = InnoDB
                            AUTO_INCREMENT = 1
                            DEFAULT CHARACTER SET = utf8;";
            $stmt = $this->em->getConnection()->prepare($queryCreate);
            $create = $stmt->execute();
            if ($create === false) {
                throw new \Exception("Erreur lors de la creation de la table de sauvegarde.");
            }
            $queryInsert = "INSERT INTO save_manga_chapter (SELECT * FROM manga_chapter);";
            $stmt = $this->em->getConnection()->prepare($queryInsert);
            $insert = $stmt->execute();
            if ($insert === false) {
                throw new \Exception("Erreur lors de l'insertion des donnees a sauvegarder.");
            }
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors de la sauvegarde de la table <manga_chapter> : ". $ex->getMessage(), $ex->getCode());
        }
    }

    public function saveTableMangaEbook() {
        try {
            $queryCreate = "CREATE TABLE IF NOT EXISTS `save_manga_ebook` (
                              `id` INT(11) NOT NULL AUTO_INCREMENT,
                              `manga_chapter_id` INT(11) NOT NULL,
                              `url_mask` VARCHAR(255) NOT NULL,
                              `page_min` VARCHAR(255) NOT NULL,
                              `page_max` VARCHAR(255) NOT NULL,
                              `page_mask` VARCHAR(255) NOT NULL,
                              `format` VARCHAR(4) NOT NULL,
                              PRIMARY KEY (`id`),
                              INDEX `fk_save_manga_ebook_manga_chapter1_idx` (`manga_chapter_id` ASC),
                              CONSTRAINT `fk_save_manga_ebook_manga_chapter1`
                                FOREIGN KEY (`manga_chapter_id`)
                                REFERENCES `save_manga_chapter` (`id`)
                                ON DELETE NO ACTION
                                ON UPDATE NO ACTION)
                            ENGINE = InnoDB
                            AUTO_INCREMENT = 1
                            DEFAULT CHARACTER SET = utf8;";
            $stmt = $this->em->getConnection()->prepare($queryCreate);
            $create = $stmt->execute();
            if ($create === false) {
                throw new \Exception("Erreur lors de la creation de la table de sauvegarde.");
            }
            $queryInsert = "INSERT INTO save_manga_ebook (SELECT * FROM manga_ebook);";
            $stmt = $this->em->getConnection()->prepare($queryInsert);
            $insert = $stmt->execute();
            if ($insert === false) {
                throw new \Exception("Erreur lors de l'insertion des donnees a sauvegarder.");
            }
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors de la sauvegarde de la table <manga_ebook> : ". $ex->getMessage(), $ex->getCode());
        }
    }

    public function setMangaAction($save = 0, $maj = 0) {
        try {
            $sql = "UPDATE manga_action SET save=" . $save . ", maj=" . $maj . ";";
            $stmt = $this->em->getConnection()->prepare($sql);
            $insert = $stmt->execute();
            if ($insert === false) {
                throw new \Exception("Erreur mise a jour.");
            }
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors de la mise a jour de la table <manga_action> : ". $ex->getMessage(), $ex->getCode());
        }
    }

}