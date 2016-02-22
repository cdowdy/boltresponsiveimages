# Bolt Responsive Images Extension 


Responsive Image Extension for Bolt CMS. Using picturefill and img srcset  

You can see a demo page with some real world uses of this extension at the following link:  

[Bolt Responsive Images Extension - In Use](https://corydowdy.com/demo/bolt-responsive-images)  

and use this Tool to help you get your responsive image widths!  

* [Responsive Images Breakpoint Generator](http://www.responsivebreakpoints.com/)

# Quick Usage With Defaults:  
  

In your template place this tag wherever you want a responsive image set:  
 
 example using a record image

```twig  
{{ respImg( record.image, 'default' ) }}  
```  

example using a file from "files"  
  
```twig  
{{ respImg( 'image-from-files.jpg', 'default' ) }}   
```

The "default" settings will create three (3) thumbnails, add in the [PictureFill polyfill](http://scottjehl.github.io/picturefill/), and set the sizes attribute. The widths that will be used are:  

 * 320 pixels wide
 * 480 pixels wide 
 * 768 pixels wide 
 
Along with those sizes "100vw" will be used as for the sizes attribute and the default settings will also use the image width and "W" descriptor to determine which image to show on screen.  

Here is how the markup will look:
 
 
```html  
<img sizes="100vw"  
    srcset="/your-site/thumbs/320x0r/filename-here.jpg 320w,
    /your-site/thumbs/480x0r/filename-here.jpg 480w,
    /your-site/thumbs/768x0r/filename-here.jpg 768w"
    src="/your-site/thumbs/320x0r/filename-here.jpg
    alt="filename is used for alt text">  
```  

## Usage Walk Through   

To use this Responsive Image Bolt Extension you can either use the defaults in the config file or define a set of rules the the extension config as follows.  
  
1). Give it a Name.

```yaml  
# default rule set above here
blogposts:  
```  

2). Define a set of widths. If left empty will default to the widths set in "default"   

```yaml  
blogposts:  
  widths: [ 340, 680, 1260 ]  
```  

If you don't know the widths but you do know the height you want put 0 (zero) in the array to proportionally scale the width to the height.  

```yaml  
blogposts:  
  widths: [ 0 ]  
```  

3). Define a set of heights. Use 0 (zero) if you want to proportionally scale the height to the new widths. If left empty defaults to 0 (zero)  

```yaml  
blogposts:
  widths: [ 340, 680, 1260 ]
  heights: [ 0 ]  
```  

4). Define if you want to use the Image Width (w) or Screen density (x) to determine which image to use. If left empty defaults to "w"  

```yaml  
blogposts:
  widths: [ 340, 680, 1260 ]
  heights: [ 0 ]  
  widthDensity: 'w'  
```  

5). Determine your sizes if using the W descriptor. Defaults to "100vw".  

```yaml  
blogposts:
  widths: [ 340, 680, 1260 ]
  heights: [ 0 ]  
  widthDensity: 'w'  
  sizes: [ '(min-width: 40em) 80vw', '100vw' ]  
```  

6). Choose how you want to image cropped. Options are the same as a regular bolt thumbnail. Defaults to either "resize" or whatever is set in "default"    

* crop ( crop or c )  
* resize  ( resize or r ) 
* fit (fit or f )  
* borders ( borders or border or b )  

```yaml  
blogposts:
  widths: [ 340, 680, 1260 ]
  heights: [ 0 ]  
  widthDensity: 'w'  
  sizes: [ '(min-width: 40em) 80vw', '100vw' ]  
  cropping: resize  
```  
   
7). Finally determine your Alt text and if you need an additional class for styling. Alt text should be supplied by you the user. Otherwise it will fall back to the filename which in most instances isn't good alt text.  
 
```yaml  
blogposts:
  widths: [ 340, 680, 1260 ]
  heights: [ 0 ]  
  widthDensity: 'w'  
  sizes: [ '(min-width: 40em) 80vw', '100vw' ]  
  cropping: resize  
  altText:
  class:  
```  
 
After you have your settings in the config file you can now use these in your templates wherever you want responsive images.  

```twig  
{{ respImg( blogpost.image, 'blogposts' ) }}   
``` 

 


## Advanced Usage  

you can override just about any of the following settings from your defined configurations.  

* widths  
* heights
* widthDensity  
* resolutions ( aka screen density )  
* sizes  
* cropping  
* altText  
* class  

Which allows you to pick and choose which settings to use from the config file and add in one off configuration settings.  

You start off by choosing the image, which config settings to use  

```twig  
{{ respImg( record.image, 'blogposts' ) }}  
```   

Then add in your custom settings. Here is how we would override some of the "blogposts" settings:  

Widths:

```twig  
{{ respImg( record.image, 'blogposts', { 'widths': [ 400, 800, 1600 ] } ) }}  
```  

Custom alt text  

```twig  
{{ respImg( record.image, 'blogposts', { 'altText': 'bolt responsive images extension marketplace preview image' } ) }}  
``` 

