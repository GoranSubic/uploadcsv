<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
  name: 'app:sql-upload-csv-data',
  description: 'Uploads data from brd.csv file to db.',
  aliases: ['app:sql-upload-csv-data'],
  hidden: false
)]
class SqlUploadCsvCommand extends Command
{
    protected static $defaultName = 'app:sql-upload-csv-data';

    protected static $defaultDescription = 'SQL uploads data from brd.csv file to db.';

    protected $projectDir;

    protected $entityManager;

    public function __construct(KernelInterface $kernel, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->projectDir = $kernel->getProjectDir();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
      $this
          ->setHelp('This command allows you to upload data from csv file named brd.csv')
      ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
      $output->writeln([
          'Uploads data to db',
          '==================',
          '',
      ]);

      $destination = $this->projectDir.'/public/uploads';
      $fileName = 'brd.csv';
      $file = $destination . '/' . $fileName;

      $filesystem = new Filesystem();
      // Import file content to db.
      if($filesystem->exists($destination) && $filesystem->exists([$file])) {
        if (($handle = fopen($file, "r")) !== FALSE) {
          $i = 0;
          while (($getData = fgetcsv($handle, 10000, ",")) !== FALSE) {
              $i++;
              $bookId = $getData[0];

              $sql = "INSERT INTO book_strings (id, series, number, name, type, publisher, author, price, release_date) 
              VALUES (:id, :series, :number, :name, :type, :publisher, :author, :price, :release_date)";
              $stmt = $this->entityManager->getConnection()->prepare($sql);
              $r = $stmt->execute([
                'id' => $getData[0],
                'series' => $getData[1],
                'number' => $getData[2],
                'name' => $getData[3],
                'type' => $getData[4],
                'publisher' => $getData[5],
                'author' => $getData[6],
                'price' => $getData[7],
                'release_date' => !empty($getData[8]) ? $getData[8] : '',
              ]);

              if (!$r) {
                $output->writeln('<comment>An error occured for book id: </comment>' . $bookId);
              }
          }
        }
  
        $output->writeln('Last imported book with id: ' . $bookId);

        fclose($handle);
        return Command::SUCCESS;
      }

      $output->writeln('File do not exist!');
      return Command::FAILURE;
    }
}
