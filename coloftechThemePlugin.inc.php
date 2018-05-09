<?php

/**
 * @file plugins/themes/coloftech/coloftechThemePlugin.inc.php
 *
 * Copyright (c) 2018 Harold Rita
 * Copyright (c) 2017 Vitaliy Bezsheiko, MD
 * Distributed under the GNU GPL v3.
 *
 * @class coloftechThemePlugin
 *
 * @brief Coloftech Theme is an original theme by Old Gregg is developed on the basis of bootstrap 4; it has build-in fucntionality of JATS Parser Plugin and browse latest articles plugin
 */
require_once  __DIR__ . '/jatsParser/src/start.php';
use JATSParser\Body\Document as Document;

import('lib.pkp.classes.plugins.GenericPlugin');
import('lib.pkp.classes.plugins.ThemePlugin');

define('coloftech_CSL_STYLE_DEFAULT', 'vancouver');
define('coloftech_LATEST_ARTICLES_DEFAULT', 20);
define('coloftech_ISSUE_COVER_RELATIVE_URL', 'images/issue_default.jpg');
define('coloftech_LATEST_ISSUES_DEFAULT', 3);

class coloftechThemePlugin extends ThemePlugin
{
	/**
	 * Initialize the theme's styles, scripts and hooks. This is only run for
	 * the currently active theme.
	 *
	 * @return null
	 */
	public function init()
	{
		// Register theme options
		$this->addOption('latestArticlesNumber', 'text', array(
			'label' => 'plugins.roy.latest.number',
			'description' => 'plugins.roy.latest.description',
		));

		$this->addOption('cslStyle', 'radio', array(
			'label' => 'plugins.roy.citation-style.type',
			'description' => 'plugins.roy.citation-style.description',
			'options' => array(
				'vancouver' => 'plugins.roy.citation-style.vancouver',
				'apa' => 'plugins.roy.citation-style.apa',			)
		));

		$this->addOption('displayIssuesSlider', 'text', array(
			'label' => 'plugins.roy.if-display.issue-slider',
			'description' => 'plugins.roy.if-display.issue-slider.description',
		));

		$this->addOption('journalSummary', 'radio', array(
			'label' => 'plugins.roy.journal.summary.display',
			'options' => array(
				'true' => 'plugins.roy.journal.summary.display.true',
				'false' => 'plugins.roy.journal.summary.display.false',
			)
		));

		$this->addStyle('bootstrap', 'bootstrap/css/bootstrap.min.css');
		$this->addStyle('header', 'css/header.css');
		$this->addStyle('footer', 'css/footer.css');
		$this->addStyle('issue', 'css/issue.css');
		$this->addStyle('site-wide', 'css/main.css');
		$this->addStyle('index', 'css/index.css');
		$this->addStyle('article', 'css/article.css');
		$this->addStyle('contact', 'css/contact.css');
		$this->addStyle('announcements', 'css/announcements.css');

		$this->addScript('jquery', 'jquery/jquery.min.js');
		$this->addScript('popper', 'bootstrap/js/popper.min.js');
		$this->addScript('bootstrap', 'bootstrap/js/bootstrap.min.js');
		$this->addScript('fontawesome', 'js/fontawesome-all.min.js');
		$this->addScript('article', 'js/article.js');


		$this->addStyle(
			'my-custom-font1',
			'//fonts.googleapis.com/css?family=Lora',
			array('baseUrl' => 'https://fonts.googleapis.com/css?family=Lora" rel="stylesheet'));

		$this->addStyle(
			'my-custom-font2',
			'//fonts.googleapis.com/css?family=PT+Serif',
			array('baseUrl' => ''));

		$this->addStyle(
			'my-custom-font3',
			'//fonts.googleapis.com/css?family=Arimo',
			array('baseUrl' => ''));
		$this->addStyle(
			'my-custom-font4',
			'//fonts.googleapis.com/css?family=Alegreya',
			array('baseUrl' => ''));
		$this->addStyle(
			'my-custom-font5',
			'//fonts.googleapis.com/css?family=Play',
			array('baseUrl' => ''));
		$this->addStyle(
			'my-custom-font6',
			'//fonts.googleapis.com/css?family=Source+Sans+Pro',
			array('baseUrl' => ''));
		$this->addStyle(
			'my-custom-font7',
			'//fonts.googleapis.com/css?family=Alegreya+Sans',
			array('baseUrl' => ''));
		$this->addStyle(
			'my-custom-font8',
			'https://fonts.googleapis.com/css?family=Roboto',
			array('baseUrl' => ''));

		$this->addMenuArea(array('primary', 'user'));

		HookRegistry::register('TemplateManager::display', array($this, 'jatsParser'), HOOK_SEQUENCE_NORMAL);
		HookRegistry::register('TemplateManager::display', array($this, 'browseLatest'), HOOK_SEQUENCE_CORE);
		HookRegistry::register('TemplateManager::display', array($this, 'citationStyle'), HOOK_SEQUENCE_LATE);
		HookRegistry::register('TemplateManager::display', array($this, 'latestIssuesSlider'), HOOK_SEQUENCE_NORMAL);
		HookRegistry::register('TemplateManager::display', array($this, 'journalDescription'), HOOK_SEQUENCE_NORMAL);
	}



