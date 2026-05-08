<?php

namespace App\Infrastructure\Persistence;

use App\Models\Company;

class CompanyRepository extends EloquentBaseRepository
{
    public function __construct(Company $model)
    {
        parent::__construct($model);
    }
}
