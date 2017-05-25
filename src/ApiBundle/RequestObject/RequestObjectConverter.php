<?php

namespace ApiBundle\RequestObject;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class RequestObjectConverter implements ParamConverterInterface
{
    /**
     * @var RequestObjectHandler
     */
    private $handler;

    public function __construct(RequestObjectHandler $handler)
    {
        $this->handler = $handler;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $className = $configuration->getClass();

        $requestObject = new $className();

        $this->handler->fillFromRequest($request, $requestObject);

        $errors = $this->handler->validate($requestObject);

        $attributeName = $configuration->getName();

        $request->attributes->set($attributeName, $requestObject);

        $errorsAttributeName = $attributeName . 'Errors';

        if (!$request->attributes->has($errorsAttributeName)) {
            return;
        }

        $request->attributes->set($errorsAttributeName, $errors);
    }

    public function supports(ParamConverter $configuration)
    {
        $implements = class_implements($configuration->getClass());

        return array_key_exists(IRequestObject::class, $implements);
    }
}
