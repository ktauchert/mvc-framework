<?php

declare(strict_types=1);

namespace Framework\Page;

class PageBuilder
{
    public function __construct(public MetaInfo $metaInfo) {}

    /**
     * Build the HTML page header with the given content.
     *
     * @return string The complete HTML page as a string.
     */
    private function buildPageHeader(): string
    {
        ob_start();
        $content = config('dir.layout') . 'partials/_header.php';
        require $content;
        $headerContent = ob_get_clean();
        return $headerContent;
    }


    /**
     * Build the complete HTML page with the given content.
     *
     * @param string $content The content to be included in the page.
     * @return string The complete HTML page as a string.
     */
    public function buildFullPage(string $content): string
    {
        $header = $this->buildPageHeader();
        $footer = $this->buildPageFooter();

        [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'author' => $author,
            'favicon' => $favicon,
            'css' => $css,
            'js' => $js
        ] = $this->metaInfo->getMetaData();


        $file = config('dir.layout') . 'page.php';

        ob_start();
        require $file;
        $htmlPageContent = ob_get_clean();

        return $htmlPageContent;
    }

    /**
     * Build the HTML page footer.
     *
     * @return string The HTML content of the page footer.
     */
    private function buildPageFooter(): string
    {
        $content = config('dir.layout') . 'partials/_footer.php';

        ob_start();
        require $content;
        $footerContent = ob_get_clean();

        return $footerContent;
    }


}
