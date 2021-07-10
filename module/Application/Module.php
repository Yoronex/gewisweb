<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Application\Service\Email;
use Application\Service\FileStorage;
use Application\Service\Legacy;
use Application\View\Helper\Acl;
use Application\View\Helper\FileUrl;
use Application\View\Helper\Infima;
use Application\View\Helper\JobCategories;
use Application\View\Helper\ModuleIsActive;
use Application\View\Helper\ScriptUrl;
use Carbon\Carbon;
use Locale;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Session\Container as SessionContainer;
use Zend\Validator\AbstractValidator;
use User\Permissions\NotAllowedException;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $locale = $this->determineLocale($e);

        $translator = $e->getApplication()->getServiceManager()->get('translator');
        $translator->setlocale($locale);

        Carbon::setLocale($locale);
        Locale::setDefault($locale);

        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'logError']);
        $eventManager->attach(MvCEvent::EVENT_RENDER_ERROR, [$this, 'logError']);

        // enable Zend\Validate default translator
        AbstractValidator::setDefaultTranslator($translator, 'validate');
    }

    public function logError(MvCEvent $e)
    {
        $sm = $e->getApplication()->getServiceManager();
        $logger = $sm->get('logger');

        if ($e->getError() === 'error-router-no-match') {
            // not an interesting error
            return;
        }
        if ($e->getError() === 'error-exception') {
            $ex = $e->getParam('exception');

            if ($ex instanceof NotAllowedException) {
                // we do not need to log access denied
                return;
            }

            $logger->error($ex);
            return;
        }
        $logger->error($e->getError());
    }

    protected function determineLocale(MvcEvent $e)
    {
        $session = new SessionContainer('lang');
        if (!isset($session->lang)) {
            // default: nl locale
            $session->lang = 'nl';
        }

        return $session->lang;
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                'application_service_legacy' => function () {
                    return new Legacy();
                },
                'application_service_email' => function ($sm) {
                    $renderer = $sm->get('ViewRenderer');
                    $transport = $sm->get('user_mail_transport');
                    $emailConfig = $sm->get('config')['email'];
                    return new Email($renderer, $transport, $emailConfig);
                },
                'application_service_storage' => function ($sm) {
                    $translator = $sm->get('translator');
                    $storageConfig = $sm->get('config')['storage'];
                    return new FileStorage($translator, $storageConfig);
                },
                'application_get_languages' => function () {
                    return ['nl', 'en'];
                },
                'logger' => function ($sm) {
                    $logger = new Logger("gewisweb");
                    $config = $sm->get('config')['logging'];

                    $handler = new RotatingFileHandler(
                        $config['logfile_path'],
                        $config['max_rotate_file_count'],
                        $config['minimal_log_level']
                    );
                    $logger->pushHandler($handler);

                    return $logger;
                }
            ],
        ];
    }

    /**
     * Get view helper configuration.
     *
     * @return array
     */
    public function getViewHelperConfig()
    {
        return [
            'factories' => [
                'acl' => function ($sm) {
                    $locator = $sm->getServiceLocator();
                    $helper = new Acl();
                    $helper->setServiceLocator($locator);
                    return $helper;
                },
                'scriptUrl' => function () {
                    $helper = new ScriptUrl();
                    return $helper;
                },
                'moduleIsActive' => function ($sm) {
                    $locator = $sm->getServiceLocator();
                    $helper = new ModuleIsActive();
                    $helper->setServiceLocator($locator);
                    return $helper;
                },
                'jobCategories' => function ($sm) {
                    $locator = $sm->getServiceLocator();
                    $helper = new JobCategories();
                    $helper->setServiceLocator($locator);
                    return $helper;
                },
                'fileUrl' => function ($sm) {
                    $locator = $sm->getServiceLocator();
                    $helper = new FileUrl();
                    $helper->setServiceLocator($locator);
                    return $helper;
                },
                'infima' => function ($sm) {
                    $locator = $sm->getServiceLocator();
                    $helper = new Infima();
                    $helper->setLegacyService($locator->get('application_service_legacy'));
                    return $helper;
                }
            ]
        ];
    }
}
