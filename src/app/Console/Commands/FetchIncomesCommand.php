<?php

namespace App\Console\Commands;

use App\Enums\ApiEntity;

class FetchIncomesCommand extends AbstractFetchCommand
{
    protected $signature;
    protected $description;

    public function __construct()
    {
        $this->signature = $this->getSignature();
        $this->description = $this->getDescription();

        parent::__construct();
    }

    protected function getApiEntity(): ApiEntity
    {
        return ApiEntity::INCOMES;
    }
}
