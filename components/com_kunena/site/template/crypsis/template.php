<?php
/**
 * Kunena Component
 * @package     Kunena.Template.Crypsis
 * @subpackage  Template
 *
 * @copyright   (C) 2008 - 2014 Kunena Team. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link        http://www.kunena.org
 **/
defined('_JEXEC') or die;

/**
 * Crypsis template.
 */
class KunenaTemplateCrypsis extends KunenaTemplate
{
	/**
	 * List of parent template names.
	 *
	 * This template will automatically search for missing files from listed parent templates.
	 * The feature allows you to create one base template and only override changed files.
	 *
	 * @var array
	 */
	protected $default = array('crypsis');

	/**
	 * Relative paths to various file types in this template.
	 *
	 * These will override default files in JROOT/media/kunena
	 *
	 * @var array
	 */
	protected $pathTypes = array(
		'emoticons' => 'media/emoticons',
		'ranks' => 'media/ranks',
		'icons' => 'media/icons',
		'topicicons' => 'media/topic_icons',
		'categoryicons' => 'media/category_icons',
		'images' => 'media/images',
		'js' => 'media/js',
		'css' => 'media/css'
	);

	protected $userClasses = array(
		'kwho-',
		'admin'=>'kwho-admin',
		'globalmod'=>'kwho-globalmoderator',
		'moderator'=>'kwho-moderator',
		'user'=>'kwho-user',
		'guest'=>'kwho-guest',
		'banned'=>'kwho-banned',
		'blocked'=>'kwho-blocked'
	);

	/**
	 * Logic to load language strings for the template.
	 *
	 * By default language files are also loaded from the parent templates.
	 *
	 * @return void
	 */
	public function loadLanguage()
	{
		$lang = JFactory::getLanguage();
		KunenaFactory::loadLanguage('com_kunena.templates', 'site');

		foreach (array_reverse($this->default) as $template)
		{
			$file = "kunena_tmpl_{$template}";
			$lang->load($file, JPATH_SITE)
				|| $lang->load($file, KPATH_SITE)
				|| $lang->load($file, KPATH_SITE . "/template/{$template}");
		}
	}

	/**
	 * Template initialization.
	 *
	 * @return void
	 */
	public function initialize()
	{
		// Template requires Mootools 1.4+ framework.
		$this->loadMootools();
		JHtml::_('behavior.tooltip');
		JHtml::_('bootstrap.modal');

		// Template also requires jQuery framework.
		JHtml::_('jquery.framework');
		//JHtml::_('formbehavior.chosen', 'select');

		// Load caret.js always before atwho.js script and use it for autocomplete, emojiis...
		$this->addScript('js/caret.js');
		$this->addScript('js/atwho.js');
		$this->addStyleSheet('css/atwho.css');

		// Load scripts to handle fileupload process
		JText::script('COM_KUNENA_EDITOR_INSERT');
		JText::script('COM_KUNENA_GEN_REMOVE_FILE');
		JText::script('COM_KUNENA_UPLOADED_LABEL_ERROR_REACHED_MAX_NUMBER_FILES');
		JText::script('COM_KUNENA_UPLOADED_LABEL_UPLOAD_BUTTON');
		JText::script('COM_KUNENA_UPLOADED_LABEL_PROCESSING_BUTTON');
		JText::script('COM_KUNENA_UPLOADED_LABEL_ABORT_BUTTON');

		$this->addScript('js/jquery.ui.widget.js');
		$this->addScript('js/load-image.min.js');
		$this->addScript('js/canvas-to-blob.min.js');
		$this->addScript('js/jquery.fileupload.js');
		$this->addScript('js/jquery.fileupload-process.js');
		$this->addScript('js/jquery.fileupload-image.js');
		$this->addScript('js/upload.main.js');
		$this->addStyleSheet('css/fileupload.css');
		$this->addStyleSheet('css/fileupload-ui.css');

		// Load JavaScript.
		$this->addScript('plugins.js');

		// Compile CSS from LESS files.
		$this->compileLess('crypsis.less', 'kunena.css');
		$this->addStyleSheet('kunena.css');

		$config = KunenaFactory::getConfig();

		// If polls are enabled, load also poll JavaScript.
		if ($config->pollenabled == 1)
		{
			JText::script('COM_KUNENA_POLL_OPTION_NAME');
			JText::script('COM_KUNENA_EDITOR_HELPLINE_OPTION');
			$this->addScript('poll.js');
		}

		// Load FancyBox library if enabled in configuration
		if ($config->lightbox == 1)
		{
			$template = KunenaTemplate::getInstance();
			if ( $template->params->get('lightboxColor') == 'white') {
				$this->addStyleSheet('css/fancybox-white.css');
			}
			else  {
				$this->addStyleSheet('css/fancybox-black.css');
			}
			$this->addScript('js/fancybox.js');
			JFactory::getDocument()->addScriptDeclaration('
				jQuery(document).ready(function() {
					jQuery(".fancybox-button").fancybox({
						prevEffect		: \'none\',
						nextEffect		: \'none\',
						closeBtn		:  true,
						helpers		: {
							title	: { type : \'inside\' },
							buttons	: {}
						}
					});
				});
			');
		}

		parent::initialize();
	}

	public function addStyleSheet($filename, $group='forum')
	{
		$filename = $this->getFile($filename, false, '', "media/kunena/cache/{$this->name}/css");
		return JFactory::getDocument()->addStyleSheet(JUri::root(true)."/{$filename}");
	}

	public function getButton($link, $name, $scope, $type, $id = null)
	{
		$types = array('communication'=>'comm', 'user'=>'user', 'moderation'=>'mod', 'permanent'=>'mod');
		$names = array('unfavorite'=>'favorite', 'unsticky'=>'sticky', 'unlock'=>'lock', 'create'=>'newtopic',
				'quickreply'=>'reply', 'quote'=>'quote', 'edit'=>'edit', 'permdelete'=>'delete',
				'flat'=>'layout-flat', 'threaded'=>'layout-threaded', 'indented'=>'layout-indented',
				'list'=>'reply');

		// Need special style for buttons in drop-down list
		$buttonsDropdown = array('reply', 'quote', 'edit', 'delete', 'subscribe', 'unsubscribe', 'unfavorite', 'favorite', 'unsticky', 'sticky', 'unlock', 'lock', 'moderate', 'undelete', 'permdelete', 'flat', 'threaded', 'indented');

		$text = JText::_("COM_KUNENA_BUTTON_{$scope}_{$name}");
		$title = JText::_("COM_KUNENA_BUTTON_{$scope}_{$name}_LONG");

		if ($title == "COM_KUNENA_BUTTON_{$scope}_{$name}_LONG") $title = '';

		if ($id) $id = 'id="'.$id.'"';

		if ( in_array($name,$buttonsDropdown) )
		{
			return <<<HTML
				<a $id style="" href="{$link}" rel="nofollow" title="{$title}">
				{$text}
				</a>
HTML;
		}
		else
		{
			return <<<HTML
				<a $id style="" href="{$link}" rel="nofollow" title="{$title}">
				<span class="{$name}"></span>
				{$text}
				</a>
HTML;
		}
	}

	public function getIcon($name, $title='')
	{
		return '<span class="kicon '.$name.'" title="'.$title.'"></span>';
	}

	public function getImage($image, $alt='')
	{
		return '<img src="'.$this->getImagePath($image).'" alt="'.$alt.'" />';
	}

}
