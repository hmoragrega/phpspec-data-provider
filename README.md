# PhpSpec Data Provider
[![Build Status](https://travis-ci.org/hmoragrega/phpspec-data-provider.svg?branch=master)](https://travis-ci.org/hmoragrega/phpspec-data-provider)

This extension allows to use data provider functions with PhpSpec

_Disclaimer: This is a restart of https://github.com/coduo/phpspec-data-provider-extension_


## Requirements
This extension requires `phpspec >=4`

## Installation
Require the package with composer
```
$ composer require --dev hmoragrega/phpspec-data-provider
```

Add the extension to the `phpspec.yml` configuration file
```
extensions:
  HMoragrega\PhpSpec\DataProvider\DataProviderExtension: ~
```

## How to use
A data provider method should return an array of data, where every row will be use to execute the test where the data provider is used with the given parameters

```php
/**
 * @dataProvider itCanConvertAStringToLowercaseDataProvider
 *
 * @param string $input     String to convert to lowercase
 * @param string $expected  Expected output
 */
function it_can_convert_a_string_to_lowercase($input, $expected)
{
    $this->lowercase($input)->shouldReturn($expected);
}

function itCanConvertAStringToLowercaseDataProvider(): array
{
    return [
        ["already lowercase",  "foo",       "foo"],
        ["first capital",      "Foo",       "foo"],
        ["camel case",         "FooBar",    "foobar"],
        ["respect spaces",     "Foo Bar",   "foo bar"],
        ["unicode characters", "Foo ⌘ Bar", "foo ⌘ bar"],
    ];
}
```

When executed this would generate an output like:
```
  80  ✔ can convert a string to lowercase
  80  ✔ 1) it can convert a string to lowercase: already lowercase
  80  ✔ 2) it can convert a string to lowercase: first capital
  80  ✔ 3) it can convert a string to lowercase: camel case
  80  ✔ 4) it can convert a string to lowercase: respect spaces
  80  ✔ 5) it can convert a string to lowercase: unicode characters
```

**NOTE:** You can omit the first extra parameter, although every example title will be the same
```php
/**
 * @dataProvider itCanConvertAStringToUppercaseDataProvider
 *
 * @param string $input     String to convert to uppercase
 * @param string $expected  Expected output
 */
function it_can_convert_a_string_to_uppercase($input, $expected)
{
    $this->uppercase($input)->shouldReturn($expected);
}

function itCanConvertAStringToUppercaseDataProvider(): array
{
    return [
        ["foo", "FOO"],
        ["bar", "BAR"],
    ];
}
```

```
  61  ✔ can convert a string to uppercase
  61  ✔ 1) it can convert a string to uppercase
  61  ✔ 2) it can convert a string to uppercase
```