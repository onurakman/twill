<?php

namespace A17\Twill\Http\Controllers\Front;

use A17\Twill\Services\FileLibrary\S3PrivateToLocal;
use Illuminate\Foundation\Application;

class FileController
{
    public function __invoke($path, Application $app)
    {
        return $app->make(S3PrivateToLocal::class)->getFile($path);
    }
}
