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

namespace Padius;

/**
 * Parser. This class needs scanner and context to parse input and 
 * produce output
 * 
 * @author Jkaub Trunecek
 */
class Parser
{
    /** @var Context */
    private $context;
    /** @var string */
    private $prefix;
    /** @var Scanner\IScanner */
    private $scanner;    
    /** @var array */
    private $stack;
    /** @var array */
    private $tokens;
    
    /**
     * @param Context $context
     * @param string $prefix
     * @param Scnanner\IScanner $scanner 
     */
    public function __construct(Context $context, $prefix = 'padius', 
            Scnanner\IScanner $scanner = NULL)
    {
        $this->context = $context;
        $this->prefix = $prefix;
        $this->scanner = $scanner ? $scanner : new Parser\Scanner();
    }
    
    /**
     * Parses input and produce output
     * @param  string $string
     * @return string 
     */
    public function parse($string)
    {
        $this->stack = array(new Parser\ContainerNode(function($t) {
            return Utils::arrayToString($t->getContents());
        }));
        $this->tokens = $this->scanner->scan($this->prefix, $string);        
        $this->stackUp();                
        return (string) end($this->stack);
    }
    
    /**
     * Used to check syntax and builds all callbacks
     * @param type $t
     * @return type 
     */
    protected function stackUp()
    {
        $context = $this->context;
        foreach ($this->tokens as $t) {            
            if (is_string($t)) {
                end($this->stack)->addContent($t);
                continue;
            }            
            switch ($t['flavor']) {
                case Parser\IScanner::FLAVOR_OPEN:                    
                    array_push(
                            $this->stack, new Parser\ContainerNode(
                                    NULL, $t['name'], $t['attrs'])
                            );                    
                    break;
                case Parser\IScanner::FLAVOR_SELF:
                    end($this->stack)->addContent(
                            new Parser\Node(function() use ($t, $context) {
                        return $context->renderTag($t['name'], $t['attrs']);
                    }));
                    break;
                case Parser\IScanner::FLAVOR_END:
                    $popped = array_pop($this->stack);
                    if ($popped->getName() !== $t['name']) {
                        throw new \Exception('Not valid tag');
                    }
                    $popped->onParse(function($t) use ($context) {
                        return $context->renderTag(
                                $t->getName(), $t->getAttrs(), function() use ($t) {
                            return Utils::arrayToString($t->getContents());
                        });
                    });
                    end($this->stack)->addContent($popped);
                    break;
            }
        }
        if (count($this->stack) !== 1) {            
            throw new \Exception('Missing end tag');
        }
    }
}