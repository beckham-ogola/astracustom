<?php
/** AstraCampus - Student Model */

class Student
{
    public static function find(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT s.*, c.name AS class_name, u.full_name AS admitted_by_name
             FROM students s
             LEFT JOIN classes c ON c.id = s.class_id
             LEFT JOIN users u ON u.id = s.admitted_by
             WHERE s.id = :id AND s.deleted_at IS NULL'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByAdmissionNo(string $admissionNo): ?array
    {
        $stmt = db()->prepare(
            'SELECT s.*, c.name AS class_name FROM students s
             LEFT JOIN classes c ON c.id = s.class_id
             WHERE s.admission_no = :a AND s.deleted_at IS NULL'
        );
        $stmt->execute(['a' => $admissionNo]);
        return $stmt->fetch() ?: null;
    }

    public static function listByStatus(string $status = 'Active', ?int $classId = null, int $limit = 5, int $offset = 0): array
    {
        $sql = 'SELECT s.*, c.name AS class_name FROM students s
                LEFT JOIN classes c ON c.id = s.class_id
                WHERE s.status = :status AND s.deleted_at IS NULL';
        $params = ['status' => $status];
        if ($classId) {
            $sql .= ' AND s.class_id = :class_id';
            $params['class_id'] = $classId;
        }
        $sql .= ' ORDER BY s.full_name ASC LIMIT :limit OFFSET :offset';

        $stmt = db()->prepare($sql);
        foreach ($params as $k => $v) { $stmt->bindValue(':' . $k, $v); }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countByStatus(string $status = 'Active', ?int $classId = null): int
    {
        $sql = 'SELECT COUNT(*) FROM students WHERE status = :status AND deleted_at IS NULL';
        $params = ['status' => $status];
        if ($classId) {
            $sql .= ' AND class_id = :class_id';
            $params['class_id'] = $classId;
        }
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public static function allActiveAndGraduatesForPayments(?int $classId = null): array
    {
        $sql = "SELECT s.id, s.admission_no, s.full_name, s.class_id, s.status, c.name AS class_name,
                    s.guardian1_name, s.guardian1_phone,
                    COALESCE(SUM(b.final_amount),0) - COALESCE((SELECT SUM(p.amount_paid) FROM payments p WHERE p.student_id = s.id AND p.deleted_at IS NULL),0) AS balance
                FROM students s
                LEFT JOIN classes c ON c.id = s.class_id
                LEFT JOIN bills b ON b.student_id = s.id AND b.deleted_at IS NULL
                WHERE s.deleted_at IS NULL AND s.status IN ('Active','Graduated')";
        $params = [];
        if ($classId) {
            $sql .= ' AND s.class_id = :class_id';
            $params['class_id'] = $classId;
        }
        $sql .= ' GROUP BY s.id ORDER BY s.full_name ASC';
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function create(array $d): int
    {
        $admissionNo = next_admission_no();
        $age = calculate_age($d['dob']);
        $stmt = db()->prepare(
            'INSERT INTO students (
                admission_no, full_name, dob, age, gender, birth_cert_no, class_id, admission_date, term, photo_consent,
                guardian1_name, guardian1_relation, guardian1_id, guardian1_phone, guardian1_phone_alt, guardian1_address,
                guardian2_name, guardian2_relation, guardian2_id, guardian2_phone, guardian2_phone_alt, guardian2_address,
                medical_conditions, admission_form_path, admitted_by
            ) VALUES (
                :admission_no, :full_name, :dob, :age, :gender, :birth_cert_no, :class_id, :admission_date, :term, :photo_consent,
                :g1_name, :g1_rel, :g1_id, :g1_phone, :g1_phone_alt, :g1_address,
                :g2_name, :g2_rel, :g2_id, :g2_phone, :g2_phone_alt, :g2_address,
                :medical, :form_path, :admitted_by
            )'
        );
        $stmt->execute([
            'admission_no'   => $admissionNo,
            'full_name'      => $d['full_name'],
            'dob'            => $d['dob'],
            'age'            => $age,
            'gender'         => $d['gender'],
            'birth_cert_no'  => $d['birth_cert_no'],
            'class_id'       => $d['class_id'],
            'admission_date' => $d['admission_date'],
            'term'           => $d['term'],
            'photo_consent'  => !empty($d['photo_consent']) ? 1 : 0,
            'g1_name'        => $d['guardian1_name'],
            'g1_rel'         => $d['guardian1_relation'],
            'g1_id'          => $d['guardian1_id'],
            'g1_phone'       => $d['guardian1_phone'],
            'g1_phone_alt'   => $d['guardian1_phone_alt'] ?: null,
            'g1_address'     => $d['guardian1_address'] ?: null,
            'g2_name'        => $d['guardian2_name'] ?: null,
            'g2_rel'         => $d['guardian2_relation'] ?: null,
            'g2_id'          => $d['guardian2_id'] ?: null,
            'g2_phone'       => $d['guardian2_phone'] ?: null,
            'g2_phone_alt'   => $d['guardian2_phone_alt'] ?: null,
            'g2_address'     => $d['guardian2_address'] ?: null,
            'medical'        => $d['medical_conditions'] ?: null,
            'form_path'      => $d['admission_form_path'] ?? null,
            'admitted_by'    => $d['admitted_by'],
        ]);
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, array $d): void
    {
        $age = calculate_age($d['dob']);
        $sql = 'UPDATE students SET
                full_name = :full_name, dob = :dob, age = :age, gender = :gender, birth_cert_no = :birth_cert_no,
                class_id = :class_id, term = :term, photo_consent = :photo_consent,
                guardian1_name = :g1_name, guardian1_relation = :g1_rel, guardian1_id = :g1_id,
                guardian1_phone = :g1_phone, guardian1_phone_alt = :g1_phone_alt, guardian1_address = :g1_address,
                guardian2_name = :g2_name, guardian2_relation = :g2_rel, guardian2_id = :g2_id,
                guardian2_phone = :g2_phone, guardian2_phone_alt = :g2_phone_alt, guardian2_address = :g2_address,
                medical_conditions = :medical';
        $params = [
            'full_name' => $d['full_name'], 'dob' => $d['dob'], 'age' => $age, 'gender' => $d['gender'],
            'birth_cert_no' => $d['birth_cert_no'], 'class_id' => $d['class_id'], 'term' => $d['term'],
            'photo_consent' => !empty($d['photo_consent']) ? 1 : 0,
            'g1_name' => $d['guardian1_name'], 'g1_rel' => $d['guardian1_relation'], 'g1_id' => $d['guardian1_id'],
            'g1_phone' => $d['guardian1_phone'], 'g1_phone_alt' => $d['guardian1_phone_alt'] ?: null, 'g1_address' => $d['guardian1_address'] ?: null,
            'g2_name' => $d['guardian2_name'] ?: null, 'g2_rel' => $d['guardian2_relation'] ?: null, 'g2_id' => $d['guardian2_id'] ?: null,
            'g2_phone' => $d['guardian2_phone'] ?: null, 'g2_phone_alt' => $d['guardian2_phone_alt'] ?: null, 'g2_address' => $d['guardian2_address'] ?: null,
            'medical' => $d['medical_conditions'] ?: null,
            'id' => $id,
        ];
        if (!empty($d['admission_form_path'])) {
            $sql .= ', admission_form_path = :form_path';
            $params['form_path'] = $d['admission_form_path'];
        }
        $sql .= ' WHERE id = :id';
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
    }

    public static function setClass(int $id, int $classId): void
    {
        $stmt = db()->prepare('UPDATE students SET class_id = :c WHERE id = :id');
        $stmt->execute(['c' => $classId, 'id' => $id]);
    }

    public static function setStatus(int $id, string $status): void
    {
        $stmt = db()->prepare('UPDATE students SET status = :s WHERE id = :id');
        $stmt->execute(['s' => $status, 'id' => $id]);
    }

    public static function graduate(int $id, ?int $graduatedBy, string $reason = 'Completed highest class level'): void
    {
        $student = self::find($id);
        if (!$student) return;
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO graduates (student_id, student_data, from_class_id, graduated_by, reason)
                 VALUES (:sid, :data, :class_id, :by, :reason)'
            );
            $stmt->execute([
                'sid'      => $id,
                'data'     => json_encode($student),
                'class_id' => $student['class_id'],
                'by'       => $graduatedBy,
                'reason'   => $reason,
            ]);
            $pdo->prepare("UPDATE students SET status = 'Graduated' WHERE id = :id")->execute(['id' => $id]);
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function permanentDelete(int $id): void
    {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $pdo->prepare('DELETE FROM payments WHERE student_id = :id')->execute(['id' => $id]);
            $pdo->prepare('DELETE FROM bills WHERE student_id = :id')->execute(['id' => $id]);
            $pdo->prepare('DELETE FROM graduates WHERE student_id = :id')->execute(['id' => $id]);
            $pdo->prepare('DELETE FROM students WHERE id = :id')->execute(['id' => $id]);
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function balances(): array
    {
        return db()->query('SELECT * FROM v_student_balances')->fetchAll();
    }

    public static function balanceFor(int $studentId): float
    {
        $stmt = db()->prepare('SELECT balance FROM v_student_balances WHERE student_id = :id');
        $stmt->execute(['id' => $studentId]);
        $val = $stmt->fetchColumn();
        return $val !== false ? (float) $val : 0.0;
    }

    public static function graduatesList(): array
    {
        return db()->query(
            "SELECT g.*, s.admission_no, s.full_name,
                    c.name AS last_class_name, u.full_name AS graduated_by_name,
                    COALESCE((SELECT SUM(b.final_amount) FROM bills b WHERE b.student_id = s.id AND b.deleted_at IS NULL),0) AS total_fees,
                    COALESCE((SELECT SUM(p.amount_paid) FROM payments p WHERE p.student_id = s.id AND p.deleted_at IS NULL),0) AS total_paid
             FROM graduates g
             JOIN students s ON s.id = g.student_id
             LEFT JOIN classes c ON c.id = g.from_class_id
             LEFT JOIN users u ON u.id = g.graduated_by
             ORDER BY g.graduated_at DESC"
        )->fetchAll();
    }
}
