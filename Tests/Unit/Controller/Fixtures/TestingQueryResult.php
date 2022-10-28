<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Controller\Fixtures;

use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Testing query result that holds an object storage for its objects.
 *
 * @template Model
 * @extentends QueryResultInterface<Model>
 */
final class TestingQueryResult implements QueryResultInterface
{
    /**
     * @var ObjectStorage<Model>
     */
    private $objectStorage;

    /**
     * @param ObjectStorage<Model>|null $storage
     */
    public function __construct(?ObjectStorage $storage = null)
    {
        if (!$storage instanceof ObjectStorage) {
            /** @var ObjectStorage<Model> $storage */
            $storage = new ObjectStorage();
        }

        $this->objectStorage = $storage;
    }

    /**
     * @return Model|null
     */
    public function current()
    {
        return $this->objectStorage->current();
    }

    public function next(): void
    {
        $this->objectStorage->next();
    }

    public function key(): string
    {
        return $this->objectStorage->key();
    }

    public function valid(): bool
    {
        return $this->objectStorage->valid();
    }

    public function rewind(): void
    {
        $this->objectStorage->rewind();
    }

    public function offsetExists($offset): bool
    {
        return $this->objectStorage->offsetExists($offset);
    }

    /**
     * @return Model|null
     */
    public function offsetGet($offset)
    {
        /** @var Model|null $offset */
        $offset = $this->objectStorage->offsetGet($offset);

        return $offset;
    }

    public function offsetSet($offset, $value): void
    {
        $this->objectStorage->offsetSet($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        $this->objectStorage->offsetUnset($offset);
    }

    public function count(): int
    {
        return $this->objectStorage->count();
    }

    /**
     * @return never
     *
     * @throws \BadMethodCallException
     */
    public function getQuery(): QueryInterface
    {
        throw new \BadMethodCallException('Not implemented.', 1665661687);
    }

    /**
     * @return Model|null
     */
    public function getFirst()
    {
        $this->objectStorage->rewind();

        return $this->objectStorage->current();
    }

    /**
     * @return array<int, Model>
     */
    public function toArray(): array
    {
        return $this->objectStorage->toArray();
    }
}
