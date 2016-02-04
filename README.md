# Image Effects module

Project page: https://drupal.org/project/image_effects

Current and past maintainers for Image Effects:
- [Berdir](https://www.drupal.org/u/Berdir)
- [fietserwin](https://www.drupal.org/u/fietserwin)
- [mondrake](https://www.drupal.org/u/mondrake)
- [slashsrsm](https://www.drupal.org/u/slashrsm)

Past maintainers for Imagecache Actions:
- [dman](https://drupal.org/user/33240)
- [sidneyshan](https://drupal.org/user/652426)


## Introduction

The Image Effects module provides a suite of additional image effects that can
be added to image styles and UI components that can be used in the image effects
configuration forms.

Image styles let you create derivations of images by applying (a series of)
effect(s) to it. Think of resizing, desaturating, masking, etc.

The effects that this module provides include:

Effect name      | Description                                                                                  | GD toolkit | ImageMagick toolkit |
-----------------|----------------------------------------------------------------------------------------------|:----------:|:-------------------:|
Auto orientation | Uses EXIF Orientation tags to determine the image orientation.                               | X          | X                   |
Brightness       | Supports changing brightness settings of an image. Also supports negative values (darkening).| X          | X                   |
Color shift      | Colorizes image.                                                                             | X          | X                   |
Set canvas       | Places the source image over a colored or a transparent background of a defined size.        | X          | X                   |
Contrast         | Supports changing contrast settings of an image. Also supports negative values.              | X          | X                   |
Strip metadata   | Strips all EXIF metadata from image.                                                         | X          | X                   |
Watermark        | Place a image with transparency anywhere over a source picture.                              | X          | X                   |

Image Effects tries to support both the GD toolkit from Drupal core and the
ImageMagick toolkit. However, please note that there may be effects that are
not supported by all toolkits, or that provide different results with different
toolkits.


## What Image Effects is not?

Image Effects does not provide a separate UI. It hooks into the Drupal core's
image styles system. See https://drupal.org/documentation/modules/image for more
information about working with images.


## Requirements

1. Image module from Drupal core
1. At least 1 of the available image toolkits:
  - GD toolkit from Drupal core.
  - [ImageMagick toolkit](https://drupal.org/project/imagemagick).


## Installing

Install as usual, see [official docs](https://www.drupal.org/documentation/install/modules-themes/modules-8)
for further information.


## Configuration

- Go to Manage > Configuration > Media > Image toolkit and configure your
  toolkit and its settings.
- Check Image Effects configuration page (Manage > Configuration > Media >
  Image Effects), and choose the UI components that effects provided by this
  module should use:
  - Color selector - allows to use either a 'color' HTML element for selecting
    colors, or a color picker provided by the Farbtastic library. Alternative
    selectors may be added by other modules.
  - Image selector - some effects (e.g. Watermark) require to define an image
    file to be used. This setting allows to use either a basic text field where
    the URI/path to the image can be entered, or a 'dropdown' select that will
    list all the image files stored in a directory specified in configuration.
    Alternative selectors may be added by other modules.
  - Font selector - some effects require to define a font file to be used.
    This setting allows to use either a basic text field where the URI/path to
    the font can be entered, or a 'dropdown' select that will list all the font
    files stored in a directory specified in configuration. Alternative
    selectors may be added by other modules.


## Usage

- Define image styles at admin/config/media/image-styles and add 1 or more
  effects as defined by this module.
- Use the image styles via e.g. the formatters of image fields.


## Support

File bugs, feature requests and support requests in the [Drupal.org issue queue
of this project](https://www.drupal.org/project/issues/image_effects).


## A note about the origin of this module

This module is the Drupal 8 successor of the [ImageCache Actions module](https://www.drupal.org/project/imagecache_actions).
It also incorporates image effects that were part of the Drupal 7 version of the
ImageMagick module.


## Which toolkit to use?

ImageMagick toolkit comes with few advantages:
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
