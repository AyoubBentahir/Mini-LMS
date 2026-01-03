<?php
use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__.'/vendor/autoload.php';

(new Dotenv())->bootEnv(__DIR__.'/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

$container = $kernel->getContainer();
$entityManager = $container->get('doctrine')->getManager();
$userRepository = $entityManager->getRepository(App\Entity\User::class);

$users = [
    'sara.idrissi@lms.com' => '$2y$13$MI8yLpUynUtSE665vn/jHuWedI2PbRb67ar/Lx8JJtGftq7CzQlO2',
    'teacherahmed@lms.com' => '$2y$13$LG2qVnCZ9hs8VExJd7y.fuY.sOaaidhM0ZphyoKburvoNzEavgTNe',
    'fatima.benali@lms.com' => '$2y$10$5PHgn2uaE7prrEZr532gwevSpqPQuOfj7HW0mL1M9S7jhBjcf5wYS',
    'youssef.alami@lms.com' => '$2y$10$Mni2/J/hRayvwBq.HXOtZ.FPxb3zjQ7HYI4u3W0aOVqp4UeWdJyyq'
];

foreach ($users as $email => $password) {
    $user = $userRepository->findOneBy(['email' => $email]);
    if ($user) {
        $user->setPassword($password);
        echo "Updating $email...\n";
    }
}

$entityManager->flush();
echo "Done.\n";
