<?php
/**
 * Kooper library (https://bitbucket.org/ottilus/library-kooper)
 *
 * @link       https://bitbucket.org/ottilus/library-kooper for the canonical source repository
 * @copyright  Copyright (c) 2013 OTTilus Ltd. (http://www.ottilus.com)
 * @category   Kooper
 * @package    Kooper_View
 * @subpackage Helper
 */
namespace Kooper\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Helper for converting arrays into an HTML table response
 *
 * @category   Kooper
 * @package    Kooper_View
 * @subpackage Helper
 */
class HtmlTable
    extends AbstractHelper
{
    /**
     * Encode data as an HTML table
     *
     * @param  mixed $data
     * @return string|void
     */
    public function __invoke($data)
    {
    	return $this->htmlTable($data);
    }
    
    /**
     * Parse the given data into an array
     * 
     * @param mixed $data
     * @return array|string
     */
    protected function _toArray($data)
    {
        if (is_object($data) && method_exists($data, 'getArrayCopy')) {
            $data = $data->getArrayCopy();
        }
        
        return $data;
    }
    
    /**
     * Create the HTML table based on the given data
     * 
     * @param mixed $data
     * @return string
     */
    protected function htmlTable($data)
    {
        $data = $this->_toArray($data);
        
        if (!is_array($data)) {
            $data = array(array('response' => $data));
    	}
    	    	
    	if (!is_numeric(current(array_keys($data))) || is_string(current($data))) {
            $data = array($data);
    	}
    	
        $current = $this->_toArray(current($data));
        
    	$html = "<table class=\"table table-striped table-hover table-bordered\">";
        if (!is_numeric(key($current))) {
            $html .= $this->_createRow(array_keys($current), 'td', 'success', '<strong>%s</strong>');
        }
    	foreach ($data as $row) {
            $row = $this->_toArray($row);
            if (is_numeric(key($row))) {
                foreach ($row as $sub) {
                    $html .= $this->_createRow((array)$sub);
                }
            } else {
                $html .= $this->_createRow($row);
            }
    	}
    	
    	$html .= "</table>";
    	
    	return $html;
    }
    
    /**
     * Create an HTML row
     * 
     * @param mixed $data
     * @param string $tag
     * @param string|null $cssClass
     * @param string $innerHtml
     * @return string
     */
    protected function _createRow($data, $tag = "td", $cssClass = null, $innerHtml = "%s")
    {
        if ($cssClass) {
            $cssClass = " class='$cssClass'";
        }
    	$html = "<tr$cssClass>";
    	foreach ($data as $value) {
            if (is_array($value) || (is_object($value) && method_exists($value, 'getArrayCopy'))) {
                $value = $this->htmlTable($value);
            }

            $html .= "<$tag>" . sprintf($innerHtml, $value) . "</$tag>";
    	}
    	$html .= "</tr>";
    	return $html;//"<tr><$tag>" . implode("</$tag><$tag>", $data) . "</$tag></tr>";
    }
}