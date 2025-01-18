<?php
namespace App\ORM\Mapping;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Table
{
    public function __construct(public string $name)
    {
    }
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class Column
{
    public function __construct(public ?string $type = null)
    {
    }
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class Id {}