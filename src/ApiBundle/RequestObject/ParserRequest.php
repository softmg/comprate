<?php

namespace ApiBundle\RequestObject;


use ParsingBundle\Entity\ParsingSite;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;

class ParserRequest implements IRequestObject
{
    public $site_code;

    public function __construct()
    {
        $this->site_code = ParsingSite::AVITO;
    }

    /**
     * @return Constraint[]
     */
    public function rules()
    {
        return [
            'site_code' => [
                new NotBlank(),
                new Choice([
                    'choices' => [
                        ParsingSite::AVITO,
                        ParsingSite::BENCHMARK,
                        ParsingSite::PCPARTPICKER,
                        ParsingSite::YANDEX_MARKET,
                    ]
                ]),
            ],
        ];
    }
}