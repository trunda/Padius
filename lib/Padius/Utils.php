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
 * Utilities 
 * @author Jakub Truneček
 */
class Utils 
{
    /**
     * Returns array of string (like implode('', $array)
     * 
     * @param  array $array
     * @param  boolean $recursive
     * @return string 
     */
    public static function arrayToString(array $array, $recursive = FALSE) {
        $result = '';
        foreach ($array as $key => $value) {
            if ($recursive && is_array($value)) {                
                $result .= self::arrayToString($value, $recursive);
            } else {                
                $result .= (string) $value;
            }
        }                   
        return $result;
    }
}