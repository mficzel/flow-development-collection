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
use Neos\Cache\Frontend\VariableFrontend;
use Neos\Flow\Annotations as Flow;

class SessionStorage
{
    /**
     * Storage cache for this session
     *
     * @Flow\Inject
     * @var VariableFrontend
     */
    protected $storageCache;

    /**
     * @return void
     * @throws InvalidBackendException
     */
    public function initializeObject()
    {
        if (!$this->storageCache->getBackend() instanceof IterableBackendInterface) {
            throw new InvalidBackendException(sprintf('The session storage cache must provide a backend implementing the IterableBackendInterface, but the given backend "%s" does not implement it.', get_class($this->storageCache->getBackend())), 1370964558);
        }
    }

    public function get(string $entryIdentifier)
    {
        return $this->storageCache->get($entryIdentifier);
    }

    public function set(string $entryIdentifier, $variable, array $tags = [], int $lifetime = null)
    {
        return $this->storageCache->set($entryIdentifier, $variable, $tags, $lifetime);
    }

    public function has(string $entryIdentifier): bool
    {
        return $this->storageCache->has($entryIdentifier);
    }

    public function flushByTag(string $tag): int
    {
        return $this->storageCache->flushByTag($tag);
    }

    public function flushByTags(array $tags): int
    {
        return $this->storageCache->flushByTags($tags);
    }

}
