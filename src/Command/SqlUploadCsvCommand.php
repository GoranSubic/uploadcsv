<?php

namespace App\Command;

use App\Service\UploadCsvContent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
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

  protected $uploadCsvContent;

  public function __construct(KernelInterface $kernel, EntityManagerInterface $entityManager, UploadCsvContent $uploadCsvContent)
  {
      parent::__construct();
      $this->projectDir = $kernel->getProjectDir();
      $this->entityManager = $entityManager;
      $this->uploadCsvContent = $uploadCsvContent;
  }

  protected function configure(): void
  {
    $this
        ->setHelp('This command allows you to upload data from csv file named brd.csv')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $progressBar = new ProgressBar($output, 2);
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
        $output->writeln([
          '',
          '<comment>Starting with filling data to BookStrings.</comment>',
          '',
        ]);

        $progressBar->start();
        // $progressBar->clear();
        // $progressBar->advance();
        // $progressBar->display();
        
        $messageArray = [];
        $lastImportedBookId = $this->uploadCsvContent->contentToDb($handle, $messageArray);

        $progressBar->advance();

        if (!empty($messageArray['skipped_id'])) {
          $bookIds = implode(", ", $messageArray['skipped_id']);
          $output->writeln([
            '',
            '<comment>An error occured for book id: </comment>' . $bookIds,
            '',
          ]);
        }

        if (!empty($lastImportedBookId)) {
          $output->writeln([
            '',
            'Last imported book in "strings" table with id: ' . $lastImportedBookId,
            '',
          ]);
        }

        fclose($handle);
        return Command::SUCCESS;
      }

      $output->writeln('File do not exist!');
      return Command::FAILURE;
    }
  }
}