When using multiple in template overrides each one should be separated by a comma.  

```twig  
{{ respImg( record.image, 'blogposts', { 'widths': [ 400, 800, 1600 ], 'heights': [ 0, 360, 800 ] } ) }}   
```  

**Important to Note** if the config file uses brackets "[ ]" ( for an array ) you must also use an array in the template overrides. If in doubt check out default config array or string table below.  
 
 For better readability with multiple overrides I would suggest placing them on a seperate line like so    
 
 
```twig  
{{ respImg( record.image, 'blogposts', { 
    'widths': [ 400, 800, 1600 ], 
    'heights': [ 0, 360, 800 ],
    'widthDensity': 'w',  
    'sizes': [ '(min-width: 40em) 70vw', '100vw' ], 
    'class': 'second class', 
    'cropping': 'resize' 
    } ) 
}}  
```  

### Is it an Array or a String?   


|  Config Option     | What it is    | 
| ---------      | --------   | 
| widths            | array         | 
| heights           | array         |  
| widthDensity      | string        | 
| resolutions       | array         |
| sizes             | array         |
| cropping          | string        | 
| altText           | string        |     
| class             | array         |  


## Responsive Images for Resolution Switching  
You may have noticed the defaults for this extension use "w" or the width descriptor. I like this one the most. If you only want to create an image for 1x, 2x, and 3x resolutions then this section is for you :-)  

For resolution switching you need to supply at the least 1 piece of information and that would be the "widthDensity" config option. That will need an "x" either in your template or config file.  
  
  
```yaml  
# Config file example  
yourImageSettings:
  widthDensity: x  
```  

```twig  
{# Twig template example #}  
{{ respImg( record.image, 'yourImageSettings', { 'widthDensity': 'x' } ) }}  
```   

After this information is supplied you can then set your resolutions (or densities) you would like.  

```yaml  
# Config file example  
yourImageSettings:
  widthDensity: x  
  resolutions: [ 1, 2, 3 ]  
```  

```twig  
{# Twig template example #}  
{{ respImg( record.image, 'yourImageSettings', { 'resolutions': [ 1, 2, 3 ] } ) }}  
```   
 
If no resolutions are supplied and the 'x' descriptor is used the extension will default to three (3) screen densities.    

* 1x  
* 2x 
* 3x  

The settings above will also use the widths set in the "default" config section. If you don't change the default widths or set widths in your config settings section your images will be served like so:  
 
 * 1x screens => 320px wide image  
 * 2x screens => 480px wide image  
 * 3x screens => 768px wide image  
 
This makes the extension kind of rigid when it comes to defaults but in my opinion there really isn't a good way to set defaults for this.  

### Setting the Image widths  
To change the widths from the defaults add in "widths" in your config file or template  

```yaml  
# Config file example  
yourImageSettings:
  widths: [ 300, 640, 1000 ]  
  widthDensity: x  
  resolutions: [ 1, 2, 3 ]  
```  

```twig  
{# Twig template example #}  
{{ respImg( record.image, 'yourImageSettings', { 'widths': [ 300, 640, 1000 ] } ) }}  
```  

The resulting img tag will look like so  

```html  
<img srcset="/your-site/thumbs/300x0r/your-image.jpg 1x, 
        /your-site/thumbs/640x0r/your-image.jpg 2x,
        /your-site/thumbs/1000x0r/your-image.jpg 3x" 
    src="/your-site/thumbs/300x0r/your-image.jpg" 
    alt="your-image">  
```  

## Resolution Switching FYI  
** if you're using resolution switching the number of widths or heights you want to use should also match the number of resolutions. For 4 images you would also need 4 resolutions. If the number of Resolutions is not the same as the number of Widths or Heights items will be removed to make them match.**  

examples: 
 
 
Config with more resolutions than widths set:  

```yaml    
yourImageSettings:
  widths: [ 300, 640, 1000 ]  
  widthDensity: x  
  resolutions: [ 1, 2, 2.5, 3 ]  
```  

rendered HTML - the last resolution (3) is removed.    

```html  
<img srcset="/your-site/thumbs/300x0r/your-image.jpg 1x, 
        /your-site/thumbs/640x0r/your-image.jpg 2x,
        /your-site/thumbs/1000x0r/your-image.jpg 2.5x" 
    src="/your-site/thumbs/300x0r/your-image.jpg" 
    alt="your-image">  
```  

More Widths than Resolutions:  

```yaml    
yourImageSettings:
  widths: [ 300, 640, 800, 1000 ]  
  widthDensity: x  
  resolutions: [ 1, 2, 3 ]  
``` 

rendered HTML - the last width (1000) is removed.    

```html  
<img srcset="/your-site/thumbs/300x0r/your-image.jpg 1x, 
        /your-site/thumbs/640x0r/your-image.jpg 2x,
        /your-site/thumbs/800x0r/your-image.jpg 3x" 
    src="/your-site/thumbs/300x0r/your-image.jpg" 
    alt="your-image">  
```  

