<?php
namespace Neos\Flow\Session;

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
 * A value object representing session metadata
 */
class SessionMetadata implements \JsonSerializable
{
    public function __construct(
       protected int $lastActivityTimestamp,
       protected string $storageIdentifier,
       protected array $tags,
    ) {}

    public function getLastActivityTimestamp(): int
    {
        return $this->lastActivityTimestamp;
    }

    public function getStorageIdentifier(): string
    {
        return $this->storageIdentifier;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function equalsWithinLastActivityThreshold(SessionMetadata $previous, int $threshold): bool
    {
        $LastActivityDifference = $this->lastActivityTimestamp - $previous->lastActivityTimestamp;
        return
            $LastActivityDifference <= $threshold
            && $this->storageIdentifier == $previous->storageIdentifier
            && $this->tags == $previous->tags;
    }

    public static function fromArray(array $data): self
    {
        return new static(
            $data['lastActivityTimestamp'],
            $data['storageIdentifier'],
            $data['tags'],
        );
    }

    public function jsonSerialize(): mixed
    {
        return [
            'lastActivityTimestamp' => $this->lastActivityTimestamp,
            'storageIdentifier' => $this->storageIdentifier,
            'tags' => $this->tags
        ];
    }
}
