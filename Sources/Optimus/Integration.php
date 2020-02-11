<?php

namespace Bugo\Optimus;

/**
 * Integration.php
 *
 * @package Optimus
 * @link https://custom.simplemachines.org/mods/index.php?mod=2659
 * @author Bugo https://dragomano.ru/mods/optimus
 * @copyright 2010-2020 Bugo
 * @license https://opensource.org/licenses/artistic-license-2.0 Artistic-2.0
 *
 * @version 2.5.1
 */

if (!defined('SMF'))
	die('Hacking attempt...');

/**
 * Main class of the Optimus mod
 */
class Integration
{
	/**
	 * Used hooks
	 *
	 * @return void
	 */
	public static function hooks()
	{
		add_integration_function('integrate_autoload', __CLASS__ . '::autoload', false, __FILE__);
		add_integration_function('integrate_load_session', __CLASS__ . '::loadSession', false, __FILE__);
		add_integration_function('integrate_buffer', __CLASS__ . '::buffer', false, __FILE__);
		add_integration_function('integrate_pre_load_theme', __CLASS__ . '::preLoadTheme', false, __FILE__);
		add_integration_function('integrate_load_theme', __CLASS__ . '::loadTheme', false, __FILE__);
		add_integration_function('integrate_menu_buttons', __CLASS__ . '::menuButtons', false, __FILE__);
		add_integration_function('integrate_actions', __CLASS__ . '::actions', false, __FILE__);
		add_integration_function('integrate_theme_context', __CLASS__ . '::themeContext', false, __FILE__);
		add_integration_function('integrate_display_topic', __NAMESPACE__ . '\TopicHooks::displayTopic', false, '$sourcedir/Optimus/TopicHooks.php');
		add_integration_function('integrate_prepare_display_context', __NAMESPACE__ . '\TopicHooks::prepareDisplayContext', false, '$sourcedir/Optimus/TopicHooks.php');
		add_integration_function('integrate_post_end', __NAMESPACE__ . '\TopicHooks::postEnd', false, '$sourcedir/Optimus/TopicHooks.php');
		add_integration_function('integrate_before_create_topic', __NAMESPACE__ . '\TopicHooks::beforeCreateTopic', false, '$sourcedir/Optimus/TopicHooks.php');
		add_integration_function('integrate_create_topic', __NAMESPACE__ . '\TopicHooks::createTopic', false, '$sourcedir/Optimus/TopicHooks.php');
		add_integration_function('integrate_modify_post', __NAMESPACE__ . '\TopicHooks::modifyPost', false, '$sourcedir/Optimus/TopicHooks.php');
		add_integration_function('integrate_remove_topics', __NAMESPACE__ . '\TopicHooks::removeTopics', false, '$sourcedir/Optimus/TopicHooks.php');
		add_integration_function('integrate_load_board', __NAMESPACE__ . '\BoardHooks::loadBoard', false, '$sourcedir/Optimus/BoardHooks.php');
		add_integration_function('integrate_board_info', __NAMESPACE__ . '\BoardHooks::boardInfo', false, '$sourcedir/Optimus/BoardHooks.php');
		add_integration_function('integrate_pre_boardtree', __NAMESPACE__ . '\BoardHooks::preBoardtree', false, '$sourcedir/Optimus/BoardHooks.php');
		add_integration_function('integrate_boardtree_board', __NAMESPACE__ . '\BoardHooks::boardtreeBoard', false, '$sourcedir/Optimus/BoardHooks.php');
		add_integration_function('integrate_edit_board', __NAMESPACE__ . '\BoardHooks::editBoard', false, '$sourcedir/Optimus/BoardHooks.php');
		add_integration_function('integrate_modify_board', __NAMESPACE__ . '\BoardHooks::modifyBoard', false, '$sourcedir/Optimus/BoardHooks.php');
		add_integration_function('integrate_admin_areas', __NAMESPACE__ . '\Settings::adminAreas', false, '$sourcedir/Optimus/Settings.php');
		add_integration_function('integrate_admin_search', __NAMESPACE__ . '\Settings::adminSearch', false, '$sourcedir/Optimus/Settings.php');
		add_integration_function('integrate_credits', __CLASS__ . '::credits', false, __FILE__);
	}

