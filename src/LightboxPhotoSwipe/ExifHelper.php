<?php
namespace LightboxPhotoSwipe;

/**
 * Functions to get EXIF data from images
 */
class ExifHelper
{
    private $exifData;

    /**
     * Try to read EXIF data from image
     */
    function readExifDataFromFile(string $file, string $extension)
    {
        $this->exifData = false;
        if (function_exists('exif_read_data')) {
            $this->exifData = @exif_read_data($file, 'EXIF', true);
        }

        if($this->exifData) {
            return true;
        }

        return false;
    }

    /**
     * Get current EXIF data array
     */
    function getExifData()
    {
        return $this->exifData;
    }

    /**
     * Get the camera model
     */
    function getCamera()
    {
        $make = '';
        if (isset($this->exifData['IFD0']['Make'])) {
            $make = $this->exifData['IFD0']['Make'];
        }

        $model = '';
        if (isset($this->exifData['IFD0']['Model'])) {
            $model .= $this->exifData['IFD0']['Model'];
        }

        $camera = '';
        if (strlen($make)>0) {
            if (substr($model, 0, strlen($make)) == $make) {
                $camera = $model;
            } else {
                $camera = $make . ' ' . $model;
            }
        } else {
            $camera = $model;
        }

        return $camera;
    }

    /**
     * Get the lens model
     */
    function getLens()
    {
        $make = '';
        if (isset($this->exifData['EXIF']['UndefinedTag:0xA433'])) {
            $make = $this->exifData['EXIF']['UndefinedTag:0xA433'];
        } else if (isset($this->exifData['EXIF']['LensMake'])) {
            $make = $this->exifData['EXIF']['LensMake'];
        }

        $model = '';
        if (isset($this->exifData['EXIF']['UndefinedTag:0xA434'])) {
            $model = $this->exifData['EXIF']['UndefinedTag:0xA434'];
        } else if (isset($this->exifData['EXIF']['LensModel'])) {
            $model .= $this->exifData['EXIF']['LensModel'];
        }

        $lens = '';
        if (strlen($make)>0) {
            if (substr($model, 0, strlen($make)) == $make) {
                $lens = $model;
            } else {
                $lens = $make . ' ' . $model;
            }
        } else {
            $lens = $model;
        }

        return $lens;
    }

    /**
     * Get the focal length
     */
    function getFocalLength()
    {
        $focal = '';
        if (isset($this->exifData['EXIF']['FocalLength'])) {
            $focal = $this->exifData['EXIF']['FocalLength'];
        } else if (isset($this->exifData['EXIF']['FocalLengthIn35mmFilm'])) {
            $focal = $this->exifData['EXIF']['FocalLengthIn35mmFilm'];
        } else {
            return '';
        }
        $focalLength = $this->exifGetFloat($focal);
        return round($focalLength) . 'mm';
    }

    /**
     * Get the shutter speed
     */
    function getShutter()
    {
        // Variant 1: ExposureTime as numerator/denominator (e.g. "1/50" or "35/10")
        if (isset($this->exifData['EXIF']['ExposureTime'])) {
            $parts = explode('/', $this->exifData['EXIF']['ExposureTime']);
            // Exposure times with a numerator of more than 1 will always be
            // displays as float value with 2 decimals maximum (e.g. 35/10 = "3.5s" or 60/1 = "60s" etc.)
            if ((float) $parts[0] != 1) {
                if ((float) $parts[1] == 0) {
                    return '';
                }
                return round((float) $parts[0] / (float) $parts[1], 2) . 's';
            }
            // Numerator is 1, then return as fraction like "1/30s" or "1/4s" etc.
            return $parts[0] . '/' . $parts[1] . 's';
        }

        // Variant 2: ShutterSpeedValue as APEX value
        if (!isset($this->exifData['EXIF']['ShutterSpeedValue'])) {
            return '';
        }
        $apex = $this->exifGetFloat($this->exifData['EXIF']['ShutterSpeedValue']);
        $shutter = pow(2, -$apex);
        if ($shutter == 0) {
            return '';
        }
        if ($shutter >= 1) {
            return round($shutter) . 's';
        }
        return '1/' . round(1 / $shutter) . 's';
    }

    /**
     * Get ISO speed rating
     */
    function getIso()
    {
        if (!isset($this->exifData['EXIF']['ISOSpeedRatings'])) {
            return '';
        }
        return 'ISO' . $this->exifData['EXIF']['ISOSpeedRatings'];
    }

    /**
     * Get the date taken
     */
    function getDateTime()
    {
        $result = '';

        if (isset($this->exifData['EXIF']['DateTimeOriginal'])) {
            $dateString = $this->exifData['EXIF']['DateTimeOriginal'];
            return sprintf('%s-%s-%s %s:%s:%s',
                substr($dateString, 0, 4),
                substr($dateString, 5, 2 ),
                substr($dateString, 8, 2),
                substr($dateString, 11, 2),
                substr($dateString, 14, 2 ),
                substr($dateString, 17, 2)
            );
        }

        return $result;
    }

    /**
     * Get the f-stop
     */
    function getFstop()
    {
        $fstop = 0;

        if (isset($this->exifData['EXIF']['ApertureValue'])) {
            $aperture = $this->exifData['EXIF']['ApertureValue'];
            $apex  = $this->exifGetFloat($aperture);
            $fstop = pow(2, $apex/2);
        } if (isset($this->exifData['EXIF']['FNumber'])) {
        $fstop = $this->exifGetFloat($this->exifData['EXIF']['FNumber']);
    }

        if (0 === $fstop) {
            return '';
        }

        return '𝑓/' . round($fstop,1);
    }

    function getOrientation()
    {
        if (isset($this->exifData['IFD0']['Orientation'])) {
            return $this->exifData['IFD0']['Orientation'];
        }

        return 1;
    }

    /**
     * Build caption string based on given parameters
     */
    function buildCaptionString($focal, $fstop, $shutter, $iso, $date, $camera, $lens, $includeDate, $includeLens)
    {
        $caption = '';

        $this->addToCaption($caption, $camera, 'camera');
        if ($includeLens) {
            $this->addToCaption($caption, $lens, 'lens');
        }
        $this->addToCaption($caption, $focal, 'focal');
        $this->addToCaption($caption, $fstop, 'fstop');
        $this->addToCaption($caption, $shutter, 'shutter');
        $this->addToCaption($caption, $iso, 'iso');
        if ($includeDate) {
            $dateTimeValue = date_create_from_format('Y-m-d H:i:s', $date);
            if (false !== $dateTimeValue) {
                $dateCaption = date_i18n( get_option( 'date_format' ), $dateTimeValue->getTimestamp());
                $this->addToCaption( $caption, $dateCaption, 'datetime');
            }
        }

        return $caption;
    }

    /**
     * Add some detail to the EXIF output
     */
    private function addToCaption(&$output, $detail, $cssclass)
    {
        if('' === $detail) {
            return;
        }
        $output .= sprintf('<span class="pswp__caption__exif_%s">%s</span> ', $cssclass, htmlspecialchars($detail));
    }

    /**
     * Get a float value from an EXIF value
     */
    private function exifGetFloat($value)
    {
        $parts = explode('/', $value);
        if (!isset($parts[1])) {
            return (float) $value;
        }
        return ($parts[1] == 0) ? (float) $parts[0] : (float) ($parts[0] / $parts[1]);
    }
}
