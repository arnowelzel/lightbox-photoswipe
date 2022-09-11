<?php
namespace LightboxPhotoSwipe;

/**
 * Functions to get EXIF data from images
 */
class ExifHelper
{
    private $exifData;

    /**
     * Set EXIF data array to be used
     */
    function setExifData(array $exifData)
    {
        $this->exifData = $exifData;
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
     * Get the focal length
     */
    function getFocalLength()
    {
        $focal = '';
        if (isset($this->exifData['EXIF']['FocalLengthIn35mmFilm'])) {
            $focal = $this->exifData['EXIF']['FocalLengthIn35mmFilm'];
        } else if (isset($this->exifData['EXIF']['FocalLength'])) {
            $focal = $this->exifData['EXIF']['FocalLength'];
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
        if (isset($this->exifData['EXIF']['ExposureTime'])) {
            return $this->exifData['EXIF']['ExposureTime'].'s';
        }
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
            $this->exifDataDate = $this->exifData['EXIF']['DateTimeOriginal'];
            $date = substr($this->exifDataDate, 0, 4).'-'.substr($this->exifDataDate, 5, 2 ).'-'.substr($this->exifDataDate, 8, 2).
                ' '.substr($this->exifDataDate, 11, 2).':'.substr($this->exifDataDate, 14, 2 ).':'.substr($this->exifDataDate, 17, 2);
            return $date;
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

        return 'f/' . round($fstop,1);
    }

    /**
     * Build caption string based on given parameters
     */
    function buildCaptionString($focal, $fstop, $shutter, $iso, $date, $camera, $includeDate)
    {
        $caption = '';

        $this->addToCaption($caption, $camera, 'camera');
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
        $pos = strpos($value, '/');
        if ($pos === false) {
            return (float) $value;
        }
        $a = (float) substr($value, 0, $pos);
        $b = (float) substr($value, $pos+1);
        return ($b == 0) ? ($a) : ($a / $b);
    }
}
