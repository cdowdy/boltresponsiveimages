<?php

namespace Bolt\Extension\cdowdy\boltresponsiveimages;

use Bolt\Application;
use Bolt\Asset\File\JavaScript;
use Bolt\Asset\Snippet\Snippet;
use Bolt\Asset\Target;
use Bolt\Controller\Zone;
use Bolt\Extension\SimpleExtension;
use Bolt\Filesystem\Handler\ImageInterface;
use Bolt\Filesystem\Handler\NullableImage;
use Bolt\Helpers\Image\Thumbnail;

/**
 * BoltResponsiveImages extension class.
 *
 * @author Cory Dowdy <cory@corydowdy.com>
 */
class BoltResponsiveImagesExtension extends SimpleExtension
{

    private $_currentPictureFill = '3.0.2';
    private $_currentLazySizes = '1.4.0';
    private $_scriptAdded = FALSE;

    /**
     * {@inheritdoc}
     */
    protected function registerTwigPaths()
    {
        return ['templates'];
    }


    /**
     * {@inheritdoc}
     */
    protected function registerTwigFunctions()
    {
        $options = ['is_safe' => ['html']];

        return [
            'respImg' => ['respImg',  $options ]
        ];
    }


    /**
     * The callback function when {{ my_twig_function() }} is used in a template.
     *
     * @return string
     */
    public function respImg( $file, $name, array $options = array() )
    {


        $this->addAssets();
        // get the config file name if using one. otherwise its 'default'
        $configName = $this->getConfigName($name);

        // gather the default options, merge them with any options passed in the template
        $defaultOptions = $this->getOptions($file, $configName, $options);


        // if a class is set in the config or options pass it to the template
        $htmlClass = $defaultOptions[ 'class' ];

        // test for lazyload
//        $lazy = $defaultOptions['lazyLoad'];


        // add the class "lazyload" to the class array in the template or config
        // also load the lazysizes script in the head with an async tag
//        if ($lazy) {
//            $htmlClass[] = 'lazyload';
//            $this->lazyLoadScript();
//        }

        $optionsWidths = $defaultOptions[ 'widths' ];
        $optionHeights = $defaultOptions[ 'heights' ];
        $resolutions = $defaultOptions[ 'resolutions' ];

        sort($optionsWidths);
        sort($optionHeights);

        // get the alt text for the Image
        // $altText = $this->getAltText($configName, $file);
        $altText = $defaultOptions['altText'];

        // get size attribute if using the W descriptor
        $sizeAttrib = $defaultOptions[ 'sizes' ];

        // Combine the Heights and Widths to use for our thumbnail parameters
        $sizeArray = $this->getCombinedArray($optionsWidths, $optionHeights, 0);


        // get what we need for the cropping parameter
        $cropping = strtolower($defaultOptions['cropping']);

        $densityWidth = $defaultOptions[ 'widthDensity' ];



        // make thumbs an empty array
        $thumb = array();


        // loop through the size array and generate a thumbnail and URL
        // place those in an array to be used in the twig template
        foreach ($sizeArray as $key => $value) {
            $thumb[] .= $this->thumbnail($file, $key, $value, $cropping);
        }

        // use the array below if using the W descriptor
        if ($densityWidth == 'w') {
            $combinedImages = array_combine($thumb, $optionsWidths);
        }

        if ($densityWidth == 'x') {
            $combinedImages = $this->resolutionErrors($thumb, $resolutions);
        }

        // get the smallest (first sizes in the size array) heights and widths for the src image
//        sort($defaultOptions['widths']);
        $srcThumbWidth = $optionsWidths[ 0 ];
        $srcThumbHeight = $optionHeights[ 0 ];

        // if not using picturefill place the smallest image in the "src" attribute of the img tag
        // <img srcset="" src="smallest image here" alt="alt text" >
        $srcThumb = $this->thumbnail($file, $srcThumbWidth, $srcThumbHeight, $cropping);
        

        $context = [
            'config' => $configName,
            'alt' => $altText,
            'sizes' => $sizeAttrib,
            'options' => $defaultOptions,
            'widthDensity' => $densityWidth,
            'combinedImages' => $combinedImages,
            'srcThumb' => $srcThumb,
            'class' => $htmlClass,
            'sizeArray' => $sizeArray,
            'cropping' => $cropping
        ];

        $renderTemplate = $this->renderTemplate('respimg.html.twig', $context);

        return new \Twig_Markup($renderTemplate, 'UTF-8');
    }


    /**
     * @param null $fileName
     * @param null $width
     * @param null $height
     * @param null $crop
     * @return mixed
     */
    public function thumbnail($fileName = null, $width = null, $height = null, $crop = null)
    {
        $thumb = $this->getThumbnail($fileName, $width, $height, $crop);

        return $this->getThumbnailUri($thumb);
    }


