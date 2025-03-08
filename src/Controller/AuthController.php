<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

final class AuthController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private JWTTokenManagerInterface $jwtManager;
    private UserRepository $userRepository;

    public function __construct(EntityManagerInterface $entityManager, JWTTokenManagerInterface $jwtManager, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->jwtManager = $jwtManager;
        $this->userRepository = $userRepository;

    }

    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['fullName'], $data['email'], $data['username'], $data['password'])) {
            return new JsonResponse(['message' => 'All data must be entered.'], 400);
        }

        $existingUser = $this->userRepository->findOneBy(['username' => $data['username']]) ?? $this->userRepository->findOneBy(['email' => $data['email']]);

        if ($existingUser) {
            return new JsonResponse(['message' => 'A user with that username or email address already exists.'], 409);
        }


        $user = new User();
        $user->setFullName($data['fullName']);
        $user->setEmail($data['email']);
        $user->setUsername($data['username']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Successful registration'], 201);
    }

    #[Route('/login', name: 'app_login', methods: ['POST'])]
    public function login(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
    $data = json_decode($request->getContent(), true);

    if (!isset($data['username'], $data['password'])) {
        return new JsonResponse(['message' => 'You must enter all login details.'], 400);
    }

    $user = $userRepository->findOneBy(['username' => $data['username']]);

    if (!$user || !$passwordHasher->isPasswordValid($user, $data['password'])) {
        return new JsonResponse(['message' => 'Invalid username or password'], 401);
    }

    $token = $this->jwtManager->create($user);

        return new JsonResponse(['token' => $token, 'message' => 'Successful login'], 200);
    }

}
