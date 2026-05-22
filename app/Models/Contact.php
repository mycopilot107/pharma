<?php

namespace App\Models;

/**
 * @deprecated Use Customer model. Alias for backward compatibility.
 */
class Contact extends Customer
{
    protected $table = 'customers';
}