	/**
	 * Get the display name of this plugin
	 * @return string
	 */
	function getDisplayName()
	{
		return __('plugins.themes.coloftech.name');
	}

	/**
	 * Get the description of this plugin
	 * @return string
	 */
	function getDescription()
	{
		return __('plugins.themes.coloftech.description');
	}

	/** For displaying article's JATS XML */
	public function jatsParser($hookName, $args)
	{

		// Retrieve the TemplateManager and the template filename
		$smarty = $args[0];
		$template = $args[1];

		// Don't do anything if we're not loading the right template
		if ($template != 'frontend/pages/article.tpl') return false;

		$articleArrays = $smarty->get_template_vars('article');

		foreach ($articleArrays->getGalleys() as $galley) {
			if ($galley && in_array($galley->getFileType(), array('application/xml', 'text/xml'))) {
				$xmlGalleys[] = $galley;
			}
		}

		// Return false if no XML galleys available
		if (!$xmlGalleys) {
			return false;
		}

		$xmlGalley = null;
		foreach ($xmlGalleys as $xmlNumber => $xmlGalleyOne) {
			if ($xmlNumber > 0) {
				if ($xmlGalleyOne->getLocale() == AppLocale::getLocale()) {
					$xmlGalley = $xmlGalleyOne;
				}
			} else {
				$xmlGalley = $xmlGalleyOne;
			}
		}

		// Parsing JATS XML
		$jatsDocument = new Document($xmlGalley->getFile()->getFilePath());

		// Assigning variables to article template
		$smarty->assign('jatsDocument', $jatsDocument);

		// Кetrieving embeded files
		$submissionFile = $xmlGalley->getFile();
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		import('lib.pkp.classes.submission.SubmissionFile'); // Constants
		$embeddableFiles = array_merge(
			$submissionFileDao->getLatestRevisions($submissionFile->getSubmissionId(), SUBMISSION_FILE_PROOF),
			$submissionFileDao->getLatestRevisionsByAssocId(ASSOC_TYPE_SUBMISSION_FILE, $submissionFile->getFileId(), $submissionFile->getSubmissionId(), SUBMISSION_FILE_DEPENDENT)
		);
		$referredArticle = null;
		$articleDao = DAORegistry::getDAO('ArticleDAO');
		$imageUrlArray = array();
		foreach ($embeddableFiles as $embeddableFile) {
			$params = array();
			if ($embeddableFile->getFileType() == 'image/png' || $embeddableFile->getFileType() == 'image/jpeg') {
				// Ensure that the $referredArticle object refers to the article we want
				if (!$referredArticle || $referredArticle->getId() != $galley->getSubmissionId()) {
					$referredArticle = $articleDao->getById($galley->getSubmissionId());
				}
				$fileUrl = Application::getRequest()->url(null, 'article', 'download', array($referredArticle->getBestArticleId(), $galley->getBestGalleyId(), $embeddableFile->getFileId()), $params);
				$imageUrlArray[$embeddableFile->getOriginalFileName()] = $fileUrl;
			}
		}
		$smarty->assign('imageUrlArray', $imageUrlArray);
	}

