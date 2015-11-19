<?php
/**
 * Created by PhpStorm.
 * User: Cory
 * Date: 10/29/2015
 * Time: 10:24 AM
 */

namespace Bolt\Extension\cdowdy\boltresponsiveimages;

use Bolt\Application;
use Bolt\BaseExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\AcceptHeader;
use Bolt\Thumbs;

class Extension extends  BaseExtension
{

    private $currentPictureFill = '3.0.1';

    public function initialize()
    {
        $this->addTwigFunction( 'respImg', 'respImg' );
    }
    /**
     * @return string
     */
    public function getName()
    {
        return "boltresponsiveimages";
    }

    public function respImg( $name,  $file, $widths, $heights, $crop, $sizes  )
    {
        // grab the config name to use in functions below
        $configName = $this->getConfigName($name);

        // only load picturefill js on pages containing respImg twig tag and if picturefill is set in the
        // extension config
        $this->addAssets();

        // load up twig template directory
        $this->app['twig.loader.filesystem']->addPath(__DIR__ . "/assets");


        // get the sizes of the Images from the config or twig parameters passed in the twig template
        $sizeArray = $this->getCombinedSizes($configName, $heights, $widths);

        // get the widths if using the 'w' attribute for responsive images
        $width = $this->getImageWidths( $configName, $widths );

        // make thumbs an empty array
        $thumb = array();

        // loop through the size array and generate a thumbnail and URL
        // place those in an array to be used in the twig template
        foreach ( $sizeArray as $key => $value) {
            $thumb[] .= $this->thumbnail($file, $key, $value, $crop);
        }

        // combine the thumbnails/urls and the widths to use with the 'w' attribute
        $combinedImgandSize = array_combine( $thumb, $width );

        // get the sizes attribute
        $sizesAttrib = $this->getSizesAttrib( $configName, $sizes );

        $altText = $this->getAltText( $configName, $file );

        $support = $this->app['render']->render('respimg.twig', array(
            'combinedSize' => $combinedImgandSize,
            'sizes'  => $sizeArray,
            'size'   => $sizesAttrib,
            'crop'   => $crop,
            'config' => $configName,
            'img'    => $thumb,
            'alt'   => $altText

        ));

        return new \Twig_Markup( $support, 'UTF-8' );
    }

    /*
     *
     * Get the size of the Image to use for responsive images:
     * ex: <img srcset="filename.jpg 800w, filename.jpg 300w" alt="alt text" />
     * take the image size width and place the width after the filename
     *
     * @param $filename
     * @return array|bool
     */
    function getImageSize( $filename )
    {
        $fullpath = sprintf(
            '%s/%s',
            $this->app['resources']->getPath('filespath'),
            $filename
        );

        if ( !is_readable( $fullpath ) || !is_file( $fullpath ) ) {
            return false;
        }

        $imagesize = getimagesize( $fullpath );

        $imgSizes = array(
            'width'       => $imagesize[0],
            'height'      => $imagesize[1],
        );

        return $imgSizes;
    }

    public function thumbnail($filename, $width = '', $height = '', $zoomcrop = 'crop')
    {
        if (!is_numeric($width)) {
            $thumbconf = $this->app['config']->get('general/thumbnails');
            $width = empty($thumbconf['default_thumbnail'][0]) ? 100 : $thumbconf['default_thumbnail'][0];
        }

        if (!is_numeric($height)) {
            $thumbconf = $this->app['config']->get('general/thumbnails');
            $height = empty($thumbconf['default_thumbnail'][1]) ? 100 : $thumbconf['default_thumbnail'][1];
        }

        switch ($zoomcrop) {
            case 'fit':
            case 'f':
                $scale = 'f';
                break;

            case 'resize':
            case 'r':
                $scale = 'r';
                break;

            case 'borders':
            case 'b':
                $scale = 'b';
                break;

            case 'crop':
            case 'c':
                $scale = 'c';
                break;

            default:
                $scale = !empty($thumbconf['cropping']) ? $thumbconf['cropping'] : 'c';
        }

        // After v1.5.1 we store image data as an array
        if (is_array($filename)) {
            $filename = isset($filename['filename']) ? $filename['filename'] : $filename['file'];
        }



        $path = $this->app['url_generator']->generate(
            'thumb',
            array(
                'thumb' => round($width) . 'x' . round($height) . $scale . '/' . $filename,
            )
        );

        return $path;
    }

    /**
     * @param $configName
     * @param $widths
     * @return mixed
     *
     * pass in the config name from the getConfigName function
     * then get the widths and return an array of the widths
     */
    function getImageWidths( $configName, $widths ) {

        $config = $this->getConfigName( $configName );

        if ( empty( $widths )  ) {
            $width = $this->config[ $config ][ 'widths' ];
        }

        return $width;

    }


