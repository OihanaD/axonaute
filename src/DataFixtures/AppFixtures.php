<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public const ADMIN_USER_REFERENCE = self::REFERENCE . 'admin';
    public const MAIL_HOST = '@axonaute.fr';
    public const REFERENCE = 'user_';
    public const SUPER_ADMIN_USER_REFERENCE = self::REFERENCE . 'super_admin';
    public const TOTAL_FIXTURES = 5;
    public const USER_PASSWORD = 'user_password';

    public const ADMIN_FIXTURES = [
        [
            'reference' => self::ADMIN_USER_REFERENCE,
        ],
        [
            'reference' => self::SUPER_ADMIN_USER_REFERENCE,
        ],
    ];

    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= self::TOTAL_FIXTURES; ++$i) {
            $reference = self::REFERENCE . $i;
            $user = new User();

            $user->setEmail($reference . static::MAIL_HOST)
                ->setPassword($this->hasher->hashPassword($user, self::USER_PASSWORD))
                ->setUsername($reference);

            $manager->persist($user);
            $this->addReference($reference, $user);
        }

        foreach (self::ADMIN_FIXTURES as $fixture) {
            $user = new User();

            $user->setEmail($fixture['reference'] . self::MAIL_HOST)
                ->setPassword($this->hasher->hashPassword($user, self::USER_PASSWORD))
                ->setUsername($fixture['reference']);

            $manager->persist($user);
            $this->addReference($fixture['reference'], $user);
        }

        $manager->flush();
    }
}
