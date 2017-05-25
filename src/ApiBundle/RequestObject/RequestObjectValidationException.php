<?php

namespace ApiBundle\RequestObject;


use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class RequestObjectValidationException extends BadRequestHttpException
{
    /**
     * @var ConstraintViolationListInterface|ConstraintViolationInterface[]
     */
    private $errors;

    public function __construct(ConstraintViolationListInterface $errors, IRequestObject $requestObject)
    {
        $className = get_class($requestObject);

        $this->errors = $errors;

        $message = sprintf('Invalid request object "%s".', $className);

        parent::__construct($message, null, null);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getErrorsAsArray()
    {
        $errors = [];

        foreach ($this->errors as $error) {
            $propertyPath = trim($error->getPropertyPath(), '[]');

            $errors[$propertyPath] = [
                'code' => $error->getCode(),
                'message' => $error->getMessage(),
                'invalidValue' => $error->getInvalidValue(),
            ];
        }

        return $errors;
    }

    public function getErrorsAsJson()
    {
        return json_encode($this->getErrorsAsArray());
    }

    public function toArray()
    {
        return $this->getErrorsAsArray();
    }

    public function toJson()
    {
        return $this->getErrorsAsJson();
    }

    public function __toString()
    {
        return $this->getErrorsAsJson();
    }
}