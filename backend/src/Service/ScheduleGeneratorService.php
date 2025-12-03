<?php

namespace App\Service;

use App\Entity\Schedule;
use App\Entity\Section;
use App\Entity\Subject;
use App\Entity\Teacher;
use App\Repository\ScheduleRepository;
use App\Repository\SubjectRepository;
use App\Repository\TeacherRepository;
use Doctrine\ORM\EntityManagerInterface;

class ScheduleGeneratorService
{
    private const DAYS_OF_WEEK = [1, 2, 3, 4, 5]; // Monday to Friday
    private const START_TIMES = ['08:00', '09:00', '10:00', '11:00', '13:00', '14:00', '15:00'];
    private const CLASS_DURATION = 60; // minutes

    public function __construct(
        private ScheduleRepository $scheduleRepository,
        private SubjectRepository $subjectRepository,
        private TeacherRepository $teacherRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Generate schedule for a section using constraint satisfaction
     */
    public function generateSchedule(Section $section, array $options = []): array
    {
        $academicYear = $options['academicYear'] ?? (int) date('Y');
        $maxHoursPerDay = $options['maxHoursPerDay'] ?? 6;
        
        // Get all subjects
        $subjects = $this->subjectRepository->findAll();
        
        // Get available teachers
        $teachers = $this->teacherRepository->findAll();
        
        $generatedSchedules = [];
        $assignments = $this->createSubjectTeacherAssignments($subjects, $teachers);
        
        $dayIndex = 0;
        $timeIndex = 0;
        
        foreach ($assignments as $assignment) {
            ['subject' => $subject, 'teacher' => $teacher] = $assignment;
            
            // Find available slot
            $slot = $this->findAvailableSlot(
                $teacher,
                $dayIndex,
                $timeIndex,
                $academicYear,
                $maxHoursPerDay
            );
            
            if (!$slot) {
                continue; // Skip if no slot available
            }
            
            $schedule = new Schedule();
            $schedule->setSection($section);
            $schedule->setSubject($subject);
            $schedule->setTeacher($teacher);
            $schedule->setDayOfWeek($slot['day']);
            $schedule->setStartTime(new \DateTime($slot['startTime']));
            $schedule->setEndTime(new \DateTime($slot['endTime']));
            $schedule->setAcademicYear($academicYear);
            
            if (isset($options['classroom'])) {
                $schedule->setClassroom($options['classroom']);
            }
            
            $this->entityManager->persist($schedule);
            $generatedSchedules[] = $schedule;
            
            // Move to next slot
            $timeIndex++;
            if ($timeIndex >= $maxHoursPerDay) {
                $timeIndex = 0;
                $dayIndex++;
                if ($dayIndex >= count(self::DAYS_OF_WEEK)) {
                    break; // Week is full
                }
            }
        }
        
        $this->entityManager->flush();
        
        return $generatedSchedules;
    }

    /**
     * Create subject-teacher assignments
     */
    private function createSubjectTeacherAssignments(array $subjects, array $teachers): array
    {
        $assignments = [];
        
        foreach ($subjects as $subject) {
            // Find best teacher for subject based on specialization
            $bestTeacher = $this->findBestTeacher($subject, $teachers);
            
            if ($bestTeacher) {
                $assignments[] = [
                    'subject' => $subject,
                    'teacher' => $bestTeacher
                ];
            }
        }
        
        return $assignments;
    }

    /**
     * Find best teacher for a subject
     */
    private function findBestTeacher(Subject $subject, array $teachers): ?Teacher
    {
        // Simple algorithm: match by specialization or return first available
        foreach ($teachers as $teacher) {
            if (stripos($teacher->getSpecialization(), $subject->getName()) !== false) {
                return $teacher;
            }
        }
        
        // Return first teacher if no match
        return $teachers[0] ?? null;
    }

    /**
     * Find available time slot
     */
    private function findAvailableSlot(
        Teacher $teacher,
        int $dayIndex,
        int $timeIndex,
        int $academicYear,
        int $maxHoursPerDay
    ): ?array {
        $attempts = 0;
        $maxAttempts = count(self::DAYS_OF_WEEK) * count(self::START_TIMES);
        
        while ($attempts < $maxAttempts) {
            $day = self::DAYS_OF_WEEK[$dayIndex % count(self::DAYS_OF_WEEK)];
            $startTimeStr = self::START_TIMES[$timeIndex % count(self::START_TIMES)];
            
            $startTime = new \DateTime($startTimeStr);
            $endTime = (clone $startTime)->modify('+' . self::CLASS_DURATION . ' minutes');
            
            // Check for conflicts
            $hasConflict = $this->scheduleRepository->hasConflict(
                $teacher->getId(),
                $day,
                $startTime,
                $endTime,
                $academicYear
            );
            
            if (!$hasConflict) {
                return [
                    'day' => $day,
                    'startTime' => $startTime->format('H:i:s'),
                    'endTime' => $endTime->format('H:i:s')
                ];
            }
            
            // Try next slot
            $timeIndex++;
            if ($timeIndex >= $maxHoursPerDay) {
                $timeIndex = 0;
                $dayIndex++;
            }
            
            $attempts++;
        }
        
        return null; // No available slot found
    }

    /**
     * Validate generated schedule
     */
    public function validateSchedule(array $schedules): array
    {
        $errors = [];
        
        foreach ($schedules as $schedule) {
            // Check for teacher conflicts
            $hasConflict = $this->scheduleRepository->hasConflict(
                $schedule->getTeacher()->getId(),
                $schedule->getDayOfWeek(),
                $schedule->getStartTime(),
                $schedule->getEndTime(),
                $schedule->getAcademicYear(),
                $schedule->getId()
            );
            
            if ($hasConflict) {
                $errors[] = [
                    'schedule' => $schedule->getId(),
                    'error' => 'Teacher conflict detected'
                ];
            }
        }
        
        return $errors;
    }

    /**
     * Optimize schedule distribution
     */
    public function optimizeSchedule(Section $section, int $academicYear): void
    {
        $schedules = $this->scheduleRepository->findBySection($section->getId(), $academicYear);
        
        // TODO: Implement optimization algorithm
        // - Balance subjects across the week
        // - Avoid consecutive heavy subjects
        // - Consider teacher preferences
        // - Optimize classroom usage
    }
}
