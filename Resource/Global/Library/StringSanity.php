<?php
declare(strict_types=1);

namespace Pentagonal\Phalcon\Application\Globals\Library;

/**
 * Class StringSanity
 * @package Pentagonal\Phalcon\Application\Globals\Library
 */
class StringSanity
{
    /**
     * Fix Path Separator
     *
     * @param string $path
     * @param bool   $useCleanPrefix
     * @return string
     */
    public function fixDirectorySeparator($path, $useCleanPrefix = false)
    {
        /**
         * Trimming path string
         */
        if (($path = trim($path)) == '') {
            return $path;
        }

        $path = preg_replace('`(\/|\\\)+`', DIRECTORY_SEPARATOR, $path);
        if ($useCleanPrefix) {
            $path = DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
        }

        return $path;
    }

    /**
     * @param string $class
     * @return string
     */
    public function filterHtmlClass($class)
    {
        //Strip out any % encoded octets
        $sanitized = preg_replace('|%[a-fA-F0-9][a-fA-F0-9]|', '', $class);

        //Limit to A-Z,a-z,0-9,_,-
        $sanitized = preg_replace('/[^A-Za-z0-9_-]/', '', $sanitized);

        return $sanitized;
    }

    /**
     * @param array $array
     *
     * @return array
     */
    public function uniqueArray(array $array) : array
    {
        if (empty($array)) {
            return $array;
        }
        return array_map("unserialize", array_unique(array_map("serialize", $array)));
    }

    /**
     * Test if a give filesystem path is absolute.
     *
     * For example, '/foo/bar', or 'c:\windows'.
     *
     * @since 2.5.0
     *
     * @param string $path File path.
     * @return bool True if path is absolute, false is not absolute.
     */
    public function isAbsolutePath($path)
    {
        /*
         * This is definitive if true but fails if $path does not exist or contains
         * a symbolic link.
         */
        if (realpath($path) == $path) {
            return true;
        }

        if (strlen($path) == 0 || $path[0] == '.') {
            return false;
        }

        // Windows allows absolute paths like this.
        if (preg_match('#^[a-zA-Z]:\\\\#', $path)) {
            return true;
        }

        // A path starting with / or \ is absolute; anything else is relative.
        return ( $path[0] == '/' || $path[0] == '\\' );
    }

    /**
     * Normalize a filesystem path.
     *
     * @param string $path Path to normalize.
     * @return string Normalized path.
     */
    public function normalizePath($path)
    {
        $path = $this->fixDirectorySeparator($path);
        $path = preg_replace('|(?<=.)/+|', DIRECTORY_SEPARATOR, $path);
        if (':' === substr($path, 1, 1)) {
            $path = ucfirst($path);
        }

        if ($this->isAbsolutePath($path) && strpos($path, '.')) {
            $explode = explode(DIRECTORY_SEPARATOR, $path);
            $array = [];
            foreach ($explode as $key => $value) {
                if ('.' == $value) {
                    continue;
                }
                if ('..' == $value) {
                    array_pop($array);
                } else {
                    $array[] = $value;
                }
            }
            $path = implode(DIRECTORY_SEPARATOR, $array);
        }

        return $path;
    }

