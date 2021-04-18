<?php
/*
 * Created by Dmytro Zolotar. on 18/04/2021.
 * Copyright (c) 2021. All rights reserved.
 */

namespace App\Traits;

trait HandleErrorsTrait
{
    private array $errors;

    private function hasErrors(): bool
    {
        return ! empty($this->errors);
    }
}
