<?php

namespace A17\Twill\View\Components\Partials\Navigation;

use A17\Twill\Facades\TwillNavigation;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Primary extends Component
{
    public function render(): View
    {
        return view(
            'twill::partials.navigation._primary_navigation',
            ['linkGroups' => TwillNavigation::buildNavigationTree()]
        );
    }
}