    /**
     * Get a thumbnail object.
     *
     * @param string|array $fileName
     * @param integer      $width
     * @param integer      $height
     * @param string       $scale
     *
     * @return Thumbnail
     */
    private function getThumbnail($fileName = null, $width = null, $height = null, $scale = null)
    {
        $app = $this->getContainer();
        $thumb = new Thumbnail($app['config']->get('general/thumbnails'));
        $thumb
            ->setFileName($fileName)
            ->setWidth($width)
            ->setHeight($height)
            ->setScale($scale)
        ;

        return $thumb;
    }


    /**
     * Get the thumbnail relative URI.
     *
     * @param Thumbnail $thumb
     *
     * @return mixed
     */
    private function getThumbnailUri(Thumbnail $thumb)
    {
        if ($thumb->getFileName() == null) {
            return false;
        }
        $app = $this->getContainer();
        return $app['url_generator']->generate(
            'thumb',
            [
                'width'  => $thumb->getWidth(),
                'height' => $thumb->getHeight(),
                'action' => $thumb->getScale(),
                'file'   => $thumb->getFileName(),
            ]
        );
    }


    /**
     * @param $name
     *
     * @return string
     *
     * get the config name. If no name is passed in the twig function then use
     * the default settings in our config file under defaults
     */
    function getConfigName($name)
    {

        if (empty($name)) {

            $configName = 'default';

        } else {

            $configName = $name;

        }

        return $configName;
    }

    /**
     * @param $option1
     * @param $option2
     * @param $padValue
     *
     * @return array
     */
    function getCombinedArray($option1, $option2, $padValue)
    {
        $option1Count = count($option1);
        $option2Count = count($option2);

        if ($option1Count != $option2Count) {
            $option1Array = array_pad($option1, $option2Count, $padValue);
        } else {
            $option1Array = $option1;
        }

        if ($option2Count != $option1Count) {
            $option2Array = array_pad($option2, $option1Count, $padValue);
        } else {
            $option2Array = $option2;
        }

        $combinedArray = array_combine($option1Array, $option2Array);

        return $combinedArray;

    }


    /**
     * @param       $filename
     * @param       $config
     * @param array $options
     *
     * @return array
     *
     * Get the default options
     */
    function getOptions($filename, $config, $options = array())
    {

        $configName = $this->getConfigName($config);
        $defaultWidths = $this->getWidthsHeights($configName, 'widths');
        $defaultHeights = $this->getWidthsHeights($configName, 'heights');
        $defaultRes = $this->getResolutions($configName);
        $cropping = $this->getCropping($configName);
        $altText = $this->getAltText($configName, $filename);
        $widthDensity = $this->getWidthDensity($configName);
        $sizes = $this->getSizesAttrib($configName);
        $class = $this->getHTMLClass($configName);
//        $lazyLoaded = $this->setLazyLoad($configName);


        $defaults = [
            'widths' => $defaultWidths,
            'heights' => $defaultHeights,
            'cropping' => $cropping,
            'widthDensity' => $widthDensity,
            'resolutions' => $defaultRes,
            'sizes' => $sizes,
            'altText' => $altText,
//            'lazyLoad' => $lazyLoaded,
            'class' => $class

        ];

        $defOptions = array_merge($defaults, $options);

        return $defOptions;
    }

    /**
     * @param $config
     * @return mixed
     */

    function getCropping($config)
    {
        $configName = $this->getConfigName($config);
        $config = $this->getConfig();
        $cropping = $config[ $configName ][ 'cropping' ];

        if (isset($cropping) && !empty($cropping)) {
            $crop = $config[ $configName ][ 'cropping' ];
        } else {
            $crop = $config[ 'default' ][ 'cropping' ];
        }

        return $crop;
    }

    /**
     * @param $config
     *
     * @return mixed
     */
    public function getSizesAttrib($config)
    {
        $configName = $this->getConfigName($config);
        $config = $this->getConfig();
        $sizes = $config[ $configName ][ 'sizes' ];

        if (isset($sizes) && !empty($sizes)) {
            $sizesAttrib = $config[ $configName ][ 'sizes' ];
        } else {
            $sizesAttrib = $config[ 'default' ][ 'sizes' ];
        }

        return $sizesAttrib;
    }

    /**
     * @param $config
     * @param $filename
     *
     * @return mixed
     */
    function getAltText($config, $filename)
    {

        $configName = $this->getConfigName($config);
        $configFile = $this->getConfig();
        $altText = $configFile[ $configName ][ 'altText' ];

        if (empty($altText)) {
            $tempAltText = pathinfo($filename);
            $altText = $tempAltText[ 'filename' ];
        }

        return $altText;
    }


    /**
     * @param $config
     * @param $option
     *
     * @return mixed
     */
    function getWidthsHeights($config, $option)
    {

        $configName = $this->getConfigName($config);
        $configFile = $this->getConfig();
        $configOption = $configFile[ $configName ][ $option ];

        if (isset($configOption) && !empty($configOption)) {
            $configParam = $configFile[ $configName ][ $option ];
        } else {
            $configParam = $configFile[ 'default' ][ $option ];
        }

        return $configParam;
    }

