<?php
namespace GrabMangaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Generates validator yml file for entities bundle given
 *
 * Class GenerateValidatorCommand
 * @package GrabMangaBundle\Command
 */
class GenerateValidatorCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('generate:validator')
            ->setDescription('Generates validator yml file for entities bundle given')
            ->addArgument('bundle', InputArgument::REQUIRED, 'Bundle name')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrinePathSave = false;
        // récupération du chemin du dossier doctrine contenant les descriptions d'entité
        $doctrinePath = $this->getDoctrinePath();
        if ($doctrinePath != null) {
            // s'il existe, on le sauvegarde en le renommant
            rename($doctrinePath, $doctrinePath.'_old');
            $doctrinePathSave = true;
        }
        // Génération des fichiers de description des entités au format XML
        if ($this->generateXmlDescriptionEntities($input->getArgument('bundle'))) {
            // génération du fichier de validation yml
            if ($this->generateValidator($output)) {
                $output->writeln('Le fichier de validation a ete genere avec succes.');
            } else {
                $output->writeln('Erreur lors de la generation du fichier de validation.');
                $output->writeln('Arret du traitement.');
            }
            // suppression du dossier doctrine contenant les descriptions d'entité
            $this->removeDoctrinePath();
        } else {
            $output->writeln('Erreur lors de la generation des fichiers de description des entites.');
            $output->writeln('Arret du traitement.');
        }
        // si un dossier doctrine existait déjà et avait donc été sauvegaerder
        if ($doctrinePathSave) {
            rename($doctrinePath.'_old', $doctrinePath);
        }
        $output->writeln('Termine.');
    }

    /**
     * Return absolute doctrine table description file path
     *
     * @return bool|null|string
     */
    private function getDoctrinePath() {
        try {
            $doctrinePath = realpath(
                dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.
                'Resources'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'doctrine'
            );

            return $doctrinePath;
        } catch (\Exception $ex) {
            return null;
        }
    }

    /**
     * Generate doctrine table xml description file
     *
     * @param $bundle
     * @return bool
     */
    private function generateXmlDescriptionEntities($bundle) {
        try {
            $command = $this->getApplication()->find('doctrine:mapping:import');
            $arguments = array(
                'bundle' => $bundle,
                'mapping-type'    => 'xml',
            );
            $greetInput = new ArrayInput($arguments);
            $returnCode = $command->run($greetInput, new NullOutput());
            if ($returnCode==0) {
                return true;
            }
            return false;
        } catch (\Exception $ex) {
            return false;
        }
    }

    /**
     * Generate validator file from doctrine xml file
     *
     * @param OutputInterface $output
     * @return bool
     */
    private function generateValidator(OutputInterface $output) {
        try {
            $doctrinePath = $this->getDoctrinePath();
            $validationYmlPath = $doctrinePath.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'validation.yml';
            $validationYmlSave = false;
            if(file_exists($validationYmlPath)) {
                rename($validationYmlPath, $validationYmlPath.'_old');
                $validationYmlSave = true;
            }
            $validationYml = fopen($validationYmlPath, 'a+');
            $files = scandir($doctrinePath);
            foreach ($files as $file) {
                if ($file!='.' && $file!='..') {
                    $xml = simplexml_load_file($doctrinePath.DIRECTORY_SEPARATOR.$file);
                    foreach($xml->entity->attributes() as $element => $value) {
                        if ($element=='name'){
                            fputs($validationYml, $value.':'."\n");
                            $namespaceEntityTiles = explode('\\', $value);
                            $namespaceEntity = '';
                            for ($i=0; $i<(count($namespaceEntityTiles)-1); $i++){
                                $namespaceEntity .= $namespaceEntityTiles[$i].'\\';
                            }
                        }
                    }
                    fputs($validationYml, '    properties:'."\n");
                    $columnField = [];
                    foreach($xml->entity->field as $element => $value) {
                        $type = '';
                        foreach ($value->attributes() as $fieldElement => $fieldValue) {
                            if ($fieldElement == 'name') {
                                $fieldName = (string)$fieldValue;
                                fputs($validationYml, '        '.$fieldName.':'."\n");
                            } elseif ($fieldElement == 'column') {
                                $columnName = (string)$fieldValue;
                            } elseif ($fieldElement == 'nullable' && $fieldValue == 'false') {
                                if ($type == 'boolean') {
                                    fputs($validationYml, '            - NotNull:'."\n");
                                } else {
                                    fputs($validationYml, '            - NotBlank:'."\n");
                                }
                                fputs($validationYml, '                message: "L\'attribut <'.$fieldName.'> est obligatoire !"'."\n");
                            } elseif ($fieldElement == 'type') {
                                $type = $fieldValue;
                                if ($fieldValue=='date') {
                                    fputs($validationYml, '            - Date: '."\n");
                                    fputs($validationYml, '                message: "L\'attribut <'.$fieldName.'> doit etre au format date"'."\n");
                                } elseif ($fieldValue=='datetime') {
                                    fputs($validationYml, '            - DateTime: '."\n");
                                    fputs($validationYml, '                format: "Y-m-d H:i:s"'."\n");
                                    fputs($validationYml, '                message: "L\'attribut <'.$fieldName.'> doit avoir un format de date '."<".'Y-m-d :i:s'.">".'"'."\n");
                                } elseif ($fieldValue=='text') {
                                    fputs($validationYml, '            - Type: '."\n");
                                    fputs($validationYml, '                type: string'."\n");
                                    fputs($validationYml, '                message: "L\'attribut <'.$fieldName.'> doit etre de type '."<".'string'.">".' !"'."\n");
                                } elseif ($fieldValue=='smallint') {
                                    fputs($validationYml, '            - Type: '."\n");
                                    fputs($validationYml, '                type: integer'."\n");
                                    fputs($validationYml, '                message: "L\'attribut <'.$fieldName.'> doit etre de type '."<".'integer'.">".' !"'."\n");
                                } elseif ($fieldValue=='boolean') {
                                    fputs($validationYml, '            - Type: '."\n");
                                    fputs($validationYml, '                type: bool'."\n");
                                    fputs($validationYml, '                message: "L\'attribut <'.$fieldName.'> doit etre de type '."<".'boolean'.">".' !"'."\n");
                                } else {
                                    fputs($validationYml, '            - Type: '."\n");
                                    fputs($validationYml, '                type: '.$fieldValue."\n");
                                    fputs($validationYml, '                message: "L\'attribut <'.$fieldName.'> doit etre de type <'.$fieldValue.'> !"'."\n");
                                }
                            } elseif ($fieldElement == 'length') {
                                fputs($validationYml, '            - Length: '."\n");
                                fputs($validationYml, '                max: '.$fieldValue."\n");
                                fputs($validationYml, '                maxMessage: "La longueur de l\'attribut <'.$fieldName.'> doit faire au plus {{ limit }} caracteres !"'."\n");
                            }
                        }
                        $columnField[$columnName] = $fieldName;
                    }
                    foreach($xml->entity->{'many-to-one'} as $element => $value) {
                        foreach ($value->attributes() as $joinElement => $joinValue) {
                            if ($joinElement == 'field') {
                                $field = (string)$joinValue;
                            } elseif ($joinElement == 'target-entity') {
                                $entity = (string)$joinValue;
                            }
                        }
                        foreach ($value->{'join-columns'} as $joinColumn => $joinColumnValue) {
                            foreach ($joinColumnValue->{'join-column'}->attributes() as $joinElement => $joinValue) {
                                if ($joinElement == 'name') {
                                    $columnField[(string)$joinValue] = $field;
                                }
                            }
                        }
                        fputs($validationYml, '        '.$field.':'."\n");
                        fputs($validationYml, '            - Type'.':'."\n");
                        fputs($validationYml, '                type'.': '.$namespaceEntity.$entity."\n");
                        fputs($validationYml, '                message'.': "L\'attribut <'.$field.'> de type <'.$entity.'> est obligatoire !"'."\n");
                    }
                    foreach($xml->entity->{'unique-constraints'} as $element => $value) {
                        fputs($validationYml, '    constraints:'."\n");
                        foreach ($value as $constraint => $constraintAttributes) {
                            foreach ($constraintAttributes->attributes() as $constraintElement => $constraintValue) {
                                if ($constraintElement=='columns') {
                                    $columns = explode(',', $constraintValue);
                                    foreach($columns as $column) {
                                        fputs($validationYml, '        - Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity: '."\n");
                                        fputs($validationYml, '            fields: '.$columnField[$column]."\n");
                                        fputs($validationYml, '            message: "La valeur attribuee a <'.$columnField[$column].'> existe deja !"'."\n");
                                    }
                                }
                            }
                        }
                    }
                    fputs($validationYml, "\n");
                }
            }
            fclose($validationYml);
            if ($validationYmlSave) {
                unlink($validationYmlPath.'_old');
            }
            return true;
        } catch (\Exception $ex) {
            $output->writeln($ex->getMessage());
            $doctrinePath = $this->getDoctrinePath();
            $validationYmlPath = $doctrinePath.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'validation.yml';
            $validationYmlPathOld = $doctrinePath.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'validation.yml_old';
            if (file_exists($validationYmlPath) && file_exists($validationYmlPathOld)) {
                unlink($validationYmlPath);
                rename($validationYmlPath.'_old', $validationYmlPath);
            }
            return false;
        }
    }

    /**
     * remove doctrine table description file
     *
     * @return bool
     */
    private function removeDoctrinePath() {
        try {
            $doctrinePath = $this->getDoctrinePath();
            $filesEntity = scandir($doctrinePath);
            foreach ($filesEntity as $file) {
                if ($file != '.' && $file != '..') {
                    unlink($doctrinePath.DIRECTORY_SEPARATOR.$file);
                }
            }
            rmdir($doctrinePath);
        } catch (\Exception $ex) {
            return false;
        }
    }

}