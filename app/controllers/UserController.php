<?php

namespace Jeffrey\Educore\Controllers;

use Jeffrey\Educore\Core\AppLogger;
use Jeffrey\Educore\Models\UserModel;

class UserController
{
    private $userModel;
    private $logger;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->logger = AppLogger::getInstance()->getLogger();
    }

    
}