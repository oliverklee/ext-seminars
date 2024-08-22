<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Renders rich text.
 *
 * This is a workaround for legacy code that needs to render richt text without Extbase/Fluid.
 */
class RichTextViewHelper
{
    private const RTE_CONFIGURATION = [
        'makelinks' => '1',
        'makelinks.' =>
            [
                'http.' =>
                    [
                        'keep' => 'path',
                        'extTarget' => '_blank',
                    ],
                'mailto.' =>
                    [
                        'keep' => 'path',
                    ],
            ],
        'tags.' =>
            [
                'a' => 'TEXT',
                'a.' =>
                    [
                        'current' => '1',
                        'typolink.' =>
                            [
                                'parameter.' =>
                                    [
                                        'data' => 'parameters:href',
                                    ],
                                'title.' =>
                                    [
                                        'data' => 'parameters:title',
                                    ],
                                'ATagParams.' =>
                                    [
                                        'data' => 'parameters:allParams',
                                    ],
                                'target.' =>
                                    [
                                        'ifEmpty.' =>
                                            [
                                                'data' => 'parameters:target',
                                            ],
                                    ],
                                'extTarget.' =>
                                    [
                                        'ifEmpty.' =>
                                            [
                                                'override' => '_blank',
                                            ],
                                        'override.' =>
                                            [
                                                'data' => 'parameters:target',
                                            ],
                                    ],
                            ],
                    ],
            ],
        'allowTags' => 'a, abbr, acronym, address, article, aside, b, bdo, big, blockquote, br, caption, center, cite, code, col, colgroup, dd, del, dfn, dl, div, dt, em, font, footer, header, h1, h2, h3, h4, h5, h6, hr, i, img, ins, kbd, label, li, link, meta, nav, ol, p, pre, q, s, samp, sdfield, section, small, span, strike, strong, style, sub, sup, table, thead, tbody, tfoot, td, th, tr, title, tt, u, ul, var',
        'denyTags' => '*',
        'sword' => '<span class="ce-sword">|</span>',
        'constants' => '1',
        'nonTypoTagStdWrap.' =>
            [
                'HTMLparser' => '1',
                'HTMLparser.' =>
                    [
                        'keepNonMatchedTags' => '1',
                        'htmlSpecialChars' => '2',
                    ],
                'encapsLines.' =>
                    [
                        'encapsTagList' => 'p,pre,h1,h2,h3,h4,h5,h6,hr,dt',
                        'remapTag.' =>
                            [
                                'DIV' => 'P',
                            ],
                        'nonWrappedTag' => 'P',
                        'innerStdWrap_all.' =>
                            [
                                'ifBlank' => '&nbsp;',
                            ],
                    ],
            ],
        'htmlSanitize' => '1',
        'externalBlocks' => 'article, aside, blockquote, div, dd, dl, footer, header, nav, ol, section, table, ul, pre, figure',
        'externalBlocks.' =>
            [
                'ol.' =>
                    [
                        'stripNL' => '1',
                        'stdWrap.' =>
                            [
                                'parseFunc' => '< lib.parseFunc',
                            ],
                    ],
                'ul.' =>
                    [
                        'stripNL' => '1',
                        'stdWrap.' =>
                            [
                                'parseFunc' => '< lib.parseFunc',
                            ],
                    ],
                'pre.' =>
                    [
                        'stdWrap.' =>
                            [
                                'parseFunc.' =>
                                    [
                                        'makelinks' => '1',
                                        'makelinks.' =>
                                            [
                                                'http.' =>
                                                    [
                                                        'keep' => 'path',
                                                        'extTarget' => '_blank',
                                                    ],
                                                'mailto.' =>
                                                    [
                                                        'keep' => 'path',
                                                    ],
                                            ],
                                        'tags.' =>
                                            [
                                                'a' => 'TEXT',
                                                'a.' =>
                                                    [
                                                        'current' => '1',
                                                        'typolink.' =>
                                                            [
                                                                'parameter.' =>
                                                                    [
                                                                        'data' => 'parameters:href',
                                                                    ],
                                                                'title.' =>
                                                                    [
                                                                        'data' => 'parameters:title',
                                                                    ],
                                                                'ATagParams.' =>
                                                                    [
                                                                        'data' => 'parameters:allParams',
                                                                    ],
                                                                'target.' =>
                                                                    [
                                                                        'ifEmpty.' =>
                                                                            [
                                                                                'data' => 'parameters:target',
                                                                            ],
                                                                    ],
                                                                'extTarget.' =>
                                                                    [
                                                                        'ifEmpty.' =>
                                                                            [
                                                                                'override' => '_blank',
                                                                            ],
                                                                        'override.' =>
                                                                            [
                                                                                'data' => 'parameters:target',
                                                                            ],
                                                                    ],
                                                            ],
                                                    ],
                                            ],
                                        'allowTags' => 'a, abbr, acronym, address, article, aside, b, bdo, big, blockquote, br, caption, center, cite, code, col, colgroup, dd, del, dfn, dl, div, dt, em, font, footer, header, h1, h2, h3, h4, h5, h6, hr, i, img, ins, kbd, label, li, link, meta, nav, ol, p, pre, q, s, samp, sdfield, section, small, span, strike, strong, style, sub, sup, table, thead, tbody, tfoot, td, th, tr, title, tt, u, ul, var',
                                        'denyTags' => '*',
                                        'sword' => '<span class="ce-sword">|</span>',
                                        'constants' => '1',
                                        'nonTypoTagStdWrap.' =>
                                            [
                                                'HTMLparser' => '1',
                                                'HTMLparser.' =>
                                                    [
                                                        'keepNonMatchedTags' => '1',
                                                        'htmlSpecialChars' => '2',
                                                    ],
                                            ],
                                        'htmlSanitize' => '1',
                                    ],
                            ],
                    ],
                'table.' =>
                    [
                        'stripNL' => '1',
                        'stdWrap.' =>
                            [
                                'HTMLparser' => '1',
                                'HTMLparser.' =>
                                    [
                                        'tags.' =>
                                            [
                                                'table.' =>
                                                    [
                                                        'fixAttrib.' =>
                                                            [
                                                                'class.' =>
                                                                    [
                                                                        'default' => 'contenttable',
                                                                        'always' => '1',
                                                                        'list' => 'contenttable',
                                                                    ],
                                                            ],
                                                    ],
                                            ],
                                        'keepNonMatchedTags' => '1',
                                    ],
                            ],
                        'HTMLtableCells' => '1',
                        'HTMLtableCells.' =>
                            [
                                'default.' =>
                                    [
                                        'stdWrap.' =>
                                            [
                                                'parseFunc' => '< lib.parseFunc_RTE',
                                                'parseFunc.' =>
                                                    [
                                                        'nonTypoTagStdWrap.' =>
                                                            [
                                                                'encapsLines.' =>
                                                                    [
                                                                        'nonWrappedTag' => '',
                                                                        'innerStdWrap_all.' =>
                                                                            [
                                                                                'ifBlank' => '',
                                                                            ],
                                                                    ],
                                                            ],
                                                    ],
                                            ],
                                    ],
                                'addChr10BetweenParagraphs' => '1',
                            ],
                    ],
                'div.' =>
                    [
                        'stripNL' => '1',
                        'callRecursive' => '1',
                    ],
                'article.' =>
                    [
                        'stripNL' => '1',
                        'callRecursive' => '1',
                    ],
                'aside.' =>
                    [
                        'stripNL' => '1',
                        'callRecursive' => '1',
                    ],
                'figure.' =>
                    [
                        'stripNL' => '1',
                        'callRecursive' => '1',
                    ],
                'blockquote.' =>
                    [
                        'stripNL' => '1',
                        'callRecursive' => '1',
                    ],
                'footer.' =>
                    [
                        'stripNL' => '1',
                        'callRecursive' => '1',
                    ],
                'header.' =>
                    [
                        'stripNL' => '1',
                        'callRecursive' => '1',
                    ],
                'nav.' =>
                    [
                        'stripNL' => '1',
                        'callRecursive' => '1',
                    ],
                'section.' =>
                    [
                        'stripNL' => '1',
                        'callRecursive' => '1',
                    ],
                'dl.' =>
                    [
                        'stripNL' => '1',
                        'callRecursive' => '1',
                    ],
                'dd.' =>
                    [
                        'stripNL' => '1',
                        'callRecursive' => '1',
                    ],
            ],
    ];

    /**
     * @var ContentObjectRenderer
     */
    private $contentObject;

    public function __construct()
    {
        $this->contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $this->contentObject->start([]);
    }

    public function render(string $content): string
    {
        return $this->contentObject->parseFunc($content, self::RTE_CONFIGURATION);
    }
}
