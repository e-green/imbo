<?php
/**
 * Imbo
 *
 * Copyright (c) 2011 Christer Edvartsen <cogo@starzinger.net>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * * The above copyright notice and this permission notice shall be included in
 *   all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @package Imbo
 * @subpackage Unittests
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/imbo
 */

namespace Imbo\Image\Transformation;

/**
 * @package Imbo
 * @subpackage Unittests
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/imbo
 */
class ConvertTest extends TransformationTests {
    protected function getTransformation() {
        return new Convert('png');
    }

    public function testConvertToSameTypeAsImage() {
        $convert = $this->getTransformation();
        $image = $this->getMock('Imbo\Image\ImageInterface');
        $image->expects($this->once())->method('getExtension')->will($this->returnValue('png'));

        $imagine = $this->getMock('Imagine\Image\ImagineInterface');
        $imagine->expects($this->exactly(0))->method('load');

        $convert->setImagine($imagine);

        $convert->applyToImage($image);
    }

    public function testApplyToImage() {
        $convert = $this->getTransformation();

        $image = $this->getMock('Imbo\Image\ImageInterface');
        $image->expects($this->once())->method('setBlob')->will($this->returnValue($image));
        $image->expects($this->once())->method('setMimeType')->with('image/png')->will($this->returnValue($image));
        $image->expects($this->once())->method('setExtension')->with('png')->will($this->returnValue($image));

        $imagineImage = $this->getMock('Imagine\Image\ImageInterface');

        $imagine = $this->getMock('Imagine\Image\ImagineInterface');
        $imagine->expects($this->once())->method('load')->will($this->returnValue($imagineImage));
        $convert->setImagine($imagine);

        $convert->applyToImage($image);
    }
}
