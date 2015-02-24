<?php

namespace Bolt\Extension\cdowdy\ResponsiveImages;

use Bolt;
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
     * @param string $cropping
     * @param string $altText
     * @param string $tag
     *
     * @return \Twig_Markup
     */
    public function respImg( $filename = '', $cropping = '', $altText = '', $tag = '', $sizes = '' )
    {
        // only load picturefill js on pages conatining respImg twig tag
        $this->addAssets();

        // load up twig template directory
        $this->app['twig.loader.filesystem']->addPath( __DIR__ . "/assets" );


        // After v1.5.1 we store image data as an array
        if (is_array( $filename )) {
            $filename = isset( $filename['filename'] ) ? $filename['filename'] : $filename['file'];
        }

        // set variable for cropping to use in $cropping switch statement
        $thumbconf = $this->config['cropping'];
        // switch statement to determine cropping/resizing of image
        switch ($cropping) {
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

        // Bolt doesn't have alt text stored or a description for images so let the person using this twig tag to set it
        // if they don't set it strip the file extension and use that.. .even though that isn't a good enough alt text
        // its better than having no alt text.
        if ( empty( $altText ) ) {

            $tempAltText    = pathinfo($filename);
            $altText        = $tempAltText['filename'];
        }

        // set up sizes variable for img srcset
        if ( empty( $sizes ) ) {
            $sizes = '100vw';
        }


        //  Get the Sizes for the Images
        // small image
        if (isset( $this->config['sizes']['small'] )) {
            $width1 = $this->config['sizes']['small'];
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

        // Set Height to 0 for proportional scaling:
        $height = 0;
        // Test For Small Images
        // Get the image path ( either by filename or record.image )
        // then get the widths, crop
        $smallImg = sprintf(
            '%sthumbs/%sx%s%s/%s',
            $this->app['paths']['root'],
            round( $width1 ),
            $height,
            $scale,
            Lib::safeFilename( $filename )
        );

        $midImg = sprintf(
            '%sthumbs/%sx%s%s/%s',
            $this->app['paths']['root'],
            round( $width2 ),
            $height,
            $scale,
            Lib::safeFilename( $filename )
        );

        $medImg = sprintf(
            '%sthumbs/%sx%s%s/%s',
            $this->app['paths']['root'],
            round( $width3 ),
            $height,
            $scale,
            Lib::safeFilename( $filename )
        );

        $lrgImg = sprintf(
            '%sthumbs/%sx%s%s/%s',
            $this->app['paths']['root'],
            round( $width4 ),
            $height,
            $scale,
            Lib::safeFilename( $filename )
        );


        $imgSource = $this->app['render']->render( $template, array(
            'smallImg' => $smallImg,
            'midImg'   => $midImg,
            'medImg'   => $medImg,
            'lrgImg'   => $lrgImg,
            'scale'    => $scale,
            'width1'   => $width1,
            'width2'   => $width2,
            'width3'   => $width3,
            'width4'   => $width4,
            'altText'   => $altText,
            'sizes'     => $sizes
        ) );

        return new \Twig_Markup( $imgSource, 'UTF-8' );
    }



    private function addAssets()
    {

        /**
         * since there is no head function or any reliable way to insert anything in to the head in Bolt we have to
         * hackishly insert picturefill into the head this way.
         *
         * first we assign a variable ($pictureFillJS) to the base URL
         * then insert that variable into a heredoc
         */

        $pictureFillJS = $this->getBaseUrl() . 'assets/picturefill.min.js';
        $pictureFill   = <<<PFILL
<script src="{$pictureFillJS}" async defer></script>
PFILL;
        // insert snippet after the last CSS file in the head
        $this->addSnippet( 'aftercss', $pictureFill );


        // for browsers that don't understand <picture> element
        $picElement = <<<PICELEM
<script>document.createElement( "picture" );</script>
PICELEM;
        // insert snippet after the last CSS file in the head
        $this->addSnippet( 'aftercss', $picElement );

    }

    public function isSafe()
    {
        return true;
    }

}






