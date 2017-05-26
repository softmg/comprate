<?php

namespace ParsingBundle\RequestObjects;


use ApiBundle\RequestObject\IRequestObject;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ParsingPaginator implements IRequestObject
{
    /**
     * @var int
     */
    public $page;

    /**
     * @var int
     */
    public $pageSize;

    /**
     * @var int
     */
    public $totalPages;

    /**
     * @var
     */
    public $items;

    public function __construct()
    {
        $this->page = 1;
        $this->items = [];
    }

    /**
     * @return Constraint[]
     */
    public function rules()
    {
        return [
            'page' => [
                new NotBlank(),
                new Range(['min' => 1]),
                new Callback(['callback' => [$this, 'pageValidate']]),
            ],

            'pageSize' => [
                new Range(['min' => 1]),
            ],

            'totalPages' => [
                new Range(['min' => 0]),
            ],

            'items' => [
                new Type(['type' => 'array']),
            ],
        ];
    }

    public function pageValidate($page, ExecutionContextInterface $context)
    {
        if (null === $this->totalPages) {
            return;
        }

        if ($page > $this->totalPages) {
            $context->addViolation(sprintf('The page(%s) cannot be greater than totalPages(%s)', $page, $this->totalPages));
        }
    }
}