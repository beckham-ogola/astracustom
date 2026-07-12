<?php
/** AstraCampus - Bill Controller: unified Billing hub + bill types, fee structure, billing actions */

class BillController
{
    /**
     * Unified Billing hub: Bill Types, Fee Structure, Bill a Student, Batch Billing.
     * Which tabs render is decided by role in the view (can_manage() vs accountant).
     */
    public static function hub(): void
    {
        require_role(['developer', 'admin', 'accountant']);

        $canManage = can_manage();
        $billTypes = $canManage ? BillType::all() : [];
        $feeStructure = $canManage ? FeeStructure::all() : [];
        $classes = ClassModel::all(true);
        $activeBillTypes = BillType::all(true);
        $currentTerm = get_setting('current_term');

        $student = null;
        $feeItems = [];
        $admissionNo = clean_input($_GET['admission_no'] ?? '');
        if (!empty($admissionNo)) {
            $student = Student::findByAdmissionNo(format_admission_search($admissionNo));
            if ($student) {
                $feeItems = FeeStructure::forClass((int) $student['class_id']);
            } else {
                flash('error', 'Student not found.');
            }
        }

        require __DIR__ . '/../views/bills/hub.php';
    }

    // ---------------- Bill Types ----------------

    public static function storeBillType(): void
    {
        require_role(['developer', 'admin']);
        verify_csrf();
        $name = clean_input($_POST['name'] ?? '');
        $description = clean_input($_POST['description'] ?? '');
        if (empty($name)) { flash('error', 'Bill type name is required.'); redirect('/billing?tab=types'); }
        $id = BillType::create($name, $description ?: null, current_user()['id']);
        audit_log('create_bill_type', 'bill_types', $id, null, ['name' => $name], 'Created bill type');
        flash('success', 'Bill type created successfully.');
        redirect('/billing?tab=types');
    }

    public static function updateBillType(array $params): void
    {
        require_role(['developer', 'admin']);
        verify_csrf();
        $id = (int) $params['id'];
        $name = clean_input($_POST['name'] ?? '');
        $description = clean_input($_POST['description'] ?? '');
        $isActive = isset($_POST['is_active']);
        if (empty($name)) { flash('error', 'Bill type name is required.'); redirect('/billing?tab=types'); }
        BillType::update($id, $name, $description ?: null, $isActive);
        audit_log('edit_bill_type', 'bill_types', $id, null, ['name' => $name], 'Updated bill type');
        flash('success', 'Bill type updated successfully.');
        redirect('/billing?tab=types');
    }

    public static function destroyBillType(array $params): void
    {
        require_role(['developer', 'admin']);
        verify_csrf();
        $id = (int) $params['id'];
        [$ok, $message] = BillType::delete($id);
        flash($ok ? 'success' : 'error', $message);
        if ($ok) audit_log('delete_bill_type', 'bill_types', $id, null, null, 'Deleted bill type');
        redirect('/billing?tab=types');
    }

    // ---------------- Fee Structure ----------------

    public static function editFeeStructureForBillType(array $params): void
    {
        require_role(['developer', 'admin']);
        $billTypeId = (int) $params['id'];
        $billType = BillType::find($billTypeId);
        if (!$billType) { flash('error', 'Bill type not found.'); redirect('/billing?tab=structure'); }
        $rows = FeeStructure::forBillType($billTypeId);
        require __DIR__ . '/../views/bills/fee_structure_edit.php';
    }

    public static function updateFeeStructure(array $params): void
    {
        require_role(['developer', 'admin']);
        verify_csrf();
        $billTypeId = (int) $params['id'];
        $amounts = $_POST['amount'] ?? [];
        foreach ($amounts as $classId => $amount) {
            $amount = (float) $amount;
            FeeStructure::upsert($billTypeId, (int) $classId, $amount, current_user()['id']);
        }
        audit_log('update_fee_structure', 'fee_structure', $billTypeId, null, ['amounts' => $amounts], 'Bulk updated fee structure');
        flash('success', 'Fee structure updated successfully.');
        redirect('/billing?tab=structure');
    }

    // ---------------- Single Student Billing ----------------

