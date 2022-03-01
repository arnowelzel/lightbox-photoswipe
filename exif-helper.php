<?php
namespace LightboxPhotoSwipe;

class ExifHelper
{
    private $exifData;

    /**
     * @param array $exifData
     */
    function setExifData(array $exifData)
    {
        $this->exifData = $exifData;
    }

    /**
     * Helper to get the camera model from an EXIF array
     *
     * @return string  The camera model as readable text
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
     * Helper to get the focal length from an EXIF array
     *
     * @return string      The focal length as readable text (e.h. "100mm")
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
     * Helper to get the shutter speed from an EXIF array
     *
     * @return string      The shutter speed as readable text (e.h. "1/250s")
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
     * Helper to get the ISO speed rating from an EXIF array
     *
     * @return string  The ISO speed rating as readable text
     */
    function getIso()
    {
        if (!isset($this->exifData['EXIF']['ISOSpeedRatings'])) {
            return '';
        }
        return 'ISO' . $this->exifData['EXIF']['ISOSpeedRatings'];
    }

    /**
     * Helper to get the date taken from an EXIF array
     *
     * @return string  The date taken in local date format
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
     * Helper to get the f-stop from an EXIF array
     *
     * @return string      The f-stop value as readable text (e.g. "f/3.5")
     */
    function getFstop()
    {
        $aperture = '';
        if (isset($this->exifData['EXIF']['ApertureValue'])) {
            $aperture = $this->exifData['EXIF']['ApertureValue'];
        } else if (isset($this->exifData['EXIF']['FNumber'])) {
            $aperture = isset($this->exifData['EXIF']['FNumber']);
        } else {
            return '';
        }
        $apex  = $this->exifGetFloat($aperture);
        $fstop = pow(2, $apex/2);
        if ($fstop == 0) return '';
        return 'f/' . round($fstop,1);
    }

    /**
     * Build caption string based on given parameters
     *
     * @return string Caption to be used for EXIF data
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
     * Helper to add some detail to the EXIF output
     *
     * @param $output  Existing output
     * @param $detail  Detail to add
     */
    private function addToCaption(&$output, $detail, $cssclass)
    {
        if('' === $detail) {
            return;
        }
        $output .= sprintf('<span class="pswp__caption__exif_%s">%s</span> ', $cssclass, htmlspecialchars($detail));
    }

    /**
     * Helper to get a float value from an EXIF value
     *
     * @param $value string  The value to work with (e.g. "10/40")
     *
     * @return float|int
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
