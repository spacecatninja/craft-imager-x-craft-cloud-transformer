# Craft Cloud transformer for Imager X

A plugin for using Craft Cloud image transforms in Imager X.   

## Requirements

This plugin requires Craft CMS 4.6.0 or later, and [Imager X 4.0.0](https://github.com/spacecatninja/craft-imager-x/) or later.
 
## Usage

Install the transformer as described below. Then, in your [Imager X config](https://imager-x.spacecat.ninja/configuration.html), 
set the transformer to `craftcloud`, ie:

```
'transformer' => 'craftcloud',
``` 

Imager will then use Craft's underlying transform functionality.

### Cave-ats, shortcomings, and tips

This transformer only supports a subset of what Imager X can do when using the default `craft` transformer, it only supports
what the native Craft CMS image transforms support. It can only transform Assets, not external URLs or already transformed images.

Remember that you can use different transformers in different environments, and that you can override the `transformer` 
config setting even at the template level. For instance by combining Craft Cloud and Imgix or similar, or using the
default `craft` transformer locally to take advantage of things like [`mockImage`](https://imager-x.spacecat.ninja/configuration.html#mockimage-int-string-asset)/
[`fallbackImage`](https://imager-x.spacecat.ninja/configuration.html#fallbackimage-int-string-asset) to simplify 
local development.

## Installation

To install the plugin, follow these instructions:

1. Install with composer via `composer require spacecatninja/imager-x-craft-cloud-transformer` from your project directory.
2. Install the plugin in the Craft Control Panel under Settings > Plugins, or from the command line via `./craft plugin/install imager-x-craft-cloud-transformer`.


## Configuration

No configuration is needed, please refer to the [documentation for Craft Cloud](https://github.com/craftcms/cloud) on how to 
utilize cloud file systems and cloud transforms. 


Price, license and support
---
The plugin is released under the MIT license. It requires Imager X, which is a commercial 
plugin [available in the Craft plugin store](https://plugins.craftcms.com/imager-x). If you 
need help, or found a bug, please post an issue in this repo, or in Imager X' repo (preferably). 
