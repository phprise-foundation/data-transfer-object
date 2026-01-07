<?php

declare(strict_types=1);

namespace Phprise\Test\DataTransferObject;

use PHPUnit\Framework\TestCase;
use Phprise\DataTransferObject\TransferObject;

class TransferObjectTest extends TestCase
{
    public function testSimpleFromArrayAndToArray(): void
    {
        $data = [
            'name' => 'John Doe',
            'age' => 30,
        ];

        $dto = SimpleUserDto::fromArray($data);

        $this->assertInstanceOf(SimpleUserDto::class, $dto);
        $this->assertEquals('John Doe', $dto->name);
        $this->assertEquals(30, $dto->age);
        $this->assertEquals($data, $dto->toArray());
    }

    public function testSnakeAndCamelCaseConversion(): void
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
        ];

        $dto = SnakeCaseDto::fromArray($data);

        $this->assertEquals('John', $dto->firstName);
        $this->assertEquals('Doe', $dto->lastName);

        $expectedSnake = $data;
        $this->assertEquals($expectedSnake, $dto->toSnakeCaseArray());
    }

    public function testJsonSerialization(): void
    {
        $data = ['name' => 'John', 'age' => 30];
        $dto = SimpleUserDto::fromArray($data);

        $json = $dto->toJson();
        $this->assertJsonStringEqualsJsonString(json_encode($data), $json);

        $dtoFromJson = SimpleUserDto::fromJson($json);
        $this->assertEquals($dto->toArray(), $dtoFromJson->toArray());
    }

    public function testNestedTransferObjects(): void
    {
        $data = [
            'user' => [
                'name' => 'John',
                'age' => 30,
            ],
            'role' => 'admin',
        ];

        $dto = ComplexDto::fromArray($data);

        $this->assertInstanceOf(ComplexDto::class, $dto);
        $this->assertInstanceOf(SimpleUserDto::class, $dto->user);
        $this->assertEquals('John', $dto->user->name);
        $this->assertEquals('admin', $dto->role);

        $this->assertEquals($data, $dto->toArray());
    }

    public function testDateTimeConversion(): void
    {
        $dateStr = '2023-10-27T10:00:00+00:00';
        $data = ['created_at' => $dateStr];

        $dto = DateDto::fromArray($data);

        $this->assertInstanceOf(\DateTimeImmutable::class, $dto->createdAt);
        $this->assertEquals($dateStr, $dto->createdAt->format(\DateTime::RFC3339));

        $this->assertEquals(['createdAt' => $dateStr], $dto->toArray());
    }
}

class SimpleUserDto extends TransferObject
{
    public string $name;
    public int $age;
}

class SnakeCaseDto extends TransferObject
{
    public string $firstName;
    public string $lastName;
}

class ComplexDto extends TransferObject
{
    public SimpleUserDto $user;
    public string $role;
}

class DateDto extends TransferObject
{
    public \DateTimeImmutable $createdAt;
}
