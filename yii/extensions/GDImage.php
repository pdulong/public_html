<?php
class GDImage
{
	/** @var pointer $imageResource Pointer to the image resource*/
	private $imageResource;
	/** @var array $metaData Holds metadata on the current image (width, height, etc) */
	private $metaData;
	/** @var string Current image filename */
	private $filename;
	/** @var bool $fileLoaded Whether the image was loaded using GD */
	private $fileLoaded = false;
	/** @var int $outputImageType Image type to output. Defaults to the image type of the input image */
	private $outputImageType;
	/** @var int $outputImageQuality Quality of the image to output. Defaults to 95 */
	private $outputImageQuality;

	/**
	* Constructor
	* @param sting $filename Filename of the image to load
	* @param string $forceLoad Set to true to load the image. Defaults to false for lazy behavior
	*/
	public function __construct($filename)
	{
		if (!function_exists('gd_info'))
			throw new CException('GDLib functionality not available');
		if ($filename == '')
			throw new CException('No image file supplied to load');
		if (!file_exists($filename))
			throw new CException('File "'.$filename.'" does not exist');

		ini_set('gd.jpeg_ignore_warning', 1);
		$this->filename = $filename;
	}

	public function process($actions)
	{
		$this->load();
		$this->raiseMemoryLimit();
		foreach($actions as $action)
		{
			$actionName = 'action'.ucfirst(strtolower($action['action']));
			if (method_exists($this, $actionName))
			{
				call_user_func_array(array($this, $actionName), $action['params']);
			}
			else
			{
				throw new CException('Unknown action "'.$actionName.'" in GDImage');
			}
		}
		$this->restoreMemoryLimit();
		return $this;
	}

	/**
	* Resize the current image
	* @param int $width The new width
	* @param int $height The new height
	* @param string $resizeMethod
	* - 'ideal' -- let the function decide whether it uses 'wh' or 'w+h'; 0.9 < aspect-ratio < 1.1 uses 'w+h', else 'wh'
	* - 'wh' -- resize to width and height, restricted by original aspect ratio
	* - 'force-w' -- Force the width to a certain value, scale the height according to this width and the aspect ratio of the image
	* - 'force-h' -- Force the height to a certain value, scale the width according to this height and the aspect ratio of the image
	* - 'fill' -- let the funtion decide whether it uses 'w+h' or 'crop'; 0.9 < aspect-ratio < 1.1 uses 'w+h', else 'crop'
	* - 'w+h' -- stretch to width and height
	* - 'crop' -- crop from the center of the image
	*/
	private function actionResize($width, $height, $resizeMethod = 'ideal')
	{
		$resizeMethod = strtolower($resizeMethod);

		if ($width < 1)	$width = 100;
		if ($height < 1) $height = 100;
		if (!in_array($resizeMethod, array('wh', 'w+h', 'ideal', 'force-w', 'force-h', 'crop', 'fill')))
			$resizeMethod = 'ideal';

		list($originalWidth, $originalHeight) = array($this->metaData['width'], $this->metaData['height']);
		list($originalAspectRatio, $newAspectRatio) = array($originalWidth / $originalHeight, $width / $height);
		
		if ($resizeMethod == 'ideal')
		{
			$x = $originalAspectRatio / $newAspectRatio;
			if ($x > 0.9 && $x < 1.1)
				$resizeMethod = 'w+h';
			else
				$resizeMethod = 'wh';
		}

		if ($resizeMethod == 'fill')
		{
			$x = $originalAspectRatio / $newAspectRatio;
			if ($x > 0.9 && $x < 1.1)
				$resizeMethod = 'w+h';
			else
				$resizeMethod = 'crop';
		}

		if ($originalAspectRatio != $newAspectRatio && $resizeMethod != 'w+h')
		{
			if (($resizeMethod == 'wh' && $originalAspectRatio > $newAspectRatio) || $resizeMethod == 'force-w')
			{
				$newWidth = $width;
				$newHeight = $newWidth / $originalAspectRatio;
			}
			else if (($resizeMethod == 'wh' && $originalAspectRatio < $newAspectRatio) || $resizeMethod == 'force-h')
			{
				$newHeight = $height;
				$newWidth = $newHeight * $originalAspectRatio;
			}
			else if ($resizeMethod == 'crop')
			{
				list($newWidth, $newHeight) = array($width, $height);

				if ($originalAspectRatio < $newAspectRatio)
				{
					$tmpHeight = round($originalWidth / $newAspectRatio);
					$yOffset =  ($originalHeight - $tmpHeight) / 2;
					$this->actionCrop(0, $yOffset, $originalWidth, $tmpHeight);
				}
				else if ($originalAspectRatio > $newAspectRatio)
				{
					$tmpWidth = round($originalHeight * $newAspectRatio);
					$xOffset = ($originalWidth - $tmpWidth) / 2;
					$this->actionCrop($xOffset, 0, $tmpWidth, $originalHeight);
				}
			}
		}
		else
		{
			list($newWidth, $newHeight) = array($width, $height);
		}

		list($newWidth, $newHeight) = array(round($newWidth), round($newHeight));

		$newImageResource = $this->createNewImageResource($newWidth, $newHeight);
		if (function_exists('ImageCopyResampled'))
		{
			ImageCopyResampled($newImageResource, $this->imageResource, 0, 0, 0, 0, $newWidth, $newHeight, $this->metaData['width'], $this->metaData['height']);
		}
		else
		{
			ImageCopyResized($newImageResource, $this->imageResource, 0, 0, 0, 0, $newWidth, $newHeight, $this->metaData['width'], $this->metaData['height']);
		}

		ImageDestroy($this->imageResource);
		$this->imageResource = $newImageResource;
		$this->metaData['width'] = $newWidth;
		$this->metaData['height'] = $newHeight;
	}

