<?php

declare(strict_types=1);

namespace Phprise\DataTransferObject;

use Traversable;
use Doctrine\Common\Collections\Order;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Expr\ClosureExpressionVisitor;

/**
 * @template TKey of array-key
 * @template T of TransferObject
 * @implements Collection<TKey, T>
 */
abstract class TransferObjectCollection extends TransferObject implements Collection
{
    public function __construct(
        /**
         * An array containing the entries of this collection.
         *
         * @psalm-var array<TKey,T>
         * @var mixed[]
         */
        private array $elements = []
    ){}

    /**
     * {@inheritDoc}
     *
     * @return array[]
     *
     * @psalm-return array<TKey, array>
     * @psalm-suppress ImplementedReturnTypeMismatch
     */
    #[\Override]
    public function toArray(): array
    {
        return array_map(
            fn (TransferObject $element) =>
            $element->toArray(), $this->elements
        );
    }

    #[\Override]
    public static function fromArray(array $array): static
    {
        /** @psalm-suppress MixedArgumentTypeCoercion */
        return new static($array);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function first(): TransferObject|false
    {
        return reset($this->elements);
    }

    /**
     * Creates a new instance from the specified elements.
     *
     * This method is provided for derived classes to specify how a new
     * instance should be created when constructor semantics have changed.
     *
     * @param array $elements Elements.
     *
     * @return static
     */
    protected function createFrom(array $elements)
    {
        /** @psalm-suppress MixedArgumentTypeCoercion */
        return new static($elements);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function last(): TransferObject|false
    {
        return end($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function key(): string|int|null
    {
        return key($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function next(): TransferObject|false
    {
        return next($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function current(): TransferObject|false
    {
        return current($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function remove(string|int $key): ?TransferObject
    {
        if (! isset($this->elements[$key]) && ! array_key_exists($key, $this->elements)) {
            return null;
        }

        $removed = $this->elements[$key];
        unset($this->elements[$key]);

        return $removed;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function removeElement(mixed $element): bool
    {
        $key = array_search($element, $this->elements, true);

        if ($key === false) {
            return false;
        }

        unset($this->elements[$key]);

        return true;
    }

    /**
     * Required by interface ArrayAccess.
     *
     * @param TKey $offset
     *
     * @return bool
     */
    #[\Override]
    #[\ReturnTypeWillChange]
    public function offsetExists(mixed $offset): bool
    {
        /**
         * @psalm-suppress InvalidArgument, MixedArgument
         */
        return $this->containsKey($offset);
    }

    /**
     * Required by interface ArrayAccess.
     *
     * @return T|null
     */
    #[\Override]
    #[\ReturnTypeWillChange]
    public function offsetGet(mixed $offset)
    {
        /**
         * @psalm-suppress InvalidArgument, MixedArgument
         */
        return $this->get($offset);
    }

    /**
     * Required by interface ArrayAccess.
     *
     * @param T         $value
     *
     * @return void
     */
    #[\Override]
    #[\ReturnTypeWillChange]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->add($value);

            return;
        }

        /**
         * @psalm-suppress InvalidArgument, MixedArgument
         */
        $this->set($offset, $value);
    }

    /**
     * Required by interface ArrayAccess.
     *
     * @return void
     */
    #[\Override]
    #[\ReturnTypeWillChange]
    public function offsetUnset(mixed $offset): void
    {
        /**
         * @psalm-suppress InvalidArgument, MixedArgument
         */
        $this->remove($offset);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function containsKey(string|int $key): bool
    {
        return isset($this->elements[$key]) || array_key_exists($key, $this->elements);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function contains(mixed $element): bool
    {
        return in_array($element, $this->elements, true);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function exists(\Closure $p): bool
    {
        foreach ($this->elements as $key => $element) {
            if ($p($key, $element)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-param TMaybeContained $element
     *
     * @return int|string|false
     * @psalm-return (TMaybeContained is T ? TKey|false : false)
     *
     * @template TMaybeContained
     */
    #[\Override]
    public function indexOf($element): string|int|false
    {
        return array_search($element, $this->elements, true);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function get(string|int $key): ?TransferObject
    {
        return $this->elements[$key] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function getKeys(): array
    {
        return array_keys($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function getValues(): array
    {
        return array_values($this->elements);
    }

    /**
     * {@inheritDoc}
     *
     * @return int<0, max>
     */
    #[\Override]
    #[\ReturnTypeWillChange]
    public function count(): int
    {
        return count($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function set(string|int $key, mixed $value): void
    {
        $this->elements[$key] = $value;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-suppress InvalidPropertyAssignmentValue
     *
     * This breaks assumptions about the template type, but it would
     * be a backwards-incompatible change to remove this method
     */
    #[\Override]
    public function add(mixed $element): void
    {
        $this->elements[] = $element;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function isEmpty()
    {
        return empty($this->elements);
    }

    /**
     * {@inheritDoc}
     *
     * @return Traversable<int|string, mixed>
     * @psalm-return Traversable<TKey, T>
     */
    #[\Override]
    #[\ReturnTypeWillChange]
    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->elements);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-param Closure(T):U $func
     *
     * @return Collection<TKey, U>
     *
     * @psalm-template U
     */
    #[\Override]
    public function map(\Closure $func): Collection
    {
        return new ArrayCollection(array_map($func, $this->elements));
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function reduce(\Closure $func, $initial = null)
    {
        return array_reduce($this->elements, $func, $initial);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-param Closure(T, TKey):bool $p
     *
     * @return static
     * @psalm-return static<TKey,T>
     */
    #[\Override]
    public function filter(\Closure $p): static
    {
        return $this->createFrom(array_filter($this->elements, $p, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function findFirst(\Closure $p): ?TransferObject
    {
        foreach ($this->elements as $key => $element) {
            if ($p($key, $element)) {
                return $element;
            }
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function forAll(\Closure $p): bool
    {
        foreach ($this->elements as $key => $element) {
            if (! $p($key, $element)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function partition(\Closure $p): array
    {
        $matches = [];
        $noMatches = [];

        foreach ($this->elements as $key => $element) {
            if ($p($key, $element)) {
                $matches[$key] = $element;
                continue;
            }

            $noMatches[$key] = $element;
        }

        return [$this->createFrom($matches), $this->createFrom($noMatches)];
    }

    /**
     * Returns a string representation of this object.
     * {@inheritDoc}
     *
     * @return string
     */
    #[\ReturnTypeWillChange]
    public function __toString(): string
    {
        return self::class . '@' . spl_object_hash($this);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function clear(): void
    {
        $this->elements = [];
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function slice(int $offset, int|null $length = null)
    {
        return array_slice($this->elements, $offset, $length, true);
    }

    /**
     * @psalm-return static<TKey, T>
     * @psalm-suppress MixedAssignment
     * @psalm-suppress MixedArgument
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    public function matching(Criteria $criteria): static
    {
        $expr     = $criteria->getWhereExpression();
        $filtered = $this->elements;

        if ($expr) {
            $visitor  = new ClosureExpressionVisitor();
            $filter   = $visitor->dispatch($expr);
            $filtered = array_filter($filtered, $filter);
        }

        $orderings = $criteria->orderings();

        if ($orderings) {
            $next = null;
            foreach (array_reverse($orderings) as $field => $ordering) {
                $next = ClosureExpressionVisitor::sortByField($field, $ordering === Order::Descending ? -1 : 1, $next);
            }

            uasort($filtered, $next);
        }

        $offset = $criteria->getFirstResult();
        $length = $criteria->getMaxResults();

        if ($offset !== null && $offset > 0 || $length !== null && $length > 0) {
            $filtered = array_slice($filtered, (int) $offset, $length, true);
        }

        return $this->createFrom($filtered);
    }

    public function getElements(): array
    {
        return $this->elements;
    }
}