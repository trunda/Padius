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
 * Base scanner implementation
 * @author Jakub Trunecek
 */
class Scanner implements IScanner
{    
    /** @var string */
    private static $pattern = "/<%s:([\w:]+?)(\s+(?:\w+\s*=\s*(?:\"[^\"]*?\"|'[^']*?')\s*)*|)(\/?)>\r?\n?|<\/%s:([\w:]+?)\s*>\r?\n?/mu";
    
    /**
     * Scans input source code and returns nodes array representing texts and
     * tags
     * 
     * @param  string $prefix
     * @param  string $data
     * @return array 
     */
    public function scan($prefix, $data)
    {
        $pattern = sprintf(self::$pattern, $prefix, $prefix);                
        $nodes = array();        
        if ($m = self::match($pattern, $data)) {                                   
            $rem = '';
            while($m) {
                @list(, $startTag, $attrs, $selfClosing, $endTag) = $m;            
                $flavor = $selfClosing[0] == '/' 
                    ? IScanner::FLAVOR_SELF 
                    : ($endTag ? IScanner::FLAVOR_END : IScanner::FLAVOR_OPEN);                
                $pre = substr($data, 0, $m[0][1]);                
                if (!preg_match('/^[\W\n\r]*$/', $pre)) {
                    $nodes[] = $pre;
                }
                $post = substr($data, $m[0][1]);
                $nodes[] = array(
                    'prefix' => $prefix, 
                    'name'   => ($endTag) ? $endTag[0] : $startTag[0],
                    'flavor' => $flavor,
                    'attrs'  => self::parseAttrs($attrs[0]),
                );
                $data = preg_replace($pattern, '', $post, 1);
                $rem = $data;
                $m = self::match($pattern, $data);
            }      
            if (!preg_match('/^\W?$/', $rem)) {
                $nodes[] = $rem;
            }
        } else {
            $nodes[] = $data;
        }
        return $nodes;
    }
    
    /**
     * Parses arguments to array
     * @param  string $attrs
     * @return array 
     */
    private static function parseAttrs($attrs)
    {
      $result = array();
      $re = "/(\w+?)\s*=\s*('|\")(.*?)\\2/";
      while ($m = self::match($re, $attrs)) {
          $result[$m[1][0]] = $m[3][0];
          $attrs = substr($attrs, $m[3][1] + mb_strlen($m[3][0]) + 1);
      }
      return $result;              
    }
    
    /**
     * Preg match wrapper
     * 
     * @param  string $pattern
     * @param  string $data
     * @return array|FALSE 
     */
    private static function match($pattern, $data)
    {        
        if (preg_match($pattern, $data, $m, PREG_OFFSET_CAPTURE)) {
            return $m;
        }             
        return FALSE;
    }
}