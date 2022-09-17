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
    /**
     * Storage cache
     *
     * @Flow\Inject
     * @var StringFrontend
     */
    protected $storageCache;

    /**
     * @var string[]
     */
    protected $runtimeStorageCache = [];

    /**
     * @var bool
     */
    protected $useIgBinary;

    public function initializeObject()
    {
        if (!$this->storageCache->getBackend() instanceof IterableBackendInterface) {
            throw new InvalidBackendException(sprintf('The session storage cache must provide a backend implementing the IterableBackendInterface, but the given backend "%s" does not implement it.', get_class($this->storageCache->getBackend())), 1370964558);
        }
        $this->useIgBinary = extension_loaded('igbinary');
    }

    public function retrieve(string $entryIdentifier)
    {
        $serializedResult = $this->retrieveSerializedValue($entryIdentifier);
        if ($serializedResult === false) {
            return false;
        }
        return ($this->useIgBinary === true) ? igbinary_unserialize($serializedResult) : unserialize($serializedResult);
    }

    public function store(string $entryIdentifier, $variable, array $tags = [], int $lifetime = null): void
    {
        $serializedValue = ($this->useIgBinary === true) ? igbinary_serialize($variable) : serialize($variable);
        $previousSerializedValue = $this->retrieveSerializedValue($entryIdentifier);
        if ($serializedValue == $previousSerializedValue) {
            return;
        }
        $this->storeSerializedValue($entryIdentifier,$serializedValue,$tags,$lifetime);
    }

    protected function retrieveSerializedValue(string $entryIdentifier): false|string
    {
        if (array_key_exists($entryIdentifier, $this->runtimeStorageCache)) {
            return $this->runtimeStorageCache[$entryIdentifier];
        } else {
            return $this->storageCache->get($entryIdentifier);
        }
    }

    protected function storeSerializedValue(string $entryIdentifier, string $variable, array $tags = [], int $lifetime = null): void
    {
        $this->runtimeStorageCache[$entryIdentifier] = $variable;
        $this->storageCache->set($entryIdentifier,$variable,$tags,$lifetime);
    }

    public function has(string $entryIdentifier): bool
    {
        if (array_key_exists($entryIdentifier, $this->runtimeStorageCache)) {
            return $this->runtimeStorageCache[$entryIdentifier] !== false;
        } else {
            return $this->storageCache->has($entryIdentifier);
        }
    }

    public function flushByTag(string $tag): int
    {
        $this->runtimeStorageCache = [];
        return $this->storageCache->flushByTag($tag);
    }
}
