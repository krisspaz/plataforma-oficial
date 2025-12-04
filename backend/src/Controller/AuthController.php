<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

#[Route('/api')]
class AuthController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
        private RateLimiterFactory $registrationLimiter,
        private RateLimiterFactory $passwordResetLimiter
    ) {}

    // ===== Helpers =====
    private function respond(mixed $data, int $status = 200): JsonResponse
    {
        return $this->json($data, $status);
    }

    private function respondNotAuthenticated(): JsonResponse
    {
        return $this->respond(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
    }

    private function respondBadRequest(string $message): JsonResponse
    {
        return $this->respond(['error' => $message], Response::HTTP_BAD_REQUEST);
    }

    private function validateEntity(object $entity): ?JsonResponse
    {
        $errors = $this->validator->validate($entity);
        if (count($errors) === 0) {
            return null;
        }

        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[$error->getPropertyPath()] = $error->getMessage();
        }

        return $this->respond(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
    }

    // ===== REGISTER =====
    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        // Rate Limiting
        $limiter = $this->registrationLimiter->create($request->getClientIp());
        if (false === $limiter->consume(1)->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }

        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setEmail($data['email'] ?? '');
        $user->setFirstName($data['firstName'] ?? '');
        $user->setLastName($data['lastName'] ?? '');
        $user->setPhone($data['phone'] ?? null);
        $user->setRoles($data['roles'] ?? ['ROLE_USER']);

        if (isset($data['password'])) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
        }

        if ($validationResponse = $this->validateEntity($user)) {
            return $validationResponse;
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->respond([
            'message' => 'User registered successfully',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
            ]
        ], Response::HTTP_CREATED);
    }

    // ===== GET CURRENT USER =====
    #[Route('/me', name: 'api_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) return $this->respondNotAuthenticated();

        return $this->respond([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'phone' => $user->getPhone(),
            'roles' => $user->getRoles(),
            'avatar' => $user->getAvatar(),
            'isActive' => $user->isActive(),
        ]);
    }

    // ===== UPDATE PROFILE =====
    #[Route('/profile', name: 'api_update_profile', methods: ['PUT'])]
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) return $this->respondNotAuthenticated();

        $data = json_decode($request->getContent(), true);

        foreach (['firstName', 'lastName', 'phone', 'avatar'] as $field) {
            if (isset($data[$field])) {
                $setter = 'set' . ucfirst($field);
                $user->$setter($data[$field]);
            }
        }

        $this->entityManager->flush();

        return $this->respond([
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'phone' => $user->getPhone(),
                'avatar' => $user->getAvatar(),
            ]
        ]);
    }

    // ===== CHANGE PASSWORD =====
    #[Route('/change-password', name: 'api_change_password', methods: ['POST'])]
    public function changePassword(Request $request): JsonResponse
    {
        // Rate Limiting
        $limiter = $this->passwordResetLimiter->create($request->getClientIp());
        if (false === $limiter->consume(1)->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }

        $user = $this->getUser();
        if (!$user instanceof User) return $this->respondNotAuthenticated();

        $data = json_decode($request->getContent(), true);
        if (!isset($data['currentPassword'], $data['newPassword'])) {
            return $this->respondBadRequest('Current and new password required');
        }

        if (!$this->passwordHasher->isPasswordValid($user, $data['currentPassword'])) {
            return $this->respondBadRequest('Current password is incorrect');
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $data['newPassword']));
        $this->entityManager->flush();

        return $this->respond(['message' => 'Password changed successfully']);
    }
}
