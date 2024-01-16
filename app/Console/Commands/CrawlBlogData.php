<?php

namespace App\Console\Commands;

use App\Models\CategoryBlog;
use App\Models\Post;
use Illuminate\Console\Command;
use Weidner\Goutte\GoutteFacade;
use Illuminate\Support\Str;
use League\CommonMark\CommonMarkConverter;

class CrawlBlogData extends Command
{
    protected $signature = 'app:crawl {url} {category_name}';
    protected $description = 'Crawl blog data from a given URL';

    public function handle()
    {
        $pageUrl = $this->argument('url');
        $categoryName = $this->argument('category_name');

        // Check if the category exists, or create it
        $category = CategoryBlog::firstOrCreate(['name' => $categoryName], ['slug' => Str::slug($categoryName)]);
        $categoryId = $category->id;
        do {
            $crawler = GoutteFacade::request('GET', $pageUrl);

            $crawler->filter('.blog-post-masonry')->each(function ($node) use($categoryId) {
                $summary = $node->filter('.content h3')->text();
                $image = optional($node->filter('.blog-post-masonry header img')->first())->attr('data-lazy-src');
                $linkHref = $node->filter('.blog-post-masonry header a')->attr('href');

                $this->scrapeData($linkHref, $image, $summary,$categoryId);
            });
            $nextLink = $crawler->filter('nav.pagination li a.next')->first();
            if (!$nextLink || !$nextLink->count()) {
                break;
            }
            $nextPageUrl = $nextLink->attr('href');
            $pageUrl = $nextPageUrl;
        } while ($pageUrl !== '');
    }

    public function scrapeData($url, $image, $summary,$categoryId)
    {
        $crawler = GoutteFacade::request('GET', $url);
        $title = $this->crawlData('.wrap-container h1', $crawler);
        $content = $this->crawlData_html('#main .post', $crawler);
        $check = Post::all();
        // Convert HTML to Markdown
        $converter = new CommonMarkConverter();
        $markdownContent = $converter->convert($content);
       
        if ($check->isEmpty()) {
            $this->createPost($title, $image, $summary, $markdownContent,$categoryId);
        } else {
            $this->checkAndUpdatePost($title, $content, $image, $summary, $markdownContent, $check,$categoryId);
        }
    }
    protected function createPost($title, $image, $summary, $markdownContent,$categoryId)
    {
        $cleanedTitle = Str::slug($title, '-');
        $slug = preg_replace('/[^A-Za-z0-9\-]/', '', $cleanedTitle);
        $dataPost = [
            'title' => $title,
            'slug' => $slug,
            'content' => $markdownContent,
            'images' => $image,
            'published_at' => now(),
            'summary' => $summary,
            'category_blog_id' => $categoryId,
            'SimilarityPercentage' => 0.0,
        ];
        Post::create($dataPost);
    }

    protected function checkAndUpdatePost($title, $content, $image, $summary, $markdownContent, $check,$categoryId)
    {
        $checkTile = false;
        $similarityPercentage = 0.0;

        foreach ($check as $blog) {
            if ($blog->title !== $title) {
                $blog1Words = explode(' ', $blog->content);
                $blog2Words = explode(' ', $content);
                $commonWords = array_intersect($blog1Words, $blog2Words);
                $similarityPercentage += count($commonWords) / count($blog1Words);
            } else {
                $checkTile = true;
            }
        }

        if (!$checkTile && $title != null) {
            $similarityPercentage = $similarityPercentage / $check->count();
            $cleanedTitle = Str::slug($title, '-');
            $slug = preg_replace('/[^A-Za-z0-9\-]/', '', $cleanedTitle) . '.html';
            $dataPost = [
                'title' => $title,
                'slug' => $slug,
                'content' =>  $markdownContent,
                'images' => $image,
                'published_at' => now(),
                'summary' => $summary,
                'category_blog_id' =>$categoryId,
                'SimilarityPercentage' => round($similarityPercentage, 2),
            ];
            Post::create($dataPost);
        }
    }

    protected function crawlData(string $type, $crawler)
    {
        $result = $crawler->filter($type)->first();

        return $result ? $result->text() : '';
    }

    protected function crawlData_html(string $type, $crawler)
    {
        $result = $crawler->filter($type)->first();

        return $result ? $result->html() : '';
    }
}
