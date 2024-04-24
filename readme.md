# ZSchema

## Table of contents

- [Basic usage](#basic_usage)
- [Types](#types)
- [Strings](#strings)
    - [Validations](#strings-validations)
    - [Transforms](#strings-transforms)
- [Numbers](#numbers)
    - [Types](#numbers-types)
    - [Validations](#numbers-validations)
    - [Transforms](#numbers-transforms)
- [Arrays](#arrays)
- [Methods](#methods)


## Basic usage

Creating a simple integer schema

```php
include_once "./zschema.php";

// creating the integer schema
$int_schema = ZSchema::int();

// parsing
print_r($int_schema->safe_parse(5)); // output: ["success" => true]
print_r($int_schema->safe_parse("hello")); // output: ["success" => false, "message" => ...]
```

Creating a array schema

```php
include_once "./zschema.php";

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

## Types

```php
ZSchema::int()
ZSchema::float()
ZSchema::string()
ZSchema::bool()
ZSchema::array()
ZSchema::null()
```

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

## Methods

### parse

```php
ZSchema::int()->parse("hola")
```

### safe_parse

```php
ZSchema::int()->safe_parse("hola")
```