    /**
     * @param $config
     *
     * @return array
     *
     * get the resolutions for resolution switching
     */
    function getResolutions($config)
    {
        $configName = $this->getConfigName($config);
        $configFile = $this->getConfig();
        $resOptions = $configFile[ $configName ][ 'resolutions' ];

        if (isset($resOptions) && !empty($resOptions)) {
            $resolutions = $configFile[ $configName ][ 'resolutions' ];
        } else {
            $resolutions = array(
                1,
                2,
                3
            );
        }

        return $resolutions;
    }

    /**
     * @param $thumb
     * @param $resolutions
     *
     * @return string
     */
    function resolutionErrors($thumb, $resolutions)
    {
        $thumbCount = count($thumb);
        $resCount = count($resolutions);
        // if the resolutions are more than the thumbnails remove the resolutions to match the thumbnail array
        if ($resCount > $thumbCount) {
//			$resError = 'You Have More Resolutions Set In Your Config Than You Have Thumbnails Being Generated.';
//			$resError .= ' Add More Resolutions Or Remove A Width Or Height To Remove This Warning';
            $newResArray = array_slice($resolutions, 0, $thumbCount);
            $resError = array_combine($thumb, $newResArray);
        }
        // if the resolution count is smaller than the number of thumbnails remove the number of thumbnails
        // to match the $resCount Array
        if ($resCount < $thumbCount) {
//			$resError = 'You Have More Thumbnails Being Generated Than You Have Resolutions Set.';
//			$resError .= ' Add More Resolutions Or Remove A Width Or Height To Remove This Warning';
            $newThumbArray = array_slice($thumb, 0, $resCount);
            $resError = array_combine($newThumbArray, $resolutions);
        }
        if ($resCount === $thumbCount ) {
            $resError = array_combine( $thumb, $resolutions);
        }
        return $resError;
    }

    /**
     * @param $config
     *
     * @return mixed
     */
    function getWidthDensity($config)
    {
        $configName = $this->getConfigName($config);
        $configFile = $this->getConfig();
        $widthDensity = $configFile[ $configName ][ 'widthDensity' ];

        if (isset($widthDensity) && !empty($widthDensity)) {
            $wd = strtolower($configFile[ $configName ][ 'widthDensity' ]);
        } else {
            $wd = strtolower($configFile[ 'default' ][ 'widthDensity' ]);
        }

        return $wd;
    }

//    function setLazyLoad($config) {
//
//        $configName = $this->getConfigName($config);
//        $configFile = $this->getConfig();
//        $lazyLoad = $configFile[ $configName ]['lazyLoad'];
//
//
//        if ($lazyLoad) {
//
//            return TRUE;
//        }
//
//        return FALSE;
//    }

    /**
     * @param $config
     *
     * @return mixed
     */
    function getHTMLClass($config)
    {
        $configName = $this->getConfigName($config);
        $config = $this->getConfig();
        $htmlClass = $config[ $configName ][ 'class' ];

        $class = $config[ 'default' ][ 'class' ];

        // if a class array is in the config set the $class variable to the class array
        if ( isset($htmlClass ) ) {
            $class = $htmlClass;
        }

        return $class;
    }

    /**
     * You can't rely on bolts methods to insert javascript/css in the location you want.
     * So we have to hack around it. Use the Snippet Class with their location methods and insert
     * Picturefill into the head. Add a check to make sure the script isn't loaded more than once ($_scriptAdded)
     * and stop the insertion of the files multiple times because bolt's registerAssets method will blindly insert
     * the files on every page
     *
     */

    protected function addAssets()
    {
        $app = $this->getContainer();

        $config = $this->getConfig();

        $pfill = $config['picturefill'];

        $extPath = $app['resources']->getUrl('extensions');

        $vendor = 'vendor/cdowdy/';
        $extName = 'boltresponsiveimages/';

        $pictureFillJS = $extPath . $vendor . $extName . 'picturefill/' . $this->_currentPictureFill . '/picturefill.min.js';
        $pictureFill = <<<PFILL
<script src="{$pictureFillJS}" async defer></script>
PFILL;
        $asset = new Snippet();
        $asset->setCallback($pictureFill)
            ->setZone(ZONE::FRONTEND)
            ->setLocation(Target::AFTER_HEAD_CSS);

        // variable to check if script is added to the page

       if ($pfill){
           if ($this->_scriptAdded) {
               $app['asset.queue.snippet']->add($asset);
           } else {

               $this->_scriptAdded = TRUE;
           }
       }
    }




    /**
     * @return array
     */
    protected function getDefaultConfig()
    {
        return [
            'default' => [
                'widths' => [ 320, 480, 768 ],
                'heights' => [ 0 ],
                'widthDensity' => 'w',
                'sizes' => [ '100vw'  ],
                'cropping' => 'resize',
                'altText' => '',
                'class' => ''
            ]
        ];
    }

    public function isSafe()
    {
        return true;
    }


}
