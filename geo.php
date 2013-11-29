<?php
/**
 * @file arkascha/units/geo.php
 * @copyright 2013-2013 Christian Reiner, Hamburg, Germany, mailto:info@christian-reiner.info
 * @license GNU Affero General Public license version 3 (AGPL)
 * @author Christian Reiner <christian.reiner@projectdocu.com>
 * @brief Part of package 'Units', an implementation of unit based arithmetics
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

namespace arkascha\units\geo;
use \arkascha\units;

require_once 'units.php';

class Align
{
  const TOP    = 1;
  const BOTTOM = 2;
  const LEFT   = 4;
  const RIGHT  = 8;
} // class Align

/**
 * @class Length
 * @author Christian Reiner <info@christian.reiner.info>
 * @brief Unit based description of a length holding a single value: length
 **/
class Length extends units\Container
{
  static protected $type = __CLASS__;
  static protected $attr = array('length');
  protected function __construct($length, $unit)
  {
    $this->unit = units\Unit::validate($unit);
    if (!($length instanceof units\Value))
      throw new units\Exception(units\Exception::UNSUITED, __CLASS__, __FUNCTION__, "Unsuited value object 'length'", array('object'=>$length));
    $this->length = $length->cloneToUnit($this->unit);
  }
  static public function fromLength ($length, $unit=NULL)
  {
    if (!($length instanceof Length))
      throw new units\Exception(units\Exception::UNSUITED, __CLASS__, __FUNCTION__, "Unsuited length object 'length'", array('object'=>$length));
    return $length->cloneToUnit($unit);
  }
  static public function fromValue  ($length, $unit=NULL)
  {
    if (!($length instanceof units\Value))
      throw new units\Exception(units\Exception::UNSUITED, __CLASS__, __FUNCTION__, "Unsuited value object 'length'", array('object'=>$length));
    return new self($length, $unit?$unit:$length->unit);
  }
  static public function fromScalar ($length, $unit)
  {
    if (!(is_numeric($length)))
      throw new units\Exception(units\Exception::UNSUITED, __CLASS__, __FUNCTION__, "Unsuited scalar object 'length'", array('object'=>$length));
    return self::fromValue(units\Value::fromScalar($length, $unit), $unit);
  }
  public function getScalar($unit=NULL) {
  $length = $this->length->cloneToUnit($unit);
  return $length->toScalar(); }
  public function getLength($unit=NULL) { return $this->length->cloneToUnit($unit); }
  public function getValue ($unit=NULL) { return $this->length->cloneToUnit($unit); }
  public function getSum   ($length, $unit=NULL) { return Length::fromScalar($this->getScalar($unit)+$length->getScalar($unit), $unit?$unit:$this->unit); }
  public function getDiff  ($length, $unit=NULL) { return Length::fromScalar($this->getScalar($unit)-$length->getScalar($unit), $unit?$unit:$this->unit); }
  public function getScaled($factor)    { return self::fromScalar($factor*$this->getScalar(), $this->unit); }
} // class Length

/**
 * @class Size
 * @author Christian Reiner <info@christian.reiner.info>
 * @brief Unit based description of a size holding two values: width and height
 **/
