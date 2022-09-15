<?php
namespace Neos\Flow\Tests\Unit\Session;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Session\SessionMetadata;
use Neos\Flow\Tests\UnitTestCase;

/**
 * Unit tests for the Flow Session Metadata implementation
 */
class SessionMetadataTest extends UnitTestCase
{

    /**
     * @test
     */
    public function serializationCreatesClassicFormat(): void
    {
        $sessionMetadata = new SessionMetadata(
            123,
            'a_string',
            ["foo", "bar"]
        );

        $this->assertEquals(
            [
                'lastActivityTimestamp' => 123,
                'storageIdentifier' => 'a_string',
                'tags' => ["foo", "bar"]
            ],
            $sessionMetadata->jsonSerialize()
        );
    }

    /**
     * @test
     */
    public function fromArrayCreatesClassicFormat(): void
    {
        $sessionMetadata = SessionMetadata::fromArray(
            [
                'lastActivityTimestamp' => 123,
                'storageIdentifier' => 'a_string',
                'tags' => ["foo", "bar"]
            ]
        );
        $this->assertEquals(123, $sessionMetadata->getLastActivityTimestamp());
        $this->assertEquals('a_string', $sessionMetadata->getStorageIdentifier());
        $this->assertEquals(["foo", "bar"], $sessionMetadata->getTags());
    }

    public function equalsWithinLastActivityThresholdHandlesLifetimeDataProvider(): array
    {
        return [
            [1000, 1051, false],
            [1000, 1050, true],
            [1000, 1049, true],
        ];
    }

    /**
     * @test
     * @dataProvider equalsWithinLastActivityThresholdHandlesLifetimeDataProvider
     */
    public function equalsWithinLastActivityThresholdHandlesLifetime(int $previousTimestamp, int $currentTimestamp, bool $expectation): void
    {
        $previousMetadata = SessionMetadata::fromArray(
            [
                'lastActivityTimestamp' => $previousTimestamp,
                'storageIdentifier' => 'a_string',
                'tags' => ["foo", "bar"]
            ]
        );

        $sessionMetadata = SessionMetadata::fromArray(
            [
                'lastActivityTimestamp' => $currentTimestamp,
                'storageIdentifier' => 'a_string',
                'tags' => ["foo", "bar"]
            ]
        );

        $this->assertEquals($expectation, $sessionMetadata->equalsWithinLastActivityThreshold($previousMetadata, 50));
    }

    public function equalsWithinLastActivityThresholdDetectsStorageIdChangesDataProvider(): array
    {
        return [
            ['foo', 'bar', false],
            ['foo', '', false],
            ['', 'bar', false],
            ['foo', 'foo', true],
        ];
    }

    /**
     * @test
     * @dataProvider equalsWithinLastActivityThresholdDetectsStorageIdChangesDataProvider
     */
    public function equalsWithinLastActivityThresholdDetectsStorageIdChanges(string $previousId, string $currentId, bool $expectation): void
    {
        $previousMetadata = SessionMetadata::fromArray(
            [
                'lastActivityTimestamp' => 1000,
                'storageIdentifier' => $previousId,
                'tags' => ["foo", "bar"]
            ]
        );

        $sessionMetadata = SessionMetadata::fromArray(
            [
                'lastActivityTimestamp' => 1000,
                'storageIdentifier' => $currentId,
                'tags' => ["foo", "bar"]
            ]
        );

        $this->assertEquals($expectation, $sessionMetadata->equalsWithinLastActivityThreshold($previousMetadata, 50));
    }


    public function equalsWithinLastActivityThresholdDetectsTagChangesDataProvider(): array
    {
        return [
            [["foo"], ["foo", "bar"], false],
            [["foo", "bar"], ["foo"], false],
            [[], ["foo", "bar"], false],
            [[], [], true],
            [["foo", "bar"], ["foo", "bar"], true],
        ];
    }
    /**
     * @test
     * @dataProvider equalsWithinLastActivityThresholdDetectsTagChangesDataProvider
     */
    public function equalsWithinLastActivityThresholdDetectsTagChanges(array $previousTags, array $currentTags, bool $expectation): void
    {
        $previousMetadata = SessionMetadata::fromArray(
            [
                'lastActivityTimestamp' => 1000,
                'storageIdentifier' => 'a_string',
                'tags' => $previousTags
            ]
        );

        $sessionMetadata = SessionMetadata::fromArray(
            [
                'lastActivityTimestamp' => 1000,
                'storageIdentifier' => 'a_string',
                'tags' => $currentTags
            ]
        );

        $this->assertEquals($expectation, $sessionMetadata->equalsWithinLastActivityThreshold($previousMetadata, 50));
    }

}
