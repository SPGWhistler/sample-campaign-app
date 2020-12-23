<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class SlidesController extends Controller
{
    public function getCampaigns() {
        $key = 'campaigns';
        $campaigns = Cache::get($key);
        if ($campaigns === NULL) {
            $campaigns = $this->_getCampaigns();
            Cache::put($key, $campaigns, 300);
        }
        return $campaigns;
    }

    /**
     * Show the profile for a given user.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function getSlides($id)
    {
        $key = 'slides1' . $id;
        $slides = Cache::get($key);
        if ($slides === NULL) {
            $slides = $this->_getSlides($id);
            Cache::put($key, $slides, 300);
        }
        return $slides;
    }

    private function _getCampaigns() {
        $campaigns = $this->_getData("https://api.digitalmedia.hhs.gov/api/v2/resources/campaigns.json?sort=-startDate");
        return $campaigns;
    }

    private function _getSlides($id) {
        $slides = [];
        $media = $this->_getData("https://api.digitalmedia.hhs.gov/api/v2/resources/campaigns/{$id}/media.json");
        foreach ($media['results'] as $mediaItem) {
            $slide = [];
            $slide['type'] = $mediaItem['mediaType'];
            $slide['name'] = $mediaItem['name'];
            $slide['desc'] = $mediaItem['description'];
            $slide['url'] = $mediaItem['sourceUrl'];
            $slide['previewUrl'] = $mediaItem['previewUrl'];
            $siteImage = $this->_getSiteImage($mediaItem['sourceUrl']);
            if ($siteImage !== false) {
                $slide['image'] = $siteImage;
            }
            $slides[] = $slide;
        }
        return $slides;
    }

    private function _getSiteImage($url, $atYT = false) {
        $html = $this->_getData($url, false);
        preg_match('/\<meta.*?property\s?=\s?(?:\"|\')og:image(?:\"|\').*?content\s?=\s?(?:\"|\')(.*?)(?:\"|\').*?\>/', $html, $matches);
        if ($matches && $matches[1]) {
            if (stripos($matches[1], 'youtube.com') !== false && !$atYT) {
                return $this->_getSiteImage($matches[1], true);
            }
            return $matches[1];
        }
        return false;
    }

    private function _getData($url, $outJson = true) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 20);
        $output = curl_exec($ch);
        curl_close($ch);
        return ($outJson) ? json_decode($output, true) : $output;
    }

}
