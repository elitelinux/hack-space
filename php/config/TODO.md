Make Compatible with Open Shift

_________________________________-
Examples:
_________________________________
                //Support OpenShift
                $str .= <<<'CONSTRUCT'
public function __construct() {
if (getenv("OPENSHIFT_MYSQL_DB_HOST") && strtolower($this->host) == 'localhost') {
$this->host = getenv("OPENSHIFT_MYSQL_DB_HOST") . ":" . getenv("OPENSHIFT_MYSQL_DB_PORT");
}
}

CONSTRUCT;
                
$str .= "}";

// Use the closing tag if it not set to false in parameters.
if (!isset($params['closingtag']) || $params['closingtag'] !== false)
{
$str .= "\n?>";
}

return $str;
}

/**
* Parse a PHP class formatted string and convert it into an object.
*
* @param string $data PHP Class formatted string to convert.
* @param array $options Options used by the formatter.
*
* @return object Data object.
*
* @since 11.1
*/
public function stringToObject($data, array $options = array())
{
return true;
}

/**
* Method to get an array as an exported string.
*
* @param array $a The array to get as a string.
*
* @return array
*
* @since 11.1
*/
protected function getArrayString($a)
{
$s = 'array(';
$i = 0;

foreach ($a as $k => $v)
{
$s .= ($i) ? ', ' : '';
$s .= '"' . $k . '" => ';

if (is_array($v) || is_object($v))
{
$s .= $this->getArrayString((array) $v);
}
else
{
$s .= '"' . addslashes($v) . '"';
}

$i++;
}

$s .= ')';

return $s;
}
}