	/**
	* Crop the image
	* @param int $x X-coordinate to start the cropping rect
	* @param int $y Y-coordinate to start the cropping rect
	* @param int $width Width of the cropping rect
	* @param int $height Height of the cropping rect
	*/
	private function actionCrop($x, $y, $width, $height)
	{
		$destImg = $this->createNewImageResource($width, $height);
		ImageCopy($destImg, $this->imageResource, 0, 0, $x, $y, $width, $height);
		ImageDestroy($this->imageResource);
		$this->imageResource = $destImg;
		$this->metaData['width'] = $width;
		$this->metaData['height']= $height;
	}

	/**
	* Add an overlay image to the current image
	* @param int $x X coordinate of the overlay
	* @param int $y Y coordinate of the overlay
	* @param pointer $overlay Another Image instance to use as overlay
	*/
	private function actionAddoverlay($file, $x, $y, $deltaX = 0, $deltaY = 0)
	{
		$overlay = new GDImage($file);
		list($x, $y) = $this->getCoordinates($x, $y, $overlay->getWidth(), $overlay->getHeight());
		ImageCopy($this->imageResource, $overlay->getImage(), $x + $deltaX, $y + $deltaY, 0, 0, $overlay->getWidth(), $overlay->getHeight());
		$overlay->close();
	}

	/**
	* Put a colored filter over the image
	* @param mixed $color A color (array(r, g, b[, a]) or '#rrggbb:a', rgb - [0...255], a - [0...127])
	* @param int $alpha Alpha value to use [0...127], 0 for opaque, 127 for transparent
	*/
	private function actionAddcoloroverlay($color, $alpha)
	{
		$overlay = $this->createNewImageResource($this->metaData['width'], $this->metaData['height']);
		$color = $this->getColor($color, $overlay);
		ImageFill($overlay, 1, 1, $color);
		ImageCopyMerge($this->imageResource, $overlay, 0, 0, 0, 0, $this->metaData['width'], $this->metaData['height'], $alpha);
		ImageDestroy($overlay);
	}

	/**
	* Add text to the image
	* @param string $text The text to draw on the image
	* @param int $font Font file (eg. verdana.ttf)
	* @param int $size Font size
	* @param mixed $color A Color (array(r, g, b[, a]) or '#rrggbb[:a]', rgb - [0...255], a - [0...127])
	* @param int $x x-coordinate where to put the text
	* @param int $y y-coordinate where to put the text
	* @param int $adjustX
	* @param int $adjustY
	* @param int $angle Angle to put the text in
	* @return array Array with coordinates of the bounding box
	*/
	private function actionAddtext($text, $font, $size, $color, $x, $y, $deltaX = 0, $deltaY = 0, $angle = 0)
	{
		$bbox = imagettfbbox($size, $angle, $font, $text);
		list($bboxHeight, $bboxWidth) = array(abs($bbox[7])-abs($bbox[1]), abs($bbox[4])-abs($bbox[6]));
		list($x, $y) = $this->getCoordinates($x, $y, $bboxWidth, $bboxHeight);
		return imagettftext($this->imageResource, $size, $angle, $x + $deltaX, $y + $deltaY, $this->getColor($color), $font, $text);
	}

