<?php

namespace App\Command;

use App\Entity\Book;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
  name: 'app:upload-csv-data',
  description: 'Uploads data from brd.csv file to db.',
  aliases: ['app:upload-csv-data'],
  hidden: false
)]
class UploadCsvCommand extends Command
{
    protected static $defaultName = 'app:upload-csv-data';

    protected static $defaultDescription = 'Uploads data from brd.csv file to db.';

    protected $projectDir;

    protected $entityManager;

    protected $bookRepository;

    public function __construct(KernelInterface $kernel, EntityManagerInterface $entityManager, BookRepository $bookRepository)
    {
        parent::__construct();
        $this->projectDir = $kernel->getProjectDir();
        $this->entityManager = $entityManager;
        $this->bookRepository = $bookRepository;
    }

    protected function configure(): void
    {
      $this
          ->setHelp('This command allows you to upload data from csv file named...')
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
              $bookId = (int)$getData[0];

              $existingBook = FALSE;
              if (!empty($bookId)) {
                $existingBook = $this->bookRepository->findBy(['id' => $bookId]);

                if (!empty($existingBook)) $output->writeln('Skiped import of existing book with id: ' . $bookId);
              }

              if (!$existingBook && !empty($bookId)) {
                $book = new Book;
                $book->setId($getData[0]);
                $book->setSeries($getData[1]);
                $book->setNumber($getData[2]);
                $book->setName($getData[3]);
                $book->setType($getData[4]);
                $book->setPublisher($getData[5]);
                $book->setAuthor($getData[6]);
                $book->setPrice($getData[7]);
                if (!empty($getData[8])) {
                    $book->setReleaseDate($getData[8]);
                }

                $this->entityManager->persist($book);
              } else if (empty($bookId)) {
                $output->writeln('Empty id, skiped import of book with name: ' . $getData[3]);
              }

              if ($i%100) {
                  $this->entityManager->flush();
                  $this->entityManager->clear();
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