class Size extends units\Container
{
  static protected $type = __CLASS__;
  static protected $attr = array('width', 'height');
  private function __construct($width, $height, $unit)
  {
    $this->unit = units\Unit::validate($unit);
    if ($width instanceof Length)
      $this->width = $width->morphToUnit($this->unit);
    else throw new units\Exception(units\Exception::UNSUITED, __CLASS__, __FUNCTION__, "Unsuited length object 'width'", array('object'=>$width));
    if ($height instanceof Length)
      $this->height = $height->morphToUnit($this->unit);
    else throw new units\Exception(units\Exception::UNSUITED, __CLASS__, __FUNCTION__, "Unsuited length object 'height'", array('object'=>$height));
  }
  /**
   * @method fromLengths
   * @brief Constructs a Units\Size object from two given Units\Length objects interpreted as width and height.
   * @param $width units\Length width of the size
   * @param $height units\Length height of the size
   * @return Units\Size
   * @access public
   */
  static public function fromLengths ($width, $height, $unit)
  {
    foreach (array('width','height') as $attr)
      if (!($$attr instanceof Length))
        throw new units\Exception(units\Exception::UNSUITED, __CLASS__, __FUNCTION__, sprintf("Unsuited length object '%s'", $attr), array('object'=>$$attr));
    return new self($width, $height, $unit?$unit:$width->unit);
  }
  /**
   * @method fromValues
   * @brief Constructs a Units\Size object from two given Units\Value objects interpreted as width and height.
   * @param $width units\Value width of the size
   * @param $height units\Value height of the size
   * @return Units\Size
   * @access public
   */
  static public function fromValues ($width, $height, $unit=NULL)
  {
    foreach (array('width','height') as $attr)
      if (!($$attr instanceof units\Value))
        throw new units\Exception(units\Exception::UNSUITED, __CLASS__, __FUNCTION__, sprintf("Unsuited value object '%s'", $attr), array('object'=>$$attr));
    return self::fromLengths(Length::fromValue($width, $unit), Length::fromValue($height, $unit), $unit);
  }
  /**
   * @method fromScalars
   * @brief Constructs a Units\Size object from two given numeric scalar values interpreted as width and height.
   * @param $width numeric width of the size
   * @param $height numeric height of the size
   * @return Units\Size
   * @access public
   */
  static public function fromScalars($width, $height, $unit)
  {
    foreach (array('width','height') as $attr)
      if (!(is_numeric($$attr)))
        throw new units\Exception(units\Exception::UNSUITED, __CLASS__, __FUNCTION__, sprintf("Unsuited scalar object '%s'", $attr), array('object'=>$$attr));
    return self::fromValues(units\Value::fromScalar($width, $unit), units\Value::fromScalar($height, $unit), $unit);
  }
  /**
   * @method fromSize
   * @brief Constructs a Units\Size object from another given Units\Size object.
   * @param $size Units\Size object
   * @return Units\Size
   * @access public
   */
  static public function fromSize ($size, $unit=NULL)
  {
    if (!($pos instanceof Size))
      throw new units\Exception(units\Exception::UNSUITED, __CLASS__, __FUNCTION__, "Unsuited size object 'pos'", array('object'=>$size));
    return $size->cloneToUnit($unit);
  }
  /**
   * @method fromImageFile
   * @brief Constructs a Units\Size object from a given image file specified as path or url.
   * @param $file string Path or url of a file to be examined
   * @return Units\Size
   * @access public
   */
  static public function fromImageFile($file, $unit)
  {
    if (empty($file) || !file_exists($file) || !is_readable($file))
      throw new units\Exception(units\Exception::UNSUITED, __CLASS__, __FUNCTION__, "Unsuited path object 'file'", array('object'=>$file));
    try { list($width, $height) = getimagesize($file); }
    catch (units\Exception $e) { throw units\Exception::morphPHPException($e); }
    return Size::fromLengths(Length::fromScalar($width, units\Unit::PT), Length::fromScalar($height, units\Unit::PT), $unit);
  }
  /**
   * @method getScalars
   * @brief Returns two numeric scalars, the width and the height of a Units\Size object
   * @param $unit const An optional Units\Unit constant which specifies the desired output unit.
   * @return array Associative array holding two numeric scalar values 'width' and 'height'
   * @access public
   */
  public function getScalars($unit=NULL)
  {
    $unit = $unit ? units\Unit::validate($unit) : $this->unit;
    $width  = $this->width->cloneToUnit($unit);
    $height = $this->height->cloneToUnit($unit);
    return array($width->getScalar(), $height->getScalar());
  }
  /**
   * @method getWidth
   * @brief Returns a Units\Length object, the width of a Units\Size object
   * @param $unit const An optional Units\Unit constant which specifies the desired output unit.
   * @return Units\Length object
   * @access public
   */
  public function getWidth  ($unit=NULL) { return $this->width->cloneToUnit($unit);  }
  /**
   * @method getHeight
   * @brief Returns a Units\Length object, the height of a Units\Size object
   * @param $unit const An optional Units\Unit constant which specifies the desired output unit.
   * @return Units\Length object
   * @access public
   */
  public function getHeight ($unit=NULL) { return $this->height->cloneToUnit($unit); }
  /**
   * @method getScaled
   * @brief Returns a scaled version of a Units\Size object
   * @param $factor numeric A numeric factor by which the objects internal width and size is multiplied
   * @return Units\Size object
   * @access public
   */
  public function getScaled ($factor)    { return self::fromLengths($this->width->getScaled($factor), $this->height->getScaled($factor)); }
} // class Size

