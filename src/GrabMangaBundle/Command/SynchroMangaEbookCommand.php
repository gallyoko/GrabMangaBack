<?php

namespace GrabMangaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SynchroMangaEbookCommand extends ContainerAwareCommand
{
    private $containerApp;

    protected function configure()
    {
        $this
            ->setName('sync:manga:ebook')
            ->setDescription('Synchronize all principal mangas ebook table');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
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
            $output->writeln('ENREGISTREMENT DES EBOOK');
            $mangaChapters = $this->containerApp->get('manga_chapter.service')->getList();
            $currentMangaChapter = 1;
            $offset = $this->containerApp->get('manga_ebook.service')->getLastChapterId() - 1;
            $mangaChapters = array_slice ($mangaChapters, $offset);
            $countMangaChapters = count($mangaChapters);
            foreach ($mangaChapters as $mangaChapter) {
                try {
                    $output->write('Ebook '.$currentMangaChapter.' / '.$countMangaChapters.'...');
                    $bookEbook = $this->containerApp->get('japscan.service')->getEbook($mangaChapter->getUrl());
                    if ($bookEbook) {
                        $this->containerApp->get('manga_ebook.service')->add($mangaChapter, $bookEbook);
                        $currentMangaChapter++;
                        $output->writeln('ajouté');
                    } else {
                        $output->writeln('erreur');
                        $countMangaChapters--;
                    }
                } catch (\Exception $ex) {
                    $output->writeln($ex->getMessage());
                }
                $bookEbook = null;
                if ($currentMangaChapter > 550) {
                    break;
                }
                gc_collect_cycles();
            }
            $timeStep = time() - $timeBegin;
            $output->writeln('Operation effectuee en '.floor($timeStep/60).'mn et '.fmod($timeStep, 60).' secondes');
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage(), $ex->getCode());
        }
    }

}
