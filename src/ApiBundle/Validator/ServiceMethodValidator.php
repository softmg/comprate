<?php

namespace ApiBundle\Validator;


use ApiBundle\Constraints\ServiceMethod;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ServiceMethodValidator extends ConstraintValidator
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ServiceMethod || !$value) {
            return;
        }

        if (!$this->container->has($constraint->service)) {
            throw new \LogicException(sprintf('The service with alias "%s" not found.', $constraint->service));
        }

        $service = $this->container->get($constraint->service);

        $methodName = $constraint->method ?: '__invoke';

        if (!method_exists($service, $methodName)) {
            throw new \LogicException(sprintf('The service "%s" no has the "%s" method.', $constraint->service, $constraint->method));
        }

        if ($constraint->reverseCheck === !(bool)call_user_func([$service, $methodName], $value)) {
            return;
        }

        $this
            ->context
            ->addViolation($constraint->message, [
                '{{ service }}' => $constraint->service,
                '{{ method }}' => $constraint->method,
                '{{ value }}' => $value,
                '{{ checkType }}' => $constraint->reverseCheck ? 'true' : 'false',
            ]);
    }
}
