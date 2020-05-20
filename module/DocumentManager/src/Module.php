<?php

namespace DocumentManager;

use Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements ConfigProviderInterface
{
    const VERSION = 1.0;
    const UPLOAD_PATH = './public/Uploads/';

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

}