<?php

namespace Jeffrey\Educore\Controllers\Core;

use Jeffrey\Educore\Utils\Utils;

class DashboardController
{
    public function showDashboard()
    {
        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
            header("Location: /login");
            exit;
        }

        $userRoles = $_SESSION['user_roles'] ?? [];

        // Determine the view name based on the user's role
        $viewName = 'general_dashboard'; // Default view
        if (in_array('master_admin', $userRoles)) {
            $viewName = 'master_admin_dashboard';
        } elseif (in_array('school_admin', $userRoles)) {
            $viewName = 'school_admin_dashboard';
        } elseif (in_array('principal', $userRoles)) {
            $viewName = 'principal_dashboard';
        } elseif (in_array('finance_officer', $userRoles)) {
            $viewName = 'finance_officer_dashboard';
        } elseif (in_array('teacher', $userRoles)) {
            $viewName = 'teacher_dashboard';
        } elseif (in_array('parent_user', $userRoles)) {
            $viewName = 'parent_dashboard';
        } elseif (in_array('student_user', $userRoles)) {
            $viewName = 'student_dashboard';
        }

        // Construct the full view path
        $viewPath = APP_ROOT . "/resources/views/dashboards/{$viewName}.html";

        // Call the reusable loadView method from the Utils class
        Utils::loadView($viewPath);
    }
}