# ZSchema

## Table of contents

-   [Introduction](#introduction)
-   [Installation](#installation)
-   [Basic usage](#basic_usage)
-   [Types](#types)
-   [Strings](#strings)
    -   [Validations](#strings-validations)
    -   [Transforms](#strings-transforms)
-   [Numbers](#numbers)
    -   [Types](#numbers-types)
    -   [Validations](#numbers-validations)
    -   [Transforms](#numbers-transforms)
-   [Arrays](#arrays)
-   [Collections](#collections)
-   [Methods](#methods)
-   [Messages](#messages)

## Introduction

Validates schemas and datatypes in a simple way, strongly inspired by [Zod](https://github.com/colinhacks/zod)

## Installation

```bash
composer require lullaby6/zschema
```

## Basic usage

Creating a simple integer schema

```php
require __DIR__ . '/vendor/autoload.php';

use Lullaby6\ZSchema\ZSchema;

// creating the integer schema
$int_schema = ZSchema::int();

// parsing
print_r($int_schema->safe_parse(5)); // output: ["success" => true]
print_r($int_schema->safe_parse("hello")); // output: ["success" => false, "message" => ...]
```

Creating a array schema

```php
require __DIR__ . '/vendor/autoload.php';

use Lullaby6\ZSchema\ZSchema;

// creating user schema
$user_schema = ZSchema::array([
    "first_name" => ZSchema::string()->min_length(3)->required(),
    "last_name" => ZSchema::string()->min_length(3),
    "email" => ZSchema::string()->email()->required(),
]);

print_r($user_schema->safe_parse([
    "first_name" => "John",
    "last_name" => "Doe",
    "email" => "john@doe.com",
])); // output: ["success" => true]

print_r($user_schema->safe_parse([
    "first_name" => "John",
    "last_name" => "Doe",
    "email" => "johndoe.com",
])); // output: ["success" => false, "message" => "email is not a valid email"]
```

[Return to table of contents](#table-of-contents)

## Types

```php
ZSchema::int()
ZSchema::float()
ZSchema::string()
ZSchema::bool()
ZSchema::array()
ZSchema::null()
```

[Return to table of contents](#table-of-contents)

## Strings

Strings have many types of specific validations

### Strings validations

```php
ZSchema::string()->required()
ZSchema::string()->not_empty()
ZSchema::string()->max_length() // the arg must be a integer, example: max_length(5)
ZSchema::string()->min_length() // the arg must be a integer, example: min_length(5)
ZSchema::string()->length() // the arg must be a integer, example: max_length(5)
ZSchema::string()->email()
ZSchema::string()->url()
ZSchema::string()->uuid()
ZSchema::string()->ipv4()
ZSchema::string()->ipv6()
ZSchema::string()->regex() // the arg must be a regex
ZSchema::string()->includes() // the arg must be a string, example: includes("http")
ZSchema::string()->not_includes() // the arg must be a string, example: not_includes("google")
ZSchema::string()->starts_with() // the arg must be a string, example: starts_with("http")
ZSchema::string()->not_starts_with() // the arg must be a string, example: not_starts_with("http")
ZSchema::string()->ends_with() // the arg must be a string, example: ends_with(".com")
ZSchema::string()->not_ends_with() // the arg must be a string, example: not_ends_with(".exe")
ZSchema::string()->date() // under review
ZSchema::string()->time() // under review
ZSchema::string()->datetime() // under review
```

### Strings transforms

The transforms methods modify the value returned by the parse

```php
ZSchema::string()->trim()
ZSchema::string()->to_lower_case()
ZSchema::string()->to_upper_case()
```

Example

```php
echo ZSchema::string->to_lower_case()->parse("Hello World!") // output: "hello world!"
```

[Return to table of contents](#table-of-contents)

## Numbers

Validation and transformations methods work for both int and float

### Numbers types

```php
ZSchema::int()
ZSchema::float()
```

### Numbers validations

```php
ZSChema::int()->required()
ZSChema::int()->not_empty()
ZSChema::int()->max()  // the arg must be a integer, example: max(100)
ZSChema::int()->min() // the arg must be a integer, example: min(0)
ZSChema::int()->positive()
ZSChema::int()->nonpositive()
ZSChema::int()->negative()
ZSChema::int()->nonnegative()
```

### Numbers transforms

The transforms methods modify the value returned by the parse

```php
ZSchema::int()->to_max() // the arg must be a integer, example: to_max(100)
ZSchema::int()->to_min() // the arg must be a integer, example: to_min(0)
```

Example

```php
echo ZSchema::int->to_max(25)->parse(10000) // output: 25
```

## Arrays

The value of the array keys must be an instance of ZSchema, otherwise it will throw an error when creating a schema.

Example:

```php
// BAD
ZSchema::array([
    "email" =>...
])

// GOOD
ZSchema::array([
    "email" => ZSchema::string()->email()
])
```

the value of the key can be any type of zschema

```php
ZSchema::array([
    "day" => ZSchema::int()
])
```

[Return to table of contents](#table-of-contents)

## Collections

`ZSchema::collection()` allows you to validate an array of items where each item must match a specific schema. It returns an instance of Illuminate\Support\Collection, enabling the use of Laravel's powerful collection methods immediately after validation.

Example:

```php
use Lullaby6\ZSchema\ZSchema;

// 1. Define the schema for a single item (e.g., a user)
$user_schema = ZSchema::array([
    "name" => ZSchema::string()->min_length(2),
    "role" => ZSchema::string()
]);

// 2. Define the collection schema wrapper
$users_list_schema = ZSchema::collection($user_schema);

// 3. Raw input data
$input = [
    ["name" => "Admin", "role" => "admin"],
    ["name" => "User",  "role" => "guest"],
];

// 4. Parse returns an Illuminate\Support\Collection
$collection = $users_list_schema->parse($input);

// Now you can use Laravel Collection methods!
$admins = $collection->where('role', 'admin');

print_r($admins->all());
// output: [ 0 => ["name" => "Admin", "role" => "admin"] ]
```

[Return to table of contents](#table-of-contents)

## Methods

### parse

The parse method executes the validations specified in the method value, if the validation fails it will throw an exception with an error message

```php
ZSchema::int()->parse(5) // return 5
ZSchema::int()->parse("hola") // throws Error
```

### safe_parse

Unlike the parse method, when the validation fails it will not throw an error, instead it will return an array with the message and the status of the validation.

```php
ZSchema::int()->safe_parse(5) // return ["success" => true, "value" => 5]
ZSchema::int()->safe_parse("hola") // return ["success" => false, message => ..., "value" => "hola"]
```

### get_validations()

```php
ZSchema::string()->email()->get_validations() // return ["email" => true]
```

### get_transforms()

```php
ZSchema::string()->to_lower_case()->get_transforms() // return ["to_lower_case" => true]
```

[Return to table of contents](#table-of-contents)

## Messages

### Type error message

```php
// by default
ZSchema::int()->safe_parse("world") // return ["sucess" => false, "message" => "world is not a valid int", ...]

// with custom type error message
ZSchema::int("The value is not a number")->safe_parse("world") // return ["sucess" => false, "message" => "The value is not a number", ...]
```

but for arrays the second argument is the message

```php
$user_schema = ZSchema::array([
    "first_name" => ZSchema::string()->min_length(3)->required(),
    "last_name" => ZSchema::string()->min_length(3),
    "email" => ZSchema::string()->email()->required(),
], "The user value is not valid");
```

### Validations error messages

for validations it is a bit more of the same, in validations where no argument is required to validate, the argument will be the error message, if the validation method has an argument, then it will be the second argument

```php
ZSchema::string()->email("The e-mail is not valid")->max_length(100, "The e-mail must not contain more than 100 characters")
```

[Return to table of contents](#table-of-contents)
