<?php
namespace LightboxPhotoSwipe;

/**
 * Parser for EXIF binary paylod in images
 */
class ExifParser
{
    private $binaryData;
    private $exifData;
    private $position;
    private $formatIntel;

    /**
     * Parse the given data
     */
    public function parse($binaryData)
    {
        $this->binaryData = $binaryData;
        $this->exifData = [];
        $this->position = 0;
        $this->formatIntel = true;
        $header = $this->fetchBytes(2);

        // Analyze header
        if($header==="II") {
            $this->formatIntel = true;
        } else if($header==="MM") {
            $this->formatIntel = false;
        }
        $tag = bin2hex($this->fetchBytes(2));
        $offset = $this->getHexValue($this->fetchBytes(4));
        $offset = hexdec($offset);
        if ($offset > 100000) {
            return false;
        }
        if ($offset > 8) {
            $this->position += ($offset - 8);
        }

        $numTags = hexdec($this->getHexValue($this->fetchBytes(2)));
        if ($numTags > 1000) {
            return false;
        }
        $offsetEXIF = false;

        for ($tag = 0; $tag < $numTags; $tag++) {
            $entry = $this->readEntry();
            if ($entry['tagName'] === 'Exif_IFD_Pointer') {
                $offsetEXIF = $entry['value'];
            } else if ($entry['tagName'] !== 'GPS_IFD_Pointer') {
                $this->exifData['IFD0'][$entry['tagName']] = $entry['value'];
            }
        }

        if ($offsetEXIF) {
            $this->position = $offsetEXIF;
            $numTags = hexdec($this->getHexValue($this->fetchBytes(2)));
            if ($numTags > 1000) {
                return false;
            }

            for ($tag = 0; $tag < $numTags; $tag++) {
                $entry = $this->readEntry();
                $this->exifData['EXIF'][$entry['tagName']] = $entry['value'];
            }
        }

        return $this->exifData;
    }

