<?php
use App\Kernel;
use App\Entity\User;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__.'/vendor/autoload.php';

(new Dotenv())->bootEnv(__DIR__.'/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

$container = $kernel->getContainer();
$entityManager = $container->get('doctrine')->getManager();
$userRepository = $entityManager->getRepository(User::class);

$users = [
    [
        'email' => 'sara.idrissi@lms.com',
        'roles' => ['ROLE_TEACHER', 'ROLE_ADMIN'],
        'password' => 'password',
        'nom' => 'Idrissi',
        'prenom' => 'Sara'
    ],
    [
        'email' => 'teacherahmed@lms.com',
        'roles' => ['ROLE_TEACHER'],
        'password' => 'password',
        'nom' => 'Ahmed',
        'prenom' => 'Teacher'
    ],
    [
        'email' => 'student@lms.com',
        'roles' => ['ROLE_STUDENT'],
        'password' => 'password',
        'nom' => 'Etudiant',
        'prenom' => 'Test'
    ]
];

foreach ($users as $userData) {
    $user = $userRepository->findOneBy(['email' => $userData['email']]);
    if (!$user) {
        echo "Creating user {$userData['email']}...\n";
        $user = new User();
        $user->setEmail($userData['email']);
        $user->setRoles($userData['roles']);
        $user->setNom($userData['nom']);
        $user->setPrenom($userData['prenom']);
        $user->setIsActive(true);
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setPassword(password_hash($userData['password'], PASSWORD_BCRYPT));
        $entityManager->persist($user);
    } else {
        echo "User {$userData['email']} exists. Updating password...\n";
        $user->setPassword(password_hash($userData['password'], PASSWORD_BCRYPT));
    }
}

$entityManager->flush();
echo "Users seeded successfully.\n";
