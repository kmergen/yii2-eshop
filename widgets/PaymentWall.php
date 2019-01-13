<?php
/**
 * @copyright Copyright &copy; Klaus Mergen, 2014
 * @package yii2-eshop
 * @version 2.0
 */

namespace kmergen\widgets;

use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;



/**
 * The payment wall accordion for the checkout page
 *
 * @author Klaus Mergen <kmergenweb@gmail.com>
 * @since 1.0
 */
class PaymentWall extends Widget
{
    /**
     * @var string $mainContainerTag the that surrounds the button list.
     */
    public $mainContainerTag = 'nav';

    /**
     * @var string $mainContainerOptions the html options for the [[mainContainerTag]].
     */
    public $mainContainerOptions = [];

    /**
     * @var array bootstrap 4 ready.
     */
    public $linkContainerOptions = ['class' => 'page-item'];

    /**
     * @var array bootstrap 4 ready.
     */
    public $linkOptions = ['class' => 'page-link'];

    /**
     * @var array bootstrap 4 ready.
     */
    public $disabledListItemSubTagOptions = ['tag' => 'a', 'class' => 'page-link', 'href' => '#'];





    /**
     * Renders the page buttons.
     * @return string the rendering result
     */
    protected function renderPageButtons()
    {
        $pageCount = $this->pagination->getPageCount();
        if ($pageCount < 2 && $this->hideOnSinglePage) {
            return '';
        }

        $buttons = [];
        $currentPage = $this->pagination->getPage();

        // first page
        $firstPageLabel = $this->firstPageLabel === true ? '1' : $this->firstPageLabel;
        if ($firstPageLabel !== false) {
            $buttons[] = $this->renderPageButton($firstPageLabel, 0, $this->firstPageCssClass, $currentPage <= 0, false);
        }

        // prev page
        if ($this->prevPageLabel !== false) {
            if (($page = $currentPage - 1) < 0) {
                $page = 0;
            }
            $buttons[] = $this->renderPageButton($this->prevPageLabel, $page, $this->prevPageCssClass, $currentPage <= 0, false);
        }

        // internal pages
        list($beginPage, $endPage) = $this->getPageRange();
        for ($i = $beginPage; $i <= $endPage; ++$i) {
            $buttons[] = $this->renderPageButton($i + 1, $i, null, $this->disableCurrentPageButton && $i == $currentPage, $i == $currentPage);
        }

        // next page
        if ($this->nextPageLabel !== false) {
            if (($page = $currentPage + 1) >= $pageCount - 1) {
                $page = $pageCount - 1;
            }
            $buttons[] = $this->renderPageButton($this->nextPageLabel, $page, $this->nextPageCssClass, $currentPage >= $pageCount - 1, false);
        }

        // last page
        $lastPageLabel = $this->lastPageLabel === true ? $pageCount : $this->lastPageLabel;
        if ($lastPageLabel !== false) {
            $buttons[] = $this->renderPageButton($lastPageLabel, $pageCount - 1, $this->lastPageCssClass, $currentPage >= $pageCount - 1, false);
        }

        $options = $this->options;
        $tag = ArrayHelper::remove($options, 'tag', 'ul');
        $buttonsList = Html::tag($tag, implode("\n", $buttons), $options);
        return Html::tag($this->mainContainerTag, $buttonsList, $this->mainContainerOptions);
    }
}
