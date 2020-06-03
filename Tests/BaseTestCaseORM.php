<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\SchemaTool;
use Gedmo\Loggable\LoggableListener;
use Gedmo\Sluggable\SluggableListener;
use Gedmo\SoftDeleteable\SoftDeleteableListener;
use Gedmo\Timestampable\TimestampableListener;
use Gedmo\Tool\Logging\DBAL\QueryAnalyzer;
use Gedmo\Translatable\TranslatableListener;
use Gedmo\Tree\TreeListener;
use PHPUnit\Framework\TestCase;

/**
 * Base of tests for extensions.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class BaseTestCaseORM extends TestCase
{
    protected ?EntityManager $em = null;

    protected ?QueryAnalyzer $queryAnalyzer = null;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        if ($this->em) {
            $this->em->close();
        }

        $this->em = null;
        $this->queryAnalyzer = null;
    }

    /**
     * EntityManager mock object together with
     * annotation mapping driver and pdo_sqlite
     * database in memory.
     *
     * @throws
     */
    protected function getMockSqliteEntityManager(?EventManager $evm = null, ?Configuration $config = null): EntityManager
    {
        $conn = [
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ];

        $config = $config ?? $this->getMockAnnotatedConfig();
        $em = EntityManager::create($conn, $config, $evm ?: $this->getEventManager());

        $schema = array_map(static function ($class) use ($em) {
            return $em->getClassMetadata($class);
        }, $this->getUsedEntityFixtures());

        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema([]);
        $schemaTool->createSchema($schema);

        return $this->em = $em;
    }

    /**
     * EntityManager mock object together with
     * annotation mapping driver and custom
     * connection.
     *
     * @throws
     */
    protected function getMockCustomEntityManager(array $conn, ?EventManager $evm = null): EntityManager
    {
        $config = $this->getMockAnnotatedConfig();
        $em = EntityManager::create($conn, $config, $evm ?: $this->getEventManager());

        $schema = array_map(static function ($class) use ($em) {
            return $em->getClassMetadata($class);
        }, $this->getUsedEntityFixtures());

        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema([]);
        $schemaTool->createSchema($schema);

        return $this->em = $em;
    }

    /**
     * EntityManager mock object with
     * annotation mapping driver.
     *
     * @throws
     */
    protected function getMockMappedEntityManager(?EventManager $evm = null): EntityManager
    {
        $driver = $this->getMockBuilder(Driver::class)->getMock();
        $driver->expects(static::once())
            ->method('getDatabasePlatform')
            ->willReturn($this->getMockBuilder(MySqlPlatform::class)->getMock())
        ;

        /** @var Connection $conn */
        $conn = $this->getMockBuilder(Connection::class)
            ->setConstructorArgs([[], $driver])
            ->getMock()
        ;

        $conn->expects(static::once())
            ->method('getEventManager')
            ->willReturn($evm ?: $this->getEventManager())
        ;

        $config = $this->getMockAnnotatedConfig();
        $this->em = EntityManager::create($conn, $config);

        return $this->em;
    }

    /**
     * Starts query statistic log.
     *
     * @throws
     */
    protected function startQueryLog(): void
    {
        if (!$this->em || !$this->em->getConnection()->getDatabasePlatform()) {
            throw new \RuntimeException('EntityManager and database platform must be initialized');
        }
        $this->queryAnalyzer = new QueryAnalyzer($this->em->getConnection()->getDatabasePlatform());
        $this->em->getConfiguration()->setSQLLogger($this->queryAnalyzer);
    }

    /**
     * Stops query statistic log and outputs
     * the data to screen or file.
     *
     * @throws \RuntimeException
     */
    protected function stopQueryLog(bool $dumpOnlySql = false, bool $writeToLog = false): void
    {
        if ($this->queryAnalyzer) {
            $output = $this->queryAnalyzer->getOutput($dumpOnlySql);
            if ($writeToLog) {
                $fileName = __DIR__.'/../../temp/query_debug_'.time().'.log';
                if (false !== ($file = fopen($fileName, 'wb+'))) {
                    fwrite($file, $output);
                    fclose($file);
                } else {
                    throw new \RuntimeException('Unable to write to the log file');
                }
            } else {
                echo $output;
            }
        }
    }

    /**
     * Creates default mapping driver.
     *
     * @throws
     */
    protected function getMetadataDriverImplementation(): AnnotationDriver
    {
        return new AnnotationDriver(new AnnotationReader());
    }

    /**
     * Get a list of used fixture classes.
     */
    abstract protected function getUsedEntityFixtures(): array;

    /**
     * Get annotation mapping configuration.
     */
    protected function getMockAnnotatedConfig(): Configuration
    {
        $config = new Configuration();
        $config->setProxyDir(__DIR__.'/../../temp');
        $config->setProxyNamespace('Proxy');
        $config->setMetadataDriverImpl($this->getMetadataDriverImplementation());

        return $config;
    }

    /**
     * Build event manager.
     */
    private function getEventManager(): EventManager
    {
        $evm = new EventManager();
        $evm->addEventSubscriber(new TreeListener());
        $evm->addEventSubscriber(new SluggableListener());
        $evm->addEventSubscriber(new LoggableListener());
        $evm->addEventSubscriber(new TranslatableListener());
        $evm->addEventSubscriber(new TimestampableListener());
        $evm->addEventSubscriber(new SoftDeleteableListener());

        return $evm;
    }
}
