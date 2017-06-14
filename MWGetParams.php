<?php

class MWGetParams
{
    protected static function getParams($args, $frame) {
        $keys = self::extractParamKeys($args, 1);
        $values = self::extractParamValues($frame);
        $params = self::marryParams($keys, $values);

        return $params;
    }

    /**
     * Assigns the $values to the proper $keys
     *
     * @param  array $keys   return from extractParamKeys()
     * @param  array $values return from extractParamValues()
     * @return array         Usable array
     */
    protected static function marryParams($keys, $values) {
        $ret = [];

        foreach ($keys as $key => $var) {
            if ($var === true && !empty($key)) {
                $ret[] = $key;
            } else {
                if (isset($values[$var])) {
                    $ret[$key] = $values[$var];
                }
            }
        }

        return $ret;
    }

    /**
     * Template variable values are stored in frame.  If calling a parser hook from inside a template,
     * we only get the variable names, not their contents, so we have to extract the contents of them
     * to match up later.
     *
     * @param  PPTemplateFrame_DOM  $frame  The $frame argument passed to our parser hook
     * @return array                        An array containing a list of variable => key
     */
    protected static function extractParamValues($frame) {
        $ret = [];

        if ($frame instanceof PPTemplateFrame_DOM) {
            foreach ($frame->numberedArgs as $item) {
                $ret[] = trim($item->nodeValue);
            }

            foreach ($frame->namedArgs as $key => $item) {
                $ret[$key] = trim($item->nodeValue);
            }
        }

        return $ret;
    }

    /**
     * Converts an array of values in form [0] => "name=value" into a real
     * associative array in form [name] => value. If no = is provided,
     * true is assumed like this: [name] => true
     *
     * shamelessly stolen from https://www.mediawiki.org/wiki/Manual:Parser_functions#Named_parameters
     * then updated to actually work
     *
     * @param array string $options
     * @return array $results
     */
    protected static function extractParamKeys(array $options) {
        $results = array();

        foreach ($options as $option) {
            if ($option instanceof PPNode_DOM) {
                $option = $option->node->textContent;
            }
            if (is_string($option)) {
                $pair = explode('=', $option, 2);
                if (count($pair) === 2) {
                    $name = trim($pair[0]);
                    $value = trim($pair[1]);
                    $results[$name] = $value;
                }

                if (count($pair) === 1) {
                    $name = trim($pair[0]);
                    $results[$name] = true;
                }
            }
        }

        return $results;
    }
}
