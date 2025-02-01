<?php
namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class Address extends Constraint
{
    public string $message = 'The address is not valid. It might be a formatting issue. Try copy-pasting the address from Google Maps.';
}