<?php declare(strict_types=1);

namespace HMoragrega\PhpSpec\DataProvider\Maintainer;

use HMoragrega\PhpSpec\DataProvider\Parser\DataProviderExtractor;
use HMoragrega\PhpSpec\DataProvider\Parser\ExampleParser;
use PhpSpec\Loader\Node\ExampleNode;
use PhpSpec\Runner\CollaboratorManager;
use PhpSpec\Runner\Maintainer\Maintainer;
use PhpSpec\Runner\MatcherManager;
use PhpSpec\Specification;

class DataProviderMaintainer implements Maintainer
{
    const EXAMPLE_NUMBER_PATTERN = '/^(\d+)\)/';

    /**
     * @var ExampleParser
     */
    private $exampleParser;

    /**
     * @var DataProviderExtractor
     */
    private $dataProviderExtractor;

    /**
     * @var array[]
     */
    private $providerData = [];

    /**
     * @param ExampleParser         $exampleParser
     * @param DataProviderExtractor $dataProviderExtractor
     */
    public function __construct(ExampleParser $exampleParser, DataProviderExtractor $dataProviderExtractor)
    {
        $this->exampleParser         = $exampleParser;
        $this->dataProviderExtractor = $dataProviderExtractor;
    }

    /**
     * @param ExampleNode $example
     *
     * @return boolean
     * @throws \ReflectionException
     */
    public function supports(ExampleNode $example): bool
    {
        $dataProviderMethod = $this->exampleParser->getDataProvider($example);

        if (isset($this->providerData[$dataProviderMethod])) {
            return true;
        }

        $providedData = $this->dataProviderExtractor->getProvidedData($example, $dataProviderMethod);

        foreach ($providedData as $dataRow) {
            if (!is_array($dataRow)) {
                return false;
            }
        }

        $this->providerData[$dataProviderMethod] = $providedData;

        return true;
    }

    /**
     * @param ExampleNode         $example
     * @param Specification       $context
     * @param MatcherManager      $matchers
     * @param CollaboratorManager $collaborators
     */
    public function prepare(ExampleNode $example, Specification $context, MatcherManager $matchers, CollaboratorManager $collaborators): void
    {
        $exampleNum   = $this->getExampleNumber($example->getTitle());
        $providedData = $this->providerData[$this->exampleParser->getDataProvider($example)];

        if (!array_key_exists($exampleNum, $providedData)) {
            return;
        }

        $data               = $providedData[$exampleNum];
        $function           = $example->getFunctionReflection();
        $numberOfParameters = $function->getNumberOfParameters();

        foreach ($function->getParameters() as $position => $parameter) {
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
     * @param ExampleNode         $example
     * @param Specification       $context
     * @param MatcherManager      $matchers
     * @param CollaboratorManager $collaborators
     */
    public function teardown(ExampleNode $example, Specification $context, MatcherManager $matchers, CollaboratorManager $collaborators): void
    {
        unset($this->providerData[$this->exampleParser->getDataProvider($example)]);
    }

    /**
     * @return integer
     */
    public function getPriority(): int
    {
        return 50;
    }

    /**
     * @param string $title
     *
     * @return int
     */
    private function getExampleNumber(string $title): int
    {
        if (!preg_match(self::EXAMPLE_NUMBER_PATTERN, $title, $matches)) {
            return 0;
        }

        return (int) $matches[1] - 1;
    }
}