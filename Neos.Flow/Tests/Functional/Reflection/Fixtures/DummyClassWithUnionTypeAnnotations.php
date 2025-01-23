<?php
namespace Neos\Flow\Tests\Functional\Reflection\Fixtures;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

/**
 * Dummy class for the Reflection tests
 *
 */
class DummyClassWithUnionTypeAnnotations
{
    /**
     * @param string|false $parameterA
     * @param false|DummyClassWithUnionTypeAnnotations $parameterB
     * @param false | DummyClassWithUnionTypeAnnotations $parameterB1 Same as B but with spaces in between
     * @param DummyClassWithUnionTypeAnnotations|null $parameterC
     * @return void
     */
    public function methodWithUnionParameters($parameterA, $parameterB, $parameterB1, $parameterC)
    {
    }

    /**
     * @return string|false
     */
    public function methodWithUnionReturnTypeA()
    {
    }

    /**
     * @return false|DummyClassWithUnionTypeAnnotations
     */
    public function methodWithUnionReturnTypesB()
    {
    }

    /**
     * @return DummyClassWithUnionTypeAnnotations|null
     */
    public function methodWithUnionReturnTypesC()
    {
    }
}
