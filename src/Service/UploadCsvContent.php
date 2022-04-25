<?php

namespace App\Service;

use App\Repository\BookStringsRepository;
use Doctrine\ORM\EntityManagerInterface;

class UploadCsvContent
{
  protected $entityManager;

  protected $bookStringsRepository;

  public function __construct(EntityManagerInterface $entityManager, BookStringsRepository $bookStringsRepository)
  {
    $this->entityManager = $entityManager;
    $this->bookStringsRepository = $bookStringsRepository;
  }

  public function contentToDb($handle, &$messageArray): string
  {
    $this->bookStringsRepository->trancateTable();

    while (($getData = fgetcsv($handle, 10000, ",")) !== FALSE) {
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
        'release_date' => !empty($getData[8]) ? $getData[8] : NULL,
      ]);

      $lastImportedBookId = $bookId;

      if (!$r) $messageArray['skipped_id'][] = $bookId;      
    }

    return $lastImportedBookId;    
  }
}