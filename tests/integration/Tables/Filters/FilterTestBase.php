<?php

namespace A17\Twill\Tests\Integration\Tables\Filters;

use A17\Twill\Services\Listings\Filters\QuickFilters;
use A17\Twill\Services\Listings\Filters\TableFilters;
use A17\Twill\Tests\Integration\ModulesTestBase;
use App\Http\Controllers\Twill\AuthorController;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FilterTestBase extends ModulesTestBase
{
    public function controllerWithFiltersAndQuickFilters(
        array $filters = [],
        array $quickFilters = [],
        array $active = []
    ): AuthorController {
        $request = Request::create(route('twill.personnel.authors.index'), 'GET', ['filter' => json_encode($active)]);

        return new class($this->app, $request, $filters, $quickFilters) extends AuthorController {
            public function __construct(
                Application $app,
                Request $request,
                public array $testFilters,
                public array $testQuickFilters
            ) {
                parent::__construct($app, $request);

                $this->user = Auth::user();
                $this->routePrefix = 'personnel';
            }

            public function quickFilters(): QuickFilters
            {
                if ($this->testQuickFilters !== []) {
                    return QuickFilters::make($this->testQuickFilters);
                }
                return parent::quickFilters();
            }

            public function filters(): TableFilters
            {
                return TableFilters::make($this->testFilters);
            }
        };
    }
}
