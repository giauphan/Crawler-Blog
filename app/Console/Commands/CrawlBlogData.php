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
        $pageUrl = 'https://ekeinterior.com/blog-noi-that';
        do {
            $crawler = GoutteFacade::request('GET', $pageUrl);
            // crawl
            $linkPost = $crawler->filter('div.boxed');
            $linkPost->each(function ($node) {
                          // summary 
                $summary  = ($node->filter('div.content p')->text());
                // image blog 
                $imagee = $node->filter('.blog-grid.grid-2 img');
                if ($imagee->count() > 0) {
                    $iamgeblog = $imagee->attr('data-lazy-src');
                }
                 // href blog
                 $links = $node->filter('div.content a');
                $linkHref = $links->attr('href');
                $this->scrapeData($linkHref, $iamgeblog,$summary);
            });
        
            $nextLink = $crawler->filter('nav.pagination li a.next')->first();
            if ($nextLink->count() > 0) {
                $nextPageUrl = $nextLink->attr('href');
            } else {
                // No "Next" link found, exit the loop
                break;
            }
            // Update the pageUrl for the next iteration
            $pageUrl = $nextPageUrl;
       
        } while ($pageUrl !== '');
    
    }
    public function scrapeData($url, $image,   $summary)
    {
   
        $crawler = GoutteFacade::request('GET', $url);
        $title = $this->crawlData('h1.page-title', $crawler);
        $content = $this->crawlData('#main', $crawler);
        $check = Blog_data::all();
        if (sizeof($check) <= 0) {
            $dataPost = [
                'tieuDe' => $title,
                'noiDung' => $content,
                "urlHinh" =>  $image,
                "ngayDang" => now(),
                'tomTat' => $summary,
                "idLT" => 3,
                'SimilarityPercentage' => 0.0
            ];
            // echo "Similarity Percentage: " . round($similarityPercentage, 2) . "%";
            Blog_data::create($dataPost);
        } else {
            $check_tile = false;
            $similarityPercentage = 0.0;
       
            foreach ($check as  $blog) {
                if ($blog->tieuDe !== $title) {
                    $blog1Words = explode(' ', $blog->noiDung);
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
                    'tieuDe' => $title,
                    'noiDung' => $content,
                    "urlHinh" => $image,
                    "ngayDang" => now(),
                    'tomTat' => $summary,
                    "idLT" => 3,
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
