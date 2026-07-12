<?php
/** AstraCampus - Route Definitions */

/** @var Router $router */

// ---------------- Parent Portal (public) ----------------
$router->get('/', fn() => redirect('/parent'));
$router->get('/parent', [ParentController::class, 'index']);
$router->post('/parent/lookup', [ParentController::class, 'lookup']);

// ---------------- Auth ----------------
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);

// ---------------- Dashboards ----------------
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/teacher/dashboard', [TeacherController::class, 'dashboard']);
$router->get('/teacher/class-report', [TeacherController::class, 'classReport']);

// ---------------- Students (list + New Admission live together as tabs) ----------------
$router->get('/students', [StudentController::class, 'index']);
$router->get('/students/admission', fn() => redirect('/students?tab=admission')); // legacy link support
$router->post('/students', [StudentController::class, 'store']);
$router->get('/students/{id}', [StudentController::class, 'view']);
$router->get('/students/{id}/edit', [StudentController::class, 'showEdit']);
$router->post('/students/{id}/edit', [StudentController::class, 'update']);
$router->post('/students/{id}/promote', [StudentController::class, 'promote']);
$router->post('/students/{id}/demote', [StudentController::class, 'demote']);
$router->post('/students/{id}/graduate', [StudentController::class, 'graduate']);
$router->post('/students/{id}/delete', [StudentController::class, 'destroy']);

// ---------------- Billing hub (Bill Types + Fee Structure + Bill a Student + Batch Billing) ----------------
$router->get('/billing', [BillController::class, 'hub']);
$router->get('/bill-types', fn() => redirect('/billing?tab=types'));           // legacy link support
$router->get('/fee-structure', fn() => redirect('/billing?tab=structure'));    // legacy link support
$router->get('/bills/student', fn() => redirect('/billing?tab=student'));      // legacy link support
$router->get('/bills/batch', fn() => redirect('/billing?tab=batch'));          // legacy link support

$router->post('/bill-types', [BillController::class, 'storeBillType']);
$router->post('/bill-types/{id}/update', [BillController::class, 'updateBillType']);
$router->post('/bill-types/{id}/delete', [BillController::class, 'destroyBillType']);
$router->get('/fee-structure/{id}/edit', [BillController::class, 'editFeeStructureForBillType']);
$router->post('/fee-structure/{id}/update', [BillController::class, 'updateFeeStructure']);
$router->post('/bills/student', [BillController::class, 'billStudentStore']);
$router->post('/bills/batch', [BillController::class, 'batchBillStore']);
$router->post('/bills/{id}/update', [BillController::class, 'updateBill']);
$router->post('/bills/{id}/delete', [BillController::class, 'destroyBill']);

// ---------------- Payments hub (Collect Payment + Recent Receipts) ----------------
$router->get('/payments', [PaymentController::class, 'index']);
$router->get('/payments/{id}/pay', [PaymentController::class, 'payForm']);
$router->post('/payments', [PaymentController::class, 'store']);
$router->post('/payments/mpesa/stk-push', [PaymentController::class, 'mpesaStkPush']);
$router->get('/payments/mpesa/status/{checkout_id}', [PaymentController::class, 'mpesaStatus']);
$router->post('/mpesa/callback', [PaymentController::class, 'mpesaCallback']); // public — called by Safaricom, no login/CSRF
$router->get('/receipts/{id}', [ReceiptController::class, 'show']);
$router->post('/receipts/{id}/reprint', [ReceiptController::class, 'reprint']);
$router->post('/receipts/{id}/sms', [ReceiptController::class, 'sendSms']);

// ---------------- Reports hub (Daily Collection + Student Balances + Class Collection + Graduates) ----------------
$router->get('/reports', [ReportController::class, 'hub']);
$router->get('/reports/daily-collection', fn() => redirect('/reports?tab=daily-collection' . (isset($_GET['date']) ? '&date=' . urlencode($_GET['date']) : '')));       // legacy link support
$router->get('/reports/student-balances', fn() => redirect('/reports?tab=student-balances' . (isset($_GET['class_filter']) ? '&class_filter=' . urlencode($_GET['class_filter']) : ''))); // legacy link support
$router->get('/reports/class-collection', fn() => redirect('/reports?tab=class-collection'));  // legacy link support
$router->get('/reports/graduates', fn() => redirect('/reports?tab=graduates'));                // legacy link support

// ---------------- Administration hub (Classes + Users + Settings + Templates + Audit Logs) ----------------
$router->get('/administration', [SystemController::class, 'administration']);
$router->get('/more', fn() => redirect('/administration')); // legacy link support

$router->get('/classes', [ClassController::class, 'index']);
$router->post('/classes', [ClassController::class, 'store']);
$router->post('/classes/{id}/update', [ClassController::class, 'update']);
$router->post('/classes/{id}/delete', [ClassController::class, 'destroy']);
$router->post('/classes/{id}/move-up', [ClassController::class, 'moveUp']);
$router->post('/classes/{id}/move-down', [ClassController::class, 'moveDown']);
$router->post('/classes/promote', [ClassController::class, 'promoteClass']);

$router->get('/settings', [SystemController::class, 'settingsIndex']);
$router->post('/settings', [SystemController::class, 'updateSettings']);
$router->get('/audit-logs', [SystemController::class, 'auditLogs']);
$router->get('/templates', [SystemController::class, 'templatesIndex']);
$router->post('/templates', [SystemController::class, 'storeTemplate']);
$router->post('/templates/{id}/delete', [SystemController::class, 'destroyTemplate']);

$router->get('/users', [AuthController::class, 'users']);
$router->post('/users', [AuthController::class, 'storeUser']);
$router->post('/users/{id}/reset-password', [AuthController::class, 'resetPassword']);
$router->post('/users/{id}/delete', [AuthController::class, 'deleteUser']);
$router->get('/account', [AuthController::class, 'account']);
$router->post('/account/change-password', [AuthController::class, 'changePassword']);
