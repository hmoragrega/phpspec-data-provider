<?php declare(strict_types=1);

namespace HMoragrega\PhpSpec\DataProvider\Parser;

use PhpSpec\Loader\Node\ExampleNode;

class DataProviderExtractor
{
    /**
     * Extract the data from the data provider method
     *
     * @param ExampleNode $example
     * @param string $dataProviderMethod
     *
     * @return array
     *
     * @throws \ReflectionException
     */
    public function getProvidedData(ExampleNode $example, string $dataProviderMethod): array
    {
        $specification = $example->getSpecification()->getClassReflection();
        if (!$specification->hasMethod($dataProviderMethod)) {
            return [];
        }

        $subject      = $specification->newInstance();
        $providedData = $specification->getMethod($dataProviderMethod)->invoke($subject);

        return is_array($providedData)
            ? $providedData
            : [];
    }
}