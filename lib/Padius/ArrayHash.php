<?php

/**
 * Copyright (c) 2012 Jakub Truneček
 * 
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 * 
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */
/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Padius;

/**
 * Provides objects to work as array.
 *
 * @author     David Grudl
 */
class ArrayHash extends \stdClass implements \ArrayAccess, 
        \Countable, \IteratorAggregate
{

    /**
     * @param  array to wrap
     * @param  bool
     * @return ArrayHash
     */
    public static function from($arr, $recursive = TRUE)
    {
        $obj = new static;
        foreach ($arr as $key => $value) {
            if ($recursive && is_array($value)) {
                $obj->$key = static::from($value, TRUE);
            } else {
                $obj->$key = $value;
            }
        }
        return $obj;
    }

    /**
     * Returns an iterator over all items.
     * @return \RecursiveArrayIterator
     */
    public function getIterator()
    {
        return new \RecursiveArrayIterator($this);
    }

    /**
     * Returns items count.
     * @return int
     */
    public function count()
    {
        return count((array) $this);
    }

    /**
     * Replaces or appends a item.
     * @param  mixed
     * @param  mixed
     * @return void
     */
    public function offsetSet($key, $value)
    {
        if (!is_scalar($key)) { // prevents NULL
            throw new InvalidArgumentException(
                "Key must be either a string or an integer, " 
                    . gettype($key) . " given."
            );
        }
        $this->$key = $value;
    }

    /**
     * Returns a item.
     * @param  mixed
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->$key;
    }

    /**
     * Determines whether a item exists.
     * @param  mixed
     * @return bool
     */
    public function offsetExists($key)
    {
        return isset($this->$key);
    }

    /**
     * Removes the element from this list.
     * @param  mixed
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->$key);
    }

}
