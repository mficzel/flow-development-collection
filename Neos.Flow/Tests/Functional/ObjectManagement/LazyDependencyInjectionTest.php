<?php
namespace Neos\Flow\Tests\Functional\ObjectManagement;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Tests\FunctionalTestCase;

/**
 * Functional tests for the Lazy Dependency Injection features
 *
 */
class LazyDependencyInjectionTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function lazyDependencyIsOnlyInjectedIfMethodOnDependencyIsCalledForTheFirstTime()
    {
        $this->objectManager->forgetInstance(Fixtures\SingletonClassA::class);

        $object = $this->objectManager->get(Fixtures\ClassWithLazyDependencies::class);
        $reflector = new \ReflectionClass($object);
        self::assertInstanceOf(Fixtures\ClassWithLazyDependencies::class, $object->lazyA);
        self::assertTrue($reflector->isUninitializedLazyObject($object));

        $actualObjectB = $object->lazyA->getObjectB();
        $this->assertNotInstanceOf(Fixtures\ClassWithLazyDependencies::class, $object->lazyA);
        self::assertFalse($reflector->isUninitializedLazyObject($object));

        $objectA = $this->objectManager->get(Fixtures\SingletonClassA::class);
        $expectedObjectB = $this->objectManager->get(Fixtures\SingletonClassB::class);
        self::assertSame($objectA, $object->lazyA);
        self::assertSame($expectedObjectB, $actualObjectB);
    }

    /**
     * @test
     */
    public function dependencyIsInjectedDirectlyIfLazyIsTurnedOff()
    {
        $object = $this->objectManager->get(Fixtures\ClassWithLazyDependencies::class);
        self::assertInstanceOf(Fixtures\SingletonClassC::class, $object->eagerC);
    }

    /**
     * @test
     */
    public function lazyDependencyIsInjectedIntoAllClassesWhichNeedItIfItIsUsedTheFirstTime()
    {
        $this->objectManager->forgetInstance(Fixtures\SingletonClassA::class);
        $this->objectManager->forgetInstance(Fixtures\SingletonClassB::class);

        $object1 = $this->objectManager->get(Fixtures\ClassWithLazyDependencies::class);
        $object2 = $this->objectManager->get(Fixtures\AnotherClassWithLazyDependencies::class);

        $reflector1 = new \ReflectionClass($object1);
        $reflector2 = new \ReflectionClass($object1);

        self::assertInstanceOf(Fixtures\ClassWithLazyDependencies::class, $object1->lazyA);
        self::assertTrue($reflector1->isUninitializedLazyObject($object1));
        self::assertInstanceOf(Fixtures\AnotherClassWithLazyDependencies::class, $object2->lazyA);
        self::assertTrue($reflector2->isUninitializedLazyObject($object2));

        $object2->lazyA->getObjectB();

        $objectA = $this->objectManager->get(Fixtures\SingletonClassA::class);
        self::assertSame($objectA, $object1->lazyA);
        self::assertSame($objectA, $object2->lazyA);
    }
}
