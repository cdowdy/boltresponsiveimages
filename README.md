# bolt-repsponsive-images  
____________________  

Responsive Image Extension for Bolt CMS. Using picturefill and img srcset  

# This is currently in beta :-)  

# Quick Usage With Defaults:  
______________________  

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
    alt="filename is used for alt text">  
```  

## Tag Explanation  
______________  

To use this Responsive Image Bolt Extension you can either use the defaults above or define a set of rules the the extension config as follows.  
  
1. Give it a Name.

```yaml  
# default rule set above here
blogposts:  
```  

2. Define a set of widths. If left empty will default to the widths set in "default"   

```yaml  
blogposts:  
  widths: [ 340, 680, 1260 ]  
```  

If you don't know the widths but you do know the height you want put 0 (zero) in the array to proportionally scale the width to the height.  

```yaml  
blogposts:  
  widths: [ 0 ]  
```  

3. Define a set of heights. Use 0 (zero) if you want to proportionally scale the height to the new widths. If left empty defaults to 0 (zero)  

```yaml  
blogposts:
  widths: [ 340, 680, 1260 ]
  heights: [ 0 ]  
```  

4. Define if you want to use the Image Width (w) or Screen density (x) to determine which image to use. If left empty defaults to "w"  

```yaml  
blogposts:
  widths: [ 340, 680, 1260 ]
  heights: [ 0 ]  
  widthDensity: 'w'  
```  

5. Determine your sizes if using the W descriptor. Defaults to "100vw".  

```yaml  
blogposts:
  widths: [ 340, 680, 1260 ]
  heights: [ 0 ]  
  widthDensity: 'w'  
  sizes: [ '(min-width: 40em) 80vw', '100vw' ]  
```  

6. Choose how you want to image cropped. Options are the same as a regular bolt thumbnail. Defaults to either "resize" or whatever is set in "default"    

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
   
7. Finally determine your Alt text and if you need an additional class for styling. Alt text should be supplied by you the user. Otherwise it will fall back to the filename which in most instances isn't good alt text.  
 
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
_______________    

you can override just about any of the following settings from your defined configurations.  

* widths  
* heights
* widthDensity  
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
    'widthDensity': 'x',  
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
| sizes             | array         |
| cropping          | string        | 
| altText           | string        |     
| class             | array         |  

