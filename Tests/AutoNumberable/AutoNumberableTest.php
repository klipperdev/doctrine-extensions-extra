<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Tests\AutoNumberable;

use Doctrine\Common\EventManager;
use Doctrine\ORM\Events;
use Doctrine\ORM\Tools\ResolveTargetEntityListener;
use Klipper\Component\DoctrineExtensionsExtra\AutoNumberable\AutoNumberableListener;
use Klipper\Component\DoctrineExtensionsExtra\AutoNumberable\AutoNumberGenerator;
use Klipper\Component\DoctrineExtensionsExtra\AutoNumberable\ExpressionLanguage;
use Klipper\Component\DoctrineExtensionsExtra\AutoNumberable\Model\AutoNumberConfigInterface;
use Klipper\Component\DoctrineExtensionsExtra\AutoNumberable\NumberGenerator;
use Klipper\Component\DoctrineExtensionsExtra\Tests\AutoNumberable\Fixtures\AutoNumberConfig;
use Klipper\Component\DoctrineExtensionsExtra\Tests\AutoNumberable\Fixtures\Bar;
use Klipper\Component\DoctrineExtensionsExtra\Tests\AutoNumberable\Fixtures\Foo;
use Klipper\Component\DoctrineExtensionsExtra\Tests\BaseTestCaseORM;

/**
 * Auto numberable tests.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @group klipper
 * @group klipper-doctrine-extensions-extra
 *
 * @internal
 */
final class AutoNumberableTest extends BaseTestCaseORM
{
    public const FOO = Foo::class;
    public const BAR = Bar::class;
    public const AUTO_NUMBER_CONFIG = AutoNumberConfig::class;

    protected function setUp(): void
    {
        parent::setUp();

        $numberGenerator = new NumberGenerator();
        $autoNumberGenerator = new AutoNumberGenerator($numberGenerator);
        $expression = new ExpressionLanguage();
        $evm = new EventManager();
        $evm->addEventSubscriber(new AutoNumberableListener($autoNumberGenerator, $expression));

        $rtel = new ResolveTargetEntityListener();
        $rtel->addResolveTargetEntity(AutoNumberConfigInterface::class, AutoNumberConfig::class, []);
        $evm->addEventListener(Events::loadClassMetadata, $rtel);

        $this->getMockSqliteEntityManager($evm);
    }

    /**
     * @throws
     */
    public function testBasic(): void
    {
        $now = new \DateTime();

        $foo = new Foo();
        $this->em->persist($foo);
        $this->em->flush();
        $this->em->detach($foo);
        static::assertSame('I'.$now->format('Ym').'-000001', $foo->getNumber());

        $foo2 = new Foo();
        $this->em->persist($foo2);
        $this->em->flush();
        $this->em->detach($foo2);
        static::assertSame('I'.$now->format('Ym').'-000002', $foo2->getNumber());
    }

    /**
     * @throws
     */
    public function testCondition(): void
    {
        $now = new \DateTime();

        $foo = new Bar();
        $foo->setNumber('@auto');
        $this->em->persist($foo);
        $this->em->flush();
        $this->em->detach($foo);
        static::assertSame('I'.$now->format('Ym').'-000001', $foo->getNumber());
    }

    /**
     * Get the used entity fixtures.
     *
     * @return string[]
     */
    protected function getUsedEntityFixtures(): array
    {
        return [
            self::FOO,
            self::BAR,
            self::AUTO_NUMBER_CONFIG,
        ];
    }
}