    public static function billStudentStore(): void
    {
        require_role(['developer', 'admin', 'accountant']);
        verify_csrf();

        $studentId = (int) ($_POST['student_id'] ?? 0);
        $student = Student::find($studentId);
        if (!$student) { flash('error', 'Student not found.'); redirect('/billing?tab=student'); }

        $term = get_setting('current_term');
        $selectedBillTypes = $_POST['bill_type_id'] ?? [];
        $discounts = $_POST['discount'] ?? [];
        $sponsored = $_POST['sponsored'] ?? [];

        $created = 0;
        $skipped = 0;

        foreach ($selectedBillTypes as $billTypeId) {
            $billTypeId = (int) $billTypeId;
            if (Bill::existsForTerm($studentId, $billTypeId, $term)) {
                $skipped++;
                continue;
            }
            $fee = FeeStructure::get($billTypeId, (int) $student['class_id']);
            $amount = $fee ? (float) $fee['amount'] : 0;
            $discount = (float) ($discounts[$billTypeId] ?? 0);
            $isSponsored = isset($sponsored[$billTypeId]);
            Bill::create($studentId, $billTypeId, $amount, $discount, $isSponsored, $term, current_user()['id'], current_user()['id']);
            $created++;
        }

        audit_log('bill_student', 'bills', $studentId, null, ['created' => $created, 'skipped' => $skipped], 'Billed individual student');
        flash('success', "{$created} bill(s) created. {$skipped} duplicate(s) skipped.");
        redirect('/students/' . $studentId);
    }

    // ---------------- Batch Billing ----------------

    public static function batchBillStore(): void
    {
        require_role(['developer', 'admin', 'accountant']);
        verify_csrf();

        $classId = (int) ($_POST['class_id'] ?? 0);
        $billTypeIds = $_POST['bill_type_id'] ?? [];
        $class = ClassModel::find($classId);
        if (!$class || empty($billTypeIds)) {
            flash('error', 'Please select a class and at least one bill type.');
            redirect('/billing?tab=batch');
        }

        $term = get_setting('current_term');
        $students = Student::listByStatus('Active', $classId, 10000, 0);

        $studentsProcessed = 0;
        $billsCreated = 0;
        $duplicatesSkipped = 0;

        foreach ($students as $s) {
            $studentsProcessed++;
            foreach ($billTypeIds as $billTypeId) {
                $billTypeId = (int) $billTypeId;
                if (Bill::existsForTerm($s['id'], $billTypeId, $term)) {
                    $duplicatesSkipped++;
                    continue;
                }
                $fee = FeeStructure::get($billTypeId, $classId);
                $amount = $fee ? (float) $fee['amount'] : 0;
                Bill::create($s['id'], $billTypeId, $amount, 0, false, $term, current_user()['id']);
                $billsCreated++;
            }
        }

        audit_log('batch_bill', 'bills', $classId, null,
            ['students' => $studentsProcessed, 'bills' => $billsCreated, 'skipped' => $duplicatesSkipped],
            'Batch billing run for class');

        flash('success', "Batch billing complete: {$studentsProcessed} students processed, {$billsCreated} bills created, {$duplicatesSkipped} duplicates skipped.");
        redirect('/billing?tab=batch');
    }

    // ---------------- Bill Management (from Student view page) ----------------

    public static function updateBill(array $params): void
    {
        require_role(['developer', 'admin', 'accountant']);
        verify_csrf();
        $id = (int) $params['id'];
        $bill = Bill::find($id);
        if (!$bill) { flash('error', 'Bill not found.'); redirect('/students'); }

        $amount = (float) ($_POST['amount'] ?? 0);
        $discount = (float) ($_POST['discount'] ?? 0);
        $sponsored = isset($_POST['sponsored']);

        Bill::update($id, $amount, $discount, $sponsored);
        audit_log('edit_bill', 'bills', $id, $bill, ['amount' => $amount, 'discount' => $discount, 'sponsored' => $sponsored], 'Bill updated');
        flash('success', 'Bill updated successfully.');
        redirect('/students/' . $bill['student_id']);
    }

    public static function destroyBill(array $params): void
    {
        require_role(['developer', 'admin']);
        verify_csrf();
        $id = (int) $params['id'];
        $bill = Bill::find($id);
        if (!$bill) { flash('error', 'Bill not found.'); redirect('/students'); }
        [$ok, $message] = Bill::delete($id);
        flash($ok ? 'success' : 'error', $message);
        if ($ok) audit_log('delete_bill', 'bills', $id, $bill, null, 'Bill deleted');
        redirect('/students/' . $bill['student_id']);
    }
}
