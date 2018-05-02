# CraftCMS LSCache Purge for Craft CMS 3.x

PURGE the LiteSpeed Cache on saving entries.

## Installation

To install the plugin, search for **LiteSpeed Cache** on the Plugin store, or install manually with the following instructions:

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to require the plugin:

        composer require thoughtfulweb/lite-speed-cache

3. In the Control Panel, go to *Settings → Plugins* and click the “Install” button for **LiteSpeed Cache**.

## Use

Choose whether or not to clear caches by URL, and set the directory where your LSCache folder is located in the plugin settings. If you do not select the per-URL option, the entire LSCache folder will be destroyed on every page save.

If you have forms on your website and you're using CSRF protection, you'll want to ensure you do not cache the form page, as that will cache the CSRF tokens too. You can either inject the CSRF dyamically by adding a `csrf` class (or any other classname) to your form and then using

````
{% js %}
    $(function() {
        $('form.csrf').prepend('<input type="hidden" name="{{ craft.app.config.general.csrfTokenName }}" value="{{ craft.app.request.getCsrfToken }}" />');
    });
{% endjs %}
````

or you can choose to not cache the page at all using the following Twig header

````
{% header "X-LiteSpeed-Cache-Control: no-cache" %}
````

**If you use the standard `{{ csrfInput() }}` inline, the tokens will be cached by Litespeed and all of your form submissions will fail.**

## Requirements

This plugin requires Craft CMS 3.0.0 or later.

## Notes

If you're using per-URL purging, the plugin taps into Craft's native caching functionality, meaing you **must** use `{% cache %}` tags so that a cache record can be found on page save. If you don't have a cache record for the page you're saving, the plugin doesn't know it needs to PURGE that page, so won't.

This plugin will not be triggered at present from CraftCommerce, as the hook for page saving is different than the standard AFTER_SAVE_ELEMENT, and the class docs aren't available for Commerce 2 yet. The recommendation is to disable the per-URL purging until Commerce is released.

### Cloudflare

Due to CloudFlare being a reverse proxy, you cannot use CloudFlare and still use per-URL purging. Either do not route through CloudFlare, or just enable the global purge.