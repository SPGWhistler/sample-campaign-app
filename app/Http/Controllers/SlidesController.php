<?php

/**
 * A simple slides controller which does multiple things.
 * For simplicities sake, I've combined the server-to-server
 * code, processing code, and caching code into this one controller.
 * 
 * Note:
 * All of these methods are written in a very naive way and
 * assume that everything is working perfectly. I've not written
 * and error handling, input sanitization, or non-happy-path code.
 * As in, DO NOT use this code in a real production environment.
 */

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class SlidesController extends Controller
{
    /**
     * Get the available campaigns.
     * This method is not currently used in the UI, but
     * a future update could allow for selecting the campaign
     * to show by using this method.
     * @return String The JSON of the campaigns endpoint.
     */
    public function getCampaigns() {
        $key = 'campaigns';
        $campaigns = Cache::get($key); //Currently this app uses the file cache on the server.
        if ($campaigns === NULL) {
            $campaigns = $this->_getCampaigns();
            Cache::put($key, $campaigns, 300);
        }
        return $campaigns;
    }

    /**
     * Get the slides for the specified campaign.
     * This method takes a campaign id, and will generate some
     * JSON that can be used to display slides for it.
     * All results from this method are cached for 5 minutes.
     * If the id is invalid or can not be found or if it returns
     * no slides with valid media, an empty array is returned.
     *
     * @param String $id The campaign id.
     * @return String The slides JSON.
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

    /**
     * Get all campaigns.
     * This queries the api for all campaigns and returns the result.
     * @return String The json returned from the api.
     */
    private function _getCampaigns() {
        $campaigns = $this->_getData("https://api.digitalmedia.hhs.gov/api/v2/resources/campaigns.json?sort=-startDate");
        return $campaigns;
    }

    /**
     * Get the slides.
     * This queries the api for slides for the given campaign id.
     * It then processes the results a little, and then finds a
     * suitable preview image by loading the source url and looking
     * for og:image meta tags in the source. If the og:image tag points
     * to youtube.com, it will recurse one time to find the preview
     * image from youtube instead.
     * @param String $id The campagin id.
     * @return String Json of the slide objects.
     */
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

    /**
     * Get the preview image for a given url.
     * This takes a url, curl's it to get the source,
     * then uses a simple regex to find the og:image meta
     * tag, if there is one. If the tag points to youtube.com,
     * it will go to youtube to find the preview image.
     * In that case, $atYT is true, and this method will not
     * recurse again (for cases that youtube's og:image points
     * back to youtube.com).
     * @param String $url The url to go to.
     * @param Boolean $atYT If we are going to youtube.
     * @return String|Boolean The preview image url or false if one can't be found.
     */
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

    /**
     * Curl a url and return the result.
     * If $outJson is true, the curl output will first be json_decoded
     * into an array and the array will be returned.
     * @param String $url The url to curl.
     * @param Boolean $outJson If the output should be json decoded into an array.
     * @return Array|String The json as an array, or the raw output from curl.
     */
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
