<?php

namespace GrabMangaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateMangaCommand extends ContainerAwareCommand
{
    private $containerApp;

    protected function configure()
    {
        $this
            ->setName('generate:manga')
            ->setDescription('Generate manga');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        try {
            $output->writeln(date("d/m/Y H:i:s")." - Lancement de la generation.");
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
            $currentDownload = $this->containerApp->get('manga_download.service')->getCurrent();
            if (!$currentDownload) {
                $nextDownload = $this->containerApp->get('manga_download.service')->getNextOne();
                if ($nextDownload) {
                    if ($nextDownload->getManga()) {
                        $this->containerApp->get('generate.service')->generateByBook($nextDownload->getManga(), $nextDownload);
                    } elseif ($nextDownload->getMangaTome()) {
                        $this->containerApp->get('generate.service')->generateByTome($nextDownload->getMangaTome(), $nextDownload);
                    } elseif ($nextDownload->getMangaChapter()) {
                        $this->containerApp->get('generate.service')->generateByChapter($nextDownload->getMangaChapter(), $nextDownload);
                    }
                }
            }
            $timeStep = time() - $timeBegin;
            $output->writeln('Operation effectuee en '.floor($timeStep/60).'mn et '.fmod($timeStep, 60).' secondes');
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage(), $ex->getCode());
        }
    }

}
