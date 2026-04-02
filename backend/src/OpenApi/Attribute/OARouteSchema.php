<?php

namespace App\OpenApi\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class OARouteSchema
{
    public function __construct(
        public string $schemaClass,
    ) {}
}
