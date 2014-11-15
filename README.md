php-unitmath
=========

A simple library for unit based mathematical computations

When layouting things one often has to compute various positions, zoom factors, scalings and the like. 
When the specific sizes are of dynamical nature the layout code quicky gets a confusing endless series of mathematical equations. You name it: spaghetti code. 
This library tries to offer two approaches to solve this problem: 
* by taking handling of units from explicit recomputation to implicit consideration, so all you have to do is name what unit a specific value is to be interpreted as
* by offering well known methematical concepts as types which allows to think in a more high level way about layouting instead of just simple numeric values. 

The code does not strive to be fast, this library is implemented in php. It only tries to help keeping the layouting code maintainable and robust. 
It would be great if additional types would be implemented in future. in geometry what I really miss is angles and their consideration on layouting code. Also volumnes (the third dimension...) would be a welcome extension. And obviously aspects outside geometry: electric units, mechanics like forces, accelerations and pressures. Or even currencies and their exchange rates. 
For performance optimization it might be fun to reimplement this approach in C or C++, for example as a php extension. 

Example: 
This is a short example of a header definition taken from a tfpdf application: 

    use arkascha\unitmath;
    // $this is an object holding some document attributes like file paths and the like
    try {
      // define a box inside the header by its position(x,y) and size(w,h) and a unit
      $box = units\geo\Area::fromScalars($this->leftHeaderPadding, 
                                         $this->topHeaderPadding, 
                                         $this->pageSize-$this->leftHeaderPadding-$this->rightHeaderPadding,
                                         $this->headerHeight-$this->leftHeaderPadding.$this->rightheaderPadding,
                                         units\Unit::MM);
      // a logo is placed inside the box on the left hand side
      $pic = units\geo\Size::fromImageFile($this->logo1, units\Unit::MM);
      $pos = $box->fitSize($pic, units\geo\Align::TOP | units\geo\Align::LEFT);
      list($left,$top,$width,$height) = $pos->getScalars();
      $this->Image($this->logo1, $left, $top, $width, $height, self::identifyImageFormat($this->logo1));
      // a logo is placed inside the box on the right hand side
      $pic = units\geo\Size::fromImageFile($this->logo2, units\Unit::MM);
      $pos = $box->fitSize($pic, units\geo\Align::TOP | units\geo\Align::RIGHT);
      list($left,$top,$width,$height) = $pos->getScalars();
      $this->Image($this->logo2, $left, $top, $width, $height, self::identifyImageFormat($this->logo2));
    } catch (units\Exception $e) {
      error_log($e->getMessage());
      error_log($e->getTraceAsString());
      throw $e;
    }

Explanation: 
- a page of size 'DIN A4' which is 210mm x 297mm (European metric standard page size)
- a header should be reserved defining a top margin of 40 for all page content
- inside that header a box is defined by its dimensions in millimeters
- those dimensions are specified as numerical values here to keep things simple
- inside that box two logs are placed using an alignment
- position and size of the images to be created are computed automatically
- note how there are no dimensions specified for the logos, these are computed by the available space