/**
 * @class Pos
 * @author Christian Reiner <info@christian.reiner.info>
 * @brief Unit based description of a position object holding two values: left and top
 **/
class Pos extends units\Container
{
  static protected $type = __CLASS__;
  static protected $attr = array('left', 'top');
  private function __construct($left, $top, $unit)
  {
    $this->unit = units\Unit::validate($unit);
    if ($left instanceof Length)
      $this->left = $left->morphToUnit($this->unit);
    else throw new units\Exception(units\Exception::UNSUITED, __CLASS__, __FUNCTION__, "Unsuited length object 'left'", array('object'=>$left));
    if ($top instanceof Length)
      $this->top  = $top->morphToUnit($this->unit);
    else throw new units\Exception(units\Exception::UNSUITED, __CLASS__, __FUNCTION__, "Unsuited length object 'right'", array('object'=>$top));
  }
  static public function fromPos ($pos, $unit=NULL)
  {
    if ($pos instanceof Pos)
      return $pos->cloneToUnit($unit);
    else throw new units\Exception(units\Exception::UNSUITED, __CLASS__, __FUNCTION__, "Unsuited pos object 'pos'", array('object'=>$pos));
  }
  static public function fromLengths($left, $top, $unit=NULL)
  {
    foreach (array('left','top') as $attr)
      if (!($$attr instanceof Length))
        throw new units\Exception(units\Exception::UNSUITED, __CLASS__, __FUNCTION__, sprintf("Unsuited length object '%s'", $attr), array('object'=>$$attr));
    return new self($left, $top, $unit?$unit:$left->unit);
  }
  static public function fromValues ($left, $top, $unit=NULL)
  {
    foreach (array('left','top') as $attr)
      if (!($$attr instanceof units\Value))
        throw new units\Exception(units\Exception::UNSUITED, __CLASS__, __FUNCTION__, sprintf("Unsuited value object '%s'", $attr), array('object'=>$$attr));
    return self::fromLengths(Length::fromValue($left, $unit), Length::fromValue($top, $unit), $unit);
  }
  static public function fromScalars($left, $top, $unit)
  {
    foreach (array('left','top') as $attr)
      if (!(is_numeric($$attr)))
        throw new units\Exception(units\Exception::UNSUITED, __CLASS__, __FUNCTION__, sprintf("Unsuited scalar object '%s'", $attr), array('object'=>$$attr));
    return self::fromValues(units\Value::fromScalar($left, $unit), units\Value::fromScalar($top, $unit), $unit);
  }
  static public function fromImageFile($file, $unit)
  {
    if (empty($file) || !file_exists($file) || !is_readable($file))
      throw new units\Exception(units\Exception::UNSUITED, __CLASS__, __FUNCTION__, "Unsuited path object 'file'", array('object'=>$file));
    try { list($width, $height) = getimagesize($file); }
    catch (units\Exception $e) { throw units\Exception::morphPHPException($e); }
    return Pos::fromScalars($width, $height, $unit);
  }
  public function getScalars($unit=NULL)
  {
    $unit = $unit ? units\Unit::validate($unit) : $this->unit;
    $left = $this->left->cloneToUnit($unit);
    $top  = $this->top->cloneToUnit($unit);
    return array($left->getScalar(), $top->getScalar());
  }
  public function getLeft  ($unit=NULL) { return $this->left->cloneToUnit($unit); }
  public function getTop   ($unit=NULL) { return $this->top->cloneToUnit($unit);  }
  public function getScaled($factor)    { return self::fromLengths($this->left->getScaled($factor), $this->top->getScaled($factor)); }
  public function getSum   ($pos, $unit=NULL)
  {
    if (!($pos instanceof Pos))
      throw new units\Exception(units\Exception::UNSUITED, __CLASS__, __FUNCTION__, "Unsuited pos object 'pos'", array('object'=>$pos));
    // morph to target unit
    $unit = $unit ? units\Unit::validate($unit) : $this->unit;
    $pos->morphToUnit($unit);
    $this->morphToUnit($unit);
    $this->left = $this->left->getSum($pos->left, $unit);
    $this->top  = $this->top->getSum($pos->top, $unit);
    return $this;
  }
  public function getDiff   ($pos, $unit=NULL)
  {
    if (!($pos instanceof Pos))
      throw new units\Exception(units\Exception::UNSUITED, __CLASS__, __FUNCTION__, "Unsuited pos object 'pos'", array('object'=>$pos));
    // morph to target unit
    $unit = $unit ? units\Unit::validate($unit) : $this->unit;
    $pos->morphToUnit($unit);
    $this->morphToUnit($unit);
    $this->left = $this->left->getDiff($pos->left, $unit);
    $this->top  = $this->top->getDiff($pos->top, $unit);
    return $this;
  }
} // class Pos

