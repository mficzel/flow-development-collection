<?php
declare(strict_types=1);

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

use Neos\Cache\Backend\IterableBackendInterface;
use Neos\Cache\Exception\InvalidBackendException;
use Neos\Cache\Frontend\StringFrontend;
use Neos\Flow\Annotations as Flow;

class SessionStorage
{
    const SESSION_LOCK_IDENTIFIER = '_garbage-collection-running';

    /**
     * Storage cache
     *
     * @Flow\Inject
     * @var StringFrontend
     */
    protected $storageCache;

    /**
     * MetaDataCache cache
     *
     * @Flow\Inject
     * @var StringFrontend
     */
    protected $metaDataCache;

    /**
     * @var string[]
     */
    protected $runtimeStorageCache = [];

    /**
     * @var array[]
     */
    protected $runtimeMetaDataCache = [];

    /**
     * @Flow\InjectConfiguration(path="session.inactivityTimeout")
     * @var integer
     */
    protected $inactivityTimeout;

    /**
     * @Flow\InjectConfiguration(path="session.inactivityThreshold")
     * @var integer
     */
    protected $inactivityThreshold;

    /**
     * @var bool
     */
    protected $useIgBinary;

    public function initializeObject()
    {
        if (!$this->metaDataCache->getBackend() instanceof IterableBackendInterface) {
            throw new InvalidBackendException(sprintf('The metadata cache must provide a backend implementing the IterableBackendInterface, but the given backend "%s" does not implement it.', get_class($this->storageCache->getBackend())), 1370964558);
        }
        $this->useIgBinary = extension_loaded('igbinary');
    }

    public function retrieveData(string $entryIdentifier)
    {
        $serializedResult = $this->retrieveSerializedSessionData($entryIdentifier);
        if ($serializedResult === false) {
            return false;
        }
        return ($this->useIgBinary === true) ? igbinary_unserialize($serializedResult) : unserialize($serializedResult);
    }

    public function storeData(string $entryIdentifier, $variable, array $tags = [], int $lifetime = null): void
    {
        $serializedValue = ($this->useIgBinary === true) ? igbinary_serialize($variable) : serialize($variable);
        $previousSerializedValue = $this->retrieveSerializedSessionData($entryIdentifier);
        if ($serializedValue == $previousSerializedValue) {
            return;
        }
        $this->storeSerializedSessionData($entryIdentifier,$serializedValue,$tags,$lifetime);
    }

    public function hasData(string $entryIdentifier): bool
    {
        if (array_key_exists($entryIdentifier, $this->runtimeStorageCache)) {
            return $this->runtimeStorageCache[$entryIdentifier] !== false;
        } else {
            return $this->storageCache->has($entryIdentifier);
        }
    }

    public function flushDataByTag(string $tag): void
    {
        $this->storageCache->flushByTag($tag);
    }

    public function flushMetaData(string $sessionIdentifier): void
    {
        $this->metaDataCache->remove($sessionIdentifier);
    }

    public function setGarbageCollectionLock(): void
    {
        $this->metaDataCache->set(self::SESSION_LOCK_IDENTIFIER, '');
    }

    public function isGarbageCollectionRunning(): bool
    {
        return $this->metaDataCache->has(self::SESSION_LOCK_IDENTIFIER);
    }

    public function removeGarbageCollectionLock(): void
    {
        $this->metaDataCache->remove(self::SESSION_LOCK_IDENTIFIER);
    }

    public function getMetaDataIterator(): iterable
    {
        foreach ($this->metaDataCache->getIterator() as $key => $value) {
            if ($key == self::SESSION_LOCK_IDENTIFIER) {
                continue;
            }
            yield $key => $value;
        }
    }

    public function hasMetaData(string $sessionIdentifier): bool
    {
        return $this->metaDataCache->has($sessionIdentifier);
    }

    public function retrieveMetaData(string $sessionIdentifier): ?array
    {
        if (!array_key_exists($sessionIdentifier, $this->runtimeMetaDataCache)) {
            $this->runtimeMetaDataCache[$sessionIdentifier] = $this->metaDataCache->get($sessionIdentifier) ?: null;
        }
        return $this->runtimeMetaDataCache[$sessionIdentifier];
    }

    public function retrieveMetaDataByTag(string $tag): array
    {
        return $this->metaDataCache->getByTag($tag);
    }

    public function storeMetaData(string $sessionIdentifier, int $lastActivityTimestamp, string $storageIdentifier, array $tags): void
    {
        if (array_key_exists($sessionIdentifier, $this->runtimeMetaDataCache)) {
            $previousMetaData = $this->runtimeMetaDataCache[$sessionIdentifier];
            if ($previousMetaData['storageIdentifier'] !== $storageIdentifier
                || $previousMetaData['tags'] !== $tags
                || $lastActivityTimestamp - $previousMetaData['lastActivityTimestamp'] <=$this->inactivityThreshold
            ) {
                return;
            }
        }

        $sessionInfo = [
            'lastActivityTimestamp' => $lastActivityTimestamp,
            'storageIdentifier' => $storageIdentifier,
            'tags' => $tags
        ];

        $tagsForCacheEntry = array_map(function ($tag) {
            return Session::TAG_PREFIX . $tag;
        }, $tags);
        $tagsForCacheEntry[] = $sessionIdentifier;
        $tagsForCacheEntry[] = 'session';

        $this->metaDataCache->set($sessionIdentifier, $sessionInfo, $tagsForCacheEntry, 0);
    }

    public function removeMetaData(string $sessionIdentifier): void
    {
        $this->metaDataCache->remove($sessionIdentifier);
    }

    public function isValidSessionIdentifier(string $identifier): bool
    {
        return $this->metaDataCache->isValidEntryIdentifier($identifier);
    }

    protected function retrieveSerializedSessionData(string $entryIdentifier): false|string
    {
        if (array_key_exists($entryIdentifier, $this->runtimeStorageCache)) {
            return $this->runtimeStorageCache[$entryIdentifier];
        } else {
            return $this->storageCache->get($entryIdentifier);
        }
    }

    protected function storeSerializedSessionData(string $entryIdentifier, string $variable, array $tags = [], int $lifetime = null): void
    {
        $this->runtimeStorageCache[$entryIdentifier] = $variable;
        $this->storageCache->set($entryIdentifier,$variable,$tags,$lifetime);
    }
}
