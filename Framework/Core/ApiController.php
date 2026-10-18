<?php

declare(strict_types=1);

namespace Framework\Core;

use Framework\Http\Request;

abstract class ApiController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->response->setHeader('Content-Type', 'application/json');
    }

    public function renderJson(array $data): void
    {
        $this->response->setBody(json_encode($data));
    }

    public function send(): void
    {
        $this->response->send();
    }
}
