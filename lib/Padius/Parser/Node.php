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
namespace Padius\Parser;

/**
 * Data holder class used by parser
 * @author Jakub Trunecek
 */
class Node
{
    /** @var \Closure */
    private $closure;
    
    /** @param \Closure $closure */
    public function __construct(\Closure $closure = NULL)
    {
        $this->closure = $closure;
    }
    
    /**
     * Sets the closure, which is evulated if
     * is tag evulated
     * @param \Closure $closure 
     */
    public function onParse(\Closure $closure)
    {
        $this->closure = $closure;
    }
    
    /**
     * Expands the tag
     * @return String 
     */
    public function __toString()
    {
        try {
            $closure = $this->closure;
            return !is_null($closure) ? (string) $closure($this) : '';
        } catch (\Exception $ex) {
            trigger_error(dump($ex), E_USER_ERROR);
        }        
    }
    
}