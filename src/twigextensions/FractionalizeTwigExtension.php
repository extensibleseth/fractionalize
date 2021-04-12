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
            new TwigFunction('someFunction', [$this, 'someInternalFunction']),
        ];
    }

    /**
     * Our function called via Twig; it can do anything you want
     *
     * @param null $text
     *
     * @return string
     */
    public function someInternalFunction($text = null)
    {
        $result = $text . " in the way";

        return $result;
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
    public function convert_decimal_to_fraction($decimal){
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
    public function devMode(string $text): text
    {
      $devMode = Craft::$app->getConfig()->general->devMode;
      if ($devMode !== FALSE) {
        return $text;
      }
      else
      {
        return false;
      }
    }
}
