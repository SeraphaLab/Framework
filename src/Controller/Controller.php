<?php
declare(strict_types=1);

namespace Serapha\Controller;

use Serapha\Template\Template;
use carry0987\Sanite\Sanite;
use carry0987\I18n\I18n;

abstract class Controller
{
    protected Sanite $sanite;
    protected Template $template;
    protected I18n $i18n;

    public function __construct(Sanite $sanite, Template $template, I18n $i18n)
    {
        $this->sanite = $sanite;
        $this->template = $template;
        $this->i18n = $i18n;
    }
}
