# Image Effects module

Project page: https://drupal.org/project/image_effects


## Introduction

The Image Effects module provides a suite of additional image effects that can
be added to image styles and UI components that can be used in the image effects
configuration forms.

Image styles let you create derivations of images by applying (a series of)
effect(s) to it. Think of resizing, desaturating, masking, etc.

Image Effects tries to support both the GD toolkit from Drupal core and the
ImageMagick toolkit. However, please note that there may be effects that are
not supported by all toolkits, or that provide different results with different
toolkits.

The effects that this module provides include:

Effect name      | Description                                                                                  | GD toolkit | [ImageMagick](https://drupal.org/project/imagemagick) toolkit |
-----------------|----------------------------------------------------------------------------------------------|:----------:|:-------------------:|
Auto orientation | Uses EXIF Orientation tags to determine the image orientation.                               | X          | X                   |
Background       | Places the source image anywhere over a selected background image.                           | X          | X                   |
Brightness       | Supports changing brightness settings of an image. Also supports negative values (darkening).| X          | X                   |
Color shift      | Colorizes image.                                                                             | X          | X                   |
Contrast         | Supports changing contrast settings of an image. Also supports negative values.              | X          | X                   |
Convolution      | Allows to build custom image filters like blur, emboss, sharpen and others (see http://docs.gimp.org/en/plug-in-convmatrix.html). | X          | X                   |
ImageMagick arguments | Directly enter ImageMagick command line arguments.                                      |            | X                   |
Interlace        | Used to specify the type of interlacing scheme for raw image formats.                        | X          | X                   |
Set canvas       | Places the source image over a colored or a transparent background of a defined size.        | X          | X                   |
Set transparent color | Defines the color to be used for transparency in GIF images.                            | X          | X                   |
Sharpen          | Sharpens an image (using convolution).                                                       | X          | X                   |
Strip metadata   | Strips all EXIF metadata from image.                                                         | X          | X                   |
Text overlay<sup>1</sup> | Overlays text on an image, defining text font, size and positioning.                 | X          | X<sup>2</sup>       |
Watermark        | Place a image with transparency anywhere over a source picture.                              | X          | X                   |

Notes:
<sup>1</sup> The [Textimage](https://drupal.org/project/textimage) module, if installed, allows this effect to present a preview of the text overlay.
<sup>2</sup> The ImageMagick toolkit actually requires the GD toolkit to build the text overlay.


## What Image Effects is not?

Image Effects does not provide a separate UI. It hooks into the Drupal core's
image styles system. See [Working with images in Drupal 7 and 8](https://drupal.org/documentation/modules/image) for more
information.


## Requirements

1. Image module from Drupal core
1. One of the supported image toolkits:
  - GD toolkit from Drupal core.
  - [ImageMagick](https://drupal.org/project/imagemagick) toolkit.


## Installing

Install as usual, see the [official documentation](https://www.drupal.org/documentation/install/modules-themes/modules-8)
for further information.


## Configuration

- Go to _Manage > Configuration > Media > Image toolkit_ and configure your
  toolkit and its settings.
- Check Image Effects configuration page (_Manage > Configuration > Media >
  Image Effects_), and choose the UI components that effects provided by this
  module should use:
  - _Color selector_ - allows to use either a 'color' HTML element for selecting
    colors, or a color picker provided by the Farbtastic library. Alternative
    selectors may be added by other modules.
  - _Image selector_ - some effects (e.g. Watermark) require to define an image
    file to be used. This setting allows to use either a basic text field where
    the URI/path to the image can be entered, or a 'dropdown' select that will
    list all the image files stored in a directory specified in configuration.
    Alternative selectors may be added by other modules.
  - _Font selector_ - some effects require to define a font file to be used.
    This setting allows to use either a basic text field where the URI/path to
    the font can be entered, or a 'dropdown' select that will list all the font
    files stored in a directory specified in configuration. Alternative
    selectors may be added by other modules.


## Usage

- Define image styles at _Manage > Configuration > Media > Image styles_ and add
  one or more effects defined by this module.
- Use the image styles via e.g. the formatters of image fields.


## Support

File bugs, feature requests and support requests in the [Drupal.org issue queue
of this project](https://www.drupal.org/project/issues/image_effects).


## A note about the origin of this module

This module is the Drupal 8 successor of the [ImageCache Actions](https://www.drupal.org/project/imagecache_actions) module.
It also incorporates image effects that were part of the Drupal 7 versions of the
[ImageMagick](https://drupal.org/project/imagemagick), [Textimage](https://drupal.org/project/textimage), [FiltersIE](https://www.drupal.org/project/filtersie)
and [ImageMagick Raw Effect](https://www.drupal.org/project/im_raw) modules.


## Which toolkit to use?

[ImageMagick](https://drupal.org/project/imagemagick) toolkit comes with few advantages:
- It is better in anti-aliasing. Try to rotate an image using both toolkits and
  you will see for yourself.
- It does not execute in the PHP memory space, so is not restricted by the
  memory_limit PHP setting.
- The GD toolkit will, at least on Windows configurations, keep the font file
  open after a text operation, so you cannot delete, move or rename it until PHP
  process is running.

Advantages of GD toolkit on the other hand:
- GD is always available, whereas ImageMagick is not always present on shared
  hosting or may be present in an antique version that might give problems.
- Simpler architecture stack.

Please also note that effects may give different results depending on the
toolkit used.


## Maintainers

Current and past maintainers for Image Effects:
- [Berdir](https://www.drupal.org/u/Berdir)
- [fietserwin](https://www.drupal.org/u/fietserwin)
- [mondrake](https://www.drupal.org/u/mondrake)
- [slashsrsm](https://www.drupal.org/u/slashrsm)

Past maintainers for Imagecache Actions:
- [dman](https://drupal.org/user/33240)
- [sidneyshan](https://drupal.org/user/652426)
