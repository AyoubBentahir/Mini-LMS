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

$email = 'admin@lms.com';
$user = $userRepository->findOneBy(['email' => $email]);

if (!$user) {
    echo "Creating admin user $email...\n";
    $user = new User();
    $user->setEmail($email);
    $user->setRoles(['ROLE_ADMIN']); 
    $user->setNom('Admin');
    $user->setPrenom('System');
    $user->setIsActive(true);
    $user->setCreatedAt(new \DateTimeImmutable());
    $user->setPassword(password_hash('password', PASSWORD_BCRYPT));
    
    $entityManager->persist($user);
    $entityManager->flush();
    echo "Admin user created successfully.\n";
} else {
    echo "Admin user already exists. Updating password...\n";
    $user->setPassword(password_hash('password', PASSWORD_BCRYPT));
    $user->setRoles(array_unique(array_merge($user->getRoles(), ['ROLE_ADMIN'])));
    $entityManager->flush();
    echo "Admin user updated.\n";
}
