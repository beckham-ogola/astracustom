<?php
/** AstraCampus - Class Controller */

class ClassController
{
    public static function index(): void
    {
        require_role(['developer', 'admin']);
        $classes = ClassModel::all();
        require __DIR__ . '/../views/system/classes.php';
    }

    public static function store(): void
    {
        require_role(['developer', 'admin']);
        verify_csrf();
        $name = clean_input($_POST['name'] ?? '');
        $description = clean_input($_POST['description'] ?? '');
        if (empty($name)) {
            flash('error', 'Class name is required.');
            redirect('/classes');
        }
        $id = ClassModel::create($name, $description ?: null, current_user()['id']);
        audit_log('create_class', 'classes', $id, null, ['name' => $name], 'Created new class');
        flash('success', 'Class created successfully.');
        redirect('/classes');
    }

    public static function update(array $params): void
    {
        require_role(['developer', 'admin']);
        verify_csrf();
        $id = (int) $params['id'];
        $old = ClassModel::find($id);
        $name = clean_input($_POST['name'] ?? '');
        $description = clean_input($_POST['description'] ?? '');
        $isActive = isset($_POST['is_active']);
        if (empty($name)) {
            flash('error', 'Class name is required.');
            redirect('/classes');
        }
        ClassModel::update($id, $name, $description ?: null, $isActive);
        audit_log('edit_class', 'classes', $id, $old, ['name' => $name, 'is_active' => $isActive], 'Updated class');
        flash('success', 'Class updated successfully.');
        redirect('/classes');
    }

    public static function destroy(array $params): void
    {
        require_role(['developer', 'admin']);
        verify_csrf();
        $id = (int) $params['id'];
        [$ok, $message] = ClassModel::delete($id);
        flash($ok ? 'success' : 'error', $message);
        if ($ok) audit_log('delete_class', 'classes', $id, null, null, 'Deleted class');
        redirect('/classes');
    }

    public static function moveUp(array $params): void
    {
        require_role(['developer', 'admin']);
        verify_csrf();
        $id = (int) $params['id'];
        ClassModel::moveUp($id);
        audit_log('reorder_class', 'classes', $id, null, null, 'Moved class up');
        flash('success', 'Class moved up.');
        redirect('/classes');
    }

    public static function moveDown(array $params): void
    {
        require_role(['developer', 'admin']);
        verify_csrf();
        $id = (int) $params['id'];
        ClassModel::moveDown($id);
        audit_log('reorder_class', 'classes', $id, null, null, 'Moved class down');
        flash('success', 'Class moved down.');
        redirect('/classes');
    }

    /** Whole-class promotion */
    public static function promoteClass(): void
    {
        require_role(['developer', 'admin']);
        verify_csrf();
        $classId = (int) ($_POST['class_id'] ?? 0);
        $class = ClassModel::find($classId);
        if (!$class) {
            flash('error', 'Class not found.');
            redirect('/classes');
        }

        $students = Student::listByStatus('Active', $classId, 10000, 0);
        $next = ClassModel::getNext($classId);
        $promoted = 0;
        $graduated = 0;

        foreach ($students as $s) {
            if ($next) {
                Student::setClass($s['id'], $next['id']);
                $promoted++;
            } else {
                Student::graduate($s['id'], current_user()['id'], 'Whole-class promotion at highest level');
                $graduated++;
            }
        }

        audit_log('promote_class', 'classes', $classId, null, ['promoted' => $promoted, 'graduated' => $graduated], 'Whole class promotion');
        flash('success', "Promotion complete: {$promoted} student(s) promoted, {$graduated} student(s) graduated.");
        redirect('/classes');
    }
}
