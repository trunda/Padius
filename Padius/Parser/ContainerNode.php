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
 * Container node
 * @author Jakub Truneček
 */
class ContainerNode extends Node
{
    /** @var string */
    private $name;
    /** @var array */  
    private $attrs;
    /** @avr array */
    private $contents = array();
    
    /**
     * @param \Closure $closure
     * @param string $name
     * @param array $attrs
     * @param array $contents 
     */
    public function __construct(\Closure $closure = NULL, $name = NULL, array $attrs = array(), 
            array $contents = array()) 
    {
        parent::__construct($closure);
        $this->name = $name;
        $this->attrs = $attrs;
        $this->contents = $contents;
    }
    
    /** @return string */
    public function getName()
    {
        return $this->name;
    }

    /** @return array */
    public function getAttrs()
    {
        return $this->attrs;
    }

    /** @return array */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * Ads content
     * @param mixed $content 
     */
    public function addContent($content)
    {
        $this->contents[] = $content;
    }
    
}