<?php declare(strict_types=1);

namespace HMoragrega\PhpSpec\DataProvider\Parser;

use PhpSpec\Loader\Node\ExampleNode;

class ExampleParser
{
    private const DATA_PROVIDER_REGEX = '/@dataProvider ([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/';

    /**
     * Returns the data provider method looking for the @dataProvider annotation
     *
     * @param ExampleNode $example
     *
     * @return string|null
     */
    public function getDataProvider(ExampleNode $example): ?string
    {
        $method = $example->getFunctionReflection();

        $docComment = $method->getDocComment();
        if (false === $docComment) {
            return null;
        }

        if (!preg_match(self::DATA_PROVIDER_REGEX, $docComment, $matches)) {
            return null;
        }

        return $matches[1];
    }
}
