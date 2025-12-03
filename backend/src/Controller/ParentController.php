<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ParentRepository;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/parents')]
class ParentController extends AbstractController
{
    public function __construct(
        private ParentRepository $parentRepository,
        private StudentRepository $studentRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('', name: 'api_parents_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $parents = $this->parentRepository->findAll();
        
        return $this->json($parents, Response::HTTP_OK, [], [
            'groups' => ['parent:read']
        ]);
    }

    #[Route('/{id}', name: 'api_parents_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $parent = $this->parentRepository->find($id);
        
        if (!$parent) {
            return $this->json(['error' => 'Parent not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($parent, Response::HTTP_OK, [], [
            'groups' => ['parent:read']
        ]);
    }

    #[Route('/my-children', name: 'api_parents_my_children', methods: ['GET'])]
    public function myChildren(): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->json(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $parent = $this->parentRepository->findOneBy(['user' => $user]);
        
        if (!$parent) {
            return $this->json(['error' => 'Parent profile not found'], Response::HTTP_NOT_FOUND);
        }

        $children = $parent->getStudents();
        
        return $this->json([
            'children' => $children,
            'count' => count($children)
        ], Response::HTTP_OK, [], [
            'groups' => ['student:read']
        ]);
    }

    #[Route('/student/{studentId}', name: 'api_parents_by_student', methods: ['GET'])]
    public function byStudent(int $studentId): JsonResponse
    {
        $parents = $this->parentRepository->findByStudent($studentId);
        
        return $this->json($parents, Response::HTTP_OK, [], [
            'groups' => ['parent:read']
        ]);
    }

    #[Route('/search', name: 'api_parents_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');
        
        if (empty($query)) {
            return $this->json(['error' => 'Search query required'], Response::HTTP_BAD_REQUEST);
        }

        $parents = $this->parentRepository->search($query);
        
        return $this->json($parents, Response::HTTP_OK, [], [
            'groups' => ['parent:read']
        ]);
    }

    #[Route('/my-payments', name: 'api_parents_my_payments', methods: ['GET'])]
    public function myPayments(): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->json(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $parent = $this->parentRepository->findOneBy(['user' => $user]);
        
        if (!$parent) {
            return $this->json(['error' => 'Parent profile not found'], Response::HTTP_NOT_FOUND);
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

        return $this->json([
            'payments' => $allPayments,
            'summary' => [
                'total_pending' => $totalPending,
                'total_paid' => $totalPaid,
                'count' => count($allPayments)
            ]
        ], Response::HTTP_OK, [], [
            'groups' => ['payment:read']
        ]);
    }

    #[Route('/my-contracts', name: 'api_parents_my_contracts', methods: ['GET'])]
    public function myContracts(): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->json(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $parent = $this->parentRepository->findOneBy(['user' => $user]);
        
        if (!$parent) {
            return $this->json(['error' => 'Parent profile not found'], Response::HTTP_NOT_FOUND);
        }

        $contracts = $parent->getContracts();
        
        return $this->json([
            'contracts' => $contracts,
            'count' => count($contracts)
        ], Response::HTTP_OK, [], [
            'groups' => ['contract:read']
        ]);
    }
}
