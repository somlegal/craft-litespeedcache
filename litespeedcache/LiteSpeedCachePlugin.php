<?php
namespace Craft;

class LiteSpeedCachePlugin extends BasePlugin
{

	public function getName()
	{
		return Craft::t('Litespeed Cache');
	}

	public function getVersion()
	{
		return '1.0.0';
	}

	public function getDeveloper()
	{
		return 'Thoughtful';
	}

	public function getDeveloperUrl()
	{
		return 'http://thoughtfulweb.com';
	}

	public function hasCpSection()
	{
	    return true;
	}

	protected function defineSettings()
	{
	    return array(
	        'lsPerUrl' => array(AttributeType::String, 'default' => 0)
	    );
	}

	public function getSettingsHtml()
	{
	   return craft()->templates->render('litespeedcache/settings', array(
	       'settings' => $this->getSettings()
	   ));
	}

	public function init()
	{
		/**
		 * onBeforeSaveElement, grab the paths we need to clear from Craft and add them to the lsclearance table
		 */
		craft()->on('elements.onBeforeSaveElement', function(Event $event)
		{
			// If we are clearing per URL
			if ($this->getSettings()->lsPerUrl) {
				$element = $event->params['element'];
				$paths = craft()->liteSpeedCache->getPaths($element);

				$result = craft()->db->createCommand()
									->selectDistinct('path')
									->from('lsclearance')
									->queryColumn();

				$urls = [];

				foreach ((array) $paths as $path) {
					// If one of the records has been tagged as global, delete the lot
					if (strpos($path['cacheKey'], 'global%%') !== false) {
						$dir = '../.lscache';

						craft()->liteSpeedCache->destroyLiteSpeedCache($dir);
						return true;
					}

					// Otherwise get the URL from the cacheKey
					// (which needs the key to be craft.request.path)
					$newPath = explode('%%', $path['cacheKey']);
					$newPath = UrlHelper::getSiteUrl($newPath[0]);
					if (!in_array($newPath, $result)) {
						$urls[] = array($newPath);
					}
				}

				$result = craft()->db->createCommand()->insertAll('lsclearance', array('path'), $urls);
			}
		});

		/**
		 * Run the PURGE commands
		 */
		craft()->on('elements.onSaveElement', function(Event $event)
		{
			// If we are clearing per URL
			if ($this->getSettings()->lsPerUrl) {
				craft()->liteSpeedCache->clearLitespeedQueue();
			} else {
				$dir = '../.lscache';

				craft()->liteSpeedCache->destroyLiteSpeedCache($dir);
			}
		});
	}


}
