<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Htmlable;

use Symfony\Component\DomCrawler\Crawler;

/**
 * The cleaner of HTML.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class HtmlCleaner implements HtmlCleanerInterface
{
    /**
     * @var string[]
     */
    protected array $tags;

    /**
     * @param string[] $tags The html tags to be deleted
     */
    public function __construct(array $tags = [])
    {
        $this->tags = $tags;
    }

    public function clean(?string $value, ?array $tags = null, string $charset = 'UTF-8'): ?string
    {
        if (!empty($value)) {
            $tags = \is_array($tags) ? $tags : $this->tags;
            $crawler = new Crawler();
            $crawler->addHtmlContent($value, $charset);
            $body = $crawler->filter('body');

            if ($body->count() > 0) {
                if (\is_array($tags) && \count($tags) > 0) {
                    $crawler->filter(implode(',', $tags))->each(static function (Crawler $crawler): void {
                        foreach ($crawler as $node) {
                            $node->parentNode->removeChild($node);
                        }
                    });
                }

                $value = trim($body->html());
            } else {
                $value = '';
            }
        }

        return empty($value) ? null : $value;
    }
}
