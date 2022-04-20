<?php

namespace App\Entity;

use App\Repository\BookRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookRepository::class)]
class BookStrings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $bookId;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $series;

    #[ORM\Column(type: 'string', nullable: true)]
    private $number;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $type;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $publisher;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $author;

    #[ORM\Column(type: 'string', nullable: true)]
    private $price;

    #[ORM\Column(type: 'string', nullable: true)]
    private $releaseDate;

    public function getBookId(): ?int
    {
        return $this->bookId;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getSeries(): ?string
    {
        return $this->series;
    }

    public function setSeries(?string $series): self
    {
        $this->series = $series;

        return $this;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(?string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPublisher(): ?string
    {
        return $this->publisher;
    }

    public function setPublisher(?string $publisher): self
    {
        $this->publisher = $publisher;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): self
    {
        $this->price = $price;
     
        return $this;
    }

    public function getReleaseDate(): ?\DateTimeImmutable
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(?string $releaseDate): self
    {
        $date = null;
        if (!empty($releaseDate)) {
            $format = "d/m/Y";
            $rlArr = explode('/', $releaseDate);
            if (count($rlArr) == 3) {
                $d = $rlArr[0];
                $m = $rlArr[1];
                $y = $rlArr[2];

                if (checkdate($m, $d, $y)) {
                    $date = DateTimeImmutable::createFromFormat($format, $d . '/' . $m . '/' . $y);
                } else {
                    $date = NULL;
                }
            }

        }

        if (!$date) {
            $date = NULL;
        }

        $this->releaseDate = $date;

        return $this;
    }
}
