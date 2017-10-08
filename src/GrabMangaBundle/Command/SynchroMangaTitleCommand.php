<?php

namespace GrabMangaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SynchroMangaTitleCommand extends ContainerAwareCommand
{
    private $containerApp;

    protected function configure()
    {
        $this
            ->setName('sync:manga:title')
            ->setDescription('Synchronize all principal mangas title table');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $output->writeln(date("d/m/Y H:i:s")." - Lancement de la synchronisation.");
            $this->init();
            $this->launch($output);
            $output->writeln(date("d/m/Y H:i:s")." - Termine.");
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
            $timeBegin = time();
            //$this->containerApp->get('bdd.service')->checkSaveOk();
            //$this->containerApp->get('bdd.service')->setMangaAction();
            $output->writeln('ENREGISTREMENT DES MANGAS');
            $bookMangas = $this->containerApp->get('japscan.service')->getMangaTitles();
            $countMangas = count($bookMangas);
            $currentManga = 0;
            foreach ($bookMangas as $bookManga) {
                try {
                    $mangaBook = $this->containerApp->get('japscan.service')->setSynopsis($bookManga);
                    $mangaBook = $this->containerApp->get('japscan.service')->setCover($mangaBook);
                    $this->containerApp->get('manga.service')->add($mangaBook);
                    $currentManga++;
                    $output->writeln($currentManga.' / '.$countMangas);
                } catch (\Exception $ex) {
                    $output->writeln($ex->getMessage());
                }
                $mangaBook = null;
            }
            $bookMangas = null;
            gc_collect_cycles();
            $timeStep = time() - $timeBegin;
            $output->writeln('Operation effectuee en '.floor($timeStep/60).'mn et '.fmod($timeStep, 60).' secondes');
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage(), $ex->getCode());
        }
    }

}
