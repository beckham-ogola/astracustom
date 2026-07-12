<?php
/** AstraCampus - System Administration Controller */

class SystemController
{
    /**
     * The Administration hub — setup/config screens (Classes, Users,
     * Settings, PDF Templates, Audit Logs). Reports now lives in its own
     * promoted top-level section, and Account is reached from the sidebar/
     * avatar, so this hub is developer & admin only.
     */
    public static function administration(): void
    {
        require_role(['developer', 'admin']);
        $user = current_user();
        require __DIR__ . '/../views/system/administration.php';
    }

    public static function settingsIndex(): void
    {
        require_role(['developer', 'admin']);
        $settings = [
            'school_name'    => get_setting('school_name'),
            'school_phone'   => get_setting('school_phone'),
            'school_email'   => get_setting('school_email'),
            'school_address' => get_setting('school_address'),
            'school_motto'   => get_setting('school_motto'),
            'current_term'   => get_setting('current_term'),
            'current_year'   => get_setting('current_year'),
        ];
        $mpesa = [
            'mpesa_environment'      => get_setting('mpesa_environment', 'sandbox'),
            'mpesa_till_number'      => get_setting('mpesa_till_number'),
            'mpesa_passkey'          => get_setting('mpesa_passkey'),
            'mpesa_consumer_key'     => get_setting('mpesa_consumer_key'),
            'mpesa_consumer_secret'  => get_setting('mpesa_consumer_secret'),
            'mpesa_callback_url'     => get_setting('mpesa_callback_url'),
            'mpesa_transaction_type' => get_setting('mpesa_transaction_type', 'CustomerBuyGoodsOnline'),
        ];
        $suggestedCallbackUrl = full_url('mpesa/callback');
        $mpesaConfigured = mpesa_is_configured();
        require __DIR__ . '/../views/system/settings.php';
    }

    public static function updateSettings(): void
    {
        require_role(['developer', 'admin']);
        verify_csrf();

        $fields = ['school_name', 'school_phone', 'school_email', 'school_address', 'school_motto', 'current_term', 'current_year'];
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                set_setting($field, clean_input($_POST[$field]), current_user()['id']);
            }
        }

        $mpesaFields = ['mpesa_environment', 'mpesa_till_number', 'mpesa_passkey', 'mpesa_consumer_key', 'mpesa_consumer_secret', 'mpesa_callback_url', 'mpesa_transaction_type'];
        $mpesaUpdated = false;
        foreach ($mpesaFields as $field) {
            if (isset($_POST[$field])) {
                set_setting($field, clean_input($_POST[$field]), current_user()['id']);
                $mpesaUpdated = true;
            }
        }

        audit_log('update_settings', 'settings', null, null,
            ['general' => true, 'mpesa' => $mpesaUpdated], 'Settings updated');
        flash('success', 'Settings updated successfully.');
        redirect('/settings');
    }

    // ---------------- Audit Logs ----------------

    public static function auditLogs(): void
    {
        require_role(['developer', 'admin']);
        [$page, $offset] = paginate_params(100);
        $stmt = db()->prepare(
            'SELECT a.*, u.full_name AS user_name FROM audit_logs a
             LEFT JOIN users u ON u.id = a.user_id
             ORDER BY a.created_at DESC LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':limit', 100, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $logs = $stmt->fetchAll();
        $total = (int) db()->query('SELECT COUNT(*) FROM audit_logs')->fetchColumn();
        $totalPages = (int) ceil($total / 100);
        require __DIR__ . '/../views/system/audit_logs.php';
    }

    // ---------------- PDF Templates ----------------

    public static function templatesIndex(): void
    {
        require_role(['developer', 'admin']);
        $templates = db()->query(
            'SELECT t.*, u.full_name AS uploaded_by_name FROM templates t
             LEFT JOIN users u ON u.id = t.uploaded_by ORDER BY t.uploaded_at DESC'
        )->fetchAll();
        require __DIR__ . '/../views/system/templates.php';
    }

    public static function storeTemplate(): void
    {
        require_role(['developer', 'admin']);
        verify_csrf();

        $name = clean_input($_POST['name'] ?? '');
        $description = clean_input($_POST['description'] ?? '');

        try {
            $path = handle_upload($_FILES['file'] ?? [], __DIR__ . '/../public/uploads/templates', ['pdf'], 10 * 1024 * 1024);
        } catch (RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect('/templates');
        }

        if (!$path) {
            flash('error', 'Please select a PDF file to upload.');
            redirect('/templates');
        }

        $stmt = db()->prepare(
            'INSERT INTO templates (name, description, file_path, uploaded_by) VALUES (:n, :d, :p, :u)'
        );
        $stmt->execute(['n' => $name ?: $path, 'd' => $description ?: null, 'p' => $path, 'u' => current_user()['id']]);
        $id = (int) db()->lastInsertId();

        audit_log('upload_template', 'templates', $id, null, ['name' => $name], 'PDF template uploaded');
        flash('success', 'Template uploaded successfully.');
        redirect('/templates');
    }

    public static function destroyTemplate(array $params): void
    {
        require_role(['developer', 'admin']);
        verify_csrf();
        $id = (int) $params['id'];
        $stmt = db()->prepare('SELECT * FROM templates WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $template = $stmt->fetch();
        if ($template) {
            $filePath = __DIR__ . '/../public/uploads/templates/' . $template['file_path'];
            if (is_file($filePath)) { @unlink($filePath); }
            db()->prepare('DELETE FROM templates WHERE id = :id')->execute(['id' => $id]);
            audit_log('delete_template', 'templates', $id, $template, null, 'Template deleted');
            flash('success', 'Template deleted successfully.');
        }
        redirect('/templates');
    }
}
