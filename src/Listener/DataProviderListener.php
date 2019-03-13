<?php

namespace HMoragrega\PhpSpec\DataProvider\Listener;

use PhpSpec\Event\SpecificationEvent;
use PhpSpec\Loader\Node\ExampleNode;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DataProviderListener implements EventSubscriberInterface
{
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
        return ['beforeSpecification' => ['beforeSpecification']];
    }

    public function beforeSpecification(SpecificationEvent $event)
    {
        $examplesToAdd = [];

        foreach ($event->getSpecification()->getExamples() as $example) {

            $dataProviderMethod = $this->getDataProvider($example->getFunctionReflection());
            if (!$dataProviderMethod) {
                continue;
            }

            if (!$example->getSpecification()->getClassReflection()->hasMethod($dataProviderMethod)) {
                continue;
            }

            $subject = $example->getSpecification()->getClassReflection()->newInstance();
            $providedData = $example->getSpecification()->getClassReflection()->getMethod($dataProviderMethod)->invoke($subject);

            if (!is_array($providedData)) {
                continue;
            }

            $numberOfParameters = $example->getFunctionReflection()->getNumberOfParameters();

            foreach ($providedData as $index => $dataRow) {

                $title  = $index+1 . ') '.$example->getTitle();

                if ($numberOfParameters < count($dataRow)) {
                    $title .= ': '.$dataRow[0];
                }

                $examplesToAdd[] = new ExampleNode($title, $example->getFunctionReflection());
            }

            $example->setTitle("- " . $example->getTitle());
        }

        foreach ($examplesToAdd as $example) {
            $event->getSpecification()->addExample($example);
        }
    }

    /**
     * @param \ReflectionFunctionAbstract $method
     *
     * @return string|null
     */
    private function getDataProvider(\ReflectionFunctionAbstract $method): ?string
    {
        $docComment = $method->getDocComment();
        if (false === $docComment) {
            return null;
        }

        if (0 === preg_match('/@dataProvider ([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/', $docComment, $matches)) {
            return null;
        }

        return $matches[1];
    }
}