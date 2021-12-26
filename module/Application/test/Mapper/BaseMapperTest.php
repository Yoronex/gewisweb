<?php

namespace ApplicationTest\Mapper;

use Application\Mapper\BaseMapper;
use Doctrine\ORM\EntityManager;
use Laminas\Mvc\Application;
use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

abstract class BaseMapperTest extends TestCase
{
    protected array $applicationConfig;
    protected ?Application $application = null;
    protected ServiceManager $serviceManager;
    protected BaseMapper $mapper;
    protected object $object;
    protected EntityManager $entityManager;

    public function setUp(): void
    {
        $this->applicationConfig = include './config/application.config.php';
        $this->getApplication();
    }

    public function getApplication(): Application
    {
        if ($this->application) {
            return $this->application;
        }

        $appConfig = $this->applicationConfig;

        $this->serviceManager = $this->initServiceManager($appConfig);

        $this->serviceManager->setAllowOverride(true);
        $this->setUpMockedServices();
        $this->serviceManager->setAllowOverride(false);

        $this->application = $this->bootstrapApplication($this->serviceManager, $appConfig);

        $events = $this->application->getEventManager();
        $this->application->getServiceManager()->get('SendResponseListener')->detach($events);

        return $this->application;
    }

    /**
     * Variation of {@link Application::init} but without initial bootstrapping.
     */
    private static function initServiceManager($configuration = []): ServiceManager
    {
        // Prepare the service manager
        $smConfig = $configuration['service_manager'] ?? [];
        $smConfig = new ServiceManagerConfig($smConfig);

        $serviceManager = new ServiceManager();
        $smConfig->configureServiceManager($serviceManager);
        $serviceManager->setService('ApplicationConfig', $configuration);

        // Load modules
        $serviceManager->get('ModuleManager')->loadModules();

        return $serviceManager;
    }

    protected function setUpMockedServices()
    {
    }

    private function bootstrapApplication($serviceManager, $configuration = []): Application
    {
        // Prepare list of listeners to bootstrap
        $listenersFromAppConfig = $configuration['listeners'] ?? [];
        $config = $serviceManager->get('config');
        $listenersFromConfigService = $config['listeners'] ?? [];

        $listeners = array_unique(array_merge($listenersFromConfigService, $listenersFromAppConfig));
        return $serviceManager->get('Application')->bootstrap($listeners);
    }

    public function testGetEntityManager(): void
    {
        $this->mapper->getEntityManager();
        $this->expectNotToPerformAssertions();
    }

    public function testFindBy(): void
    {
        $this->mapper->findBy([]);
        $this->expectNotToPerformAssertions();
    }

    public function testFindOneBy(): void
    {
        $this->mapper->findOneBy([]);
        $this->expectNotToPerformAssertions();
    }

    public function testFlush(): void
    {
        $this->mapper->flush();
        $this->expectNotToPerformAssertions();
    }

    public function testGetConnection(): void
    {
        $this->mapper->getConnection();
        $this->expectNotToPerformAssertions();
    }

    public function testCount(): void
    {
        $this->mapper->count([]);
        $this->expectNotToPerformAssertions();
    }

    public function testFindAll(): void
    {
        $this->mapper->findAll();
        $this->expectNotToPerformAssertions();
    }

//    public function testPersist(): void
//    {
//        $this->mapper->persist($this->object);
//        $this->expectNotToPerformAssertions();
//    }
//
//    public function testPersistMultiple(): void
//    {
//        $this->mapper->persistMultiple([$this->object]);
//        $this->expectNotToPerformAssertions();
//    }
//
//    public function testRemove(): void
//    {
//        $this->mapper->remove($this->object);
//        $this->expectNotToPerformAssertions();
//    }
//
//    public function testRemoveMultiple(): void
//    {
//        $this->mapper->removeMultiple([$this->object]);
//        $this->expectNotToPerformAssertions();
//    }
//
//    public function testRemoveById(): void
//    {
//        $this->mapper->removeById(0);
//        $this->expectNotToPerformAssertions();
//    }
//
//    public function testDetach(): void
//    {
//        $this->mapper->detach($this->object);
//        $this->expectNotToPerformAssertions();
//    }
}
