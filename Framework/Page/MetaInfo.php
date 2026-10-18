<?php

declare(strict_types=1);

namespace Framework\Page;

class MetaInfo
{
    public string $title = 'Default Title';
    public string $description = 'Default Description';
    public string $keywords = 'default, keywords';
    public string $author = 'Default Author';
    public string $favicon = '/images/favicon.ico';
    public string $css = '/css/app.css';
    public string $js = '/js/app.js';

    public function __construct() {}

    public function getMetaData(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'keywords' => $this->keywords,
            'author' => $this->author,
            'favicon' => $this->favicon,
            'css' => $this->css,
            'js' => $this->js,
        ];
    }
}
