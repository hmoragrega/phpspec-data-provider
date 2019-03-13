<?php declare(strict_types=1);

namespace spec\HMoragrega\PhpSpec\DataProvider;

use PhpSpec\ObjectBehavior;

class ExampleSpec extends ObjectBehavior
{
    function let()
    {
$definition = <<<'EOF'

namespace HMoragrega\PhpSpec\DataProvider;

class Example
{
    /**
     * @param string $input
     *
     * @return string
     */
    public function uppercase(string $input): string
    {
        return strtoupper($input);
    }

    /**
     * @param string $input
     *
     * @return string
     */
    public function lowercase(string $input): string
    {
        return strtolower($input);
    }

    /**
     * @param \DateTimeInterface $datetime
     *
     * @return string
     */
    public function date(\DateTimeInterface $datetime): string
    {
        return $datetime->format('Y-m-d');
    }
}
EOF;

        if (!class_exists('HMoragrega\PhpSpec\DataProvider\Example'))
        {
            eval($definition);
        }
    }

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
            ["already lowercase",  "foo", "foo"],
            ["first capital",      "Foo", "foo"],
            ["camel case",         "FooBar", "foobar"],
            ["respect spaces",     "Foo Bar", "foo bar"],
            ["unicode characters", "Foo ⌘ Bar", "foo ⌘ bar"],
        ];
    }

    /**
     * @dataProvider itCanGetTheDateFromADatetimeDataProvider
     *
     * @param string $input     String to convert to lowercase
     * @param string $expected  Expected output
     */
    function it_can_get_the_date($input, $expected)
    {
        $this->date($input)->shouldReturn($expected);
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    function itCanGetTheDateFromADatetimeDataProvider(): array
    {
        $today = new \DateTime();

        $tomorrow = clone $today;
        $tomorrow->add(new \DateInterval("P1D"));

        return [
            ["Today",    $today,    $today->format('Y-m-d')],
            ["Tomorrow", $tomorrow, $tomorrow->format('Y-m-d')],
        ];
    }
}
