<?php

namespace Bolt\Extension\cdowdy\ResponsiveImages;

use Bolt\Application;
use Bolt\BaseExtension;
use Bolt\Library as Lib;

/**
 * Class Extension
 * @package Bolt\Extension\cdowdy\ResponsiveImages
 */
class Extension extends BaseExtension
{

    public function initialize()
    {
        $this->addTwigFunction( 'respImg', 'respImg' );

        /**
         * since there is no head function or any reliable way to insert anything in to the head in Bolt we have to
         * hackishly insert picturefill into the head this way.
         *
         * first we assign a variable ($extensionsPath) to the base URL
         * then insert that variable into a heredoc
         */

        $pictureFillJS = $this->getBaseUrl() . 'assets/picturefill.min.js';
        $pictureFill   = <<<PFILL
<script src="{$pictureFillJS}" async defer></script>
PFILL;
        $this->addSnippet( 'aftercss', $pictureFill );

        // for browsers that don't understand <picture> element
        $picElement = <<<PICELEM
<script>document.createElement( "picture" );</script>
PICELEM;

        $this->addSnippet( 'aftercss', $picElement );

    }

    /**
     * @return string
     */
    public function getName()
    {
        return "responsiveimages";
    }

    /**
     * @param string $filename
     * @param int $width1
     * @param int $width2
     * @param int $width3
     * @param int $width4
     * @param string $sizing
     *
     * @return \Twig_Markup
     */
    public function respImg( $filename = '', $width1 = 0, $width2 = 0, $width3 = 0, $width4 = 0, $sizing = '' )
    {
        /**
         * load up twig template directory
         */
        $this->app['twig.loader.filesystem']->addPath( __DIR__ . "/assets" );

        /**
         * set variable for cropping to use in $sizing switch statement
         */
        $thumbconf = $this->config['cropping'];


        // After v1.5.1 we store image data as an array
        if (is_array( $filename )) {
            $filename = isset( $filename['filename'] ) ? $filename['filename'] : $filename['file'];
        }

        /**
         * Get the Sizes for the Images
         */
        // small image
        if (isset( $this->config['sizes']['small'][0] )) {
            $width1 = $this->config['sizes']['small'][0];
        } else {
            $width1 = 320;
        }

        // middle medium image
        if (isset( $this->config['sizes']['mid'] )) {
            $width2 = $this->config['sizes']['mid'];
        } else {
            $width2 = 640;
        }

        // medium image
        if (isset( $this->config['sizes']['medium'] )) {
            $width3 = $this->config['sizes']['medium'];
        } else {
            $width3 = 800;
        }

        // large image
        if (isset( $this->config['sizes']['large'] )) {
            $width4 = $this->config['sizes']['large'];
        } else {
            $width4 = 1100;
        }

        /**
         * switch statement to determine cropping/resizing of image
         */
        switch ($sizing) {
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
                $scale = ! empty( $thumbconf ) ? $thumbconf : 'c';
        }


        // Test For Small Images
        // Get the image path ( either by filename or record.image )
        // then get the widths, crop
        $smallImg = sprintf(
            '%sthumbs/%sx%s%s/%s',
            $this->app['paths']['root'],
            round( $width1 ),
            round( $height1 ),
            $scale,
            Lib::safeFilename( $filename )
        );

        $midImg = sprintf(
            '%sthumbs/%sx%s%s/%s',
            $this->app['paths']['root'],
            round( $width2 ),
            round( $height2 ),
            $scale,
            Lib::safeFilename( $filename )
        );

        $medImg = sprintf(
            '%sthumbs/%sx%s%s/%s',
            $this->app['paths']['root'],
            round( $width3 ),
            round( $height3 ),
            $scale,
            Lib::safeFilename( $filename )
        );

        $lrgImg = sprintf(
            '%sthumbs/%sx%s%s/%s',
            $this->app['paths']['root'],
            round( $width4 ),
            round( $height4 ),
            $scale,
            Lib::safeFilename( $filename )
        );


        $imgSource = $this->app['render']->render( 'respImg.srcset.html.twig', array(
            'smallImg' => $smallImg,
            'midImg'   => $midImg,
            'medImg'   => $medImg,
            'lrgImg'   => $lrgImg,
            'scale'    => $scale,
            'width1'   => $width1,
            'width2'   => $width2,
            'width3'   => $width3,
            'width4'   => $width4
        ) );

        return new \Twig_Markup( $imgSource, 'UTF-8' );
    }

}






