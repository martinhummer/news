<?php
namespace GeorgRinger\News\Processor;

/**
 * Class NewsProcessor
 *
 * @package GeorgRinger\News\Processor
 */
class NewsProcessor implements \Portrino\PxSemantic\Processor\ProcessorInterface
{

    /**
     * @var \Portrino\PxSemantic\Domain\Model\Page
     */
    protected $currentPage;

    /**
     * @var int
     */
    protected $currentPageUid;

    /**
     * @var \Portrino\PxSemantic\Domain\Repository\PageRepository
     * @inject
     */
    protected $pageRepository;

    /**
     * @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected $typoScriptFrontendController;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @inject
     */
    protected $objectManager;

    /**
     * @var \GeorgRinger\News\Domain\Repository\NewsRepository
     * @inject
     */
    protected $newsRepository;

    /**
     * Initializes the controller before invoking an action method.
     *
     * Override this method to solve tasks which all actions have in
     * common.
     *
     * @return void
     */
    public function initializeObject()
    {
        if (TYPO3_MODE === 'FE') {
            $this->typoScriptFrontendController = $GLOBALS['TSFE'];
            $this->currentPageUid = $this->typoScriptFrontendController->id;
            $this->currentPage = $this->pageRepository->findByUid($this->currentPageUid);
        }
    }

    /**
     * @param \TYPO3\CMS\Extbase\DomainObject\AbstractEntity $entity
     * @param array $settings
     */
    public function process(&$entity, $settings = array(), $resourceId = null)
    {
        if ($entity instanceof \Portrino\PxSemantic\SchemaOrg\Article) {
            if ($this->currentPage) {

                /** @var \GeorgRinger\News\Domain\Model\News $newsItem */
                $newsItem = $this->newsRepository->findByUid($settings['newsItemUid']);

                /** @var \Portrino\PxSemantic\SchemaOrg\Person $author */
                $author = $this->objectManager->get('Portrino\\PxSemantic\\SchemaOrg\\Person');
                $author->setName($newsItem->getAuthor());

                /** @var \Portrino\PxSemantic\SchemaOrg\Organization $organization */
                $organization = $this->objectManager->get('Portrino\\PxSemantic\\SchemaOrg\\Organization');
                $organization->setName('myCompanyName');

                if ($newsItem) {
                    $entity
                        ->setHeadline($newsItem->getTitle())
                        ->setArticleBody($newsItem->getBodytext())
                        ->setAuthor($author)
                        ->setDatePublished($newsItem->getDatetime())
                        ->setPublisher($organization);
                }
            }
        }
    }

}