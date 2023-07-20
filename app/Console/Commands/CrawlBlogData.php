<?php

namespace App\Console\Commands;

use App\Models\Blog_data;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Weidner\Goutte\GoutteFacade;

class CrawlBlogData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:crawl';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function handle()
    {
        // $crawler = GoutteFacade::request('GET', 'https://langngheviet.com.vn/ocop');
        // $linkPost = $crawler->filter('h3.article-title a')->each(function ($node) {
        //     return $node->attr("href");
        // });
        // $filterDiv = $crawler->filter('div.__MB_ARTICLE_PAGING');
        // $filterLinks = $filterDiv->filter('a');

        // $nextLink = $crawler->filter('div.__MB_ARTICLE_PAGING a')->eq(1);
        // $previousLink = $filterLinks->eq(0)->attr('href');
        // if ($nextLink->count() >0 ) {
        //     $nextLink = $filterLinks->eq(1)->attr('href');
        //     dd($nextLink);
        // }
        // dd($linkPost);
        $pageUrl = 'https://langngheviet.com.vn/ocop';
        do {
            $crawler = GoutteFacade::request('GET', $pageUrl);
            $linkPost = $crawler->filter('h3.article-title a');
            $linkPost->each(function ($node) {
                $link = $node->attr('href');
                $this->scrapeData($link);
            });
            $nextLink = $crawler->filter('div.__MB_ARTICLE_PAGING a:contains("Sau")')->first();

            if ($nextLink->count() > 0) {
                $nextPageUrl = $nextLink->attr('href');
            } else {
                echo "Next link not found.";
            }
            // Update the pageUrl for the next iteration
            $pageUrl = $nextPageUrl;
        } while ($nextPageUrl !== '');
    }
    public function scrapeData($url)
    {
        $crawler = GoutteFacade::request('GET', $url);

        $title = $this->crawlData('h1.article-detail-title', $crawler);

        $content = $this->crawlData('div.__MASTERCMS_CONTENT', $crawler);

        $check = Blog_data::all();
        if (sizeof($check) <= 0) {
            $dataPost = [
                'title' => $title,
                'content' => $content,
                'source' => '',
                'SimilarityPercentage' => 0.0
            ];
            // echo "Similarity Percentage: " . round($similarityPercentage, 2) . "%";
            Blog_data::create($dataPost);
        } else {
            $check_tile = false;
            $similarityPercentage = 0.0;
            foreach ($check as  $blog) {
                if ($blog->title != $title) {
                    $blog1Words = explode(' ', $blog->content);
                    $blog2Words = explode(' ', $content);
                    $commonWords = array_intersect($blog1Words, $blog2Words);
                    $similarityPercentage += count($commonWords) / count($blog1Words);
                } else {
                    $check_tile = true;
                }
            }
            if ($check_tile == false && $title != null) {
                $similarityPercentage = $similarityPercentage / sizeof($check);
                $dataPost = [
                    'title' => $title,
                    'content' => $content,
                    'source' => '',
                    'SimilarityPercentage' => round($similarityPercentage, 2)
                ];
                Blog_data::create($dataPost);
            }
        }
    }

    protected function crawlData(string $type, $crawler)
    {
        $result = $crawler->filter($type)->each(function ($node) {
            return $node->html();
        });

        if (!empty($result)) {
            return $result[0];
        }

        return '';
    }
}