	/* For retrieving articles from the database */
	public function browseLatest($hookName, $args)
	{
		$smarty = $args[0];
		$template = $args[1];

		if ($template != 'frontend/pages/indexJournal.tpl') return false;

		/* get number of latest article to display from user input; if there was none - use default */
		$latestArticles = $this->getOption("latestArticlesNumber");
		if (is_null($latestArticles)) {
			$latestArticles = coloftech_LATEST_ARTICLES_DEFAULT;
		} else {
			$latestArticles = intval($latestArticles);
		}

		$rangeArticles = new DBResultRange($latestArticles, 1);
		$publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');

		/* retrieve current journal id from the request */
		$request = $this->getRequest();
		$journal = $request->getJournal();
		$journalId = $journal->getId();

		/* retrieve latest articles */
		$publishedArticleObjects = $publishedArticleDao->getPublishedArticlesByJournalId($journalId, $rangeArticles, $reverse = true);

		$publishedArticles = array();

		while ($publishedArticle = $publishedArticleObjects->next()) {
			$publishedArticles[] = $publishedArticle;
		}
		$smarty->assign('publishedArticles', $publishedArticles);
	}

	public function citationStyle($hookName, $args) {
		$smarty = $args[0];
		$template = $args[1];

		if ($template != 'frontend/pages/article.tpl') return false;

		$cslStyle = $this->getOption("cslStyle");
		if (is_null($cslStyle)) {
			$cslStyle = coloftech_CSL_STYLE_DEFAULT;
		}

		$smarty->assign('cslStyle', $cslStyle);
	}

	public function latestIssuesSlider($hookName, $args) {
		$smarty = $args[0];
		$template = $args[1];

		if ($template != 'frontend/pages/indexJournal.tpl') return false;

		$latestIssuesInput = $this->getOption("displayIssuesSlider");
		if (is_null($latestIssuesInput)) {
			$latestIssuesInput = coloftech_LATEST_ISSUES_DEFAULT;
		} elseif (intval($latestIssuesInput) === 0) {
			return false;
		} else {
			$latestIssuesInput = intval($latestIssuesInput);
		}

		$request = $this->getRequest();
		$journal = $request->getJournal();
		$journalId = $journal->getId();

		$issueDao = DAORegistry::getDAO('IssueDAO');
		$rangeIssues = new DBResultRange($latestIssuesInput, 1);
		$latestIssuesObjects = $issueDao->getPublishedIssues($journalId, $rangeIssues);

		$latestIssues = array();
		while ($latestIssue = $latestIssuesObjects->next()) {
			$latestIssues[] = $latestIssue;
		}

		$defaultCoverImageUrl = "/" . $this->getPluginPath() . "/" . coloftech_ISSUE_COVER_RELATIVE_URL;

		$smarty->assign('latestIssues', $latestIssues);
		$smarty->assign('defaultCoverImageUrl', $defaultCoverImageUrl);
	}

	public function journalDescription ($hookName, $args) {
		$smarty = $args[0];

		$showSummaryData = $this->getOption("journalSummary");

		$showSummary = false;
		if (!is_null($showSummaryData) && ($showSummaryData == "true")) {
			$showSummary = true;
		}

		$smarty->assign('showSummary', $showSummary);
	}

}

?>