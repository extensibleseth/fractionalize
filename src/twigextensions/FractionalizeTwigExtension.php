<?php
/**
 * Fractionalize plugin for Craft CMS 3.x
 *
 * Provides a twig filter to display decimal values as fractions.
 *
 * @link      https://github.com/extensibleseth/
 * @copyright Copyright (c) 2020 Seth Hendrick
 */

namespace extensibleseth\fractionalize\twigextensions;

use extensibleseth\fractionalize\Fractionalize;

use Craft;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\Extra\Intl\IntlExtension;

/**
 * Twig can be extended in many ways; you can add extra tags, filters, tests, operators,
 * global variables, and functions. You can even extend the parser itself with
 * node visitors.
 *
 * http://twig.sensiolabs.org/doc/advanced.html
 *
 * @author    Seth Hendrick
 * @package   Fractionalize
 * @since     0.1.0
 */
class FractionalizeTwigExtension extends AbstractExtension
{
    // Public Methods
    // =========================================================================

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'Fractionalize';
    }

    /**
     * Returns an array of Twig filters, used in Twig templates via:
     *
     *      {{ 'something' | someFilter }}
     *
     * @return array
     */
    public function getFilters()
    {
        return [
            new TwigFilter('fractionalize', [$this, 'convert_decimal_to_fraction']),
            new TwigFilter('dec2hex', [$this, 'convert_decimal_to_hex']),
            new TwigFilter('devMode', [$this, 'devMode']),
            new TwigFilter('fileExists', [$this, 'fileExists']),
            new TwigFilter('typeOf', [$this, 'getTypeOf']),
            new TwigFilter('existsOrTrue', [$this, 'existsOrTrue']),
        ];
    }

    /**
     * Returns an array of Twig functions, used in Twig templates via:
     *
     *      {% set this = someFunction('something') %}
     *
    * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('fractionalize', [$this, 'convert_decimal_to_fraction']),
            new TwigFunction('dec2hex', [$this, 'convert_decimal_to_hex']),
            new TwigFunction('devMode', [$this, 'devMode']),
            new TwigFunction('fileExists', [$this, 'fileExists']),
            new TwigFunction('typeOf', [$this, 'getTypeOf']),
            new TwigFunction('existsOrTrue', [$this, 'existsOrTrue'])
        ];
    }


    /**
     * Takes a base 10 int, returns a hexidecimal number string.
     *
     * @param null $dec
     *
     * @return string 
     */
    public function convert_decimal_to_hex($dec)
    {

        $options = array(
          'options' => array('min_range' => 0)
        );

        if (filter_var($dec, FILTER_VALIDATE_INT, $options) !== FALSE) {
          $hex = base_convert(strval($dec), 10, 16);
          if (strlen($hex) === 1)
          {
              $hex = '0' . $hex;
          }
          if (strcmp($hex, '256') === 0)
          {
              $hex = '255';
          }
          return $hex;
        }
        else
        {
            return false;
        }
    }


    /**
     * Returns a fraction string.
     *
     * @param null $decimal
     *
     * @return array
     */
    public function convert_decimal_to_fraction($decimal)
    {
        $tolerance = 1.e-3;

        $h1=1; $h2=0;
        $k1=0; $k2=1;
        $b = 1/$decimal;
        do {
            $b = 1/$b;
            $a = floor($b);
            $aux = $h1; $h1 = $a*$h1+$h2; $h2 = $aux;
            $aux = $k1; $k1 = $a*$k1+$k2; $k2 = $aux;
            $b = $b-$a;
        } while (abs($decimal-$h1/$k1) > $decimal*$tolerance);

        $big_fraction = "$h1/$k1";
        $num_array = explode('/', $big_fraction);
        $numerator = $num_array[0];
        $denominator = $num_array[1];
        $whole_number = floor( $numerator / $denominator );
        $numerator = $numerator % $denominator;
    
        if($numerator == 0){
            return $whole_number;
        }else if ($whole_number == 0){
            return $numerator . '/' . $denominator;
        }else{
            return $whole_number . ' ' . $numerator . '/' . $denominator;
        }
    }


    /**
     * Outputs in devMode only
     *
     * @param null $text
     *
     * @return string
     */
    public function devMode(string $text)
    {
      $devMode = Craft::$app->getConfig()->general->devMode ?? null;
      if ($devMode !== FALSE)
      {
        return $text;
      }
      else
      {
        return false;
      }
    }


    /**
     * Our function called via Twig; it can do anything you want
     *
     * @param null $text
     *
     * @return string
     */
    public function getTypeOf($variable)
    {
        return gettype($variable);
    }


    public function existsOrTrue($variable)
    {
        if (isset($variable)) {
            if ($variable == NULL) {
                return false;
            }

            switch (gettype($variable)) {
                case 'NULL':
                    return false;
                    break;
                case 'array':
                    if (sizeof($variable) < 1) {
                        return false;
                    } else {
                        return true;
                    }
                    break;
                case 'string':
                    if (strlen($variable) == 0) {
                        return false;
                    } else {
                        return true;
                    }
                    break;
                case 'integer':
                case 'double':
                    if ($variable == 0) {
                        return false;
                    } else {
                        return true;
                    }
                    break;
                case 'boolean':
                    return $variable;
                    break;
                case 'object':
                    if (sizeof(get_object_vars($variable)) < 1) {
                        return false;
                    } else {
                        return true;
                    }
                    break;
            }
        } else {
            return false;
        }
    }


    /**
     *
     * @param null $url
     *
     * @return string
     */
    public function fileExists($url = null)
    {
        $urlParsed = parse_url($url, PHP_URL_PATH);
        $fullUrl = '.'.'/'.ltrim($urlParsed,'/');
        if (file_exists($fullUrl)) {
            return true;
        }
        if (@file_get_contents($url,0,NULL,0,1)) {
            return true;
        }
        return false;
    }
}