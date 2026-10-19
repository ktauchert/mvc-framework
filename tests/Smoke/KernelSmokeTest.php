<?php

declare(strict_types=1);

namespace Tests\Smoke;

use App\Controllers\Test\IndexClass;
use Framework\Core\Resolver;
use Framework\Http\Request;
use PHPUnit\Framework\TestCase;

final class KernelSmokeTest extends TestCase
{
    public function test_config_paths_exist(): void
    {
        $views = config('dir.views');
        $layout = config('dir.layout');

        $this->assertIsString($views);
        $this->assertIsString($layout);
        $this->assertDirectoryExists($views);
        $this->assertDirectoryExists($layout);
        $this->assertNotSame('', (string) config('app.name'));
    }

    public function test_test_page_renders_html_without_send(): void
    {
        $className = Resolver::resolveRouteToClass('/test');
        $this->assertSame(IndexClass::class, $className);

        $request = new Request('GET', '/test', [], '/test');
        $page = new IndexClass($request);
        $page->run();

        $response = $page->response();
        $body = $response->getBody();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('<html', $body);
        $this->assertStringContainsString('Hello from Test IndexClass!', $body);
    }

    public function test_unknown_path_does_not_resolve(): void
    {
        $this->assertFalse(Resolver::resolveRouteToClass('/no-such-page'));
    }
}
