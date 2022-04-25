<?php

namespace App\Command;

use App\Entity\Author;
use App\Entity\BookRelational;
use App\Entity\Publisher;
use App\Repository\AuthorRepository;
use App\Repository\BookRelationalRepository;
use App\Repository\BookStringsRepository;
use App\Repository\PublisherRepository;
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
  name: 'app:relational-upload-csv-data',
  description: 'Uploads data from brd.csv file to db - Book, Publisher, Author.',
  aliases: ['app:relcsvupl'],
  hidden: false
)]
class RelationalUploadCsvCommand extends Command
{
    protected static $defaultName = 'app:relational-upload-csv-data';

    protected static $defaultDescription = 'SQL uploads data from brd.csv file to db - Book related with Publisher and Author.';

    protected $projectDir;

    protected $entityManager;

    protected $bookStringsRepository;

    protected $bookRelationalRepository;

    protected $publisherRepository;

    protected $authorRepository;

    protected $uploadCsvContent;

    public function __construct(
      KernelInterface $kernel, 
      EntityManagerInterface $entityManager, 
      BookStringsRepository $bookStringsRepository,
      BookRelationalRepository $bookRelationalRepository,
      PublisherRepository $publisherRepository,
      AuthorRepository $authorRepository,
      UploadCsvContent $uploadCsvContent)
    {
        parent::__construct();
        $this->projectDir = $kernel->getProjectDir();
        $this->entityManager = $entityManager;
        $this->bookStringsRepository = $bookStringsRepository;
        $this->bookRelationalRepository = $bookRelationalRepository;
        $this->publisherRepository = $publisherRepository;
        $this->authorRepository = $authorRepository;
        $this->uploadCsvContent = $uploadCsvContent;
    }

    protected function configure(): void
    {
      $this
          ->setHelp('This command allows you to upload data from csv file named brd.csv to db tables BookRelational, Publisher, Author.')
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
          // $progressBar->advance();
          // $progressBar->clear();
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
          
          $output->writeln([
            '',
            '<comment>Starting with filling data to BookRelational, Publisher and Author tables.</comment>',
            '',
          ]);

          $filled = $this->fillBookRelational($input, $output, $progressBar);

          if ($filled) {
            return Command::SUCCESS;
          }

          $output->writeln([
            '',
            'Table BookStrings is empty!',
            '',
          ]);
          return Command::FAILURE;
        }
      }

      $output->writeln([
        '',
        'File is empty!',
        '',
      ]);
      return Command::FAILURE;
    }

    public function fillBookRelational($input, $output, $progressBar): bool
    {

          $bookStrings = $this->bookStringsRepository->findAll();

          if (!empty($bookStrings)) {
          $progressBar->setMaxSteps(count($bookStrings));
          $progressBar->display();

          $skippedIds = '';
          $countSkipped = 0;
          $i = 0;
          foreach($bookStrings as $book) {            
            $progressBar->clear();
            $progressBar->advance();
            $progressBar->display();

            $i++;
            $bookId = (int)$book->getId();

            $bookRelational = NULL;
            if (!empty($bookId)) {
              $bookRelational = $this->bookRelationalRepository->findOneBy(['id' => $bookId]);
            } else {
              $output->writeln([
                '',
                'Empty id, skiped import of book with name: ' . $book->getName(),
                '',
              ]);
              continue;
            }

            if (empty($bookRelational)) {
              $bookRelational = new BookRelational;
            }
              
            $bookRelational->setId($book->getId());
            $bookRelational->setSeries($book->getSeries());
            $bookRelational->setNumber($book->getNumber());
            $bookRelational->setName($book->getName());
            $bookRelational->setType($book->getType());

            // Check if existing publisher, if not then create one.
            if (!empty($book->getPublisher())) {
              $publisher = $this->publisherRepository->findOneBy(['name' => $book->getPublisher()]);
              if (empty($publisher)) {
                $publisher = new Publisher($book->getPublisher());
              }
              $this->entityManager->persist($publisher);
              $this->entityManager->flush();
              $bookRelational->setPublisher($publisher);
            }

            // Check if existing author, if not then create one.
            if (!empty($book->getAuthor())) {
              $author = $this->authorRepository->findOneBy(['name' => $book->getAuthor()]);
              if (empty($author)) {
                $author = new Author($book->getAuthor());
              }
              $this->entityManager->persist($author);
              $this->entityManager->flush();
              $bookRelational->setAuthor($author);
            }

            $bookRelational->setPrice($book->getPrice());
            if (!empty($book->getReleaseDate())) {
                $bookRelational->setReleaseDate($book->getReleaseDate());
            }

            $this->entityManager->persist($bookRelational);
            
            // if ($i%100) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            // }
          }

          if (!empty($skippedIds)) {
            $output->writeln([
              '',
              'Skiped import books with ids: ' . $skippedIds,
              'Total number skipped books: ' . $countSkipped,
              '',
            ]);
          }
          $progressBar->finish();
          return TRUE;
        } else {
          $progressBar->finish();
          return FALSE;
        }

    }
}
