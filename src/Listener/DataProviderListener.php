<?php declare(strict_types=1);

namespace HMoragrega\PhpSpec\DataProvider\Listener;

use HMoragrega\PhpSpec\DataProvider\Parser\DataProviderExtractor;
use HMoragrega\PhpSpec\DataProvider\Parser\ExampleParser;
use PhpSpec\Event\SuiteEvent;
use PhpSpec\Loader\Node\ExampleNode;
use PhpSpec\Loader\Node\SpecificationNode;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DataProviderListener implements EventSubscriberInterface
{
    /**
     * @var ExampleParser
     */
    private $exampleParser;

    /**
     * @var DataProviderExtractor
     */
    private $dataProviderExtractor;

    /**
     * @param ExampleParser $exampleParser
     * @param DataProviderExtractor $dataProviderExtractor
     */
    public function __construct(ExampleParser $exampleParser, DataProviderExtractor $dataProviderExtractor)
    {
        $this->exampleParser         = $exampleParser;
        $this->dataProviderExtractor = $dataProviderExtractor;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * ['eventName' => 'methodName']
     *  * ['eventName' => ['methodName', $priority]]
     *  * ['eventName' => [['methodName1', $priority], ['methodName2']]]
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            'beforeSuite' => ['beforeSuite', 100],
        ];
    }

    /**
     * @param SuiteEvent $event
     *
     * @throws \ReflectionException
     */
    public function beforeSuite(SuiteEvent $event)
    {
        foreach ($event->getSuite()->getSpecifications() as $specification) {
            $this->addExamplesToSpecification($specification);
        }
    }

    /**
     * @param SpecificationNode $specificationNode
     *
     * @throws \ReflectionException
     */
    private function addExamplesToSpecification(SpecificationNode $specificationNode)
    {
        foreach ($specificationNode->getExamples() as $example) {
            foreach ($this->getExampleFromDataProvider($example) as $newExample) {
                $specificationNode->addExample($newExample);
            }
        }
    }

    /**
     * @param ExampleNode $example
     *
     * @return \Generator
     *
     * @throws \ReflectionException
     */
    private function getExampleFromDataProvider(ExampleNode $example): \Generator
    {
        $dataProviderMethod = $this->exampleParser->getDataProvider($example);
        if (empty($dataProviderMethod)) {
            return null;
        }

        $providedData       = $this->dataProviderExtractor->getProvidedData($example, $dataProviderMethod);
        $exampleFunction    = $example->getFunctionReflection();
        $numberOfParameters = $exampleFunction->getNumberOfParameters();

        foreach ($providedData as $index => $dataRow) {
            $title = $this->buildExampleTitle($index, $numberOfParameters, $dataRow, $example);
            yield new ExampleNode($title, $exampleFunction);
        }
    }

    /**
     * @param int         $index
     * @param int         $numberOfParameters
     * @param array       $dataRow
     * @param ExampleNode $example
     *
     * @return string
     */
    private function buildExampleTitle(int $index, int $numberOfParameters, array $dataRow, ExampleNode $example)
    {
        $title = $index + 1 . ') ' . $example->getTitle();

        if ($numberOfParameters < count($dataRow)) {
            $title .= ': ' . $dataRow[0];
        }

        return $title;
    }
}