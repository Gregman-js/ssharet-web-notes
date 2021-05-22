<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use AppBundle\Entity\Words;

class FetchWordsCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'app:fetch-words';

    protected function configure()
    {
        $this
        ->addArgument('fileName', InputArgument::REQUIRED, 'Name of words file.')
        ->setDescription('Fetch words from txt to db.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $name = __DIR__."/".$input->getArgument('fileName');
        $output->writeln($name);
        if(file_exists($name)) {
            $fn = fopen($name,"r");
  
            while(! feof($fn))  {
                $result = trim(fgets($fn));
                if(strlen($result) >= 3 && strlen($result) <= 6) {
                    if(!$em->getRepository(Words::class)->findOneByWord($result)){
                        // $output->writeln("Not find ".$result);
                        $word = new Words();
                        $word->setWord($result);
                        $em->persist($word);
                    }
                    // else $output->writeln("Find ".$result);
                }
            }
            fclose($fn);
            $em->flush();
            if(unlink($name))
                $output->writeln("File Deleted.");
            $output->writeln("DB successfully updated");
        } else {
            $output->writeln("Can't find a file");
        }

        // curl http://www.desiquintans.com/downloads/nounlist/nounlist.txt >> src/AppBundle/Command/nounlist.txt
        // curl https://raw.githubusercontent.com/imsky/wordlists/master/nouns/food.txt >> src/AppBundle/Command/food.txt

    }
}