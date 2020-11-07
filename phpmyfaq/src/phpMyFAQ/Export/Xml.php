<?php

/**
 * XML Export class for phpMyFAQ.
 *
 * This Source Code Form is subject to the terms of the Mozilla Public License,
 * v. 2.0. If a copy of the MPL was not distributed with this file, You can
 * obtain one at http://mozilla.org/MPL/2.0/.
 *
 * @package   phpMyFAQ
 * @author    Thorsten Rinne <thorsten@phpmyfaq.de>
 * @copyright 2009-2020 phpMyFAQ Team
 * @license   http://www.mozilla.org/MPL/2.0/ Mozilla Public License Version 2.0
 * @link      https://www.phpmyfaq.de
 * @since     2009-10-07
 */

namespace phpMyFAQ\Export;

use phpMyFAQ\Category;
use phpMyFAQ\Configuration;
use phpMyFAQ\Date;
use phpMyFAQ\Export;
use phpMyFAQ\Faq;
use phpMyFAQ\Strings;
use XMLWriter;

/**
 * Class Xml
 *
 * @package phpMyFAQ\Export
 */
class Xml extends Export
{
    /**
     * XMLWriter object.
     *
     * @var XMLWriter
     */
    private $xml = null;

    /**
     * Constructor.
     *
     * @param Faq           $faq      FaqHelper object
     * @param Category      $category Entity object
     * @param Configuration $config   Configuration
     */
    public function __construct(Faq $faq, Category $category, Configuration $config)
    {
        $this->faq = $faq;
        $this->category = $category;
        $this->config = $config;
        $this->xml = new XMLWriter();

        $this->xml->openMemory();
        $this->xml->setIndent(true);
    }

    /**
     * Generates the export.
     *
     * @param int    $categoryId Entity Id
     * @param bool   $downwards  If true, downwards, otherwise upward ordering
     * @param string $language   Language
     *
     * @return string
     */
    public function generate($categoryId = 0, $downwards = true, $language = '')
    {
        // Initialize categories
        $this->category->transform($categoryId);

        $faqdata = $this->faq->get(FAQ_QUERY_TYPE_EXPORT_XML, $categoryId, $downwards, $language);
        $version = $this->config->getVersion();
        $comment = sprintf(
            ' XML output by phpMyFAQ %s | Date: %s ',
            $version,
            Date::createIsoDate(date('YmdHis'))
        );

        $this->xml->startDocument('1.0', 'utf-8', 'yes');
        $this->xml->writeComment($comment);
        $this->xml->startElement('phpmyfaq');

        if (count($faqdata)) {
            foreach ($faqdata as $data) {
                $this->xml->startElement('article');
                $this->xml->writeAttribute('id', $data['id']);
                $this->xml->writeElement('language', $data['lang']);
                $this->xml->writeElement('category', $this->category->getPath($data['category_id'], ' >> '));

                if (!empty($data['keywords'])) {
                    $this->xml->writeElement('keywords', $data['keywords']);
                } else {
                    $this->xml->writeElement('keywords');
                }

                $this->xml->writeElement('question', strip_tags($data['topic']));
                $this->xml->writeElement('answer', Strings::htmlspecialchars($data['content']));

                if (!empty($data['author_name'])) {
                    $this->xml->writeElement('author', $data['author_name']);
                } else {
                    $this->xml->writeElement('author');
                }

                $this->xml->writeElement('data', Date::createIsoDate($data['lastmodified']));
                $this->xml->endElement();
            }
        }

        $this->xml->endElement();

        header('Content-type: text/xml');

        return $this->xml->outputMemory();
    }
}
