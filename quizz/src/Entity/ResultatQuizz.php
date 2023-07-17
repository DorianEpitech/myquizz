<?php

namespace App\Entity;

use App\Repository\ResultatQuizzRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResultatQuizzRepository::class)]
class ResultatQuizz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'resultatQuizzs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quizz $id_quizz = null;

    #[ORM\ManyToOne(inversedBy: 'resultatQuizzs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $id_user = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $result = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdQuizz(): ?Quizz
    {
        return $this->id_quizz;
    }

    public function setIdQuizz(?Quizz $id_quizz): self
    {
        $this->id_quizz = $id_quizz;

        return $this;
    }

    public function getIdUser(): ?User
    {
        return $this->id_user;
    }

    public function setIdUser(?User $id_user): self
    {
        $this->id_user = $id_user;

        return $this;
    }

    public function getResult(): ?int
    {
        return $this->result;
    }

    public function setResult(int $result): self
    {
        $this->result = $result;

        return $this;
    }
}
