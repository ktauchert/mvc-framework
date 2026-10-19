<?php

declare(strict_types=1);

namespace Framework\Core;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Core\Resolver;
use Framework\Page\MetaInfo;
use Framework\Page\PageBuilder;

abstract class Controller
{
    protected Request $request;
    protected Response $response;
    protected MetaInfo $metaInfo;
    protected PageBuilder $pageBuilder;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->response = new Response();
        $this->metaInfo = new MetaInfo();
        $this->pageBuilder = new PageBuilder($this->metaInfo);
    }

    public function render(string $view, $data = []): void
    {

        extract($data, EXTR_SKIP);

        $resolvedViewPath = Resolver::resolveView($view);

        ob_start();
        require $resolvedViewPath;
        $content = ob_get_clean();

        $this->response->setBody(
            $this->pageBuilder->buildFullPage($content)
        );
    }
    public function send(): void
    {
        $this->response->send();
    }

    public function response(): Response
    {
        return $this->response;
    }

    abstract public function run(): void;

}
