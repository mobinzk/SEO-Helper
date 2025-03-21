<?php

declare(strict_types=1);

namespace Arcanedev\SeoHelper\Tests;

use Arcanedev\SeoHelper\Contracts\Entities\Analytics as AnalyticsContract;
use Arcanedev\SeoHelper\Contracts\Entities\Description as DescriptionContract;
use Arcanedev\SeoHelper\Contracts\Entities\Keywords as KeywordsContract;
use Arcanedev\SeoHelper\Contracts\Entities\MetaCollection as MetaCollectionContract;
use Arcanedev\SeoHelper\Contracts\Entities\MiscTags as MiscTagsContract;
use Arcanedev\SeoHelper\Contracts\Entities\Title as TitleContract;
use Arcanedev\SeoHelper\Contracts\Entities\Webmasters as WebmastersContract;
use Arcanedev\SeoHelper\Contracts\Renderable;
use Arcanedev\SeoHelper\Contracts\SeoMeta as SeoMetaContract;
use Arcanedev\SeoHelper\Entities\Analytics;
use Arcanedev\SeoHelper\Entities\Description;
use Arcanedev\SeoHelper\Entities\Keywords;
use Arcanedev\SeoHelper\Entities\MetaCollection;
use Arcanedev\SeoHelper\Entities\MiscTags;
use Arcanedev\SeoHelper\Entities\Title;
use Arcanedev\SeoHelper\Entities\Webmasters;
use Arcanedev\SeoHelper\SeoMeta;
use Arcanedev\SeoHelper\Tests\Traits\CanAssertsGoogleAnalytics;
use PHPUnit\Framework\Attributes\Test;

