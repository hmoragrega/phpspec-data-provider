<?php

namespace HMoragrega\PhpSpec\DataProvider\Maintainer;

use PhpSpec\Loader\Node\ExampleNode;
use PhpSpec\Runner\CollaboratorManager;
use PhpSpec\Runner\Maintainer\Maintainer;
use PhpSpec\Runner\MatcherManager;
use PhpSpec\Specification;

class DataProviderMaintainer implements Maintainer
{
    const EXAMPLE_NUMBER_PATTERN = '/^(\d+)\)/';

    /**
     * @param ExampleNode $example
     *
     * @return boolean
     * @throws \ReflectionException
     */
    public function supports(ExampleNode $example): bool
    {
        $docComment = $example->getFunctionReflection()->getDocComment();
        if (false === $docComment) {
            return false;
        }

        if (0 === preg_match('/@dataProvider ([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/', $docComment, $matches)) {
            return false;
        }

        $dataProviderMethod = $matches[1];

        if (!$example->getSpecification()->getClassReflection()->hasMethod($dataProviderMethod)) {
            return false;
        }

        $subject = $example->getSpecification()->getClassReflection()->newInstance();
        $providedData = $example->getSpecification()->getClassReflection()->getMethod($dataProviderMethod)->invoke($subject);

        if (!is_array($providedData)) {
            return false;
        }

        foreach ($providedData as $dataRow) {
            if (!is_array($dataRow)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param ExampleNode $example
     * @param Specification $context
     * @param MatcherManager $matchers
     * @param CollaboratorManager $collaborators
     *
     * @throws \ReflectionException
     */
    public function prepare(ExampleNode $example, Specification $context, MatcherManager $matchers, CollaboratorManager $collaborators): void
    {
        $exampleNum   = $this->getExampleNumber($example->getTitle());
        $providedData = $this->getDataFromProvider($example);

        if (!array_key_exists($exampleNum, $providedData)) {
            return ;
        }

        $data = $providedData[$exampleNum];

        foreach ($example->getFunctionReflection()->getParameters() as $position => $parameter) {
            $numberOfParameters = $example->getFunctionReflection()->getNumberOfParameters();
            if ($numberOfParameters < count($data)) {
                $position++;
            }

            if (!isset($data[$position])) {
                continue;
            }
            $collaborators->set($parameter->getName(), $data[$position]);
        }
    }

    /**
     * @param ExampleNode $example
     *
     * @return bool|mixed
     * @throws \ReflectionException
     */
    private function getDataFromProvider(ExampleNode $example)
    {
        $docComment = $example->getFunctionReflection()->getDocComment();
        if (false === $docComment) {
            return [];
        }

        if (0 === preg_match('/@dataProvider ([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/', $docComment, $matches)) {
            return [];
        }

        $dataProviderMethod = $matches[1];

        if (!$example->getSpecification()->getClassReflection()->hasMethod($dataProviderMethod)) {
            return array();
        }

        $subject = $example->getSpecification()->getClassReflection()->newInstance();
        $providedData = $example->getSpecification()->getClassReflection()->getMethod($dataProviderMethod)->invoke($subject);

        return (is_array($providedData)) ? $providedData : array();
    }

    /**
     * @param ExampleNode $example
     * @param Specification $context
     * @param MatcherManager $matchers
     * @param CollaboratorManager $collaborators
     */
    public function teardown(ExampleNode $example, Specification $context, MatcherManager $matchers, CollaboratorManager $collaborators): void
    {

    }

    /**
     * @return integer
     */
    public function getPriority(): int
    {
        return 50;
    }

    /**
     * @param $title
     * @return int
     */
    private function getExampleNumber($title)
    {
        if (0 === preg_match(self::EXAMPLE_NUMBER_PATTERN, $title, $matches)) {
            return 0;
        }

        return (int) $matches[1] - 1;
    }
}