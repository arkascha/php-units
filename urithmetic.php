<?php
/**
 * @file arkascha/urithmetic/urithmetic.php
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

require_once 'exception.php';

/**
 * @class Unit
 * @author Christian Reiner <info@christian.reiner.info>
 * @brief Description of a unit, its attributes and some conversion routines
 **/
abstract class Unit
{
  const MM = 'mm'; // millimeter
  const CM = 'cm'; // centimeter
  const DM = 'dm'; // decimeter
  const PT = 'pt'; // point
  const PI = 'pi'; // pica
  const IN = 'in'; // inch
  const FT = 'ft'; // foot
  static protected function units() { return array(self::MM, self::CM, self::DM, self::PT, self::PI, self::IN, self::FT); }
  static private $factor = array (
    self::PT => array(self::PT=>1,             self::MM=>0.352777778, self::PI=>0.083333333, self::CM=>0.035277778, self::IN=>0.013888889,  self::DM=>0.003527778, self::FT=>0.001157407),
    self::MM => array(self::PT=>2.834645669,   self::MM=>1,           self::PI=>0.236220472, self::CM=>0.1,         self::IN=>0.039370079,  self::DM=>0.01,        self::FT=>0.00328084 ),
    self::PI => array(self::PT=>12,            self::MM=>4.233333333, self::PI=>1,           self::CM=>0.423333333, self::IN=>0.166666667,  self::DM=>0.042333333, self::FT=>0.013888889),
    self::CM => array(self::PT=>28.346456693,  self::MM=>10,          self::PI=>2.362204724, self::CM=>1,           self::IN=>0.393700787,  self::DM=>0.1,         self::FT=>0.032808399),
    self::IN => array(self::PT=>72,            self::MM=>25.4,        self::PI=>6.0,         self::CM=>2.54,        self::IN=>1,            self::DM=>0.254,       self::FT=>0.083333333),
    self::DM => array(self::PT=>283.464566929, self::MM=>100,         self::PI=>0.00328084,  self::CM=>10,          self::IN=>3.937007874,  self::DM=>1,           self::FT=>0.32808399 ),
    self::FT => array(self::PT=>864,           self::MM=>304.8,       self::PI=>72,          self::CM=>30.48,       self::IN=>12,           self::DM=>3.048,       self::FT=>1          ),
  );
  static public function conversionFactor($source, $target)       { return static::$factor[$source][$target];}
  static public function valueToUnit ($source, $target,  $scalar) { return $scalar * static::conversionFactor(Unit::validate($source), Unit::validate($target)); }
  static public function morphToUnit ($source, $target, &$scalar) { $scalar = static::valueToUnit(static::validate($source), static::validate($target), $scalar); return $scalar; }
  static public function cloneToUnit ($source, $target,  $scalar) { $clone = $scalar; static::morphToUnit($source, $target, $clone); return $clone; }
  static public function validate($unit)
  {
    if ( ! in_array($unit, self::units()))
      throw new Exception(Exception::UNSUITED, __CLASS__, __FUNCTION__, "Unsuited unit object 'unit'", array('object'=>$unit));
    return $unit;
  }
} // class Unit

/**
 * @class Container
 * @author Christian Reiner <info@christian.reiner.info>
 * @brief Base type of all uni based descriptions implementing the basic features
 **/
abstract class Container
{
  static protected $type = NULL;
  static protected $attr = array();
  protected $unit = NULL;
  protected $data = array();
  public function __set($attr, $val)
  {
    if (in_array($attr, static::$attr))
      $this->data[$attr] = $val;
    else throw new Exception(Exception::UNDEFINED, __CLASS__, __FUNCTION__, sprintf("Undefined attribute '%s'", $attr), array('attr' =>$attr));
  }
  public function __get($attr)
  {
    if (in_array($attr, static::$attr))
      return isset($this->data[$attr]) ? $this->data[$attr] : NULL;
    else throw new Exception(Exception::UNDEFINED, __CLASS__, __FUNCTION__, sprintf("Undefined attribute '%s'", $attr), array('attr' =>$attr));
  }
  public function __toString()
  {
    $data = array();
    foreach (static::$attr as $attr)
      $data[] = sprintf('"%s": %s', $attr, $this->data[$attr]);
    return sprintf('%s(%s)[%s] ', strrchr(get_class($this),'\\'), $this->unit, implode(', ', $data));
  }
  public function __clone()
  {
    foreach ($this->data as &$attr)
      if ($attr instanceof Container)
        $attr = clone $attr;
  }
  public function morphToUnit($unit=NULL)
  {
    if ($unit && Unit::validate($unit)!=$this->unit) {
      foreach($this->data as &$attr)
        if ($attr instanceof Container)
          $attr->morphToUnit($unit);
        else Unit::morphToUnit($this->unit, $unit, $attr);
      $this->unit = $unit;
    }
    return $this;
  }
  public function cloneToUnit($unit=NULL)
  {
    $clone = clone $this;
    if ($unit && Unit::validate($unit)!=$this->unit) {
      foreach($clone->data as &$attr)
        if ($attr instanceof Container)
          $attr->morphToUnit($unit);
        else Unit::morphToUnit($this->unit, $unit, $attr);
      $clone->unit = $unit;
    }
    return $clone;
  }
} // class Container

/**
 * @class Value
 * @author Christian Reiner <info@christian.reiner.info>
 * @brief Unit based description of a plain scalar without specific purpose
 **/
class Value extends Container
{
  static protected $type = __CLASS__;
  static protected $attr = array('scalar');
  private function __construct($scalar, $unit)
  {
    $this->unit = Unit::validate($unit);
    if (!(is_numeric($scalar)))
      throw new Exception(Exception::UNSUITED, __CLASS__, __FUNCTION__, "Unsuited scalar object 'scalar'", array('object'=>$scalar));
    $this->scalar = $scalar;
  }
  static public function fromScalar($scalar, $unit)      { return new self($scalar, $unit); }
  static public function fromValue ($scalar, $unit=NULL) { return $scalar->cloneToUnit($unit); }
  public function getValue ($unit=NULL) { return $this->cloneToUnit($unit); }
  public function toScalar ($unit=NULL) { return Unit::cloneToUnit($this->unit, $unit?$unit:$this->unit, $this->scalar); }
  public function toInt    ($unit=NULL) { return round($this->getScalar($unit)); }
  public function toFloat  ($unit=NULL) { return (float)  $this->getScalar($unit); }
  public function toDouble ($unit=NULL) { return (double) $this->getScalar($unit); }
} // class Value

?>
