<?php
/** AstraCampus - Teacher Controller */

class TeacherController
{
    public static function dashboard(): void
    {
        require_role(['teacher', 'developer', 'admin']);
        $user = current_user();
        $classId = $user['class_assigned'];
        $class = $classId ? ClassModel::find($classId) : null;
        $students = $classId ? Student::listByStatus('Active', $classId, 1000, 0) : [];
        require __DIR__ . '/../views/teacher/dashboard.php';
    }

    public static function classReport(): void
    {
        require_role(['teacher', 'developer', 'admin']);
        $user = current_user();
        $classId = $user['class_assigned'];
        if (!$classId) {
            flash('error', 'You are not assigned to a class.');
            redirect('/teacher/dashboard');
        }
        $balances = array_filter(Student::balances(), fn($b) => (int)$b['class_id'] === (int)$classId);
        $class = ClassModel::find($classId);
        require __DIR__ . '/../views/teacher/class_report.php';
    }
}
