<?php

namespace App\Entity;

use App\Repository\BookRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;

#[ORM\Entity(repositoryClass: BookRelationalRepository::class)]
#[ORM\Table(name: 'book_relational', indexes: ['name'])]
class BookRelational
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $bookId;

    #[ORM\Column(type: 'integer', unique: true)]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $series;

    #[ORM\Column(type: 'string', nullable: true)]
    private $number;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $type;

    #[ManyToOne(targetEntity: "Publisher")]
    private $publisher;

    #[ManyToOne(targetEntity: "Author")]
    private $author;

    #[ORM\Column(type: 'integer', nullable: true, options: ["unsigned" => true, "default" => 0])]
    private $price;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private $releaseDate;

    public function getBookId(): ?int
    {
        return $this->bookId;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = (int)$id;

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

    public function getPublisher(): ?Publisher
    {
        return $this->publisher;
    }

    public function setPublisher(Publisher $publisher): void
    {
        $this->publisher = $publisher;
    }

    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    public function setAuthor(Author $author): void
    {
        $this->author = $author;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?string $price): self
    {
        if (empty($price)) {
            $this->price = 0;
        } else {
            $priceArr = explode(',', $price);
            $first = !empty($priceArr[0]) ? $priceArr[0] : "0";
            $sec = !empty($priceArr[1]) ? substr($priceArr[1], 0, 2) : "00";
            $secNum = is_numeric($sec) ? $sec : "00";
            $this->price = (int)($first.$secNum);
        }

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