	/**
	 * Autoloading of used classes
	 *
	 * @param array $classMap
	 * @return void
	 */
	public static function autoload(&$classMap)
	{
		$classMap['Bugo\\Optimus\\'] = 'Optimus/';
		$classMap['Bugo\\Optimus\\Addons\\'] = 'Optimus/addons/';
	}

	/**
	 * Change some PHP settings
	 *
	 * @return void
	 */
	public static function loadSession()
	{
		global $modSettings;

		@ini_set('session.use_only_cookies', !empty($modSettings['optimus_use_only_cookies']));
	}

	/**
	 * Remove index.php from $scripturl
	 *
	 * @param string $buffer
	 * @return void
	 */
	public static function buffer($buffer)
	{
		global $modSettings, $boardurl;

		if (empty($modSettings['optimus_remove_index_php']))
			return $buffer;

		return str_replace($boardurl . '/index.php', $boardurl . '/', $buffer);
	}

	/**
	 * Remove index.php from $scripturl
	 *
	 * @return void
	 */
	public static function preLoadTheme()
	{
		global $modSettings, $scripturl, $boardurl;

		if (!empty($modSettings['optimus_remove_index_php']))
			$scripturl = $boardurl . '/';
	}

	/**
	 * Language files and various operations
	 *
	 * @return void
	 */
	public static function loadTheme()
	{
		loadLanguage('Optimus/');

		self::loadClass('Subs');
		self::loadClass('Keywords');
		Subs::changeFrontPageTitle();
		Subs::addCounters();
	}

	/**
	 * Various scripts and variables
	 *
	 * @return void
	 */
	public static function menuButtons()
	{
		Subs::addFavicon();
		Subs::addJsonLd();
		Subs::addFrontPageDescription();
		Subs::makeErrorCodes();
		Subs::makeTopicDescription();
		Subs::getOgImage();
		Subs::addSitemapLink();
		Subs::runAddons();
	}

	/**
	 * Add "keywords" action
	 *
	 * @param array $actions
	 * @return void
	 */
	public static function actions(&$actions)
	{
		global $modSettings;

		if (!empty($modSettings['optimus_allow_change_topic_keywords']) || !empty($modSettings['optimus_show_keywords_block']))
			$actions['keywords'] = array('Optimus/Keywords.php', array(__NAMESPACE__ . '\Keywords', 'showTableWithTheSameKeyword'));
	}

	/**
	 * Change various metatags
	 *
	 * @return void
	 */
	public static function themeContext()
	{
		Subs::makeExtendTitles();
		Subs::prepareMetaTags();
	}

	/**
	 * The mod credits for action=credits
	 *
	 * @return void
	 */
	public static function credits()
	{
		global $context;

		$context['credits_modifications'][] = Subs::getOptimusLink() . ' &copy; 2010&ndash;2020, Bugo';
	}

	/**
	 * Calling sitemap generation via task manager
	 *
	 * @return boolean
	 */
	public static function scheduledTask()
	{
		global $modSettings;

		if (empty($modSettings['optimus_sitemap_enable']))
			return false;

		self::loadClass('Subs');
		self::loadClass('Sitemap');

		$links   = Subs::getLinks();
		$sitemap = new Sitemap($links, '', !empty($modSettings['optimus_sitemap_name']) ? $modSettings['optimus_sitemap_name'] : '');

		return $sitemap->generate();
	}

	/**
	 * Include $class.php file if it is not loaded previously (for Tapatalk etc)
	 *
	 * @param string $className
	 * @return void
	 */
	public static function loadClass($className)
	{
		global $sourcedir;

		if (empty($className))
			return;

		if (!class_exists(__NAMESPACE__ . "\{$className}"))
			require_once($sourcedir . "/Optimus/{$className}.php");
	}
}