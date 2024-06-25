<?php
namespace Serapha\Template;

use Serapha\Core\Container;
use carry0987\Sanite\Sanite;
use carry0987\Redis\RedisTool;
use carry0987\Template\Template as TemplateEngine;
use carry0987\Template\Controller\DBController;
use carry0987\Template\Controller\RedisController;

class Template
{
    private TemplateEngine $template;

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
     * Render a template file.
     * @param array $templates
     * @param array $data
     * @return void
     * @throws \Exception
     */
    public function render(array $templates, array $data = []): void
    {
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
}
