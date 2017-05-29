<?php

namespace ApiBundle\Middleware;


use ApiBundle\RequestObject\CommandRequestInjector;
use ApiBundle\RequestObject\IRequestInjectableCommand;
use ApiBundle\RequestObject\IRequestObject;
use ApiBundle\RequestObject\RequestObjectErrors;
use ApiBundle\RequestObject\RequestObjectHandler;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ConsoleCommandListener
{
    /**
     * @var RequestObjectHandler
     */
    private $handler;

    public function __construct(RequestObjectHandler $handler)
    {
        $this->handler = $handler;
    }

    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        $input = $event->getInput();

        $command = $event->getCommand();

        $classRef = new \ReflectionClass($command);

        $methods = $classRef->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $methodName = $method->getName();

            if (strpos($methodName, 'set') !== 0 || $method->getNumberOfParameters() > 2) {
                continue;
            }

            $parameters = $method->getParameters();

            $requestObjectClass = null;
            $requestObjectParameterIndex = null;
            $errorsClass = null;

            foreach ($parameters as $index => $parameter) {
                $parameterClass = $parameter->getClass();

                if (!$parameterClass) {
                    continue;
                }

                if ($parameterClass->implementsInterface(IRequestObject::class)) {
                    $requestObjectClass = $parameterClass;
                    $requestObjectParameterIndex = $index;
                    continue;
                }

                if ($parameterClass->getName() === ConstraintViolationListInterface::class || $parameterClass->implementsInterface(ConstraintViolationListInterface::class)) {
                    $errorsClass = $parameterClass;
                    continue;
                }
            }

            if (!$requestObjectClass) {
                continue;
            }


            /** @var IRequestObject $requestObject */
            $requestObject = $requestObjectClass->newInstance();

            $this->handler->fillFromConsoleInput($input, $requestObject);

            $errors = $this->handler->validate($requestObject);

            $arguments = [$requestObject];


            if ($errorsClass) {
                if ($requestObjectParameterIndex === 0) {
                    $arguments = [$requestObject, $errors];
                } else {
                    $arguments = [$errors, $requestObject];
                }
            }

            call_user_func_array([$command, $methodName], $arguments);
        }
    }
}