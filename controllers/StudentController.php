<?php
/** AstraCampus - Student Controller */

class StudentController
{
    public static function index(): void
    {
        require_role(['developer', 'admin', 'teacher']);
        $classes = ClassModel::all(true);

        $filter = $_GET['class_filter'] ?? 'all_active';
        $status = 'Active';
        $classId = null;

        if ($filter === 'graduates') {
            $status = 'Graduated';
        } elseif ($filter !== 'all_active' && is_numeric($filter)) {
            $classId = (int) $filter;
        }

        // Teachers only see their assigned class
        if (is_teacher() && !is_admin()) {
            $classId = current_user()['class_assigned'];
            $status = 'Active';
        }

        [$page, $offset] = paginate_params(5);
        $students = Student::listByStatus($status, $classId, 5, $offset);
        $total = Student::countByStatus($status, $classId);
        $totalPages = (int) ceil($total / 5);

        // Fee structure per class, for the New Admission tab's live bill-selection checklist.
        $feeStructureByClass = [];
        if (can_manage()) {
            foreach ($classes as $c) {
                $feeStructureByClass[$c['id']] = array_map(
                    fn($f) => ['bill_type_id' => (int) $f['bill_type_id'], 'name' => $f['bill_type_name'], 'amount' => (float) $f['amount']],
                    FeeStructure::forClass((int) $c['id'])
                );
            }
        }

        require __DIR__ . '/../views/students/index.php';
    }

    public static function store(): void
    {
        require_role(['developer', 'admin']);
        verify_csrf();

        $required = ['full_name', 'dob', 'gender', 'birth_cert_no', 'class_id', 'guardian1_name', 'guardian1_relation', 'guardian1_id', 'guardian1_phone'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                flash('error', 'Please fill in all required fields.');
                redirect('/students?tab=admission');
            }
        }

        $formPath = null;
        try {
            if (!empty($_FILES['admission_form']['name'])) {
                $formPath = handle_upload(
                    $_FILES['admission_form'],
                    __DIR__ . '/../public/uploads/admission_forms',
                    ['pdf', 'jpg', 'jpeg', 'png'],
                    5 * 1024 * 1024
                );
            }
        } catch (RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect('/students?tab=admission');
        }

        $data = clean_input($_POST);
        $data['admission_date'] = $data['admission_date'] ?: date('Y-m-d');
        $data['term'] = get_setting('current_term', 'Term 1');
        $data['admitted_by'] = current_user()['id'];
        $data['admission_form_path'] = $formPath;

        $pdo = db();
        $pdo->beginTransaction();
        try {
            $studentId = Student::create($data);

            // Bill only what the admin explicitly selected on the admission form
            // (with live-computed discounts/sponsorship), not the full fee structure.
            $selectedBillTypes = $_POST['bill_type_id'] ?? [];
            $discounts = $_POST['discount'] ?? [];
            $sponsored = $_POST['sponsored'] ?? [];
            $billsCreated = 0;

            foreach ($selectedBillTypes as $billTypeId) {
                $billTypeId = (int) $billTypeId;
                if (Bill::existsForTerm($studentId, $billTypeId, $data['term'])) {
                    continue;
                }
                $fee = FeeStructure::get($billTypeId, (int) $data['class_id']);
                if (!$fee) {
                    continue; // no fee structure for this class/bill type combination — skip silently
                }
                $amount = (float) $fee['amount'];
                $discount = (float) ($discounts[$billTypeId] ?? 0);
                $isSponsored = isset($sponsored[$billTypeId]);
                Bill::create($studentId, $billTypeId, $amount, $discount, $isSponsored, $data['term'], current_user()['id'], current_user()['id']);
                $billsCreated++;
            }

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            flash('error', 'Failed to admit student: ' . $e->getMessage());
            redirect('/students?tab=admission');
        }

