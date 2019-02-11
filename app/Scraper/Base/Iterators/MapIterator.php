<?php

namespace App\Scraper\Base\Iterators;

/**
 * Class MapIterator
 *
 * See:
 * https://github.com/guzzle/guzzle/issues/2260
 * https://github.com/alexeyshockov/guzzle-dynamic-pool
 * https://stackoverflow.com/questions/42754389/guzzle-pool-wait-for-requests/43525426#43525426
 *
 * Do not extend IteratorIterator, because it cashes the return values somehow!
 *
 * @package App\Scraper\Base\Iterators
 */
class MapIterator implements \Iterator
{
    /**
     * @var \Iterator
     */
    private $inner;
    private $handler;

    public function __construct(\Iterator $inner, callable $handler)
    {
        $this->inner = $inner;
        $this->handler = $handler;
    }

    public function next()
    {
        $this->inner->next();
    }

    public function current()
    {
        return call_user_func($this->handler, $this->inner->current(), $this->inner);
    }

    public function rewind()
    {
        $this->inner->rewind();
    }

    public function key()
    {
        return $this->inner->key();
    }

    public function valid()
    {
        return $this->inner->valid();
    }
}