    /**
     * Fetch a number of bytes from the binary data buffer
     */
    private function fetchBytes($count)
    {
        if (strlen($this->binaryData) >= $this->position + $count) {
            $result = substr($this->binaryData, $this->position, $count);
            $this->position += $count;
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Get hex string for binary data
     */
    function getHexValue($data)
    {
        $value = bin2hex($data);
        if ($this->formatIntel) {
            $len = strlen($value);
            $reversed = '';
            for($i=0; $i<=$len; $i+=2) {
                $reversed .= substr($value,$len-$i,2);
            }
            $value = $reversed;
        }
        return $value;
    }

    /**
     * Read data entry at the current position from the binary data buffer
     */
    private function readEntry()
    {
        $tag = $this->getHexValue($this->fetchBytes(2));
        $tagName = $this->getTagName($tag);

        $typeId = $this->getHexValue($this->fetchBytes(2));
        $size = 0;
        $this->getType($typeId, $type, $size);

        $count = $this->getHexValue($this->fetchBytes(4));
        $bytesofdata = $size * hexdec($count);

        $value = $this->fetchBytes(4);
        $data = false;

        if ($bytesofdata <= 4) {
            $data = $value;
        } else if ($bytesofdata < 100000) {
            $offset = hexdec($this->getHexValue($value));
            $data = substr($this->binaryData, $offset, $bytesofdata);
        } else {
            return;
        }

        return [
            'tagName' => $tagName,
            'value' => $this->formatData($type, $tag, $data),
        ];
    }

    /**
     * Get EXIF datatype and length for given ID
     */
    private function getType($typeId, &$type, &$size) {
        switch($typeId) {
            case "0001":
                $type = "UBYTE";
                $size = 1;
                break;
            case "0002":
                $type = "ASCII";
                $size = 1;
                break;
            case "0003":
                $type = "USHORT";
                $size = 2;
                break;
            case "0004":
                $type = "ULONG";
                $size = 4;
                break;
            case "0005":
                $type = "URATIONAL";
                $size = 8;
                break;
            case "0006":
                $type = "SBYTE";
                $size = 1;
                break;
            case "0007":
                $type = "UNDEFINED";
                $size = 1;
                break;
            case "0008":
                $type = "SSHORT";
                $size=2;
                break;
            case "0009":
                $type = "SLONG";
                $size = 4;
                break;
            case "000a":
                $type = "SRATIONAL";
                $size = 8;
                break;
            case "000b":
                $type = "FLOAT";
                $size = 4;
                break;
            case "000c":
                $type = "DOUBLE";
                $size = 8;
                break;
            default:
                $type = "UNKNOWN-".$typeId;
                $size = 0;
                break;
        }
        return $type;
    }


    function getTagName($tag) {
        $tagNames = [
            '0001' => 'InteroperabilityIndex',
            '0002' => 'InteroperabilityVersion',
            '000b' => 'ACDComment',
            '00fe' => 'ImageType',
            '00ff' => 'SubfileType',
            '0100' => 'ImageWidth',
            '0101' => 'ImageLength',
            '0102' => 'BitsPerSample',
            '0103' => 'Compression',
            '0106' => 'PhotometricInterpretation',
            '010e' => 'ImageDescription',
            '010f' => 'Make',
            '0110' => 'Model',
            '0111' => 'StripOffsets',
            '0112' => 'Orientation',
            '0116' => 'RowsPerStrip',
            '0117' => 'StripByteCounts',
            '0115' => 'SamplesPerPixel',
            '011a' => 'XResolution',
            '011b' => 'YResolution',
            '011c' => 'PlanarConfiguration',
            '0128' => 'ResolutionUnit',
            '012d' => 'TransferFunction',
            '0131' => 'Software',
            '0132' => 'DateTime',
            '013b' => 'Artist',
            '013c' => 'HostComputer',
            '013d' => 'Predictor',
            '013e' => 'WhitePoint',
            '013f' => 'PrimaryChromaticities',
            '0142' => 'TileWidth',
            '0143' => 'TileLength',
            '0144' => 'TileOffsets',
            '0145' => 'TileByteCounts',
            '014a' => 'SubIFDs',
            '015b' => 'JPEGTables',
            '0201' => 'JpegIFOffset',
            '0202' => 'JpegIFByteCount',
            '0212' => 'YCbCrSubSampling',
            '0211' => 'YCbCrCoefficients',
            '0213' => 'YCbCrPositioning',
            '0214' => 'ReferenceBlackWhite',
            '1000' => 'RelatedImageFileFormat',
            '1001' => 'RelatedImageWidth',
            '1002' => 'RelatedImageLength',
            '828d' => 'CFARepeatPatternDim',
            '828e' => 'CFAPattern',
            '828f' => 'BatteryLevel',
            '8298' => 'Copyright',
            '829a' => 'ExposureTime',
            '829d' => 'FNumber',
            '83bb' => 'IPTC/NAA',
            '8649' => 'PhotoshopSettings',
            '8769' => 'Exif_IFD_Pointer',
            '8822' => 'ExposureProgram',
            '8824' => 'SpectralSensitivity',
            '8825' => 'GPS_IFD_Pointer',
            '8827' => 'ISOSpeedRatings',
            '8773' => 'InterColorProfile',
            '8828' => 'OECF',
            '8829' => 'Interlace',
            '882a' => 'TimeZoneOffset',
            '882b' => 'SelfTimerMode',
            '9000' => 'ExifVersion',
            '9003' => 'DateTimeOriginal',
            '9004' => 'DateTimedigitized',
            '9101' => 'ComponentsConfiguration',
            '9102' => 'CompressedBitsPerPixel',
            '9201' => 'ShutterSpeedValue',
            '9202' => 'ApertureValue',
            '9203' => 'BrightnessValue',
            '9204' => 'ExposureBiasValue',
            '9205' => 'MaxApertureValue',
            '9206' => 'SubjectDistance',
            '9207' => 'MeteringMode',
            '9208' => 'LightSource',
            '9209' => 'Flash',
            '920a' => 'FocalLength',
            '920b' => 'FlashEnergy',
            '920c' => 'SpatialFrequencyResponse',
            '920d' => 'Noise',
            '9211' => 'ImageNumber',
            '9212' => 'SecurityClassification',
            '9213' => 'ImageHistory',
            '9214' => 'SubjectLocation',
            '9215' => 'ExposureIndex',
            '9216' => 'TIFF/EPStandardID',
            '927c' => 'MakerNote',
            '9286' => 'UserComment',
            '9290' => 'SubsecTime',
            '9291' => 'SubsecTimeOriginal',
            '9292' => 'SubsecTimeDigitized',
            'a000' => 'FlashPixVersion',
            'a001' => 'ColorSpace',
            'a002' => 'ExifImageWidth',
            'a003' => 'ExifImageHeight',
            'a004' => 'RelatedSoundFile',
            'a005' => 'ExifInteroperabilityOffset',
            'a20c' => 'SpacialFreqResponse',
            'a20b' => 'FlashEnergy',
            'a20e' => 'FocalPlaneXResolution',
            'a20f' => 'FocalPlaneYResolution',
            'a210' => 'FocalPlaneResolutionUnit',
            'a214' => 'SubjectLocation',
            'a215' => 'ExposureIndex',
            'a217' => 'SensingMethod',
            'a300' => 'FileSource',
            'a301' => 'SceneType',
            'a302' => 'CFAPattern',
            'a401' => 'CustomerRender',
            'a402' => 'ExposureMode',
            'a403' => 'WhiteBalance',
            'a404' => 'DigitalZoomRatio',
            'a406' => 'SceneCaptureMode',
            'a407' => 'GainControl',
            'a408' => 'Contrast',
            'a409' => 'Saturation',
            'a40a' => 'Sharpness',
        ];

        if (isset($tagNames[$tag])) {
            return $tagNames[$tag];
        }

        return false;
    }

    private function formatData($type, $tag, $data) {
        if('ASCII' === $type) {
            // Make sure there are no unwanted leading or trailing spaces or null bytes
            $data = trim($data);
        } else if('URATIONAL' === $type || 'SRATIONAL' == $type) {
            $data = $this->getHexValue($data);

            if ($this->formatIntel) {
                $top = hexdec(substr($data, 8, 8));
            } else {
                $top = hexdec(substr($data, 0, 8));
            }

            if($this->formatIntel) {
                $bottom = hexdec(substr($data,0,8));
            }
            else {
                $bottom = hexdec(substr($data,8,8));
            }

            if ('SRATIONAL' === $type && $top>2147483647) {
                // This is a signed value
                $top = $top - 4294967296;
            }
            if(0 !== $bottom ) {
                $data = $top / $bottom;
            }
            else if(0 === $top) {
                $data = 0;
            }
            else {
                $data = $top."/".$bottom;
            }

            if (('011a' === $tag || '011b' == $tag) && $bottom==1) {
                // XResolution YResolution
                $data = $top;
            } else if ('829a' === $tag) {
                // Exposure Time
                if($top/10 == 1) {
                    $data = "1/".round($bottom / 10, 0);
                } else {
                    $data = $top."/".$bottom;
                }
            } else if('829d' === $tag) {
                // FNumber
            } else if('9204' === $tag) {
                // ExposureBiasValue
            } else if('9205' === $tag || '9202' === $tag) {
                // ApertureValue and MaxApertureValue
                $data = exp(($data * log(2)) / 2);
                $data = round($data, 1);
            } else if($tag=="920a") {
                // FocalLength
            } else if($tag=="9201") {
                // ShutterSpeedValue
                $data = exp($data * log(2));
                if ($data > 1) $data = floor($data);
                if ($data > 0) {
                    $data = 1 / $data;
                    $nominator = 0;
                    $denominator = 0;
                    $this->convertToFraction($data, $nominator, $denominator);
                    if ($nominator >= 1 && $denominator == 1) $data = $nominator;
                    else $data = $nominator.'/'.$denominator;
                } else {
                    $data = "b";
                }
            }

        } else if (in_array($type, ['USHORT', 'SSHORT', 'ULONG', 'SLONG', 'FLOAT', 'DOUBLE'])) {
            $data = $this->getHexValue($data);
            if(!$this->formatIntel && ('USHORT' === $type || 'SSHORT' === $type)) {
                $data = substr($data,0,4);
            } else {
                $data = substr($data, 4, 4);
            }
            $data=hexdec($data);

            if ('SSHORT' === $type && $data > 32767) {
                // This is a signed value
                $data = $data - 65536;
            }
            if ('SLONG' === $type && $data > 2147483647) {
                // This is a signed value
                $data = $data - 4294967296;
            }

            if ('0112' === $tag) {
                // Orientation
                // 1 - normal
                // 2 - mirrored
                // 3 - upsidedown
                // 4 - upsidedown mirrored
                // 5 - 90 degree clockwise mirrored
                // 6 - 90 degree counter clockwise
                // 7 - 90 degree counter clockwise mirrored
                // 8 - 90 degree clockwise
            } else if('0128' === $tag || 'a210' === $tag) {
                // ResolutionUnit, FocalPlaneResolutionUnit and ThumbnailResolutionUnit
                // 1 - no unit
                // 2 - inch
                // 3 - centimeter
            } else if('0213' === $tag) {
                // YCbCrPositioning
                // 1 - Center of pixel array
                // 2 - Datum point
            } else if ('8822' === $tag) {
                // ExposureProgram
                // 1 - Manual
                // 2 - Program
                // 3 - Aperature Priority
                // 4 - Shutter Priority
                // 5 - Program Creative
                // 6 - Program Action
                // 7 - Portrat
                // 8 - Landscape
            } else if ('9207' === $tag) {
                // MeteringMode
                // 0 - Unkown
                // 1 - Average
                // 2 - Center Weighted Average
                // 3 - Spot
                // 4 - Multi-Spot
                // 5 - Multi-Segment
                // 6 - Partial
                // 255 - Other
            } else if ('9208' === $tag) {
                // LightSource
                // 0 - Unknown or Auto
                // 1 - Daylight
                // 2 - Flourescent
                // 3 - Tungsten
                // 10 - Flash
                // 17 - Standard Light A
                // 18 - Standard Light B
                // 19 - Standard Light C
                // 20 - D55
                // 21 - D65
                // 22 - D75
                // 255 - Other
            } else if('9209' === $tag) {
                // Flash
                // 0 - No flash
                // 1 - Flash
                // 5 - Flash, strobe return light not detected
                // 7 - Flash, strob return light detected
                // 9 - Compulsory flash
                // 13 - Compulsory flash, return light not detected
                // 15 - Compulsory flash, return light detected
                // 16 - No flash;
                // 24 - No flash;
                // 25 - Flash, auto-mode
                // 29 - Flash, auto-mode, return light not detected
                // 31 - Flash, auto-mode, return light detected
                // 32 - No flash
                // 65 - Red eye
                // 69 - Red eye, return light not detected
                // 71 - Red eye, return light detected
                // 73 - Red eye, compulsory flash
                // 77 - Red eye, compulsory flash, return light not detected
                // 79 - Red eye, compulsory flash, return light detected
                // 89 - Red eye, auto-mode
                // 93 - Red eye, auto-mode, return light not detected
                // 95 - Red eye, auto-mode, return light detected
            } else if ('a001' === $tag) {
                // ColorSpace
                // 1 - sRGB
            } else if('a002' === $tag || 'a003' === $tag) {
                // ExifImageWidth, ExifImageWidthHeight
            } else if ('0103' === $tag) {
                // Compression
                // 1 - No compression
                // 6 - JPEG compression
            } else if ('a217' === $tag) {
                // SensingMethod
                // 1 - Not defined
                // 2 - One Chip Color Area Sensor
                // 3 - Two Chip Color Area Sensor
                // 4 - Three Chip Color Area Sensor
                // 5 - Color Sequential Area Sensor
                // 7 - Trilinear Sensor
                // 8 - Color Sequential Linear Sensor
            } else if('0106' === $tag) {
                // PhotometricInterpretation
                // 1 - Monochrome
                // 2 - RGB
                // 6 - YCbCr
            }

        } else if ('UNDEFINED' === $type) {
            if('9000' === $tag || 'a000' === $tag || '0002' === $tag) {
                // ExifVersion, FlashPixVersion, InteroperabilityVersion
                $data = $data / 100;
            }
            if('a300' === $tag) {
                // FileSource
                // 0 - None
                // 3 - Digital still camera
            }
            if('a301' === $tag) {
                // SceneType
                // 0 - None
                // 1 - Directly photographed
            }
            if($tag=="9101") {
                // ComponentsConfiguration
                // 0 - None
                // 1 - Y
                // 2 - Cb
                // 3 - Cr
                // 4 - R
                // 5 - G
                // 6 - B
            }
        } else {
            $data = $this->getHexValue($data);
        }

        return $data;
    }

    private function convertToFraction($value, &$nominator, &$denominator)
    {
        $maxLoops = 15;
        $minDenominator = 0.000001;
        $maxError = 0.00000001;

        $f = $value;
        $nominatorOne = 1;
        $denominatorOne = 0;
        $nominatorTwo = 0;
        $denominatorTwo = 1;

        $loopCount = 0;
        while ($loopCount < $maxLoops)
        {
            $a = floor($f);
            $f = $f - $a;
            $nominator = $nominatorOne * $a + $nominatorTwo;
            $denominator = $denominatorOne * $a + $denominatorTwo;
            $nominatorTwo = $nominatorOne;
            $denominatorTwo = $denominatorOne;
            $nominatorOne = $nominator;
            $denominatorOne = $denominator;

            if ($f < $minDenominator)
                break;

            if (abs($value - $nominator / $denominator) < $maxError)
                break;

            $f = 1 / $f;
            $loopCount++;
        }
    }
}