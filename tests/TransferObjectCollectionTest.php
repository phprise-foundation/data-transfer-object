<?php

declare(strict_types=1);

namespace Phprise\Test\DataTransferObject;

use PHPUnit\Framework\TestCase;
use Phprise\DataTransferObject\TransferObject;
use Phprise\DataTransferObject\TransferObjectCollection;

class TransferObjectCollectionTest extends TestCase
{
    public function testCollectionBasics(): void
    {
        $user1 = SimpleUserDto::fromArray(['name' => 'John', 'age' => 30]);
        $user2 = SimpleUserDto::fromArray(['name' => 'Jane', 'age' => 25]);

        $collection = new UserCollection([$user1, $user2]);

        $this->assertCount(2, $collection);
        $this->assertEquals($user1, $collection->first());
        $this->assertEquals($user2, $collection->last());
        $this->assertFalse($collection->isEmpty());
    }

    public function testFromArray(): void
    {
        $data = [
            new SimpleUserDto(), // Empty for simplicity, or pre-filled
            new SimpleUserDto()
        ];
        // Note: TransferObjectCollection::fromArray expects an array of TransferObjects usually,
        // as the constructor takes array of elements.
        // Logic in custom createFrom might differ.
        // But default implementation just does `new static($array)`.

        $collection = UserCollection::fromArray($data);
        $this->assertCount(2, $collection);
    }

    public function testCollectionManipulation(): void
    {
        $user1 = SimpleUserDto::fromArray(['name' => 'John', 'age' => 30]);
        $collection = new UserCollection();

        $collection->add($user1);
        $this->assertCount(1, $collection);
        $this->assertTrue($collection->contains($user1));

        $collection->removeElement($user1);
        $this->assertTrue($collection->isEmpty());
    }

    public function testFilter(): void
    {
        $user1 = SimpleUserDto::fromArray(['name' => 'John', 'age' => 30]);
        $user2 = SimpleUserDto::fromArray(['name' => 'Jane', 'age' => 25]);
        $collection = new UserCollection([$user1, $user2]);

        $filtered = $collection->filter(fn($val, $key) => $val->age > 28);

        $this->assertCount(1, $filtered);
        $this->assertEquals($user1, $filtered->first());
    }
}

class UserCollection extends TransferObjectCollection
{
}
