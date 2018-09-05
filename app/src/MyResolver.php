<?php

use DNADesign\Elemental\Models\BaseElement;
use GraphQL\Type\Definition\CustomScalarType;
use SilverStripe\GraphQL\Manager;
use SilverStripe\GraphQL\Scaffolding\Scaffolders\CRUD\ReadOne;

class MyResolver extends ReadOne
{
    public function __construct()
    {
        $dataObjectClass = BaseElement::class;

        parent::__construct($dataObjectClass);
    }

    public function createDefaultArgs(Manager $manager)
    {
        $defaultArgs = parent::createDefaultArgs($manager);

        $defaultArgs['SummaryData'] = [
            'type' => new CustomScalarType([
                'name' => 'ObjectType',
                'serialise' => function ($value) {
                    return (object) $value;
                },
                'parseValue' => function ($value) {
                    return (array) $value;
                },
                'parseLiteral' => function ($ast) {
                    return $ast->value;
                },
            ]),
        ];

        return $defaultArgs;
    }
}