/**
 * Class     SeoMetaTest
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class SeoMetaTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Traits
     | -----------------------------------------------------------------
     */

    use CanAssertsGoogleAnalytics;

    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    private SeoMetaContract $seoMeta;

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    public function setUp(): void
    {
        parent::setUp();

        $this->seoMeta = new SeoMeta(
            $this->getSeoHelperConfig(),
        );
        $this->seoMeta->setUrl($this->baseUrl);
    }

    public function tearDown(): void
    {
        unset($this->seoMeta);

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    #[Test]
    public function it_can_be_instantiated(): void
    {
        $expectations = [
            Renderable::class,
            SeoMetaContract::class,
            SeoMeta::class,
        ];

        foreach ($expectations as $expected) {
            static::assertInstanceOf($expected, $this->seoMeta);
        }

        static::assertNotEmpty($this->seoMeta->render());
    }

    #[Test]
    public function it_can_be_instantiated_by_container(): void
    {
        $this->seoMeta = $this->app[SeoMetaContract::class];

        static::assertInstanceOf(SeoMeta::class, $this->seoMeta);
        static::assertNotEmpty($this->seoMeta->render());
        static::assertNotEmpty((string) $this->seoMeta);
    }

    #[Test]
    public function it_can_set_and_get_and_render_title(): void
    {
        $title    = 'Awesome Title';
        $siteName = $this->getSeoHelperConfig('title.site-name');

        $this->seoMeta->setTitle($title);

        $expected = "<title>{$title} - {$siteName}</title>";

        static::assertStringContainsString($expected, $this->seoMeta->render());
        static::assertStringContainsString($expected, (string) $this->seoMeta);

        $siteName = 'Company name';
        $this->seoMeta->setTitle($title, $siteName);

        $expected = "<title>{$title} - {$siteName}</title>";

        static::assertStringContainsString($expected, $this->seoMeta->render());
        static::assertStringContainsString($expected, (string) $this->seoMeta);

        $separator = '|';
        $this->seoMeta->setTitle($title, $siteName, $separator);

        $expected = "<title>{$title} {$separator} {$siteName}</title>";

        static::assertStringContainsString($expected, $this->seoMeta->render());
        static::assertStringContainsString($expected, (string) $this->seoMeta);

        // Entity
        $titleEntity  = $this->seoMeta->getTitleEntity();
        $expectations = [
            TitleContract::class,
            Title::class,
        ];

        foreach ($expectations as $expected) {
            static::assertInstanceOf($expected, $titleEntity);
        }

        static::assertSame($title, $titleEntity->getTitleOnly());
        static::assertSame($siteName, $titleEntity->getSiteName());
        static::assertSame($separator, $titleEntity->getSeparator());
    }

    #[Test]
    public function it_can_set_and_get_and_render_description(): void
    {
        $description = 'Awesome Description';
        $this->seoMeta->setDescription($description);

        $expected = '<meta name="description" content="' . $description . '">';

        static::assertStringContainsString($expected, $this->seoMeta->render());
        static::assertStringContainsString($expected, (string) $this->seoMeta);

        // Entity
        $descriptionEntity = $this->seoMeta->getDescriptionEntity();
        $expectations      = [
            DescriptionContract::class,
            Description::class,
        ];

        foreach ($expectations as $expected) {
            static::assertInstanceOf($expected, $descriptionEntity);
        }

        static::assertSame($description, $descriptionEntity->getContent());
    }

    #[Test]
    public function it_can_set_and_get_and_render_keywords(): void
    {
        $keywords = ['keyword-1', 'keyword-2', 'keyword-3', 'keyword-4', 'keyword-5'];

        $this->seoMeta->setKeywords($keywords);

        $expected = '<meta name="keywords" content="' . implode(', ', $keywords) . '">';

        static::assertStringContainsString($expected, $this->seoMeta->render());
        static::assertStringContainsString($expected, (string) $this->seoMeta);

        $this->seoMeta->setKeywords(implode(',', $keywords));

        $expected = '<meta name="keywords" content="' . implode(', ', $keywords) . '">';

        static::assertStringContainsString($expected, $this->seoMeta->render());
        static::assertStringContainsString($expected, (string) $this->seoMeta);

        // Entity
        $this->seoMeta->setKeywords($keywords);

        $keywordsEntity = $this->seoMeta->getKeywordsEntity();
        $expecations    = [
            KeywordsContract::class,
            Keywords::class,
        ];

        foreach ($expecations as $expected) {
            static::assertInstanceOf($expected, $keywordsEntity);
        }

        static::assertSame($keywords, $keywordsEntity->getContent());
    }

    #[Test]
    public function it_can_add_one_keyword(): void
    {
        $keywords = ['keyword-1', 'keyword-2', 'keyword-3', 'keyword-4', 'keyword-5'];
        $this->seoMeta->setKeywords($keywords);

        $expected = '<meta name="keywords" content="' . implode(', ', $keywords) . '">';

        static::assertStringContainsString($expected, $this->seoMeta->render());
        static::assertStringContainsString($expected, (string) $this->seoMeta);

        $keywords[] = $keyword = 'keyword-6';
        $this->seoMeta->addKeyword($keyword);

        $expected = '<meta name="keywords" content="' . implode(', ', $keywords) . '">';

        static::assertStringContainsString($expected, $this->seoMeta->render());
        static::assertStringContainsString($expected, (string) $this->seoMeta);
    }

    #[Test]
    public function it_can_add_many_keywords(): void
    {
        $keywords = ['keyword-1', 'keyword-2', 'keyword-3', 'keyword-4', 'keyword-5'];
        $expected = '<meta name="keywords" content="' . implode(', ', $keywords) . '">';
        $this->seoMeta->setKeywords($keywords);

        static::assertStringContainsString($expected, $this->seoMeta->render());
        static::assertStringContainsString($expected, (string) $this->seoMeta);

        $new       = ['keyword-6', 'keyword-7', 'keyword-8'];
        $keywords  = array_merge($keywords, $new);
        $expected  = '<meta name="keywords" content="' . implode(', ', $keywords) . '">';

        $this->seoMeta->addKeywords($new);

        static::assertStringContainsString($expected, $this->seoMeta->render());
        static::assertStringContainsString($expected, (string) $this->seoMeta);
    }

    #[Test]
    public function it_can_add_remove_reset_and_render_a_misc_tag(): void
    {
        $expectations = [
            '<meta name="robots" content="noindex, nofollow">',
            '<link rel="canonical" href="' . $this->baseUrl . '">',
        ];

        foreach ($expectations as $expected) {
            static::assertStringContainsString($expected, $this->seoMeta->render());
            static::assertStringContainsString($expected, (string) $this->seoMeta);
        }

        $this->seoMeta->removeMeta(['robots', 'canonical']);
        $output = $this->seoMeta->render();

        foreach ($expectations as $expected) {
            static::assertStringNotContainsString($expected, $output);
            static::assertStringNotContainsString($expected, (string) $this->seoMeta);
        }

        $this->seoMeta->addMetas([
            'copyright' => 'ARCANEDEV',
            'expires'   => 'never',
        ]);

        $output = $this->seoMeta->render();

        $expectations = [
            '<meta name="copyright" content="ARCANEDEV">',
            '<meta name="expires" content="never">',
        ];

        foreach ($expectations as $expected) {
            static::assertStringContainsString($expected, $output);
        }

        $this->seoMeta->removeMeta('copyright');

        static::assertStringNotContainsString(
            '<meta name="copyright" content="ARCANEDEV">',
            $this->seoMeta->render(),
        );

        $this->seoMeta->removeMeta('expires');

        static::assertStringNotContainsString(
            '<meta name="expires" content="never">',
            $this->seoMeta->render(),
        );

        $this->seoMeta->addMeta('viewport', 'width=device-width, initial-scale=1.0');

        static::assertStringContainsString(
            '<meta name="viewport" content="width=device-width, initial-scale=1.0">',
            $this->seoMeta->render(),
        );

        $this->seoMeta->addMetas([
            'copyright' => 'ARCANEDEV',
            'expires'   => 'never',
        ]);

        $this->seoMeta->resetMetas();

        foreach (['viewport', 'copyright', 'expires'] as $blacklisted) {
            static::assertStringNotContainsString($blacklisted, $this->seoMeta->render());
        }

        // Entity
        $miscEntity   = $this->seoMeta->getMiscEntity();
        $expectations = [
            MiscTagsContract::class,
            MiscTags::class,
        ];

        foreach ($expectations as $expected) {
            static::assertInstanceOf($expected, $miscEntity);
        }

        static::assertMetaCollection($miscEntity->all());
    }

    #[Test]
    public function it_can_render_add_reset_webmasters(): void
    {
        $expectations = [
            '<meta name="google-site-verification" content="site-verification-code">',
            '<meta name="msvalidate.01" content="site-verification-code">',
            '<meta name="alexaVerifyID" content="site-verification-code">',
            '<meta name="p:domain_verify" content="site-verification-code">',
            '<meta name="yandex-verification" content="site-verification-code">',
        ];

        foreach ($expectations as $excepted) {
            static::assertStringContainsString($excepted, $this->seoMeta->render());
            static::assertStringContainsString($excepted, (string) $this->seoMeta);
        }

        $this->seoMeta->resetWebmasters();

        foreach ($expectations as $excepted) {
            static::assertStringNotContainsString($excepted, $this->seoMeta->render());
            static::assertStringNotContainsString($excepted, (string) $this->seoMeta);
        }

        $this->seoMeta->addWebmaster('google', 'site-verification-code');

        $excepted = '<meta name="google-site-verification" content="site-verification-code">';

        static::assertStringContainsString($excepted, $this->seoMeta->render());
        static::assertStringContainsString($excepted, (string) $this->seoMeta);

        // Entity
        $webmastersEntity = $this->seoMeta->getWebmastersEntity();
        $expectations     = [
            WebmastersContract::class,
            Webmasters::class,
        ];

        foreach ($expectations as $expected) {
            static::assertInstanceOf($expected, $webmastersEntity);
        }

        static::assertMetaCollection($webmastersEntity->all());
    }

    #[Test]
    public function it_can_set_and_render_google_analytics(): void
    {
        static::assertGoogleAnalytics('UA-12345678-9', $this->seoMeta->render());

        $this->seoMeta->setGoogleAnalytics('UA-98765432-1');

        static::assertGoogleAnalytics('UA-98765432-1', $this->seoMeta->render());

        // Entity
        $analyticsEntity = $this->seoMeta->getAnalyticsEntity();
        $expectations    = [
            AnalyticsContract::class,
            Analytics::class,
        ];

        foreach ($expectations as $expected) {
            static::assertInstanceOf($expected, $analyticsEntity);
        }
    }

    /* -----------------------------------------------------------------
     |  Custom Assertions
     | -----------------------------------------------------------------
     */

    /**
     * Assert the meta collection.
     */
    protected static function assertMetaCollection(mixed $metas): void
    {
        $expectations = [
            MetaCollectionContract::class,
            MetaCollection::class,
        ];

        foreach ($expectations as $expected) {
            static::assertInstanceOf($expected, $metas);
        }
    }
}
