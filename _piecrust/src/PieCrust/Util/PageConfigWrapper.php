<?php

namespace PieCrust\Util;

use PieCrust\IPage;


/**
 * A class that wraps a page's configuration array
 * and only loads the page when needed.
 *
 * @formatObject
 * @explicitInclude
 * @documentation The configuration header for that page.
 */
class PageConfigWrapper implements \ArrayAccess, \Iterator
{
    protected $page;
    protected $values;
    protected $lazyValues;

    public function __construct(IPage $page)
    {
        $this->page = $page;
    }

    // {{{ ArrayAccess members
    public function offsetExists($offset)
    {
        $this->ensureLoaded();
        $this->ensureLazyLoaded($offset);
        return isset($this->values[$offset]);
    }
    
    public function offsetGet($offset) 
    {
        $this->ensureLoaded();
        $this->ensureLazyLoaded($offset);
        return $this->values[$offset];
    }
    
    public function offsetSet($offset, $value)
    {
        throw new PieCrustException('PageConfigWrapper is read-only.');
    }
    
    public function offsetUnset($offset)
    {
        throw new PieCrustException('PageConfigWrapper is read-only.');
    }
    // }}}
    
    // {{{ Iterator members
    public function rewind()
    {
        $this->ensureLoaded();
        $this->ensureAllLazyLoaded();
        return reset($this->values);
    }
  
    public function current()
    {
        $this->ensureLoaded();
        return current($this->values);
    }
  
    public function key()
    {
        $this->ensureLoaded();
        return key($this->values);
    }
  
    public function next()
    {
        $this->ensureLoaded();
        return next($this->values);
    }
  
    public function valid()
    {
        $this->ensureLoaded();
        return key($this->values) !== null;
    }
    // }}}
    
    protected function ensureLoaded()
    {
        if ($this->values == null)
        {
            $this->values = $this->page->getConfig()->get();

            // Sub-classes can overload `addCustomValues` to add
            // more stuff to the values array besides the page's
            // configuration.
            $this->addCustomValues();
        }
    }

    protected function addCustomValues()
    {
    }

    protected function ensureAllLazyLoaded()
    {
        foreach (array_keys($this->lazyValues) as $name)
        {
            $this->ensureLazyLoaded($name, false);
        }
        $this->lazyValues = null;
    }

    protected function ensureLazyLoaded($name, $consume = true)
    {
        if ($this->lazyValues and isset($this->lazyValues[$name]))
        {
            $loader = $this->lazyValues[$name];
            $this->$loader();

            if ($consume)
            {
                unset($this->lazyValues[$name]);
                if (count($this->lazyValues) == 0)
                    $this->lazyValues = null;
            }
        }
    }
}

