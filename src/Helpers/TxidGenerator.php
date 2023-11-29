<?php
namespace NFService\Sicoob\Helpers;

use Ramsey\Uuid\Uuid;

class TxidGenerator
{
    public static function generate(): string
    {
        return strtoupper(preg_replace('/[^0-9A-Za-z]/', '', Uuid::uuid4()->toString()));
    }
}