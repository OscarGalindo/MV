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

    public function indexAction()
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
        // $json->setEncodingOptions(128);
        return $json;
    }

    public function forumAction($slug_forum)
    {
        $data = $this->_getHtml();
        return Response::create($data);
    }


    /**
     * Devuelve la URL de MediaVida configurada en el config.yml del bundle
     *
     * @return string
     */
    private function _getMvUrl()
    {
        return $this->container->getParameter('mv.forum_url');
    }

    /**
     * Devuelve el crawler contenedor de la parte de foros de MV
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    private function _getHtml($slug = '')
    {
        $this->_url = $this->_getMvUrl();
        $crawler = $this->client->request('GET', $this->_url . $slug);
        return $crawler->filter('ul.foros');
    }
}