/**
 * @class Area
 * @author Christian Reiner <info@christian.reiner.info>
 * @brief Unit based description of an space object holding four values: left, top, right and bottom
 **/
class Area extends units\Container
{
  static protected $type = __CLASS__;
  static protected $attr = array('pos', 'size');
  private function __construct($pos, $size, $unit)
  {
    $this->unit = units\Unit::validate($unit);
    if ($pos instanceof Pos)
      $this->data['pos'] = $pos->morphToUnit($this->unit);
    else throw new units\Exception(units\Exception::UNSUITED, __CLASS__, __FUNCTION__, 'Unsuited pos object', array('object'=>$pos));
    if ($size instanceof Size)
      $this->data['size'] = $size->morphToUnit($this->unit);
    else throw new units\Exception(units\Exception::UNSUITED, __CLASS__, __FUNCTION__, 'Unsuited size object', array('object'=>$size));
  }
  static public function fromArea ($area, $unit=NULL)
  {
    if (!($area instanceof Area))
      throw new units\Exception(units\Exception::UNSUITED, __CLASS__, __FUNCTION__, sprintf("Unsuited area object 'area'"), array('object'=>$area));
    return $area->cloneToUnit($unit);
  }
  static public function fromPosSize($pos, $size, $unit=NULL)
  {
    if (!($pos instanceof Pos))
      throw new units\Exception(units\Exception::UNSUITED, __CLASS__, __FUNCTION__, sprintf("Unsuited pos object '%pos'"), array('object'=>$pos));
    if (!($size instanceof Size))
      throw new units\Exception(units\Exception::UNSUITED, __CLASS__, __FUNCTION__, sprintf("Unsuited size object '%size'"), array('object'=>$size));
    return new Area($pos, $size, $unit?$unit:$pos->unit);
  }
  static public function fromLengths ($left, $top, $width, $height, $unit=NULL)
  {
    foreach (array('left','top','width','height') as $attr)
      if (!($$attr instanceof Length))
        throw new units\Exception(units\Exception::UNSUITED, __CLASS__, __FUNCTION__, sprintf("Unsuited length object '%s'", $attr), array('object'=>$$attr));
    return self::fromPosSize(Pos::fromLengths ($left,  $top,    $unit),
                             Size::fromLengths($width, $height, $unit));
  }
  static public function fromValues ($left, $top, $width, $height, $unit=NULL)
  {
    foreach (array('left','top','width','height') as $attr)
      if (!($$attr instanceof units\Value))
        throw new units\Exception(units\Exception::UNSUITED, __CLASS__, __FUNCTION__, sprintf("Unsuited value object '%s'", $attr), array('object'=>$$attr));
    return self::fromLengths(Length::fromValue($left,   $unit),
                             Length::fromValue($top,    $unit),
                             Length::fromValue($width,  $unit),
                             Length::fromValue($height, $unit), $unit);
  }
  static public function fromScalars($left, $top, $width, $height, $unit)
  {
    foreach (array('left','top','width','height') as $attr)
      if (!(is_numeric($$attr)))
        throw new units\Exception(units\Exception::UNSUITED, __CLASS__, __FUNCTION__, sprintf("Unsuited scalar object '%s'", $attr), array('object'=>$$attr));
    return self::fromValues(units\Value::fromScalar($left,   $unit),
                            units\Value::fromScalar($top,    $unit),
                            units\Value::fromScalar($width,  $unit),
                            units\Value::fromScalar($height, $unit), $unit);
  }
  static public function fromImageFile($file, $unit)
  {
    if (empty($file) || !file_exists($file) || !is_readable($file))
      throw new units\Exception(units\Exception::UNSUITED, __CLASS__, __FUNCTION__, "Unsuited path object 'file'", array('object'=>$file));
    try { list($width, $height) = getimagesize($file); }
    catch (units\Exception $e) { throw units\Exception::morphPHPException($e); }
    return Area::fromScalars(0, 0, $width, $height, $unit);
  }
  public function getScalars($unit=NULL)
  {
    $unit = $unit ? units\Unit::validate($unit) : $this->unit;
    $pos  = $this->pos->cloneToUnit($unit);
    $size = $this->size->cloneToUnit($unit);
    return array($pos->left->getScalar(), $pos->top->getScalar(), $size->width->getScalar(), $size->height->getScalar());
  }
  public function getSize  ($unit=NULL) { return $this->size->morphToUnit($unit);   }
  public function getPos   ($unit=NULL) { return $this->pos->morphToUnit($unit);    }
  public function getLeft  ($unit=NULL) { return $this->pos->left->morphToUnit($unit);   }
  public function getTop   ($unit=NULL) { return $this->pos->top->morphToUnit($unit);    }
  public function getWidth ($unit=NULL) { return $this->size->width->morphToUnit($unit);  }
  public function getHeight($unit=NULL) { return $this->size->height->morphToUnit($unit); }
  public function getRight ($unit=NULL) { return $this->left->getSum($this->width, $unit); }
  public function getBottom($unit=NULL) { return $this->top->getSum($this->height, $unit); }
  public function getScaled($factor)    { return self::fromPosSize($this->pos->getScaled($factor), $this->size->getScaled($factor)); }
  public function moveTo($pos, $unit=NULL)
  {
    if (!($pos instanceof Pos))
      throw new units\Exception(units\Exception::UNSUITED, __CLASS__, __FUNCTION__, "Unsuited pos object 'pos'", array('object'=>$pos));
    // morph to target unit
    $unit = $unit ? units\Unit::validate($unit) : $this->unit;
    $pos->morphToUnit($unit);
    $this->morphToUnit($unit);
    // new pos
    $this->pos = $pos;
    return $this;
  }
  public function moveBy($pos, $unit=NULL)
  {
    if (!($pos instanceof Pos))
      throw new units\Exception(units\Exception::UNSUITED, __CLASS__, __FUNCTION__, "Unsuited pos object 'pos'", array('object'=>$pos));
    // morph to target unit
    $unit = $unit ? units\Unit::validate($unit) : $this->unit;
    $pos->morphToUnit($unit);
    $this->morphToUnit($unit);
    // new pos
    $this->pos = $this->pos->getSum($pos, $unit);
    return $this;
  }
  public function putSize($size, $align=0, $unit=NULL)
  {
    if (!($size instanceof Size))
      throw new units\Exception(units\Exception::UNSUITED, __CLASS__, __FUNCTION__, "Unsuited size object 'size'", array('object'=>$size));
    // morph to target unit
    $unit = $unit ? units\Unit::validate($unit) : $this->unit;
    $size->morphToUnit($unit);
    $this->morphToUnit($unit);
    // new size
    $ratioWidth  = $size->width->getScalar()  / $this->size->width->getScalar();
    $ratioHeight = $size->height->getScalar() / $this->size->height->getScalar();
    $ratio = max($ratioWidth, $ratioHeight);
    return (1>=$ratio) ? $size : Size::fromScalars($size->width->getScalar()/$ratio, $size->height->getScalar()/$ratio, $unit);
  }
  public function fitSize($size, $align=0, $unit=NULL)
  {
    // new size
    $scaled = $this->putSize($size, $align, $unit);
    // new pos
    switch ($align &(~Align::TOP) &(~Align::BOTTOM)) {
      case Align::LEFT:
        $newPosLeft = $this->pos->left;
        break;
      case Align::RIGHT:
        $widthDiff  = $this->size->width->getDiff($scaled->width);
        $newPosLeft = $this->pos->left->getSum($widthDiff);
        break;
      default:
        $widthDiff  = $this->size->width->getDiff($scaled->width);
        $newPosLeft = $this->pos->left->getSum($widthDiff->getScaled(0.5));
    }
    switch ($align &(~Align::LEFT) &(~Align::RIGHT)) {
      case Align::TOP:
        $newPosTop  = $this->pos->top;
        break;
      case Align::BOTTOM:
        $heightDiff = $this->size->height->getDiff($scaled->height);
        $newPosTop  = $this->pos->top->getSum($heightDiff);
        break;
      default:
        $heightDiff = $this->size->height->getDiff($scaled->height);
        $newPosTop  = $this->pos->top->getSum($heightDiff->getScaled(0.5));
    }
    $newPos = Pos::fromLengths($newPosLeft, $newPosTop);
    // final result is a area
    return Area::fromPosSize($newPos, $scaled);
  } // function fitSize

} // class Area

?>
