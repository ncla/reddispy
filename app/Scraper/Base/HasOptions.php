<?php

namespace App\Scraper\Base;

trait HasOptions
{
    protected $options = [];

    protected function mergeOptions($options)
    {
        return $this->options = array_merge($this->options, $options);
    }

    public function options($options)
    {
        return $this->mergeOptions($options);
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getOption($key)
    {
        return $this->options[$key];
    }

    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
    }
}