	/**
	 * Rotate the current image
	 * @param int $degrees The number of degrees to rotate the image [0...359]
	 * @param bool $ignoretrans Only for use in PHP5 or higher
	 * @param string $bgcolor Background color to fill empty space with (if degrees not a multiple of 90)
	 */
	private function actionRotate($degrees, $bgcolor = '#000000')
	{
		$degrees = 360 - ($degrees % 360);
		if ($degrees == 0) return;

		if (!function_exists('imagerotate'))
		{
			throw new CException('Rotate not available');
			return;
		}

		$bgcolor = $this->getColor($bgcolor);
		$this->imageResource = ImageRotate($this->imageResource, $degrees, $bgcolor);
		$this->metaData['width'] = imagesx($this->imageResource);
		$this->metaData['height'] = imagesy($this->imageResource);
	}

	/**
	* Output the image in another format the input image format
	* @var string $type Image type to output. Defaults to the image type of the input
	* @var int $quality Quality of the image to output. Defaults to 95
	*/
	private function actionConvert($type, $quality = 95)
	{
		switch (strtolower($type))
		{
			case 'png':
				$this->outputImageType = IMAGETYPE_PNG;
				break;
			case 'gif':
				$this->outputImageType = IMAGETYPE_GIF;
				break;
			case 'jpg':
			case 'jpeg':
			default:
				$this->outputImageType = IMAGETYPE_JPEG;
				$this->outputImageQuality = $quality;
				break;
		}
	}

	private function createNewImageResource($width, $height)
	{
		if (function_exists('ImageCreateTrueColor'))
		{
			$newImageResource = ImageCreateTrueColor($width, $height);
			if ( ($this->metaData['type'] == IMAGETYPE_GIF) || ($this->metaData['type'] == IMAGETYPE_PNG) )
			{
				$transparancyIndex = ImageColorTransparent($this->imageResource);

				// If we have a specific transparent color
				if ($transparancyIndex >= 0 && $transparancyColor = @ImageColorsForIndex($this->imageResource, $transparancyIndex))
				{
					$transparancyIndex = ImageColorAllocate($newImageResource, $transparancyColor['red'], $transparancyColor['green'], $transparancyColor['blue']);
					ImageFill($newImageResource, 0, 0, $transparancyIndex);
					ImageColorTransparent($newImageResource, $transparancyIndex);
				}
				else if ($this->metaData['type'] == IMAGETYPE_PNG)
				{
					ImageAlphaBlending($newImageResource, false);
					$color = ImageColorAllocateAlpha($newImageResource, 0, 0, 0, 127);
					ImageFill($newImageResource, 0, 0, $color);
					ImageSaveAlpha($newImageResource, true);
				}
			}
			return $newImageResource;
		}
		return ImageCreate($width, $height);
	}

	/**
	* Load an image from a file
	* @param string $file File to load the image from
	*/
	private function load()
	{
		if ($this->fileLoaded) return;
		$info = getImageSize($this->filename);
		$this->metaData = array(
			'width' => $info[0],
			'height' => $info[1],
			'type' => $info[2],
		);

		switch($this->metaData['type'])
		{
			case IMAGETYPE_GIF:
				$this->imageResource = ImageCreateFromGif($this->filename);
				$this->outputImageType = IMAGETYPE_GIF;
				break;
			case IMAGETYPE_PNG:
				$this->imageResource = ImageCreateFromPng($this->filename);
				$this->outputImageType = IMAGETYPE_PNG;
				break;
			case IMAGETYPE_JPEG:
				$this->imageResource = ImageCreateFromJpeg($this->filename);
				$this->outputImageType = IMAGETYPE_JPEG;
				$this->outputImageQuality = 95;
				break;
			default:
				throw new CException('Unknown image type: '.$this->metaData['type'].' ('.$this->filename.')');
				break;
		}
		$this->fileLoaded = true;
	}

