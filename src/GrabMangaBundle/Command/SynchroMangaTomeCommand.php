<?php

namespace GrabMangaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SynchroMangaTomeCommand extends ContainerAwareCommand
{
    private $containerApp;

    protected function configure()
    {
        $this
            ->setName('sync:manga:tome')
            ->setDescription('Synchronize all principal mangas tome table');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        try {
            $output->writeln(date("d/m/Y H:i:s")." - Lancement de la synchronisation.");
            $this->init();
            $this->launch($output);
            $output->writeln(date("d/m/Y H:i:s")." - Termine.\n");
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
            $output->writeln('ENREGISTREMENT DES TOMES ET CHAPITRES');
            $mangas = $this->containerApp->get('manga.service')->getList();
            $mangas = array_slice ($mangas, 1949);
            $countMangas = count($mangas);
            $currentManga = 1;
            foreach ($mangas as $manga) {
                try {
                    $output->write('Manga '.$currentManga.' / '.$countMangas.'...');
                    $bookTomes = $this->containerApp->get('japscan.service')->getTomeAndChapter($manga->getUrl());
                    if (count($bookTomes) > 0){
                        $this->containerApp->get('manga_tome.service')->add($manga, $bookTomes);
                        $currentManga++;
                        $countTomes = count($bookTomes);
                        $output->writeln($countTomes.' tome(s) ajouté(s)');
                    } else {
                        $output->writeln(' aucun tome');
                        $countMangas--;
                    }
                } catch (\Exception $ex) {
                    $output->writeln($ex->getMessage());
                }
                $bookTomes = null;
                if ($currentManga > 200) {
                    break;
                }
            }
            $timeStep = time() - $timeBegin;
            $output->writeln('Operation effectuee en '.floor($timeStep/60).'mn et '.fmod($timeStep, 60).' secondes');
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage(), $ex->getCode());
        }
    }

}
