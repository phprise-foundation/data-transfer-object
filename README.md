# Data Transfer Object

A strict, no-nonsense Data Transfer Object (DTO) library for PHP, following the **OTAKU Philosophy**.

It simplifies data transport between layers of your application, ensuring type safety and automatic mapping between `snake_case` (common in databases/APIs) and `camelCase` (standard in PHP).

## Installation

```bash
composer require phprise/data-transfer-object
```

## Usage

### Basic Usage

Define your DTO by extending `Phprise\DataTransferObject\TransferObject` and adding public typed properties.

```php
use Phprise\DataTransferObject\TransferObject;

class UserDto extends TransferObject
{
    public string $name;
    public int $age;
}

// Create from array
$data = ['name' => 'John Doe', 'age' => 30];
$dto = UserDto::fromArray($data);

echo $dto->name; // John Doe
echo $dto->age;  // 30

// Convert back to array
$array = $dto->toArray();
// ['name' => 'John Doe', 'age' => 30]
```

### Snake Case Magic

The library automatically handles conversion between `snake_case` keys and `camelCase` properties.

```php
class UserProfileDto extends TransferObject
{
    public string $firstName;
    public string $lastName;
}

$data = [
    'first_name' => 'Alice',
    'last_name' => 'Wonderland',
];

$dto = UserProfileDto::fromArray($data);

echo $dto->firstName; // Alice

// Export as snake_case array
$snakeArray = $dto->toSnakeCaseArray();
// ['first_name' => 'Alice', 'last_name' => 'Wonderland']
```

### Nested DTOs

You can nest DTOs. The library will recursively hydrate them.

```php
class AddressDto extends TransferObject
{
    public string $street;
    public string $city;
}

class CustomerDto extends TransferObject
{
    public string $name;
    public AddressDto $address;
}

$data = [
    'name' => 'Bob',
    'address' => [
        'street' => '123 Maker Lane',
        'city' => 'Buildtown',
    ],
];

$dto = CustomerDto::fromArray($data);

echo $dto->address->city; // Buildtown
```

### DateTime Support

Strings in standard formats (ISO 8601 / RFC 3339) are automatically converted to `DateTimeImmutable` objects if typed as such.

```php
class EventDto extends TransferObject
{
    public string $title;
    public \DateTimeImmutable $date;
}

$data = [
    'title' => 'Conference',
    'date' => '2023-10-27T10:00:00+00:00',
];

$dto = EventDto::fromArray($data);
echo $dto->date->format('Y-m-d'); // 2023-10-27
```

### JSON Serialization

```php
$json = $dto->toJson();
// or
$jsonSnake = $dto->toSnakeCaseJson();

$dto = UserDto::fromJson($json);
```

## Philosophy

We follow **The OTAKU Manifesto: Fluid Structure Design**.

1.  **O** - Own your Discipline (Be strict with yourself)
2.  **T** - Tools for Composition (Compose like Unix)
3.  **A** - Armor the Core (Protect the heart of the business)
4.  **K** - Keep Infrastructure Silent (Infrastructure is just a detail)
5.  **U** - Universal Language & Contracts (Speak the user's language via clear contracts)

Please read more about it in [PHILOSOPHY.md](PHILOSOPHY.md).

## License

MIT License. Free to use, modify, and distribute.

## Contributing

See how to contribute in [CONTRIBUTING.md](CONTRIBUTING.md).