	/**
	* Write the current image to disk
	* @param string $type The imagetype to save the file as (usually JPEG, PNG or GIF)
	* @param string $location Location to save the image (make sure the target dir is web-writable!)
	* @param int $quality Quality of the image
	*/
	public function save($location)
	{
		switch($this->outputImageType)
		{
			case IMAGETYPE_GIF:
				ImageAlphablending($this->imageResource, false);
				ImageSaveAlpha($this->imageResource, true);
				ImageGif($this->imageResource, $location);
				break;
			case IMAGETYPE_PNG:
				ImageAlphablending($this->imageResource, false);
				ImageSaveAlpha($this->imageResource, true);
				ImagePng($this->imageResource, $location);
				break;
			case IMAGETYPE_JPEG:
			default:
				ImageJpeg($this->imageResource, $location, $this->outputImageQuality);
				break;
		}
		return( array($this->metaData['width'], $this->metaData['height']) );
	}

	/**
	* Get the width of the current image
	* @return int The width of the current image (in pixels)
	*/
	public function getWidth()
	{
		$this->load();
		return $this->metaData['width'];
	}

	/**
	* Get the heigth of the current image
	* @return int The height of the current image (in pixels)
	*/
	public function getHeight()
	{
		$this->load();
		return $this->metaData['height'];
	}

	/**
	* Get the type of the current image
	* @return int The type of the current image
	*/
	public function getType()
	{
		$this->load();
		return $this->metaData['type'];
	}

	/**
	* Get the filename of the image
	* This might have been changed due to image type conversion
	* @return string file name
	*/
	public function getFileExtension()
	{
		switch($this->outputImageType)
		{
			case IMAGETYPE_PNG:
				return 'png';
			case IMAGETYPE_GIF:
				return 'gif';
			case IMAGETYPE_JPEG:
			default:
				return 'jpg';
		}
	}

	/**
	* Returns the image resource
	* @return pointer The image resource of the current image
	*/
	public function getImage()
	{
		$this->load();
		return $this->imageResource;
	}

	/**
	* Destructor
	*/
	public function __destruct()
	{
		$this->close();
	}

	/**
	* Destory the image resource, cleaning up memory
	*/
	public function close()
	{
		@ImageDestroy($this->imageResource);
		$this->fileLoaded = false;
	}

	/**
	* Calculate true X,Y from natural language (top, bottom, left, right, center)
	* @param mixed $x x-coordinate
	* @param mixed $y y-coordinate
	* @param mixed $dX Delta for the x-coordinate
	* @param mixed $dY Delta for the y-coordinate
	*/
	private function getCoordinates($x, $y, $dX, $dY)
	{
		if (!is_numeric($x))
		{
			if ($x == 'left')
				$x = 0;
			else if ($x == 'right')
				$x = $this->getWidth() - $dX;
			else if ($x == 'center')
				$x = floor(($this->getWidth() - $dX) / 2);
		}

		if (!is_numeric($y))
		{
			if ($y == 'top')
				$y = 0;
			else if ($y == 'bottom')
				$y = $this->getHeight() - $dY;
			else if ($y == 'center')
				$y = floor(($this->getHeight() - $dY) / 2);
		}

		return array($x, $y);
	}

	/**
	 * Get a color for the current image from RGB array or a string
	 * @param mixed $color A color (array(r, g, b[, a]) or '#rrggbb:a', rgb - [0...255], a - [0...127])
	 */
	private function getColor($color, $imageResource = null)
	{
		if (is_null($imageResource))
			$imageResource = $this->imageResource;

		if (is_array($color))
		{
			if (count($color) == 3) // array w/o alpha
				return ImageColorAllocate($imageResource, $color[0], $color[1], $color[2]);

			if (count($color) == 4) // array w/ alpha
				return ImageColorAllocateAlpha($imageResource, $color[0], $color[1], $color[2], $color[3]);
		}
		else if (is_string($color))
		{
			if (!strpos($color, ':')) // string w/o alpha
				return ImageColorAllocate($imageResource, hexdec(substr($color, 1, 2)), hexdec(substr($color, 3, 2)), hexdec(substr($color, 5, 2)));

			else // string w/ alpha
				return ImageColorallocateAlpha($imageResource, hexdec(substr($color, 1, 2)), hexdec(substr($color, 3, 2)), hexdec(substr($color, 5, 2)), substr($color, 8, strlen($color) - 8));
		}
	}

	/**
	* Used internally only
	* Makes an estimation of the memory needed to load the image and tries to raise the PHP memory limit
	* @ignore
	*/
	private function raiseMemoryLimit()
	{
		if (ini_get('suhosin.memory_limit'))
			ini_set('memory_limit', ini_get('suhosin.memory_limit'));
	}

	/**
	* Used internally only
	* @ignore
	*/
	private function restoreMemoryLimit()
	{
		ini_restore('memory_limit');
	}
}