    /**
     * @param $configName
     * @param $heights
     * @return mixed
     *
     * pass in the config name from the getConfigName function
     * then return and array of heights to be used in the thumbnail function
     */
    function getImageHeights( $configName, $heights ) {

        $config = $this->getConfigName( $configName );

        if ( empty( $heights )  ) {
            $height = $this->config[ $config ][ 'heights' ];
        }

        return $height;

    }

    function getCombinedSizes( $config, $widths, $heights )
    {

        $config = $this->getConfigName($config);

        $heightArray = $this->getImageHeights($config, $heights);
        $widthArray = $this->getImageWidths($config, $widths);

        $widthCount = count($widthArray);
        $heightCount = count($heightArray);

        if ( $widthCount != $heightCount ) {

            $newWidthArray = array_pad($widthArray, $heightCount, 0);

        } else {

            $newWidthArray = $widthArray;
        }

        if ( $heightCount != $widthCount ) {

            $newHeightArray = array_pad( $heightArray, $widthCount, 0 );
        } else {
            $newHeightArray = $heightArray;
        }

        $combinedArray = array_combine( $newWidthArray, $newHeightArray );

        return $combinedArray;
    }


    /**
     * @param $name
     * @return string
     *
     * get the config name. If no name is passed in the twig function then use
     * the default settings in our config file under defaults
     */
    function getConfigName( $name ) {

        if ( empty( $name ) ) {

            $configName = 'default';

        } else {

            $configName = $name ;

        }

        return $configName;
    }

    function getSizesAttrib( $config, $sizes ) {

        $configName = $this->getConfigName( $config );

        if ( empty( $sizes ) ) {
            $sizes = $this->config[ $configName ]['sizes'];
        }

        return $sizes;
    }

    function getAltText( $config, $filename ) {

        $configName = $this->getConfigName( $config );
        $altText = $this->config[ $configName ][ 'altText' ];

        if ( empty( $altText ) ) {
            $tempAltText    = pathinfo($filename);
            $altText    = $tempAltText['filename'];
        }

        return $altText;
    }

    // check headers for Accept: image/webp
    /**
     * @return bool
     */
    function checkWebpSupport()
    {
        $webpsupport = (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') >= 1);

        if ( $webpsupport ) {
            $webpSupported = true;
        } else {
            $webpSupported = false;
        }

        return $webpSupported;
    }

    /**
     * @return bool
     */
    function checkGDVersion()
    {
        // check gd_info
        // http://php.net/manual/en/function.gd-info.php
        if (gd_info()['WebP Support'] == true ) {
            return true;
            // PHP (at least as of 5.6 on Windows) gives weird results with gd_info
            // it doesn't show webp support bool(true) but false
            // the function imagewebp avail in PHP is usually available though
            // http://php.net/manual/en/function.imagewebp.php
        } elseif (function_exists('imagewebp')) {
            return true;
        } else {
            return false;
        }

    }

    /**
     *
     * used if serving webp images. Chrome/Blink Based Browsers show they can display
     * webp images in their accept header ( found in checkWebpSupport above )
     * Internet Explorer (and maybe Edge) has broken vary support and wont cache outbound headers
     * which causes a re-validation request which we don't want. So we mark this image as private.
     * https://www.igvita.com/2013/05/01/deploying-webp-via-accept-content-negotiation/
     *
     */
    function setHeader() {
        $response = new Response();

        $response->setVary('Accept');
        // mark the cache as private
        $response->setPrivate();

        // send the header
        $response->send();
    }

    // Add Picturefill to the current page!!!
    private function addAssets()
    {
        /**
         * since there is no head function or any reliable way to insert anything in to the head in Bolt we have to
         * hackishly insert picturefill into the head this way.
         *
         * first we assign a variable ($pictureFillJS) to the base URL
         * then insert that variable into a heredoc
         */
        $pictureFillJS = $this->getBaseUrl() . 'js/picturefill/' . $this->currentPictureFill . '/picturefill.min.js';
        $pictureFill   = <<<PFILL
<script src="{$pictureFillJS}" async defer></script>
PFILL;

        if ( $this->config['picturefill'] == true ) {
            // insert snippet after the last CSS file in the head
            $this->addSnippet('afterheadcss', $pictureFill);
        }
        // for browsers that don't understand <picture> element
//        $picElement = <<<PICELEM
//<script>document.createElement( "picture" );</script>
//PICELEM;
//        // insert snippet after the last CSS file in the head
//        $this->addSnippet( 'afterheadcss', $picElement );
    }



    public function isSafe()
    {
        return true;
    }
}
