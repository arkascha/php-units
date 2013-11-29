php-units
=========

A simple library for unit carrying mathematical computations



Example: 
This is a short example of a header definition taken from a tfpdf application: 

    use arkascha\units;
    // $this is an object hgolding some document attributes like file paths and the like
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
