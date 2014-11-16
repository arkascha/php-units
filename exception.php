<?php
/**
 * @file arkascha/urithmetic/exception.php
 * @copyright 2013-2014 Christian Reiner, Hamburg, Germany, mailto:info@christian-reiner.info
 * @license GNU Affero General Public license version 3 (AGPL)
 * @author Christian Reiner <info@christian-reiner.info>
 * @brief Part of package 'PHP Urithmetic', an implementation of unit based arithmetic
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the license, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.
 * If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace arkascha\urithmetic;

/**
 * @class Exception
 * @brief Package specific exception class
 * @author Christian Reiner <info@christian-reiner.info>
 * @description
 * This is a library specific exception class.
 * Exceptions are PHP exceptions (inheritance) extended by an additional 'data' attribute.
 * This attribute stores some additional runtime information about the occurance.
 * It is an array structure holding technical information.
 */
class Exception extends \Exception
{
  const TITLE        = 'Urithmetic Exception';
  const UNDEFINED    = 1;
  const UNSUITED     = 2;
  const UNCOMPATIBLE = 3;

  /**
   * @member data
   * @type array
   * @brief Associative array holding arbitrary additional technical information about the incident.
   */
  protected $data;

  /**
   * @method morphPHPException
   * @brief Morphs a given PHPexception into a library specific exception. Typically used inside catch blocks.
   * @param $e exception
   * @return PDException
   * @access public
   */
  static public function morphPHPException ($e)
  {
    if ( ! is_a('Exception', $e))
      return new Exception('Thrown unsupported object', array('class' =>get_class($e),
                                                              'object'=>$e));
    return new Exception('Technical PHP exception', array('class' =>get_class($e),
                                                          'errno'=>$e->getCode(),
                                                          'error'=>$e->getMessage()));
  } // function morphPHPException

  /**
   * @method __construct
   * @brief Constructor, instiantiates a valid php exception object
   * @param $message string Human readable error message
   * @param $data array Optional technical data that might help to debug problems
   * @access public
   */
  public function __construct ($code, $class, $method, $message, $data=array())
  {
    parent::__construct(sprintf('%s::%s: %s', $class, $method, $message), $code);
    $this->data = is_array($data) ? $data : array($data);
    // convert more complex elements to strings
    foreach ($this->data as &$data)
      if (is_array($data))
        $data = print_r($data,true);
  } // function __construct

  /**
   * @method getData
   * @brief Return data structure generated at runtime
   * @access public
   */
  public function getData()
  {
    return $this->data;
  } // function getData

  /**
   * @method __toString
   * @brief Returns exception as human readable error string
   * @access public
   */
  public function __toString()
  {
    return sprintf('%1$s: [%2$s] %3$s', static::TITLE, $this->code, $this->message);
  } // function __toString

  /**
   * @method __toHeader
   * @brief returns exception as http headers
   * @access public
   */
  public function __toHeader()
  {
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-type: text/plain');
    header(sprintf('%1$s: [%2$s] %3$s', static::TITLE, urlencode($this->code, $this->message)));
  } // function toHeader

} // class Exception

?>