        $student = Student::find($studentId);
        audit_log('admission', 'students', $studentId, null, $data, 'New student admitted');
        flash('success', "Student admitted successfully! Admission Number: {$student['admission_no']}. {$billsCreated} bill(s) created.");
        redirect('/students');
    }

    public static function view(array $params): void
    {
        require_role(['developer', 'admin', 'teacher']);
        $id = (int) $params['id'];
        $student = Student::find($id);
        if (!$student) { flash('error', 'Student not found.'); redirect('/students'); }

        // Teachers may only view their own class students
        if (is_teacher() && !is_admin() && (int)$student['class_id'] !== (int) current_user()['class_assigned']) {
            require_role(['developer', 'admin']); // will 403
        }

        $bills = Bill::forStudent($id);
        require __DIR__ . '/../views/students/view.php';
    }

    public static function showEdit(array $params): void
    {
        require_role(['developer', 'admin']);
        $id = (int) $params['id'];
        $student = Student::find($id);
        if (!$student) { flash('error', 'Student not found.'); redirect('/students'); }
        $classes = ClassModel::all(true);
        require __DIR__ . '/../views/students/edit.php';
    }

    public static function update(array $params): void
    {
        require_role(['developer', 'admin']);
        verify_csrf();
        $id = (int) $params['id'];
        $old = Student::find($id);
        if (!$old) { flash('error', 'Student not found.'); redirect('/students'); }

        $formPath = null;
        try {
            if (!empty($_FILES['admission_form']['name'])) {
                $formPath = handle_upload(
                    $_FILES['admission_form'],
                    __DIR__ . '/../public/uploads/admission_forms',
                    ['pdf', 'jpg', 'jpeg', 'png'],
                    5 * 1024 * 1024
                );
            }
        } catch (RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect('/students/' . $id . '/edit');
        }

        $data = clean_input($_POST);
        if ($formPath) { $data['admission_form_path'] = $formPath; }

        Student::update($id, $data);
        audit_log('edit_student', 'students', $id, $old, $data, 'Student record updated');
        flash('success', 'Student updated successfully.');
        redirect('/students/' . $id);
    }

    public static function promote(array $params): void
    {
        require_role(['developer', 'admin']);
        verify_csrf();
        $id = (int) $params['id'];
        $student = Student::find($id);
        if (!$student) { flash('error', 'Student not found.'); redirect('/students'); }

        $next = ClassModel::getNext((int) $student['class_id']);
        if ($next) {
            Student::setClass($id, $next['id']);
            audit_log('promote', 'students', $id, ['class_id' => $student['class_id']], ['class_id' => $next['id']], 'Student promoted');
            flash('success', "Student promoted to {$next['name']}.");
        } else {
            Student::graduate($id, current_user()['id'], 'Promoted at highest class level');
            audit_log('graduate', 'students', $id, null, null, 'Student auto-graduated on promotion');
            flash('success', 'Student was at the highest level and has been graduated.');
        }
        redirect('/students/' . $id);
    }

    public static function demote(array $params): void
    {
        require_role(['developer', 'admin']);
        verify_csrf();
        $id = (int) $params['id'];
        $student = Student::find($id);
        if (!$student) { flash('error', 'Student not found.'); redirect('/students'); }

        $prev = ClassModel::getPrevious((int) $student['class_id']);
        if (!$prev) {
            flash('error', 'Student is already at the lowest class level and cannot be demoted.');
            redirect('/students/' . $id);
        }
        Student::setClass($id, $prev['id']);
        audit_log('demote', 'students', $id, ['class_id' => $student['class_id']], ['class_id' => $prev['id']], 'Student demoted');
        flash('success', "Student demoted to {$prev['name']}.");
        redirect('/students/' . $id);
    }

    public static function graduate(array $params): void
    {
        require_role(['developer', 'admin']);
        verify_csrf();
        $id = (int) $params['id'];
        $reason = clean_input($_POST['reason'] ?? '') ?: 'Manual graduation';
        Student::graduate($id, current_user()['id'], $reason);
        audit_log('graduate', 'students', $id, null, ['reason' => $reason], 'Student manually graduated');
        flash('success', 'Student graduated successfully.');
        redirect('/students/' . $id);
    }

    public static function destroy(array $params): void
    {
        require_role(['developer']);
        verify_csrf();
        $id = (int) $params['id'];

        $password = $_POST['password'] ?? '';
        $user = User::findById(current_user()['id']);
        if (!$user || !verify_password($password, $user['password'])) {
            flash('error', 'Incorrect password. Permanent deletion cancelled.');
            redirect('/students/' . $id);
        }

        $student = Student::find($id);
        Student::permanentDelete($id);
        audit_log('delete_student', 'students', $id, $student, null, 'PERMANENT DELETE of student and all related data');
        flash('success', 'Student and all related records permanently deleted.');
        redirect('/students');
    }
}
