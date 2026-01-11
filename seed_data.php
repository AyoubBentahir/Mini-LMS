use App\Kernel;
use App\Entity\User;
use App\Entity\Course;
use App\Entity\Module;
use App\Entity\Resource;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__.'/vendor/autoload.php';

echo "Starting...\n";

(new Dotenv())->bootEnv(__DIR__.'/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

$container = $kernel->getContainer();
$entityManager = $container->get('doctrine')->getManager();
$userRepository = $entityManager->getRepository(User::class);
$courseRepository = $entityManager->getRepository(Course::class);

// 1. Get Teacher (Sara)
$teacher = $userRepository->findOneBy(['email' => 'sara.idrissi@lms.com']);
if (!$teacher) {
    die("Error: Teacher Sara not found. Run seed_user.php first.\n");
}

// 2. Create Student
$studentEmail = 'student@lms.com';
$student = $userRepository->findOneBy(['email' => $studentEmail]);
if (!$student) {
    echo "Creating student...\n";
    $student = new User();
    $student->setEmail($studentEmail);
    $student->setRoles(['ROLE_STUDENT']);
    $student->setNom('Etudiant');
    $student->setPrenom('Demo');
    $student->setIsActive(true);
    $student->setCreatedAt(new \DateTimeImmutable());
    $student->setPassword(password_hash('password', PASSWORD_BCRYPT));
    $entityManager->persist($student);
} else {
    echo "Student already exists.\n";
}

// 3. Create Course
$courseTitle = 'Maîtriser Symfony 7';
$course = $courseRepository->findOneBy(['title' => $courseTitle]);

if (!$course) {
    echo "Creating course...\n";
    $course = new Course();
    $course->setTitle($courseTitle);
    $course->setDescription('Un cours complet pour apprendre Symfony 7 de zéro à héros. Architecture MVC, Doctrine, Twig et Sécurité.');
    // $course->setCategory('Développement Web'); // Does not exist
    $course->setCodeUnique('SYM-7-101');
    $course->setTeacher($teacher);
    $course->setCreatedAt(new \DateTimeImmutable());
    
    $entityManager->persist($course);
    
    // 4. Create Module
    $module = new Module();
    $module->setTitle('Module 1: Les Bases');
    // $module->setDescription('Installation et structure du projet'); // Property does not exist
    $module->setCourse($course);
    $module->setOrderIndex(1); // Assuming this field exists based on spec "order_index"
    // If setOrderIndex doesn't exist, I'll check Entity content later, but standard setters usually match column names.
    // Spec said 'order_index' INT.
    
    $entityManager->persist($module);
    
    // 5. Create Resource
    $resource = new Resource();
    $resource->setTitle('Guide d\'installation');
    // $resource->setDescription('PDF officiel de la documentation'); // Does not exist
    $resource->setType('link');
    $resource->setContent('https://symfony.com/doc/current/setup.html'); // url -> content
    $resource->setModule($module);
    
    $entityManager->persist($resource);
} else {
    echo "Course already exists.\n";
}

$entityManager->flush();
echo "Data seeding completed.\n";
