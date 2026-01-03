<?php

namespace App\Command;

use App\Entity\Course;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-course',
    description: 'Create a new course linked to a teacher',
)]
class CreateCourseCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('title', InputArgument::REQUIRED, 'Course title')
            ->addArgument('description', InputArgument::REQUIRED, 'Course description')
            ->addArgument('code_unique', InputArgument::REQUIRED, 'Unique code')
            ->addArgument('teacher_email', InputArgument::REQUIRED, 'Email of the teacher')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $title = $input->getArgument('title');
        $description = $input->getArgument('description');
        $codeUnique = $input->getArgument('code_unique');
        $teacherEmail = $input->getArgument('teacher_email');

        $teacher = $this->userRepository->findOneBy(['email' => $teacherEmail]);

        if (!$teacher) {
            $io->error(sprintf('Teacher with email "%s" not found.', $teacherEmail));
            return Command::FAILURE;
        }

        $course = new Course();
        $course->setTitle($title);
        $course->setDescription($description);
        $course->setCodeUnique($codeUnique);
        $course->setTeacher($teacher);
        $course->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($course);
        $this->entityManager->flush();

        $io->success(sprintf('Course "%s" created successfully assigned to %s!', $title, $teacher->getNom()));

        return Command::SUCCESS;
    }
}
