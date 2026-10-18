<?php

declare(strict_types=1);

namespace App\Controllers\Test;

use Framework\Core\Controller;

class IndexClass extends Controller
{
    public function run(): void
    {
        $this->render('test.index', ['message' => 'Hello from Test IndexClass!']);
    }
}
