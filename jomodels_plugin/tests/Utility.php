<?php
namespace Joomla\CMS\Utility;
class Utility
{
    /**
     * Method to extract key/value pairs out of a string with XML style attributes
     *
     * @param   string  $string  String containing XML style attributes
     *
     * @return  array  Key/Value pairs for the attributes
     *
     * @since   1.7.0
     */
    public static function parseAttributes($string)
    {
        $attr     = [];
        $retarray = [];

        // Let's grab all the key/value pairs using a regular expression
        preg_match_all('/([\w:-]+)[\s]?=[\s]?"([^"]*)"/i', $string, $attr);

        if (\is_array($attr)) {
            $numPairs = \count($attr[1]);

            for ($i = 0; $i < $numPairs; $i++) {
                $retarray[$attr[1][$i]] = $attr[2][$i];
            }
        }

        return $retarray;
    }
}
