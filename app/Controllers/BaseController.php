<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Controllers\AuthController;

class BaseController extends Controller
{
    protected $session;
    protected $userData;

    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->userData = $this->getUserData();
    }

    /**
     * Get current user data
     */
    protected function getUserData()
    {
        if ($this->session->get('is_logged_in')) {
            return [
                'id' => $this->session->get('user_id'),
                'username' => $this->session->get('username'),
                'full_name' => $this->session->get('full_name'),
                'role' => $this->session->get('role'),
            ];
        }
        return null;
    }

    /**
     * Check if user is authenticated
     */
    protected function requireAuth()
    {
        $session = \Config\Services::session();
        if (!$session->get('is_logged_in')) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['error' => 'Authentication required'])->setStatusCode(401);
            }
            return redirect()->to('/auth');
        }
        return true;
    }

    /**
     * Check if user has admin role
     */
    protected function requireAdmin()
    {
        $this->requireAuth();
        
        $session = \Config\Services::session();
        if ($session->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Access denied. Admin privileges required.');
        }
    }

    /**
     * Check if user has cashier role
     */
    protected function requireCashier()
    {
        $this->requireAuth();
        
        $session = \Config\Services::session();
        if ($session->get('role') !== 'cashier') {
            return redirect()->to('/dashboard')->with('error', 'Access denied. Cashier privileges required.');
        }
    }

    /**
     * Get common view data
     */
    protected function getCommonData()
    {
        return [
            'user' => $this->userData,
            'is_admin' => $this->session->get('role') === 'admin',
            'is_cashier' => $this->session->get('role') === 'cashier',
            'current_url' => current_url(),
        ];
    }

    /**
     * Render view with common data
     */
    protected function renderView($view, $data = [])
    {
        $commonData = $this->getCommonData();
        $data = array_merge($commonData, $data);
        
        return view($view, $data);
    }

    /**
     * Get flash messages
     */
    protected function getFlashMessages()
    {
        $messages = [];
        
        if ($this->session->getFlashdata('success')) {
            $messages['success'] = $this->session->getFlashdata('success');
        }
        
        if ($this->session->getFlashdata('error')) {
            $messages['error'] = $this->session->getFlashdata('error');
        }
        
        if ($this->session->getFlashdata('warning')) {
            $messages['warning'] = $this->session->getFlashdata('warning');
        }
        
        if ($this->session->getFlashdata('info')) {
            $messages['info'] = $this->session->getFlashdata('info');
        }
        
        return $messages;
    }

    /**
     * Set success message
     */
    protected function setSuccessMessage($message)
    {
        $this->session->setFlashdata('success', $message);
    }

    /**
     * Set error message
     */
    protected function setErrorMessage($message)
    {
        $this->session->setFlashdata('error', $message);
    }

    /**
     * Set warning message
     */
    protected function setWarningMessage($message)
    {
        $this->session->setFlashdata('warning', $message);
    }

    /**
     * Set info message
     */
    protected function setInfoMessage($message)
    {
        $this->session->setFlashdata('info', $message);
    }

    /**
     * Validate CSRF token
     */
    protected function validateCSRF()
    {
        $csrf = \Config\Services::csrf();
        
        if (!$csrf->verify($this->request->getPost('csrf_token_name'))) {
            $this->setErrorMessage('CSRF token validation failed. Please try again.');
            return false;
        }
        
        return true;
    }

    /**
     * Get pagination data
     */
    protected function getPaginationData($page = 1, $perPage = 20)
    {
        return [
            'page' => max(1, (int)$page),
            'per_page' => max(1, (int)$perPage),
            'offset' => (max(1, (int)$page) - 1) * max(1, (int)$perPage),
        ];
    }

    /**
     * Get search parameters
     */
    protected function getSearchParams()
    {
        return [
            'search' => $this->request->getGet('search') ?? '',
            'category' => $this->request->getGet('category') ?? '',
            'start_date' => $this->request->getGet('start_date') ?? '',
            'end_date' => $this->request->getGet('end_date') ?? '',
            'status' => $this->request->getGet('status') ?? '',
        ];
    }

    /**
     * Format currency
     */
    protected function formatCurrency($amount)
    {
        return '₱' . number_format($amount, 2);
    }

    /**
     * Format date
     */
    protected function formatDate($date, $format = 'M d, Y')
    {
        return date($format, strtotime($date));
    }

    /**
     * Format datetime
     */
    protected function formatDateTime($datetime, $format = 'M d, Y H:i')
    {
        return date($format, strtotime($datetime));
    }
}
