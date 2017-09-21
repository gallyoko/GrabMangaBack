<?php

namespace GrabMangaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SaveMangaCommand extends ContainerAwareCommand
{
    private $containerApp;

    protected function configure()
    {
        $this
            ->setName('save:manga')
            ->setDescription('Save all principal mangas table');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->init();
            $output->write("Lancement de la sauvegarde.");
            $this->launch($output);
            $output->writeln("Termine.");
        } catch (\Exception $ex) {
            $output->writeln($ex->getMessage());
        }
    }

    private function init() {
        try {
            $this->containerApp = $this->getApplication()->getKernel()->getContainer();
            ini_set('memory_limit', '512M');
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors de l'initialisation des donnees : ". $ex->getMessage(), $ex->getCode());
        }
    }

    private function launch($output) {
        try {
            $this->containerApp->get('bdd.service')->dropSaveTables();
            $output->write(".");
            $this->containerApp->get('bdd.service')->saveTableManga();
            $output->write(".");
            $this->containerApp->get('bdd.service')->saveTableMangaTome();
            $output->write(".");
            $this->containerApp->get('bdd.service')->saveTableMangaChapter();
            $output->write(".");
            $this->containerApp->get('bdd.service')->saveTableMangaEbook();
            $output->write(".");
            $this->containerApp->get('bdd.service')->setMangaAction(1);
            $output->write(".");
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage(), $ex->getCode());
        }
    }

}
