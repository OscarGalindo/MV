<?php

namespace MV\ForumParserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Goutte\Client;

class PostsController extends Controller
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

    public function getPostsAction($slug_forum, $slug_post, $page)
    {
        $posts = array();
        $url = $slug_forum . '/' . $slug_post;
        $data = $this->_getHtml($url);

        $data->filter('div.post')->each(function(Crawler $msg, $i) use (&$posts) {
                $posts[$i]['id_post'] = trim($msg->filter('span.qn')->html());
                $posts[$i]['img'] = $msg->filter('.ui-block-a img')->attr('src');
                $count_links = $msg->filter('div.autor a')->count();
                $posts[$i]['autor'] = ($count_links) ? $msg->filter('div.autor a')->text() : $msg->filter('div.autor span')->first()->text();
                $posts[$i]['date'] = $msg->filter('div.autor > span')->last()->text();
                $posts[$i]['likes'] = trim($msg->filter('span.mola')->text());
                $posts[$i]['msg'] = trim($msg->filter('div.cuerpo')->html());
            });

        return new JsonResponse($posts);
    }


    /**
     * Devuelve el crawler contenedor de la parte de foros de MV
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    private function _getHtml($url = '', $page = 1)
    {
        $url = $this->_url . $url . '/' . $page;
        $crawler = $this->client->request('GET', $url);
        return $crawler->filter('div[data-role="content"]');
    }
}
