<?php

namespace App\Scraper\Base\Iterators;

/**
 * Class ExpectingIterator
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
class ExpectingIterator implements \Iterator
{
    /**
     * @var \Iterator
     */
    private $inner;
    private $wasValid;

    public function __construct(\Iterator $inner)
    {
        $this->inner = $inner;
    }

    public function next()
    {
        if (!$this->wasValid && $this->valid()) {
            // Just do nothing, because the inner iterator has became valid.
        } else {
            $this->inner->next();
        }
        $this->wasValid = $this->valid();
    }

    public function current()
    {
        return $this->inner->current();
    }

    public function rewind()
    {
        $this->inner->rewind();
        $this->wasValid = $this->valid();
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