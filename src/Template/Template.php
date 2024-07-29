<?php
declare(strict_types=1);

namespace Serapha\Template;

use Serapha\Core\Container;
use carry0987\Sanite\Sanite;
use carry0987\Redis\RedisTool;
use carry0987\Template\Template as TemplateEngine;
use carry0987\Template\Controller\DBController;
use carry0987\Template\Controller\RedisController;

final class Template
{
    private TemplateEngine $template;
    private array $data = [];

    public function __construct(Container $container)
    {
        // Initialize template engine
        $this->template = new TemplateEngine();

        // Set DB for template engine
        $db = new DBController($container->get(Sanite::class));
        $this->template->setDatabase($db);

        // Set Redis for template engine
        $redis = new RedisController($container->get(RedisTool::class));
        $this->template->setRedis($redis);
    }

    /**
     * Set data for the template engine.
     * @param array|string $data
     */
    public function setData(string|array $data, mixed $value = null): self
    {
        if (is_array($data)) {
            $this->data = array_merge($data, $this->data);
            return $this;
        }

        $this->data[$data] = $value;

        return $this;
    }

    /**
     * Get data from the template engine.
     * @param string $key
     * @return mixed
     */
    public function getData(?string $key = null): mixed
    {
        return $key ? $this->data[$key] : $this->data;
    }

    /**
     * Render a template file.
     * @param array $templates
     * @param array $data
     * @return void
     * @throws \Exception
     */
    public function render(array $templates, array $data = []): void
    {
        $data = $this->setData($data)->getData();

        foreach ($templates as $template) {
            $filepath = $this->template->loadTemplate($template);
            if (file_exists($filepath)) {
                // Import array key values as variable names into the current symbol table
                extract($data);
                include($filepath);
            } else {
                throw new \Exception("Template file not found: $template");
            }
        }
    }

    /**
     * Set options for the template engine.
     * @param array $options
     * @return void
     */
    public function setOption(array $options): self
    {
        $this->template->setOptions($options);

        return $this;
    }

    /**
     * Set the path to the assets.
     * @param callable $holder
     * @return void
     */
    public function assetPath(callable $holder): self
    {
        $this->template->assetPath($holder);

        return $this;
    }
}
