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
    private $_url;

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

        $data = $this->_getGlobalHtml($slug_forum, $page);
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

    public function getPostsAction($cat, $slug_post, $page)
    {
        $posts = array();



        $json = new JsonResponse($posts);
        $json->setEncodingOptions(128);
        return $json;
    }
    /**
     * Devuelve la URL de MediaVida configurada en el config.yml del bundle
     *
     * @return string
     */
    private function _getMvUrl($foro = '')
    {
        return $this->container->getParameter('mv.forum_url') . $foro;
    }

    /**
     * Devuelve el crawler contenedor de la parte de foros de MV
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    private function _getGlobalHtml($slug = '', $page = '')
    {
        $this->_url = $this->_getMvUrl('/foro/');
        $crawler = $this->client->request('GET', $this->_url . $slug . '/' . $page);
        return $crawler->filter('ul[data-role="listview"]');
    }
}
