<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Traits\ApiResponseTrait;
use App\Entity\User;
use App\Repository\ParentRepository;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/api/parents')]
#[OA\Tag(name: 'Parents')]
class ParentController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private readonly ParentRepository $parentRepository,
        private readonly StudentRepository $studentRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'api_parents_index', methods: ['GET'])]
    #[OA\Get(path: '/api/parents', summary: 'List all parents')]
    public function index(): JsonResponse
    {
        $parents = $this->parentRepository->findAll();
        
        return $this->success($parents, 200, [], ['parent:read']);
    }

    #[Route('/{id}', name: 'api_parents_show', methods: ['GET'])]
    #[OA\Get(path: '/api/parents/{id}', summary: 'Get parent details')]
    public function show(int $id): JsonResponse
    {
        $parent = $this->parentRepository->find($id);
        
        if (!$parent) {
            return $this->notFound('Parent');
        }

        return $this->success($parent, 200, [], ['parent:read']);
    }

    #[Route('/my-children', name: 'api_parents_my_children', methods: ['GET'])]
    #[OA\Get(path: '/api/parents/my-children', summary: 'Get authenticated parent children')]
    public function myChildren(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->unauthorized();
        }

        $parent = $this->parentRepository->findOneBy(['user' => $user]);
        if (!$parent) {
            return $this->notFound('Parent profile');
        }

        $children = $parent->getStudents();
        
        return $this->success([
            'children' => $children,
            'count' => count($children)
        ], 200, [], ['student:read']);
    }

    #[Route('/student/{studentId}', name: 'api_parents_by_student', methods: ['GET'])]
    #[OA\Get(path: '/api/parents/student/{studentId}', summary: 'Get parents by student ID')]
    public function byStudent(int $studentId): JsonResponse
    {
        $parents = $this->parentRepository->findByStudent($studentId);
        
        return $this->success($parents, 200, [], ['parent:read']);
    }

    #[Route('/search', name: 'api_parents_search', methods: ['GET'])]
    #[OA\Get(path: '/api/parents/search', summary: 'Search parents')]
    public function search(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');
        
        if (empty($query)) {
            return $this->validationError(['q' => 'Search query required']);
        }

        $parents = $this->parentRepository->search($query);
        
        return $this->success($parents, 200, [], ['parent:read']);
    }

    #[Route('/my-payments', name: 'api_parents_my_payments', methods: ['GET'])]
    #[OA\Get(path: '/api/parents/my-payments', summary: 'Get authenticated parent payments')]
    public function myPayments(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->unauthorized();
        }

        $parent = $this->parentRepository->findOneBy(['user' => $user]);
        if (!$parent) {
            return $this->notFound('Parent profile');
        }

        $allPayments = [];
        $totalPending = 0;
        $totalPaid = 0;

        foreach ($parent->getStudents() as $student) {
            foreach ($student->getEnrollments() as $enrollment) {
                if ($enrollment->getStatus() === 'active') {
                    $payments = $enrollment->getPayments();
                    foreach ($payments as $payment) {
                        $allPayments[] = $payment;
                        if ($payment->getStatus() === 'pending') {
                            $totalPending += $payment->getAmount();
                        } else {
                            $totalPaid += $payment->getAmount();
                        }
                    }
                }
            }
        }

        return $this->success([
            'payments' => $allPayments,
            'summary' => [
                'total_pending' => $totalPending,
                'total_paid' => $totalPaid,
                'count' => count($allPayments)
            ]
        ], 200, [], ['payment:read']);
    }

    #[Route('/my-contracts', name: 'api_parents_my_contracts', methods: ['GET'])]
    #[OA\Get(path: '/api/parents/my-contracts', summary: 'Get authenticated parent contracts')]
    public function myContracts(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->unauthorized();
        }

        $parent = $this->parentRepository->findOneBy(['user' => $user]);
        if (!$parent) {
            return $this->notFound('Parent profile');
        }

        $contracts = $parent->getContracts();
        
        return $this->success([
            'contracts' => $contracts,
            'count' => count($contracts)
        ], 200, [], ['contract:read']);
    }
}
