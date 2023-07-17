<?php

namespace App\Form;

use Symfony\Component\Form\DataTransformerInterface;

class JsonArrayTransformer implements DataTransformerInterface
{
    public function transform($value): string
    {

        return json_encode($value);
    }

    public function reverseTransform($value): array
    {

        return json_decode($value, true);
    }
}
