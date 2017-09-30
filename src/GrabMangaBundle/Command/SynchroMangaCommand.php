<?php

namespace GrabMangaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SynchroMangaCommand extends ContainerAwareCommand
{
    private $containerApp;

    protected function configure()
    {
        $this
            ->setName('sync:manga')
            ->setDescription('Synchronize all principal mangas table');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $output->writeln("Lancement de la synchronisation.");
            $this->init();
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

    private function launch(OutputInterface $output) {
        try {
            //$this->containerApp->get('bdd.service')->checkSaveOk();
            //$this->containerApp->get('bdd.service')->setMangaAction();
            $bookMangas = $this->containerApp->get('japscan.service')->getMangaTitles();
            foreach ($bookMangas as $bookManga) {
                try {
                    $mangaBook = $this->containerApp->get('japscan.service')->setTomeAndChapter($bookManga);
                    $this->containerApp->get('manga.service')->add($mangaBook);
                } catch (\Exception $ex) {
                    $output->writeln($ex->getMessage(), $ex->getCode());
                }
                $mangaBook = null;
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage(), $ex->getCode());
        }
    }

}
