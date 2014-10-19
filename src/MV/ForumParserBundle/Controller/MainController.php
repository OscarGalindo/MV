<?php

namespace MV\ForumParserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Goutte\Client;

class MainController extends Controller
{
    /**
     * @var string
     */
    private $_url = 'http://m.mediavida.com/foro/';

    /**
     * @var Client
     */
    private $client;

    public function __construct(){
        $this->client = new Client();
    }

    /**
     * Devuelve la lista de foros de MV
     *
     * @return JsonResponse
     */
    public function getForumsAction()
    {
        $mv = array();

        $data = $this->_getHtml();
        $data->filter('li')->each(function(Crawler $headers, $i) use (&$countForums, &$title, &$mv) {
                if($headers->attr('data-role')) {
                    $title = $headers->text();
                    $mv[$title] = '';
                    $countForums = 0;
                } else {
                    $mv[$title][$countForums]['title'] = trim($headers->text());
                    $mv[$title][$countForums]['url'] = $headers->filter('a')->first()->link()->getUri();
                    $countForums++;
                }
            });

        $json = new JsonResponse($mv);
        $json->setEncodingOptions(128);
        return $json;
    }

    /**
     * Devuelve los topics del foro seleccionado
     *
     * @param $slug_forum string
     * @return JsonResponse
     */
    public function getTopicsAction($slug_forum, $page)
    {
        $posts = array();
        $page = 'p' . $page;

        $data = $this->_getHtml($slug_forum, $page);
        $data->filter('li')->each(function(Crawler $post, $i) use (&$posts) {
                // $href = array_filter(explode('/', $post->filter('a')->attr('href')));
                $resp = $post->filter('a span')->text();

                $posts[$i]['topic'] = trim($post->filter('a')->attr('title'));
                $posts[$i]['href']  = $post->filter('a')->attr('href');
                $posts[$i]['resp']  = $resp;
                $posts[$i]['pages'] = (($resp - $resp % 30) / 30) + 1;
            });

        $json = new JsonResponse($posts);
        $json->setEncodingOptions(128);
        return $json;
    }

    public function getPostsAction($slug_forum, $slug_post, $page)
    {
        $posts = array();
        $url = $slug_forum . '/' . $slug_post;
        $data = $this->_getHtml($url, $page);

        var_dump($data->html());

        $json = new JsonResponse($posts);
        $json->setEncodingOptions(128);
        return $json;
    }

    /**
     * Devuelve el crawler contenedor de la parte de foros de MV
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    private function _getHtml($url = '', $page = '')
    {
        $url = $this->_url . $url . '/' . $page;
        echo $url;
        $crawler = $this->client->request('GET', $url);
        return $crawler->filter('ul[data-role="listview"]');
    }
}
