<?php

namespace App\Entity;

use App\Repository\EnrollmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EnrollmentRepository::class)]
/**
 * ENTITÉ PIVOT (Le Lien Vital)
 * Cette classe représente l'inscription d'un élève à un cours.
 * Elle fait le pont entre l'entité User (l'étudiant) et l'entité Course (le cours).
 * Sans cette entité, un élève ne peut pas "posséder" de cours.
 */
class Enrollment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * LA PREUVE DE TEMPS
     * Quand s'est-il inscrit ? Essentiel pour l'historique et le tri.
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $enrolledAt = null;

    /**
     * L'ÉTUDIANT
     * Qui s'inscrit ? -> Relation ManyToOne vers User.
     */
    #[ORM\ManyToOne(inversedBy: 'enrollments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * LE COURS
     * À quoi s'inscrit-il ? -> Relation ManyToOne vers Course.
     */
    #[ORM\ManyToOne(inversedBy: 'enrollments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Course $course = null;

    public function __construct()
    {
        $this->enrolledAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEnrolledAt(): ?\DateTimeImmutable
    {
        return $this->enrolledAt;
    }

    public function setEnrolledAt(\DateTimeImmutable $enrolledAt): static
    {
        $this->enrolledAt = $enrolledAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): static
    {
        $this->course = $course;

        return $this;
    }
}