    /**
     * Entities the Multi bytes deep string
     *
     * @param mixed $mixed  the string to detect multi bytes
     * @param bool  $entity true if want to entity the output
     *
     * @return mixed
     */
    public function multiByteEntities($mixed, $entity = false)
    {
        static $hasIconV;
        static $limit;
        if (!isset($hasIconV)) {
            // safe resource check
            $hasIconV = function_exists('iconv');
        }

        if (!isset($limit)) {
            $limit = @ini_get('pcre.backtrack_limit');
            $limit = ! is_numeric($limit) ? 4096 : abs($limit);
            // minimum regex is 512 byte
            $limit = $limit < 512 ? 512 : $limit;
            // limit into 40 KB
            $limit = $limit > 40960 ? 40960 : $limit;
        }

        if (! $hasIconV && ! $entity) {
            return $mixed;
        }

        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[$key] = $this->multiByteEntities($value, $entity);
            }
        } elseif (is_object($mixed)) {
            foreach (get_object_vars($mixed) as $key => $value) {
                $mixed->{$key} = $this->multiByteEntities($value, $entity);
            }
        } /**
         * Work Safe with Parse @uses @var $limit Bit
         * | 4KB data split for regex callback & safe memory usage
         * that maybe fail on very long string
         */
        elseif (strlen($mixed) > $limit) {
            return implode('', $this->multiByteEntities(str_split($mixed, $limit), $entity));
        }

        if ($entity) {
            $mixed = htmlentities(html_entity_decode($mixed));
        }

        return $hasIconV
            ? preg_replace_callback(
                '/[\x{80}-\x{10FFFF}]/u',
                function ($match) {
                    $char = current($match);
                    $utf = iconv('UTF-8', 'UCS-4//IGNORE', $char);
                    return sprintf("&#x%s;", ltrim(strtolower(bin2hex($utf)), "0"));
                },
                $mixed
            ) : $mixed;
    }

    /* --------------------------------------------------------------------------------*
     |                              Serialize Helper                                   |
     |                                                                                 |
     | Custom From WordPress Core wp-includes/functions.php                            |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Check value to find if it was serialized.
     * If $data is not an string, then returned value will always be false.
     * Serialized data is always a string.
     *
     * @param  mixed $data   Value to check to see if was serialized.
     * @param  bool  $strict Optional. Whether to be strict about the end of the string. Defaults true.
     * @return bool  false if not serialized and true if it was.
     */
    public function isSerialized($data, $strict = true)
    {
        /* if it isn't a string, it isn't serialized
         ------------------------------------------- */
        if (! is_string($data) || trim($data) == '') {
            return false;
        }

        $data = trim($data);
        // null && boolean
        if ('N;' == $data || $data == 'b:0;' || 'b:1;' == $data) {
            return true;
        }

        if (strlen($data) < 4 || ':' !== $data[1]) {
            return false;
        }

        if ($strict) {
            $last_char = substr($data, -1);
            if (';' !== $last_char && '}' !== $last_char) {
                return false;
            }
        } else {
            $semicolon = strpos($data, ';');
            $brace     = strpos($data, '}');

            // Either ; or } must exist.
            if (false === $semicolon && false === $brace
                || false !== $semicolon && $semicolon < 3
                || false !== $brace && $brace < 4
            ) {
                return false;
            }
        }

        $token = $data[0];
        switch ($token) {
            /** @noinspection PhpMissingBreakStatementInspection */
            case 's':
                if ($strict) {
                    if ('"' !== substr($data, -2, 1)) {
                        return false;
                    }
                } elseif (false === strpos($data, '"')) {
                    return false;
                }
            // or else fall through
            case 'a':
            case 'O':
                return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'i':
            case 'd':
                $end = $strict ? '$' : '';
                return (bool) preg_match("/^{$token}:[0-9.E-]+;$end/", $data);
        }

        return false;
    }

    /**
     * Un-serialize value only if it was serialized.
     *
     * @param  string $original Maybe un-serialized original, if is needed.
     * @return mixed  Un-serialized data can be any type.
     */
    public function maybeUnSerialize($original)
    {
        if (! is_string($original) || trim($original) == '') {
            return $original;
        }

        /**
         * Check if serialized
         * check with trim
         */
        if ($this->isSerialized($original)) {
            /**
             * use trim if possible
             * Serialized value could not start & end with white space
             */
            return @unserialize(trim($original));
        }

        return $original;
    }

    /**
     * Serialize data, if needed. @uses for ( un-compress serialize values )
     * This method to use safe as save data on database. Value that has been
     * Serialized will be double serialize to make sure data is stored as original
     *
     *
     * @param  mixed $data Data that might be serialized.
     * @return mixed A scalar data
     */
    public function maybeSerialize($data)
    {
        if (is_array($data) || is_object($data)) {
            return @serialize($data);
        }

        // Double serialization is required for backward compatibility.
        if ($this->isSerialized($data, false)) {
            return serialize($data);
        }

        return $data;
    }
}
