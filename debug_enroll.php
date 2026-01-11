<?php
use App\Kernel;
use App\Entity\User;
use App\Entity\Course;
use App\Entity\Enrollment;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__.'/vendor/autoload.php';

(new Dotenv())->bootEnv(__DIR__.'/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

$container = $kernel->getContainer();
$entityManager = $container->get('doctrine')->getManager();
$userRepository = $entityManager->getRepository(User::class);
$courseRepository = $entityManager->getRepository(Course::class);
$enrollmentRepository = $entityManager->getRepository(Enrollment::class);

$studentEmail = 'student@lms.com';
$student = $userRepository->findOneBy(['email' => $studentEmail]);

if (!$student) {
    die("Student $studentEmail not found.\n");
}

$course = $courseRepository->findOneBy([]); // Get first course
if (!$course) {
    die("No course found.\n");
}

echo "Checking enrollment for Student: " . $student->getEmail() . " in Course: " . $course->getTitle() . "\n";

$enrollment = $enrollmentRepository->findOneBy(['user' => $student, 'course' => $course]);

if ($enrollment) {
    echo "Enrollment ALREADY EXISTS. ID: " . $enrollment->getId() . ", Enrolled At: " . $enrollment->getEnrolledAt()->format('Y-m-d H:i:s') . "\n";
} else {
    echo "Enrollment does NOT exist. Creating now...\n";
    $enrollment = new Enrollment();
    $enrollment->setUser($student);
    $enrollment->setCourse($course);
    // enrolledAt is set in constructor? Let's check Entity.
    
    $entityManager->persist($enrollment);
    $entityManager->flush();
    echo "Enrollment created successfully. ID: " . $enrollment->getId() . "\n";
}

// Double check directly via SQL to be sure
$conn = $entityManager->getConnection();
$sql = "SELECT * FROM enrollment WHERE user_id = :uid AND course_id = :cid";
$stmt = $conn->executeQuery($sql, ['uid' => $student->getId(), 'cid' => $course->getId()]);
$result = $stmt->fetchAssociative();

print_r($result);
