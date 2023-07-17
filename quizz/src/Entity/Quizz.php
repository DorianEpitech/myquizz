<?php

namespace App\Entity;

use App\Repository\QuizzRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: QuizzRepository::class)]
class Quizz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'quizzs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Categorie $id_categorie = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'id_quizz', targetEntity: Question::class, orphanRemoval: true)]
    private Collection $questions;

    #[ORM\OneToMany(mappedBy: 'id_quizz', targetEntity: ResultatQuizz::class, orphanRemoval: true)]
    private Collection $resultatQuizzs;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $this->resultatQuizzs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdCategorie(): ?Categorie
    {
        return $this->id_categorie;
    }

    public function setIdCategorie(?Categorie $id_categorie): self
    {
        $this->id_categorie = $id_categorie;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Question>
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }


    // public function getQuestions($offset = 0, $length = null)
    // {
    //     return array_slice($this->questions, $offset, $length);
    // }

    // public function getQuestions($max = 0)
    // {
    //     $criteria = \Doctrine\Common\Collections\Criteria::create()
    //     ->setMaxResults(10);
    //     return $this->questions->matching($criteria)[$max];
    // }

    public function addQuestion(Question $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
            $question->setIdQuizz($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): self
    {
        if ($this->questions->removeElement($question)) {
            // set the owning side to null (unless already changed)
            if ($question->getIdQuizz() === $this) {
                $question->setIdQuizz(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ResultatQuizz>
     */
    public function getResultatQuizzs(): Collection
    {
        return $this->resultatQuizzs;
    }

    public function addResultatQuizz(ResultatQuizz $resultatQuizz): self
    {
        if (!$this->resultatQuizzs->contains($resultatQuizz)) {
            $this->resultatQuizzs->add($resultatQuizz);
            $resultatQuizz->setIdQuizz($this);
        }

        return $this;
    }

    public function removeResultatQuizz(ResultatQuizz $resultatQuizz): self
    {
        if ($this->resultatQuizzs->removeElement($resultatQuizz)) {
            // set the owning side to null (unless already changed)
            if ($resultatQuizz->getIdQuizz() === $this) {
                $resultatQuizz->setIdQuizz(null);
            }
        }

        return $this;
    }
}
